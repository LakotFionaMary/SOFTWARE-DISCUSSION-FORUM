<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Concerns\TracksParticipation;
use App\Http\Controllers\Api\Concerns\HandlesFlagAutoBlacklist;
use App\Models\GroupAdmin;
use App\Models\Post;
use App\Models\Reply;
use App\Models\User;
use App\Services\NotificationService;
use App\Events\MessageBroadcast;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Threaded replies to a Post. Supports the discussion-post management use
 * case (SDD Table 36) and feeds the Grading and Participation Module (5.6).
 */
class ReplyController extends Controller
{
    use TracksParticipation, HandlesFlagAutoBlacklist;

    public function __construct(private NotificationService $notifications)
    {
    }

    public function store(Request $request, Post $post)
    {
        $request->validate(['content' => 'required|string']);

        $author = $request->user();

        if ($author->isBlacklistedIn($post->topic->group_id)) {
            return response()->json(['message' => 'You are blacklisted from replying in this group.'], 403);
        }

        $reply = Reply::create([
            'post_id' => $post->post_id,
            'author_id' => $author->user_id,
            'content' => $request->content,
            'replied_at' => now(),
        ]);

        $author->update(['last_active_at' => now()]);
        $this->recordParticipation($author, $post->topic->group_id, 'reply');

        if ($post->author_id !== $author->user_id) {
            $this->notifications->send(
                $post->author,
                'Reply',
                "{$author->full_name} replied to your post.",
                'Reply',
                $reply->reply_id
            );
        }

        // Real-time push is a nice-to-have; it must not take the whole
        // request down when Reverb isn't running (see PostController::store()).
        try {
            event(new MessageBroadcast($reply, $post->topic_id));
        } catch (\Throwable $e) {
            report($e);
        }

        return response()->json($reply->load('author'), 201);
    }

    /** Mirrors PostController::authorizeFlag() — see that method for details. */
    private function authorizeFlag(User $user, int $groupId): bool
    {
        if ($user->hasRole('Administrator')) {
            return true;
        }

        if ($user->hasRole('Lecturer') && $user->isMemberOf($groupId)) {
            return true;
        }

        return GroupAdmin::where('group_id', $groupId)
            ->where('user_id', $user->user_id)
            ->where('is_active', true)
            ->exists();
    }

    /** Content moderation: flag (or unflag) a reply as irrelevant/inappropriate. */
    public function flag(Request $request, Reply $reply)
    {
        $request->validate(['flagged' => 'nullable|boolean']);

        $flagger = $request->user();
        $reply->loadMissing('post.topic');
        $groupId = $reply->post->topic->group_id ?? null;

        if (! $groupId || ! $this->authorizeFlag($flagger, $groupId)) {
            return response()->json([
                'message' => 'Access denied. You must be an Administrator, a Lecturer in this group, or an active group admin to flag content here.',
            ], Response::HTTP_FORBIDDEN);
        }

        $flagged = $request->boolean('flagged', true);
        $reply->update(['is_flagged' => $flagged]);

        $topicTitle = $reply->post->topic->title ?? 'a topic';
        $responseMessage = $flagged ? 'Reply flagged for moderation.' : 'Flag removed from reply.';

        if ($flagged) {
            // FIXED: mirrors PostController::flag() — every Administrator now
            // gets notified when a reply is flagged, so it surfaces on the
            // admin dashboard's Inactivity Warnings and Flags list just like
            // flagged posts. Previously called the undefined User::role()
            // method, which threw a BadMethodCallException on every flag.
            //
            // NOTE: notifications.type is a strict DB enum (Quiz
            // Announcement, Warning, Blacklist, New Post, Reply, General)
            // that does NOT include 'Reply Flagged' — using it throws a
            // QueryException ("Data truncated for column 'type'"). 'General'
            // is used instead; the admin dashboard already detects flag
            // notifications by scanning the message text, not the type.
            User::whereHas('roles', fn ($q) => $q->where('role_name', 'Administrator'))
                ->get()
                ->each(function (User $admin) use ($flagger, $reply, $topicTitle) {
                    $this->notifications->send(
                        $admin,
                        'General',
                        "{$flagger->full_name} flagged a reply in '{$topicTitle}' for moderation.",
                        'Reply',
                        $reply->reply_id
                    );
                });

            // Auto-blacklist a member once their flagged content in this
            // group crosses the threshold (see HandlesFlagAutoBlacklist).
            $author = $reply->author;
            $blacklistedBefore = $author ? $author->isBlacklistedIn($groupId) : false;
            $this->autoBlacklistIfFlaggedTooMuch($author, $groupId, $this->notifications);
            if ($author && ! $blacklistedBefore && $author->isBlacklistedIn($groupId)) {
                $responseMessage = 'Reply flagged for moderation. The author has been automatically blacklisted from this group after repeated flags.';
            }
        }

        return response()->json(['message' => $responseMessage, 'reply' => $reply]);
    }
}
