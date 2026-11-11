<?php

namespace App\Http\Controllers\User;

use App\Enums\IssueActivityAction;
use App\Enums\IssuePriority;
use App\Enums\IssueSource;
use App\Enums\IssueStatus;
use App\Enums\IssueType;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\IssueRequest;
use App\Models\Issue;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class IssueController extends Controller
{
    /**
     * Display a listing of the issues.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Issue::class);

        $issues = $this->paginate($this->listQuery($request));

        if ($request->wantsJson()) {
            return $issues;
        }

        $assignees = $this->assigneeOptions();
        $summary = $this->summary();

        return view('user.issues.index', compact('issues', 'assignees', 'summary'));
    }

    /**
     * Display a listing of the issues assigned to or created by the user.
     */
    public function mine(Request $request)
    {
        Gate::authorize('viewAny', Issue::class);

        $user = $request->user();
        $defaultTab = $user->isProgrammer() ? 'assigned' : 'created';
        $tab = in_array($request->query('tab'), ['assigned', 'created']) ? $request->query('tab') : $defaultTab;
        $tabColumns = ['assigned' => 'assigned_to', 'created' => 'created_by'];

        $issues = $this->paginate($this->listQuery($request)->where($tabColumns[$tab], $user->id));

        if ($request->wantsJson()) {
            return $issues;
        }

        $assignees = $this->assigneeOptions();
        $counts = array_map(fn (string $column) => Issue::query()->where($column, $user->id)->count(), $tabColumns);

        return view('user.issues.mine', compact('issues', 'assignees', 'counts', 'tab', 'defaultTab'));
    }

    /**
     * Get the filtered, searched and sorted query for the issue list.
     */
    protected function listQuery(Request $request): Builder
    {
        return Issue::query()
            ->status($request->enum('status', IssueStatus::class))
            ->type($request->enum('type', IssueType::class))
            ->priority($request->enum('priority', IssuePriority::class))
            ->assignee($request->query('assignee'))
            ->when($request->boolean('open'), fn (Builder $query) => $query->open())
            ->when($request->boolean('released_this_week'), fn (Builder $query) => $query->releasedThisWeek())
            ->searchWildcard($request->query('search_value'), ['title', 'key', 'merchant_name'])
            ->resolveSortString($request->query('sort'), 'id', 'desc', ['id', 'title', 'priority', 'status', 'created_at']);
    }

    /**
     * Paginate the issue list query.
     */
    protected function paginate(Builder $query): LengthAwarePaginator
    {
        return $query->select(['id', 'key', 'title', 'type', 'priority', 'status', 'assigned_to', 'created_at'])
            ->with('assignee:id,name')
            ->paginate(20)
            ->withQueryString();
    }

    /**
     * Get the assignee filter options.
     *
     * @return \Illuminate\Support\Collection<int, string>
     */
    protected function assigneeOptions(): Collection
    {
        return User::query()
            ->where('role', UserRole::Programmer)
            ->orderBy('name')
            ->pluck('name', 'id');
    }

    /**
     * Get the issue counts for the dashboard summary cards.
     *
     * @return array<string, int>
     */
    protected function summary(): array
    {
        $statusCounts = Issue::query()
            ->whereIn('status', [IssueStatus::New, IssueStatus::InDevelopment, IssueStatus::InReview, IssueStatus::ReadyToRelease])
            ->groupBy('status')
            ->selectRaw('status, count(*) as total')
            ->pluck('total', 'status');

        return [
            ...collect([IssueStatus::New, IssueStatus::InDevelopment, IssueStatus::InReview, IssueStatus::ReadyToRelease])
                ->mapWithKeys(fn (IssueStatus $status) => [$status->value => (int) ($statusCounts[$status->value] ?? 0)])
                ->all(),
            'released_this_week' => Issue::query()->releasedThisWeek()->count(),
            'unassigned' => Issue::query()
                ->open()
                ->assignee('unassigned')
                ->count(),
        ];
    }

    /**
     * Show the form for creating a new issue.
     */
    public function create()
    {
        Gate::authorize('create', Issue::class);

        return view('user.issues.form', [
            'issue' => ['priority' => IssuePriority::Medium->value],
        ]);
    }

    /**
     * Store a newly created issue in storage.
     */
    public function store(IssueRequest $request)
    {
        Gate::authorize('create', Issue::class);

        $user = $request->user();

        $issue = DB::transaction(function () use ($request, $user) {
            $issue = Issue::create([
                ...$request->safe()->except('attachments'),
                'source' => IssueSource::fromRole($user->role),
                'status' => IssueStatus::New,
                'created_by' => $user->id,
            ]);

            $issue->activities()->create([
                'user_id' => $user->id,
                'action' => IssueActivityAction::Created,
            ]);

            $disk = config('filesystems.attachments');

            foreach ($request->file('attachments', []) as $file) {
                $issue->attachments()->create([
                    'user_id' => $user->id,
                    'disk' => $disk,
                    'path' => $file->store('issues/' . $issue->id, $disk),
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'size_bytes' => $file->getSize(),
                ]);
            }

            return $issue;
        });

        return redirect(route('user.issues.show', $issue))
            ->withSuccessNotification(__('Issue :key has been created successfully.', ['key' => $issue->key]));
    }

    /**
     * Display the specified issue.
     */
    public function show(Issue $issue)
    {
        Gate::authorize('view', $issue);

        $issue->load(['creator', 'assignee', 'attachments.user']);

        $assignees = $this->assigneeOptions();

        return view('user.issues.show', compact('issue', 'assignees'));
    }
}
