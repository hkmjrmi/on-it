<?php

namespace App\Http\Controllers\User;

use App\Enums\IssueActivityAction;
use App\Enums\IssuePriority;
use App\Enums\IssueSource;
use App\Enums\IssueStatus;
use App\Enums\IssueSystem;
use App\Enums\IssueType;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\IssueRequest;
use App\Models\Issue;
use App\Models\User;
use App\Notifications\User\IssueCreatedNotification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

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
        $counts = array_map(fn (string $column) => Issue::query()->archived(false)->where($column, $user->id)->count(), $tabColumns);

        return view('user.issues.mine', compact('issues', 'assignees', 'counts', 'tab', 'defaultTab'));
    }

    /**
     * Update the title and description of the specified issue.
     */
    public function update(IssueRequest $request, Issue $issue)
    {
        Gate::authorize('update', $issue);

        $issue->fill($request->validated());

        if ($issue->isDirty()) {
            DB::transaction(function () use ($request, $issue) {
                $issue->save();

                $issue->activities()->create([
                    'user_id' => $request->user()->id,
                    'action' => IssueActivityAction::Edited,
                ]);
            });
        }

        return response()->json([
            'message' => __('Issue :key has been updated successfully.', ['key' => $issue->key]),
        ]);
    }

    /**
     * Remove the specified issue from storage.
     */
    public function destroy(Issue $issue)
    {
        Gate::authorize('delete', $issue);

        $issue->delete();

        return response()->json([
            'message' => __('Issue :key has been deleted successfully.', ['key' => $issue->key]),
            'redirect' => route('user.main'),
        ]);
    }

    /**
     * Get the filtered, searched and sorted query for the issue list.
     */
    protected function listQuery(Request $request): Builder
    {
        return Issue::query()
            ->status($request->enum('status', IssueStatus::class))
            ->type($request->enum('type', IssueType::class))
            ->system($request->enum('system', IssueSystem::class))
            ->priority($request->enum('priority', IssuePriority::class))
            ->assignee($request->query('assignee'))
            ->archived($request->boolean('archived'))
            ->when($request->boolean('open'), fn (Builder $query) => $query->open())
            ->when($request->boolean('released_this_week'), fn (Builder $query) => $query->releasedThisWeek())
            ->searchWildcard($request->query('search_value'), ['title', 'key', 'merchant_name'])
            ->resolveSortString($request->query('sort'), 'id', 'desc', [
                'id',
                'title',
                'priority' => fn (Builder $query, $direction) => $query->orderByEnum('priority', IssuePriority::cases(), $direction),
                'status' => fn (Builder $query, $direction) => $query->orderByEnum('status', IssueStatus::cases(), $direction),
                'created_at',
            ]);
    }

    /**
     * Paginate the issue list query.
     */
    protected function paginate(Builder $query): LengthAwarePaginator
    {
        return $query->select(['id', 'key', 'title', 'type', 'system', 'priority', 'status', 'assigned_to', 'created_at'])
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
            'released_this_week' => Issue::query()->archived(false)->releasedThisWeek()->count(),
            'unassigned' => Issue::query()
                ->open()
                ->assignee('unassigned')
                ->count(),
        ];
    }

    /**
     * Show the form for creating a new issue.
     */
    public function create(Request $request)
    {
        Gate::authorize('create', Issue::class);

        $source = $request->filled('clone')
            ? Issue::query()->where('key', $request->query('clone'))->first()
            : null;

        $issue = $source
            ? [
                ...array_map(fn ($value) => $value instanceof \BackedEnum ? $value->value : $value, $source->only(['description', 'type', 'system', 'priority', 'merchant_name'])),
                'title' => Str::limit(__('Copy of :title', ['title' => $source->title]), 255, ''),
            ]
            : ['priority' => IssuePriority::Medium->value];

        return view('user.issues.form', compact('issue', 'source'));
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
                ...$request->safe()->except(['attachments', 'cloned_from']),
                'source' => IssueSource::fromRole($user->role),
                'status' => IssueStatus::New,
                'created_by' => $user->id,
            ]);

            $issue->activities()->create([
                'user_id' => $user->id,
                'action' => IssueActivityAction::Created,
                'from_value' => $request->validated('cloned_from'),
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

        Notification::send(
            User::query()->receivesNotifications()->where('role', UserRole::Programmer)->whereKeyNot($user->id)->get(),
            new IssueCreatedNotification($issue, $user),
        );

        return redirect(route('user.issues.show', $issue))
            ->withSuccessNotification(__('Issue :key has been created successfully.', ['key' => $issue->key]));
    }

    /**
     * Display the specified issue.
     */
    public function show(Issue $issue)
    {
        Gate::authorize('view', $issue);

        $issue->load([
            'creator',
            'assignee',
            'attachments.user',
            'comments' => fn ($query) => $query->with('user:id,name,role')->oldest('id'),
            'activities' => fn ($query) => $query->with('user:id,name')->latest('id'),
        ]);

        $activityUserNames = User::query()
            ->whereIn('id', $issue->activities->where('action', IssueActivityAction::Assigned)->pluck('to_value')->filter())
            ->pluck('name', 'id');

        $assignees = $this->assigneeOptions();

        return view('user.issues.show', compact('issue', 'assignees', 'activityUserNames'));
    }
}
