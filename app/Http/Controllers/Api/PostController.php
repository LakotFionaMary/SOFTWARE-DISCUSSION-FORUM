<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Concerns\TracksParticipation;
use App\Http\Controllers\Api\Concerns\HandlesFlagAutoBlacklist;
use App\Jobs\GenerateUserRecommendations;
use App\Models\GroupAdmin;
use App\Models\Post;
use App\Models\PostExclusion;
use App\Models\Topic;
use App\Models\User;
use App\Services\NotificationService;
use App\Events\MessageBroadcast;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Managing discussion posts use case (SDD Table 36) + Selective Communication
 * (post exclusion) and Content Moderation (flagging), both part of the
 * Moderation and Inactivity Management Module (SDD 5.2).
 */
class PostController extends Controller
{
    use TracksParticipation, HandlesFlagAutoBlacklist;

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

        // Real-time push is a nice-to-have on top of the post/exclusions
        // above, which are already safely saved by this point either way.
        // It must not be able to take the whole request down when Reverb
        // isn't running (e.g. local dev with Reverb off, or a network
        // blip) - broadcast() with QUEUE_CONNECTION=sync talks to Reverb
        // synchronously right here, so a connection failure needs to be
        // swallowed rather than bubbling up and aborting everything above.
        try {
            broadcast(new MessageBroadcast($post, $post->topic_id));
        } catch (\Throwable $e) {
            report($e);
        }

        return response()->json($post->load('author'), 201);
    }

    /**
     * Server-side authorization for flag(): mirrors StatisticsController's
     * authorizeGroupAccess() pattern and the lecturer dashboard's
     * canFlagInGroup() client-side guard, which this endpoint must enforce
     * for real — an Administrator, OR a Lecturer who belongs to the group
     * (owner, active group admin, or plain member), OR a student who is an
     * active GroupAdmin for the group, may flag content in it.
     */
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

    /** Content moderation: flag (or unflag) a post as irrelevant/spam (SDD Table 31). */
    public function flag(Request $request, Post $post)
    {
        $request->validate(['flagged' => 'nullable|boolean']);

        $flagger = $request->user();
        $post->loadMissing('topic');
        $groupId = $post->topic->group_id ?? null;

        if (! $groupId || ! $this->authorizeFlag($flagger, $groupId)) {
            return response()->json([
                'message' => 'Access denied. You must be an Administrator, a Lecturer in this group, or an active group admin to flag content here.',
            ], Response::HTTP_FORBIDDEN);
        }

        $flagged = $request->boolean('flagged', true);
        $post->update(['is_flagged' => $flagged]);

        $topicTitle = $post->topic->title ?? 'a topic';
        $responseMessage = $flagged ? 'Post flagged for moderation.' : 'Flag removed from post.';

        if ($flagged) {
            // ADDED: flagging previously updated the row silently — no one was
            // ever told. Every Administrator now gets a notification so it
            // actually surfaces (e.g. on the admin dashboard's Inactivity
            // Warnings and Flags list), matching the same
            // NotificationService->send() pattern store() already uses for
            // new-post notifications.
            //
            // NOTE: notifications.type is a strict DB enum (Quiz
            // Announcement, Warning, Blacklist, New Post, Reply, General)
            // that does NOT include 'Post Flagged' — using it throws a
            // QueryException ("Data truncated for column 'type'"). 'General'
            // is used instead; the admin dashboard already detects flag
            // notifications by scanning the message text, not the type.
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

            // Auto-blacklist a member once their flagged content in this
            // group crosses the threshold (see HandlesFlagAutoBlacklist).
            // This trait already existed but was never actually called.
            $author = $post->author;
            $blacklistedBefore = $author ? $author->isBlacklistedIn($groupId) : false;
            $this->autoBlacklistIfFlaggedTooMuch($author, $groupId, $this->notifications);
            if ($author && ! $blacklistedBefore && $author->isBlacklistedIn($groupId)) {
                $responseMessage = 'Post flagged for moderation. The author has been automatically blacklisted from this group after repeated flags.';
            }
        }

        return response()->json(['message' => $responseMessage, 'post' => $post]);
    }

    public function destroy(Post $post)
    {
        $post->delete();

        return response()->json(['message' => 'Post deleted.']);
    }
}
