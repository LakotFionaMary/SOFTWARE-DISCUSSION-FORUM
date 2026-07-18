<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Blacklist - a temporary ban record for a member. See SDD 4.2 "Blacklist" table.
 */
class Blacklist extends Model
{
    protected $table = 'blacklists';
    protected $primaryKey = 'blacklist_id';

    // A blacklist is either 'manual' (an admin/lecturer action), 'inactivity'
    // (ModerationController::scanInactivity() — locks the whole account),
    // or 'flag' (HandlesFlagAutoBlacklist — suspends from one group only).
    public const REASON_MANUAL = 'manual';
    public const REASON_INACTIVITY = 'inactivity';
    public const REASON_FLAG = 'flag';

    protected $fillable = ['user_id', 'group_id', 'reason', 'start_date', 'duration_days', 'end_date'];

    protected $casts = ['start_date' => 'datetime', 'end_date' => 'datetime'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'group_id', 'group_id');
    }

    public function isActive(): bool
    {
        return $this->end_date->isFuture();
    }
}
