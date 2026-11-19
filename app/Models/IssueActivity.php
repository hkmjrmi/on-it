<?php

namespace App\Models;

use App\Enums\IssueActivityAction;
use App\Enums\IssuePriority;
use App\Enums\IssueProgress;
use App\Enums\IssueStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class IssueActivity extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'issue_id',
        'user_id',
        'action',
        'from_value',
        'to_value',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'action' => IssueActivityAction::class,
        ];
    }

    /**
     * Get the issue that owns the activity.
     */
    public function issue(): BelongsTo
    {
        return $this->belongsTo(Issue::class);
    }

    /**
     * Get the user who performed the activity.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the readable description of the activity.
     *
     * @param  \Illuminate\Support\Collection<int, string>  $userNames
     */
    public function description(Collection $userNames): string
    {
        $name = $this->user->name;

        return match ($this->action) {
            IssueActivityAction::Created => $this->from_value
                ? __(':name created the issue (cloned from :key)', ['name' => $name, 'key' => $this->from_value])
                : __(':name created the issue', ['name' => $name]),
            IssueActivityAction::Edited => __(':name edited the title and description', ['name' => $name]),
            IssueActivityAction::Commented => __(':name commented', ['name' => $name]),
            IssueActivityAction::CommentEdited => __(':name edited a comment', ['name' => $name]),
            IssueActivityAction::CommentDeleted => __(':name deleted a comment', ['name' => $name]),
            IssueActivityAction::Archived => __(':name archived the issue', ['name' => $name]),
            IssueActivityAction::Unarchived => __(':name unarchived the issue', ['name' => $name]),
            IssueActivityAction::StatusChanged => __(':name changed status :from → :to', [
                'name' => $name,
                'from' => IssueStatus::tryFrom((string) $this->from_value)?->label() ?? '-',
                'to' => IssueStatus::tryFrom((string) $this->to_value)?->label() ?? '-',
            ]),
            IssueActivityAction::ProgressChanged => __(':name changed progress :from → :to', [
                'name' => $name,
                'from' => IssueProgress::tryFrom((string) $this->from_value)?->label() ?? '-',
                'to' => IssueProgress::tryFrom((string) $this->to_value)?->label() ?? '-',
            ]),
            IssueActivityAction::PriorityChanged => __(':name changed priority :from → :to', [
                'name' => $name,
                'from' => IssuePriority::tryFrom((string) $this->from_value)?->label() ?? '-',
                'to' => IssuePriority::tryFrom((string) $this->to_value)?->label() ?? '-',
            ]),
            IssueActivityAction::Assigned => $this->to_value
                ? __(':name assigned the issue to :assignee', ['name' => $name, 'assignee' => $userNames[$this->to_value] ?? '-'])
                : __(':name unassigned the issue', ['name' => $name]),
        };
    }
}
