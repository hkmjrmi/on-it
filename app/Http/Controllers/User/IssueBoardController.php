<?php

namespace App\Http\Controllers\User;

use App\Actions\UpdateIssueWorkflow;
use App\Enums\IssuePriority;
use App\Enums\IssueProgress;
use App\Enums\IssueSystem;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Issue;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class IssueBoardController extends Controller
{
    /**
     * The maximum number of cards shown in each column.
     */
    public const int COLUMN_LIMIT = 50;

    /**
     * Display the issue board.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Issue::class);

        $query = fn () => Issue::query()
            ->archived(false)
            ->open()
            ->system($request->enum('system', IssueSystem::class))
            ->priority($request->enum('priority', IssuePriority::class))
            ->assignee($request->query('assignee'));

        $counts = $query()->groupBy('progress')->selectRaw('progress, count(*) as total')->pluck('total', 'progress');

        $columns = collect(IssueProgress::cases())->map(fn (IssueProgress $progress) => [
            'progress' => $progress,
            'total' => (int) ($counts[$progress->value] ?? 0),
            'issues' => $query()
                ->progress($progress)
                ->with('assignee:id,name,role')
                ->orderByEnum('priority', IssuePriority::cases(), 'desc')
                ->latest('id')
                ->limit(self::COLUMN_LIMIT)
                ->get(['id', 'key', 'title', 'system', 'priority', 'progress', 'assigned_to']),
        ]);

        $assignees = User::query()->where('role', UserRole::Programmer)->orderBy('name')->pluck('name', 'id');

        return view('user.board.index', compact('columns', 'assignees'));
    }

    /**
     * Move the specified issue to another progress step.
     */
    public function move(Request $request, Issue $issue, UpdateIssueWorkflow $updateWorkflow)
    {
        Gate::authorize('changeWorkflow', $issue);

        $validated = $request->validate([
            'progress' => ['required', 'enum:' . IssueProgress::class],
        ]);

        $updateWorkflow->handle($issue, $request->user(), $validated);

        return response()->json([
            'message' => __('Issue :key moved to :status.', ['key' => $issue->key, 'status' => $issue->progress->label()]),
        ]);
    }
}
