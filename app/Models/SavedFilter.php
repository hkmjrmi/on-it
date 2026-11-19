<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavedFilter extends Model
{
    /**
     * The issue list params that can be saved.
     *
     * @var array<int, string>
     */
    public const array PARAMS = [
        'status',
        'progress',
        'system',
        'type',
        'priority',
        'assignee',
        'open',
        'released_this_week',
        'archived',
        'search_value',
        'sort',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'params',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'params' => 'array',
        ];
    }

    /**
     * Get the user who owns the saved filter.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
