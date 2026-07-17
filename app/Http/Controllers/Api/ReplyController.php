<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Concerns\TracksParticipation;
use App\Models\Post;
use App\Models\Reply;
use App\Models\User;
use App\Services\NotificationService;
use App\Events\MessageBroadcast;
use Illuminate\Http\Request;

/**
 * Threaded replies to a Post. Supports the discussion-post management use
 * case (SDD Table 36) and feeds the Grading and Participation Module (5.6).
 */
class ReplyController extends Controller
{
    use TracksParticipation;

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
        event(new MessageBroadcast($reply, $post->topic_id));
        // event(new MessageBroadcast($post));

       // event(new MessageBroadcast($post->topic_id, 'reply', $reply->load('author')->toArray()));

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

        return response()->json($reply->load('author'), 201);
    }

    /** Content moderation: flag a reply as irrelevant/inappropriate. */
    public function flag(Request $request, Reply $reply)
    {
        $reply->update(['is_flagged' => true]);

        $flagger = $request->user();
        $topicTitle = $reply->post->topic->title ?? 'a topic';

        // ADDED: mirrors PostController::flag() — every Administrator now
        // gets notified when a reply is flagged, so it surfaces on the
        // admin dashboard's Flagged Content list just like flagged posts.
        User::role('Administrator')->get()->each(function (User $admin) use ($flagger, $reply, $topicTitle) {
            $this->notifications->send(
                $admin,
                'Reply Flagged',
                "{$flagger->full_name} flagged a reply in '{$topicTitle}' for moderation.",
                'Reply',
                $reply->reply_id
            );
        });

        return response()->json(['message' => 'Reply flagged for moderation.', 'reply' => $reply]);
    }
}