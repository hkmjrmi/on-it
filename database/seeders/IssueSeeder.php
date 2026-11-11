<?php

namespace Database\Seeders;

use App\Models\Issue;
use App\Models\IssueComment;
use App\Models\User;
use Illuminate\Database\Seeder;

class IssueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::query()->whereNotNull('email_verified_at')->get();

        Issue::factory()
            ->count(20)
            ->recycle($users)
            ->create()
            ->each(fn (Issue $issue) => IssueComment::factory()
                ->count(fake()->numberBetween(0, 3))
                ->for($issue)
                ->recycle($users)
                ->create());
    }
}
