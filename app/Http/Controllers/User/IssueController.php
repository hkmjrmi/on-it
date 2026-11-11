<?php

namespace App\Http\Controllers\User;

use App\Enums\IssuePriority;
use App\Enums\IssueSource;
use App\Enums\IssueStatus;
use App\Enums\IssueType;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\IssueRequest;
use App\Models\Issue;
use App\Models\User;
use Illuminate\Http\Request;
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

        $sort = $request->query('sort');

        // Get the query builder
        $query = Issue::query();

        // Filters
        $query->status($request->enum('status', IssueStatus::class))
            ->type($request->enum('type', IssueType::class))
            ->priority($request->enum('priority', IssuePriority::class))
            ->assignee($request->query('assignee'));

        // Search
        $query->searchWildcard($request->query('search_value'), ['title', 'key', 'merchant_name']);

        // Sort
        $validSortColumns = ['id', 'title', 'priority', 'status', 'created_at'];

        $query->resolveSortString($sort, 'id', 'desc', $validSortColumns);

        // Get the result
        $columns = ['id', 'key', 'title', 'type', 'priority', 'status', 'assigned_to', 'created_at'];

        $issues = $query->select($columns)
            ->with('assignee:id,name')
            ->paginate(20)
            ->withIndexPathAndQueryString();

        if ($request->wantsJson()) {
            return $issues;
        }

        $assignees = User::query()
            ->where('role', UserRole::Programmer)
            ->orderBy('name')
            ->pluck('name', 'id');

        return view('user.issues.index', compact('issues', 'assignees'));
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
                'action' => 'created',
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

        return view('user.issues.show', compact('issue'));
    }
}
