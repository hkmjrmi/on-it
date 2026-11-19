<?php

namespace App\Enums;

use Onpay\Core\Enums\Attributes\ForJs;
use Onpay\Core\Enums\Attributes\Translated;
use Onpay\Core\Enums\EnumHelpers;

enum IssueActivityAction: string
{
    use EnumHelpers;

    case Created = 'created';
    case Edited = 'edited';
    case Assigned = 'assigned';
    case StatusChanged = 'status_changed';
    case ProgressChanged = 'progress_changed';
    case PriorityChanged = 'priority_changed';
    case Commented = 'commented';
    case CommentEdited = 'comment_edited';
    case CommentDeleted = 'comment_deleted';
    case Archived = 'archived';
    case Unarchived = 'unarchived';

    /**
     * Get the label for the case.
     */
    public function label(): string
    {
        return match ($this) {
            self::Created => __('Created'),
            self::Edited => __('Edited'),
            self::Assigned => __('Assigned'),
            self::StatusChanged => __('Status Changed'),
            self::ProgressChanged => __('Progress Changed'),
            self::PriorityChanged => __('Priority Changed'),
            self::Commented => __('Commented'),
            self::CommentEdited => __('Comment Edited'),
            self::CommentDeleted => __('Comment Deleted'),
            self::Archived => __('Archived'),
            self::Unarchived => __('Unarchived'),
        };
    }

    /**
     * Get all labels.
     */
    #[ForJs]
    #[Translated]
    public static function labels(): array
    {
        return self::mapToValues(fn ($case) => $case->label());
    }
}
