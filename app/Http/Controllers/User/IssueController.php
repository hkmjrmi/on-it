<?php

namespace App\Http\Controllers\User;

use App\Enums\IssuePriority;
use App\Enums\IssueSource;
use App\Enums\IssueStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\IssueRequest;
use App\Models\Issue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class IssueController extends Controller
{
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
                ...$request->validated(),
                'source' => IssueSource::fromRole($user->role),
                'status' => IssueStatus::New,
                'created_by' => $user->id,
            ]);

            $issue->activities()->create([
                'user_id' => $user->id,
                'action' => 'created',
            ]);

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

        $issue->load(['creator', 'assignee', 'attachments']);

        return view('user.issues.show', compact('issue'));
    }
}
