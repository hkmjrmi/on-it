<?php

namespace Database\Seeders;

use App\Enums\IssueActivityAction;
use App\Enums\IssuePriority;
use App\Enums\IssueProgress;
use App\Enums\IssueSource;
use App\Enums\IssueStatus;
use App\Enums\IssueSystem;
use App\Enums\IssueType;
use App\Enums\UserRole;
use App\Models\Issue;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class IssueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = $this->seedUsers();

        collect($this->issues())
            ->sortByDesc('reported')
            ->each(fn (array $data) => $this->seedIssue($data, $users));
    }

    /**
     * Seed the team members who report and work on issues.
     *
     * @return \Illuminate\Support\Collection<string, \App\Models\User>
     */
    protected function seedUsers(): Collection
    {
        $team = [
            'ummi' => ['Ummi', UserRole::CustomerSupport],
            'zaim' => ['Zaim', UserRole::Programmer],
            'haiqal' => ['Haiqal', UserRole::Programmer],
            'hakim' => ['Hakim', UserRole::Programmer],
            'haq' => ['Haq', UserRole::Programmer],
            'ammar' => ['Ammar', UserRole::Programmer],
            'suhailah' => ['Suhailah', UserRole::Programmer],
        ];

        return collect($team)
            ->map(fn (array $member, string $handle) => User::firstOrCreate([
                'email' => $handle . '@test.com',
            ], [
                'name' => $member[0],
                'email_verified_at' => now(),
                'password' => 'abcd1234',
                'role' => $member[1],
            ]))
            ->put('support', User::where('email', 'user@test.com')->firstOrFail());
    }

    /**
     * Seed an issue together with its workflow history and comments.
     *
     * @param  \Illuminate\Support\Collection<string, \App\Models\User>  $users
     */
    protected function seedIssue(array $data, Collection $users): void
    {
        $reporter = $users[$data['reporter']];
        $assignee = isset($data['assignee']) ? $users[$data['assignee']] : null;
        $reportedAt = $this->daysAgo($data['reported']);
        $workflow = $data['workflow'] ?? [];
        $progress = collect($workflow)->last(fn (array $step) => $step[0] instanceof IssueProgress)[0] ?? IssueProgress::New;
        $status = collect($workflow)->last(fn (array $step) => $step[0] instanceof IssueStatus)[0] ?? IssueStatus::Open;
        $releasedAt = collect($workflow)->first(fn (array $step) => $step[0] === IssueProgress::Released);

        $issue = Issue::forceCreate([
            'title' => $data['title'],
            'description' => $data['description'],
            'type' => $data['type'],
            'system' => $data['system'],
            'priority' => $data['priority'],
            'progress' => $progress,
            'status' => $status,
            'source' => IssueSource::fromRole($reporter->role),
            'merchant_name' => $data['merchant'] ?? null,
            'created_by' => $reporter->id,
            'assigned_to' => $assignee?->id,
            'released_at' => $releasedAt ? $this->daysAgo($releasedAt[1]) : null,
            'created_at' => $reportedAt,
            'updated_at' => $reportedAt,
        ]);

        $events = collect([[$reportedAt, $reporter, IssueActivityAction::Created, null, null]]);

        if ($assignee) {
            $assignedAt = $this->daysAgo($data['assigned'] ?? $workflow[0][1] ?? $data['reported']);
            $events->push([$assignedAt, $assignee, IssueActivityAction::Assigned, null, $assignee->id]);
        }

        if (isset($data['escalated'])) {
            [$from, $daysAgo, $by] = $data['escalated'];
            $events->push([$this->daysAgo($daysAgo), $users[$by], IssueActivityAction::PriorityChanged, $from->value, $data['priority']->value]);
        }

        $previous = [IssueProgress::class => IssueProgress::New, IssueStatus::class => IssueStatus::Open];

        foreach ($workflow as $step) {
            $by = isset($step[2]) ? $users[$step[2]] : $assignee;
            $action = $step[0] instanceof IssueStatus ? IssueActivityAction::StatusChanged : IssueActivityAction::ProgressChanged;
            $events->push([$this->daysAgo($step[1]), $by, $action, $previous[$step[0]::class]->value, $step[0]->value]);
            $previous[$step[0]::class] = $step[0];
        }

        foreach ($data['comments'] ?? [] as [$handle, $daysAgo, $body]) {
            $commentedAt = $this->daysAgo($daysAgo);

            $issue->comments()->forceCreate([
                'user_id' => $users[$handle]->id,
                'body' => $body,
                'created_at' => $commentedAt,
                'updated_at' => $commentedAt,
            ]);

            $events->push([$commentedAt, $users[$handle], IssueActivityAction::Commented, null, null]);
        }

        $events->sortBy(fn (array $event) => $event[0]->getTimestamp())
            ->each(fn (array $event) => $issue->activities()->forceCreate([
                'user_id' => $event[1]->id,
                'action' => $event[2],
                'from_value' => $event[3],
                'to_value' => $event[4],
                'created_at' => $event[0],
                'updated_at' => $event[0],
            ]));

        $issue->timestamps = false;
        $issue->forceFill(['updated_at' => $events->max(fn (array $event) => $event[0])])->saveQuietly();
    }

    /**
     * Get the timestamp for the given number of days ago.
     */
    protected function daysAgo(float $days): Carbon
    {
        return now()->subMinutes((int) round($days * 1440));
    }

    /**
     * Get the sample issues, based on the OnPay-Next system testing checklist.
     *
     * Each workflow step is [progress or status, days ago, optional user handle (defaults to the assignee)].
     * Each comment is [user handle, days ago, body].
     *
     * @return array<int, array<string, mixed>>
     */
    protected function issues(): array
    {
        return [
            [
                'title' => 'Kemaskini pembayaran Billplz ke CHIP tidak dikemas kini pada rekod jualan',
                'description' => "Bila pembayaran jualan ditukar dari Billplz ke CHIP, status pada rekod jualan tidak berubah. Walaupun pembayaran CHIP dah berjaya, sistem tak sahkan jualan sebab kaedah pembayaran masih dikira sebagai \"Billplz\".\n\nSelain itu, status pembayaran sepatutnya \"Menunggu\" dan Log perlu dipaparkan. Tarikh dan masa pembayaran juga tidak tepat.\n\nLokasi: Admin > Senarai Jualan > Status Pembayaran",
                'type' => IssueType::Bug,
                'system' => IssueSystem::OnPay,
                'priority' => IssuePriority::Urgent,
                'reporter' => 'hakim',
                'assignee' => 'zaim',
                'reported' => 22,
                'workflow' => [[IssueProgress::InDevelopment, 21.8]],
                'comments' => [
                    ['zaim', 20, 'Dah jumpa punca. Callback CHIP masih rujuk gateway asal pada rekod jualan, jadi verification skip. Tengah betulkan mapping gateway semasa pembayaran dikemas kini.'],
                    ['zaim', 6, 'Fix untuk status dah siap, tinggal pembetulan timezone pada tarikh & masa pembayaran. Akan push untuk review minggu ni.'],
                ],
            ],
            [
                'title' => 'Fungsi carian di Senarai Langganan tidak berfungsi',
                'description' => "Di akaun Admin, carian pada Senarai > Langganan tidak memaparkan sebarang hasil walaupun nama pengguna atau emel yang dicari memang wujud.\n\nLangkah:\n1. Log masuk akaun Admin\n2. Pergi ke Senarai > Langganan\n3. Taip nama pengguna di ruangan carian\n\nJangkaan: Senarai ditapis ikut carian\nSebenar: Senarai kosong",
                'type' => IssueType::Bug,
                'system' => IssueSystem::OnPay,
                'priority' => IssuePriority::Medium,
                'reporter' => 'ummi',
                'reported' => 22,
            ],
            [
                'title' => 'Penerangan diwajibkan ketika kemaskini Borang Prospek',
                'description' => "Ketika ingin kemaskini Borang Prospek, ruangan Penerangan diwajibkan untuk diisi. Di OnPay lama, Penerangan tidak diwajibkan.\n\nMerchant terpaksa isi penerangan yang tak diperlukan setiap kali nak simpan borang.",
                'type' => IssueType::Complaint,
                'system' => IssueSystem::OnPay,
                'priority' => IssuePriority::Medium,
                'merchant' => 'Dapur Bonda Catering',
                'reporter' => 'support',
                'assignee' => 'haq',
                'reported' => 22,
                'workflow' => [
                    [IssueProgress::InDevelopment, 19],
                    [IssueProgress::InReview, 1.2],
                ],
                'comments' => [
                    ['haq', 18.9, 'Validation untuk description ada dalam shared form request. Saya asingkan rule untuk Borang Prospek supaya jadi optional semula.'],
                    ['haq', 1.2, 'PR dah up. Boleh test kemaskini borang prospek tanpa penerangan di staging.'],
                ],
            ],
            [
                'title' => 'Modul Auto Follow Up kembali tidak aktif selepas simpan',
                'description' => "Selepas aktifkan Auto Follow Up dan simpan/kemaskini borang, tetapan Auto Follow Up kembali kepada tidak diaktifkan.\n\nLangkah:\n1. Buka borang jualan > Tab Auto Follow Up\n2. Aktifkan dan isi template\n3. Klik Simpan\n4. Refresh halaman\n\nSebenar: Auto Follow Up kembali tidak aktif",
                'type' => IssueType::Bug,
                'system' => IssueSystem::OnPay,
                'priority' => IssuePriority::High,
                'merchant' => 'Mira Skincare Enterprise',
                'reporter' => 'ummi',
                'assignee' => 'haiqal',
                'reported' => 21,
                'workflow' => [
                    [IssueProgress::InDevelopment, 20],
                    [IssueProgress::InReview, 9],
                    [IssueProgress::ReadyToRelease, 6],
                    [IssueProgress::Released, 2],
                ],
                'comments' => [
                    ['haiqal', 19.8, 'Checkbox tak dihantar bila unchecked dan field ni tak ada dalam fillable. Tengah betulkan.'],
                    ['haiqal', 2, 'Dah live. Ummi boleh minta merchant cuba aktifkan semula Auto Follow Up.'],
                    ['ummi', 1.5, 'Merchant dah confirm okay. Terima kasih!'],
                ],
            ],
            [
                'title' => 'Hebahan Emel tiada fungsi masukkan gambar dari Image List',
                'description' => "Ketika membina kandungan Hebahan Emel, tiada pilihan untuk masukkan gambar dari Image List. Merchant terpaksa upload semula gambar yang sama setiap kali buat hebahan.\n\nDi OnPay lama, gambar boleh dipilih terus dari Image List.",
                'type' => IssueType::Complaint,
                'system' => IssueSystem::OnPay,
                'priority' => IssuePriority::Medium,
                'reporter' => 'ummi',
                'assignee' => 'ammar',
                'reported' => 21,
                'workflow' => [
                    [IssueProgress::InDevelopment, 18],
                    [IssueProgress::InReview, 12],
                    [IssueProgress::ReadyToRelease, 9],
                    [IssueProgress::Released, 5],
                    [IssueStatus::Closed, 4, 'ummi'],
                ],
                'comments' => [
                    ['ammar', 12, 'Butang Image List dah ditambah pada editor hebahan emel.'],
                ],
            ],
            [
                'title' => 'Pilihan "Hanya benarkan satu produk" tidak berfungsi',
                'description' => "Pilihan Produk \"Hanya benarkan satu produk\" dan \"Paparkan kuantiti\" tidak berfungsi. Pelanggan masih boleh membuat pembelian lebih dari satu produk dalam satu borang.\n\nBorang yang terlibat menggunakan stok terhad, jadi amaran kuantiti juga tidak dipaparkan.",
                'type' => IssueType::Bug,
                'system' => IssueSystem::OnPay,
                'priority' => IssuePriority::High,
                'merchant' => 'Kedai Tudung Aisyah',
                'reporter' => 'support',
                'assignee' => 'hakim',
                'reported' => 16,
                'workflow' => [
                    [IssueProgress::InDevelopment, 15.5],
                    [IssueProgress::InReview, 8],
                    [IssueProgress::ReadyToRelease, 4],
                    [IssueProgress::Released, 1],
                ],
                'comments' => [
                    ['hakim', 15, 'Setting disimpan tapi tak digunakan pada validation checkout. Saya tambah check di frontend dan backend.'],
                    ['support', 0.8, 'Dah test dengan merchant, sekarang hanya satu produk boleh dipilih. Terima kasih.'],
                ],
            ],
            [
                'title' => 'Butang WhatsApp tiada di sebelah nombor telefon di Tab Jualan',
                'description' => "Di OnPay lama, ada butang WhatsApp di sebelah nombor telefon pelanggan dalam Tab Jualan. Merchant guna butang ini untuk follow up pelanggan dengan cepat.\n\nDi OnPay-Next butang ini tiada.",
                'type' => IssueType::Complaint,
                'system' => IssueSystem::OnPay,
                'priority' => IssuePriority::Low,
                'merchant' => 'Kopi Kampung Enterprise',
                'reporter' => 'ummi',
                'assignee' => 'haiqal',
                'reported' => 16,
                'workflow' => [
                    [IssueProgress::InDevelopment, 12],
                    [IssueProgress::InReview, 5],
                    [IssueProgress::ReadyToRelease, 3],
                ],
            ],
            [
                'title' => 'Sahkan komisen ejen secara automatik apabila jualan disahkan tidak berfungsi',
                'description' => "Tetapan \"Sahkan komisen ejen secara automatik apabila jualan disahkan\" telah diaktifkan, tetapi komisen ejen masih berstatus belum disahkan selepas jualan disahkan.\n\nAdmin terpaksa sahkan komisen satu per satu secara manual.",
                'type' => IssueType::Bug,
                'system' => IssueSystem::OnPay,
                'priority' => IssuePriority::High,
                'reporter' => 'support',
                'assignee' => 'suhailah',
                'reported' => 16,
                'workflow' => [
                    [IssueProgress::InDevelopment, 14],
                    [IssueProgress::InReview, 6],
                    [IssueProgress::ReadyToRelease, 2],
                ],
                'comments' => [
                    ['suhailah', 13.8, 'Listener untuk event jualan disahkan tak register untuk komisen. Akan fix.'],
                    ['suhailah', 2, 'Approved. Akan masuk dalam release seterusnya.'],
                ],
            ],
            [
                'title' => 'Tapisan Laporan Jualan ikut kod produk masih papar produk lain',
                'description' => "Bila tapis Laporan Jualan mengikut Produk (Kod), laporan masih memaparkan produk yang tidak ditapis.\n\nMerchant perlukan laporan ini untuk tutup akaun bulanan, jadi agak mendesak.\n\nLangkah:\n1. Analisis > Laporan Jualan\n2. Tapis Produk (Kod), pilih satu kod produk\n3. Jana laporan",
                'type' => IssueType::Bug,
                'system' => IssueSystem::OnPay,
                'priority' => IssuePriority::Urgent,
                'escalated' => [IssuePriority::High, 13, 'hakim'],
                'merchant' => 'Owala Malaysia',
                'reporter' => 'ummi',
                'assignee' => 'ammar',
                'reported' => 14,
                'workflow' => [[IssueProgress::InDevelopment, 13]],
                'comments' => [
                    ['ummi', 13.5, 'Merchant follow up lagi pagi ni, mereka nak hantar laporan kepada akauntan minggu ni.'],
                    ['hakim', 13, 'Naikkan ke Urgent sebab melibatkan laporan kewangan merchant.'],
                    ['ammar', 3, 'Query tapisan join ke item jualan, bukan produk. Fix dah siap, tengah test dengan data besar.'],
                ],
            ],
            [
                'title' => 'Tambah fungsi tapisan mengikut Pakej di Senarai Pengguna',
                'description' => 'Di akaun Admin, Senarai Pengguna tiada tapisan mengikut Pakej langganan (Basic, Lite, Premium dan lain-lain). Fungsi ini memudahkan team support semak pengguna mengikut pakej.',
                'type' => IssueType::Suggestion,
                'system' => IssueSystem::OnPay,
                'priority' => IssuePriority::Low,
                'reporter' => 'haq',
                'reported' => 14,
            ],
            [
                'title' => 'Tiada label untuk setiap ikon',
                'description' => "Ikon-ikon pada menu dan butang tindakan tiada label atau tooltip. Pengguna baru keliru fungsi setiap ikon.\n\nCadangan: Tambah label atau tooltip pada setiap ikon.",
                'type' => IssueType::Suggestion,
                'system' => IssueSystem::OnPay,
                'priority' => IssuePriority::Low,
                'reporter' => 'support',
                'assignee' => 'haiqal',
                'reported' => 13,
                'workflow' => [[IssueProgress::InDevelopment, 7]],
            ],
            [
                'title' => 'Pautan pendek hanya ada pilihan ab.opy.la',
                'description' => 'Di OnPay-Next, pautan pendek hanya ada pilihan ab.opy.la sedangkan OnPay lama ada yup.la dan ezy.la.',
                'type' => IssueType::Complaint,
                'system' => IssueSystem::OnPay,
                'priority' => IssuePriority::Low,
                'reporter' => 'ummi',
                'assignee' => 'hakim',
                'reported' => 12,
                'workflow' => [[IssueStatus::WontFix, 11]],
                'comments' => [
                    ['hakim', 11, 'Bukan isu sistem. Domain yup.la dan ezy.la belum ditambah dalam config environment testing. Di production semua domain akan tersedia.'],
                ],
            ],
            [
                'title' => 'Pilihan "Paparkan bantuan dalam bentuk pop-up" tidak mesra mudah alih',
                'description' => "Bila pilihan \"Paparkan bantuan dalam bentuk pop-up\" diaktifkan, pop-up terpotong dan tak boleh ditutup pada paparan Android.\n\nPeranti: Samsung A54, Chrome",
                'type' => IssueType::Bug,
                'system' => IssueSystem::OnPay,
                'priority' => IssuePriority::Medium,
                'merchant' => 'Butik Nur Ayu',
                'reporter' => 'support',
                'reported' => 10,
            ],
            [
                'title' => 'Fungsi Jana Kata Laluan Rawak tiada di OnPay-Next',
                'description' => 'Di akaun Admin > Kemaskini butiran pengguna, tiada butang "Jana Kata Laluan Rawak" seperti di OnPay lama. Support guna fungsi ini untuk bantu pengguna yang terlupa kata laluan.',
                'type' => IssueType::Suggestion,
                'system' => IssueSystem::OnPay,
                'priority' => IssuePriority::Medium,
                'reporter' => 'ummi',
                'assignee' => 'haiqal',
                'reported' => 9,
                'workflow' => [[IssueProgress::InDevelopment, 8]],
                'comments' => [
                    ['haiqal', 7.9, 'Akan tambah "Jana Kata Laluan Rawak" sahaja. Fungsi "Hantar kata laluan baru kepada pengguna" tidak diteruskan atas sebab keselamatan.'],
                ],
            ],
            [
                'title' => 'Pilihan akaun bank susah dipilih jika nama akaun terlalu panjang',
                'description' => "Pada borang jualan, jika nama akaun bank terlalu panjang, pilihan akaun bank bertindih dan susah untuk dipilih terutamanya di telefon.\n\nBorang untuk test: https://yashimi.onpay.my/order/form/owala_malaysia",
                'type' => IssueType::Bug,
                'system' => IssueSystem::OnPay,
                'priority' => IssuePriority::Urgent,
                'merchant' => 'Owala Malaysia',
                'reporter' => 'ummi',
                'assignee' => 'haiqal',
                'reported' => 6,
                'workflow' => [[IssueProgress::InDevelopment, 5.8]],
                'comments' => [
                    ['haiqal', 5.7, 'Boleh reproduce. Teks nama bank tak wrap dalam radio card. Akan fix CSS untuk paparan mudah alih.'],
                ],
            ],
            [
                'title' => 'Tiada Stripe Payment Intent ID pada medan pilihan Laporan Jualan',
                'description' => 'Merchant yang guna Stripe nak eksport Laporan Jualan bersama Stripe Payment Intent ID untuk reconcile dengan dashboard Stripe. Medan ini tiada dalam pilihan medan laporan.',
                'type' => IssueType::Suggestion,
                'system' => IssueSystem::OnPay,
                'priority' => IssuePriority::Medium,
                'merchant' => 'Glow Lab Cosmetics',
                'reporter' => 'support',
                'assignee' => 'zaim',
                'reported' => 6,
                'workflow' => [
                    [IssueProgress::InDevelopment, 5],
                    [IssueProgress::NeedInfo, 4],
                ],
                'comments' => [
                    ['zaim', 4, 'Merchant perlukan Payment Intent ID sahaja atau Charge ID sekali? Boleh minta contoh fail reconcile yang mereka guna sekarang?'],
                ],
            ],
            [
                'title' => 'ID Ejen Pengedar tidak dipaparkan',
                'description' => 'Di Senarai Jualan, ID Ejen untuk jualan oleh Pengedar tidak dipaparkan. ID Ejen untuk Affiliate dan Dropship dipaparkan seperti biasa.',
                'type' => IssueType::Bug,
                'system' => IssueSystem::OnPay,
                'priority' => IssuePriority::Medium,
                'reporter' => 'ummi',
                'reported' => 6,
            ],
            [
                'title' => 'Status DIHANTAR tidak dipaparkan selepas kemaskini penghantaran',
                'description' => "Selepas kemaskini penghantaran (Lori Biru) dan jana tracking melalui integrasi EasyParcel OnSend, status DIHANTAR tidak dipaparkan pada Senarai Jualan.\n\nNotifikasi penghantaran juga tidak dihantar ke emel pelanggan.",
                'type' => IssueType::Bug,
                'system' => IssueSystem::OnSend,
                'priority' => IssuePriority::High,
                'merchant' => 'Kedai Tudung Aisyah',
                'reporter' => 'support',
                'assignee' => 'suhailah',
                'reported' => 5,
                'workflow' => [
                    [IssueProgress::InDevelopment, 4.5],
                    [IssueProgress::NeedInfo, 3],
                ],
                'comments' => [
                    ['suhailah', 3, 'Tak dapat reproduce dengan akaun test. Boleh dapatkan nombor invois jualan yang terlibat dan nombor tracking dari merchant?'],
                ],
            ],
            [
                'title' => 'Had belian variasi produk tidak berfungsi',
                'description' => "Had belian yang disetkan pada variasi produk tidak diambil kira. Pelanggan boleh beli melebihi had, dan untuk stok terhad, stok jadi negatif.\n\nContoh: Variasi Saiz M, had belian 2, pelanggan boleh beli 5.",
                'type' => IssueType::Bug,
                'system' => IssueSystem::OnPay,
                'priority' => IssuePriority::Urgent,
                'merchant' => 'Butik Nur Ayu',
                'reporter' => 'ummi',
                'reported' => 2,
            ],
            [
                'title' => 'Jana Laporan Jualan dalam format PDF',
                'description' => 'Merchant minta Laporan Jualan boleh dijana dalam format PDF, bukan hanya CSV dan Excel.',
                'type' => IssueType::Suggestion,
                'system' => IssueSystem::OnPay,
                'priority' => IssuePriority::Low,
                'reporter' => 'support',
                'assignee' => 'hakim',
                'reported' => 2,
                'workflow' => [[IssueStatus::WontFix, 1.5]],
                'comments' => [
                    ['hakim', 1.5, 'Feature Jana PDF tidak akan diteruskan untuk OnPay-Next. Merchant boleh eksport ke Excel dan print sebagai PDF.'],
                ],
            ],
            [
                'title' => 'Tanda "Rekod Berkaitan Yang Belum Disahkan" tiada di Tab Jualan',
                'description' => 'Di OnPay lama, ada tanda seru warna oren pada kolum pertama Tab Jualan untuk jualan yang ada rekod berkaitan belum disahkan. Fungsi ini belum ada di OnPay-Next.',
                'type' => IssueType::Complaint,
                'system' => IssueSystem::OnPay,
                'priority' => IssuePriority::Medium,
                'reporter' => 'ummi',
                'reported' => 2,
            ],
            [
                'title' => 'Tiada butang "Bina Teks Borang" di OnPay-Next',
                'description' => 'Butang "Bina Teks Borang" di bahagian Teks borang jualan tiada di OnPay-Next. Merchant guna fungsi ini untuk jana copywriting borang dengan cepat.',
                'type' => IssueType::Complaint,
                'system' => IssueSystem::OnPay,
                'priority' => IssuePriority::Medium,
                'merchant' => 'Mira Skincare Enterprise',
                'reporter' => 'support',
                'reported' => 1,
            ],
        ];
    }
}
