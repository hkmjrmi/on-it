<?php

namespace App\Models;

use App\Enums\IssuePriority;
use App\Enums\IssueSource;
use App\Enums\IssueStatus;
use App\Enums\IssueSystem;
use App\Enums\IssueType;
use App\Observers\IssueObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Onpay\Core\Eloquent\Concerns\SortStringResolver;
use Onpay\Core\Eloquent\Concerns\ValueSearcher;

#[ObservedBy(IssueObserver::class)]
class Issue extends Model
{
    use HasFactory, SoftDeletes, SortStringResolver, ValueSearcher;

    /**
     * The prefix of the issue key.
     */
    public const string KEY_PREFIX = 'ONIT';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'type',
        'system',
        'priority',
        'status',
        'source',
        'merchant_name',
        'created_by',
        'assigned_to',
        'released_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => IssueType::class,
            'system' => IssueSystem::class,
            'priority' => IssuePriority::class,
            'status' => IssueStatus::class,
            'source' => IssueSource::class,
            'released_at' => 'datetime',
        ];
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'key';
    }

    /**
     * Get the user who created the issue.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user assigned to the issue.
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the comments for the issue.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(IssueComment::class);
    }

    /**
     * Get the attachments for the issue.
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(IssueAttachment::class);
    }

    /**
     * Get the activities for the issue.
     */
    public function activities(): HasMany
    {
        return $this->hasMany(IssueActivity::class);
    }

    /**
     * Scope a query to only include issues with the given status.
     */
    public function scopeStatus(Builder $query, IssueStatus|null $status): void
    {
        if ($status) {
            $query->where('status', $status);
        }
    }

    /**
     * Scope a query to only include open issues.
     */
    public function scopeOpen(Builder $query): void
    {
        $query->whereNotIn('status', IssueStatus::closed());
    }

    /**
     * Scope a query to only include issues released this week.
     */
    public function scopeReleasedThisWeek(Builder $query): void
    {
        $query->status(IssueStatus::Released)->where('released_at', '>=', now()->startOfWeek());
    }

    /**
     * Scope a query to only include issues of the given type.
     */
    public function scopeType(Builder $query, IssueType|null $type): void
    {
        if ($type) {
            $query->where('type', $type);
        }
    }

    /**
     * Scope a query to only include issues for the given system.
     */
    public function scopeSystem(Builder $query, IssueSystem|null $system): void
    {
        if ($system) {
            $query->where('system', $system);
        }
    }

    /**
     * Scope a query to only include issues with the given priority.
     */
    public function scopePriority(Builder $query, IssuePriority|null $priority): void
    {
        if ($priority) {
            $query->where('priority', $priority);
        }
    }

    /**
     * Order a query by an enum column following the declared order of its cases.
     *
     * @param  array<int, \BackedEnum>  $cases
     */
    public function scopeOrderByEnum(Builder $query, string $column, array $cases, \SortDirection|string $direction = 'asc'): void
    {
        $direction = $direction === \SortDirection::Descending || $direction === 'desc' ? 'desc' : 'asc';
        $whens = implode(' ', array_map(fn (int $index) => "when ? then {$index}", array_keys($cases)));

        $query->orderByRaw(
            "case {$query->qualifyColumn($column)} {$whens} end {$direction}",
            array_map(fn (\BackedEnum $case) => $case->value, $cases),
        );
    }

    /**
     * Scope a query to only include issues assigned to the given user, or unassigned issues.
     */
    public function scopeAssignee(Builder $query, string|null $assignee): void
    {
        match (true) {
            $assignee === 'unassigned' => $query->whereNull('assigned_to'),
            is_numeric($assignee) => $query->where('assigned_to', (int) $assignee),
            default => $query,
        };
    }
}
