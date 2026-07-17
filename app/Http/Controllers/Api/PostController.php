<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Concerns\TracksParticipation;
use App\Http\Controllers\Api\Concerns\HandlesFlagAutoBlacklist;
use App\Jobs\GenerateUserRecommendations;
use App\Models\Post;
use App\Models\PostExclusion;
use App\Models\Topic;
use App\Models\User;
use App\Services\NotificationService;
use App\Events\MessageBroadcast;
use Illuminate\Http\Request;

/**
 * Managing discussion posts use case (SDD Table 36) + Selective Communication
 * (post exclusion) and Content Moderation (flagging), both part of the
 * Moderation and Inactivity Management Module (SDD 5.2).
 */
class PostController extends Controller
{
    use TracksParticipation;
    use HandlesFlagAutoBlacklist;

    public function __construct(private NotificationService $notifications)
    {
    }

    public function index(Request $request, Topic $topic)
    {
        $userId = $request->user()->user_id;

        // Selective communication: hide posts that exclude the requesting user.
        $posts = $topic->posts()
            ->with(['author', 'replies.author'])
            ->whereDoesntHave('exclusions', fn ($q) => $q->where('excluded_user_id', $userId))
            ->latest('posted_at')
            ->paginate(20);

        return response()->json($posts);
    }

    public function store(Request $request, Topic $topic)
    {
        $request->validate([
            'content' => 'required|string',
            'attachment_url' => 'nullable|string',
            'exclude_user_ids' => 'nullable|array',
            'exclude_user_ids.*' => 'integer|exists:users,user_id',
        ]);

        $author = $request->user();

        if ($author->isBlacklistedIn($topic->group_id)) {
            return response()->json(['message' => 'You are blacklisted from posting in this group.'], 403);
        }

        $post = Post::create([
            'topic_id' => $topic->topic_id,
            'author_id' => $author->user_id,
            'content' => $request->content,
            'attachment_url' => $request->attachment_url,
            'posted_at' => now(),
        ]);
        $post->load('author');
        broadcast(new MessageBroadcast($post, $post->topic_id));

//broadcast(new MessageBroadcast($post))->toOthers();

        // RIGHT: Pass only the $post model instance
        // event(new MessageBroadcast($post));
        //event(new MessageBroadcast($topic->topic_id, 'post', $post->load('author')->toArray()));

        foreach ($request->input('exclude_user_ids', []) as $excludedUserId) {
            PostExclusion::create(['post_id' => $post->post_id, 'excluded_user_id' => $excludedUserId]);
        }

        $author->update(['last_active_at' => now()]);
        $this->recordParticipation($author, $topic->group_id, 'post');

        // Refresh this user's recommendations now that their reply history
        // has changed (new category weight from this post).
        GenerateUserRecommendations::dispatch($author);

        // Notify the topic creator (and, in a fuller implementation, every
        // non-excluded group member) of the new post.
        if ($topic->created_by !== $author->user_id) {
            $this->notifications->send(
                $topic->creator,
                'New Post',
                "{$author->full_name} posted in your topic '{$topic->title}'.",
                'Post',
                $post->post_id
            );
        }

        return response()->json($post->load('author'), 201);
    }

    /** Content moderation: flag a post as irrelevant/spam (SDD Table 31). */
    public function flag(Request $request, Post $post)
    {
        $post->update(['is_flagged' => true]);

        $flagger = $request->user();
        $topicTitle = $post->topic->title ?? 'a topic';

        // ADDED: flagging previously updated the row silently — no one was
        // ever told. Every Administrator now gets a notification so it
        // actually surfaces (e.g. on the admin dashboard's Flagged Content
        // list), matching the same NotificationService->send() pattern
        // store() already uses for new-post notifications.
        // FIXED: the notifications.type column is a strict MySQL ENUM
        // ('Quiz Announcement', 'Warning', 'Blacklist', 'New Post', 'Reply',
        // 'General') — see 2024_01_01_000014_create_notifications_table.php.
        // 'Post Flagged' was never a valid enum value, so every insert threw
        // PDOException 1265 ("Data truncated for column 'type'") and this
        // whole endpoint 500'd. Reusing 'General' avoids an ENUM schema
        // migration; the actual Post/Reply distinction already lives in
        // related_type (a plain string column, no enum), and the message
        // text still says "flagged" for the admin dashboard's filter.
        User::whereHas('roles', fn ($q) => $q->where('role_name', 'Administrator'))
            ->get()
            ->each(function (User $admin) use ($flagger, $post, $topicTitle) {
                $this->notifications->send(
                    $admin,
                    'General',
                    "{$flagger->full_name} flagged a post in '{$topicTitle}' for moderation.",
                    'Post',
                    $post->post_id
                );
            });

        // ADDED: content-moderation escalation (SDD 5.2) — once this
        // author's currently-flagged posts+replies in this group reach the
        // threshold, they're automatically blacklisted for the group's
        // configured duration and notified. See HandlesFlagAutoBlacklist
        // for the full rationale; access denial and the automatic lift once
        // the suspension ends are both already handled by the existing
        // Blacklist.end_date check in User::isBlacklistedIn().
        $this->autoBlacklistIfFlaggedTooMuch($post->author, $post->topic->group_id ?? null, $this->notifications);

        return response()->json(['message' => 'Post flagged for moderation.', 'post' => $post]);
    }

    public function destroy(Post $post)
    {
        $post->delete();

        return response()->json(['message' => 'Post deleted.']);
    }
}
