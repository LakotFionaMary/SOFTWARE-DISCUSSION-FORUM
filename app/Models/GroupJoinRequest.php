<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * GroupJoinRequest - a pending request from a User to join a Group,
 * requiring approval from that group's admin (owner or active GroupAdmin)
 * before a real Membership row is created.
 */
class GroupJoinRequest extends Model
{
    protected $table = 'group_join_requests';
    protected $primaryKey = 'join_request_id';

    protected $fillable = ['user_id', 'group_id', 'rules_accepted', 'status', 'requested_at', 'resolved_at', 'resolved_by'];

    protected $casts = [
        'rules_accepted' => 'boolean',
        'requested_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'group_id', 'group_id');
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by', 'user_id');
    }
}
