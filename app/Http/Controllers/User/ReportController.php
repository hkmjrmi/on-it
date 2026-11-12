<?php

namespace App\Http\Controllers\User;

use App\Enums\IssueActivityAction;
use App\Enums\IssuePriority;
use App\Enums\IssueStatus;
use App\Enums\IssueSystem;
use App\Enums\IssueType;
use App\Http\Controllers\Controller;
use App\Models\Issue;
use App\Models\IssueActivity;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;

class ReportController extends Controller
{
    /**
     * The selectable report ranges, in weeks.
     *
     * @var array<int, int>
     */
    public const array RANGES = [4, 12, 26, 52];

    /**
     * Display the reports page.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Issue::class);

        $weeks = in_array($request->integer('weeks'), self::RANGES, true) ? $request->integer('weeks') : 12;
        $start = CarbonImmutable::now()->startOfWeek()->subWeeks($weeks - 1);

        $weekKeys = collect(range(0, $weeks - 1))->map(fn (int $i) => $start->addWeeks($i)->toDateString());
        $weekOf = fn ($date) => CarbonImmutable::parse($date)->startOfWeek()->toDateString();

        $created = Issue::query()->where('created_at', '>=', $start)->get(['id', 'type', 'system', 'created_at']);

        $resolved = IssueActivity::query()
            ->where('action', IssueActivityAction::StatusChanged)
            ->whereIn('to_value', array_map(fn (IssueStatus $status) => $status->value, IssueStatus::closed()))
            ->where('created_at', '>=', $start)
            ->whereHas('issue')
            ->get(['issue_id', 'created_at'])
            ->groupBy(fn (IssueActivity $activity) => $weekOf($activity->created_at))
            ->map(fn (Collection $activities) => $activities->unique('issue_id')->count());

        $released = Issue::query()
            ->status(IssueStatus::Released)
            ->where('released_at', '>=', $start)
            ->get(['created_at', 'released_at'])
            ->groupBy(fn (Issue $issue) => $weekOf($issue->released_at));

        $createdPerWeek = $created->countBy(fn (Issue $issue) => $weekOf($issue->created_at));
        $daysToRelease = fn (Collection $issues) => round($issues->avg(fn (Issue $issue) => $issue->created_at->diffInHours($issue->released_at) / 24), 1);

        $report = [
            'weeks' => $weekKeys->map(fn (string $week) => CarbonImmutable::parse($week)->format('j M'))->all(),
            'created' => $weekKeys->map(fn (string $week) => $createdPerWeek[$week] ?? 0)->all(),
            'resolved' => $weekKeys->map(fn (string $week) => $resolved[$week] ?? 0)->all(),
            'days_to_release' => $weekKeys->map(fn (string $week) => isset($released[$week]) ? $daysToRelease($released[$week]) : null)->all(),
            'by_system' => $this->countByEnum($created, 'system', IssueSystem::cases()),
            'by_type' => $this->countByEnum($created, 'type', IssueType::cases()),
            'open_by_priority' => $this->countByEnum(Issue::query()->open()->get(['priority']), 'priority', IssuePriority::cases()),
        ];

        $allReleased = $released->flatten(1);

        $totals = [
            'created' => $created->count(),
            'resolved' => array_sum($report['resolved']),
            'days_to_release' => $allReleased->isNotEmpty() ? $daysToRelease($allReleased) : null,
            'open' => array_sum($report['open_by_priority']['values']),
        ];

        return view('user.reports.index', compact('report', 'totals', 'weeks'));
    }

    /**
     * Count the issues by an enum column, in the declared order of its cases.
     *
     * @param  \Illuminate\Support\Collection<int, \App\Models\Issue>  $issues
     * @param  array<int, \BackedEnum>  $cases
     * @return array{keys: array<int, string>, labels: array<int, string>, values: array<int, int>}
     */
    protected function countByEnum(Collection $issues, string $column, array $cases): array
    {
        $counts = $issues->countBy(fn (Issue $issue) => $issue->{$column}?->value);

        return [
            'keys' => array_map(fn (\BackedEnum $case) => $case->value, $cases),
            'labels' => array_map(fn (\BackedEnum $case) => $case->label(), $cases),
            'values' => array_map(fn (\BackedEnum $case) => $counts[$case->value] ?? 0, $cases),
        ];
    }
}
