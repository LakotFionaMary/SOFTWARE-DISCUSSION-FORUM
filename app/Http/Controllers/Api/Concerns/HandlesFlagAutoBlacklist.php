<?php

namespace App\Http\Controllers\Api\Concerns;

use App\Models\Blacklist;
use App\Models\Group;
use App\Models\Post;
use App\Models\Reply;
use App\Models\User;
use App\Services\NotificationService;

/**
 * Shared moderation escalation logic used by PostController::flag() and
 * ReplyController::flag(): once a member's currently-flagged content within
 * a single group reaches FLAG_BLACKLIST_THRESHOLD, they're automatically
 * blacklisted from that group for its configured blacklist_duration_days —
 * mirroring the same auto-blacklist pattern
 * ModerationController::scanInactivity() already uses for repeated
 * unresolved inactivity warnings (SDD 5.2).
 *
 * This is a reason='flag' blacklist: it only suspends the member from this
 * one group (enforced by User::isBlacklistedIn() / BlacklistMiddleware on
 * topic creation, messaging, posting, and replying) and never blocks login
 * — that whole-account lock is reserved for reason='inactivity' blacklists
 * (see ModerationController::scanInactivity() and AuthController::login()).
 * It expires automatically once Blacklist.end_date passes, or an admin can
 * lift it early via POST /moderation/blacklists/{blacklist}/lift.
 */
trait HandlesFlagAutoBlacklist
{
    /** Currently-flagged content count at/above this in one group triggers an automatic blacklist. */
    private const FLAG_BLACKLIST_THRESHOLD = 4;

    protected function autoBlacklistIfFlaggedTooMuch(?User $author, ?int $groupId, NotificationService $notifications): void
    {
        if (! $author || ! $groupId) {
            return;
        }

        // Already serving an active suspension in this group — don't stack
        // another one on top or re-notify every time a further flag comes in
        // while they're already blacklisted.
        if ($author->isBlacklistedIn($groupId)) {
            return;
        }

        $flaggedPosts = Post::where('author_id', $author->user_id)
            ->whereHas('topic', fn ($q) => $q->where('group_id', $groupId))
            ->where('is_flagged', true)
            ->count();

        $flaggedReplies = Reply::where('author_id', $author->user_id)
            ->whereHas('post.topic', fn ($q) => $q->where('group_id', $groupId))
            ->where('is_flagged', true)
            ->count();

        if (($flaggedPosts + $flaggedReplies) < self::FLAG_BLACKLIST_THRESHOLD) {
            return;
        }

        $group = Group::find($groupId);
        if (! $group) {
            return;
        }

        Blacklist::create([
            'user_id' => $author->user_id,
            'group_id' => $groupId,
            'reason' => Blacklist::REASON_FLAG,
            'start_date' => now(),
            'duration_days' => $group->blacklist_duration_days,
            'end_date' => now()->addDays($group->blacklist_duration_days),
        ]);

        $notifications->send(
            $author,
            'Blacklist',
            "You have been suspended from '{$group->name}' for {$group->blacklist_duration_days} days after repeated content moderation flags. Access will be restored automatically once the suspension ends.",
            'Group',
            $groupId
        );
    }
}
