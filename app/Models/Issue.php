<?php

namespace App\Models;

use App\Enums\IssuePriority;
use App\Enums\IssueSource;
use App\Enums\IssueStatus;
use App\Enums\IssueType;
use App\Observers\IssueObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy(IssueObserver::class)]
class Issue extends Model
{
    use HasFactory;

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
            'priority' => IssuePriority::class,
            'status' => IssueStatus::class,
            'source' => IssueSource::class,
            'released_at' => 'datetime',
        ];
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
}
