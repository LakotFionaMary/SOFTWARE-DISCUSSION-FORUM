<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Concerns\TracksParticipation;
use App\Models\Post;
use App\Models\PostExclusion;
use App\Models\Topic;
use App\Services\NotificationService;
use Illuminate\Http\Request;

/**
 * Managing discussion posts use case (SDD Table 36) + Selective Communication
 * (post exclusion) and Content Moderation (flagging), both part of the
 * Moderation and Inactivity Management Module (SDD 5.2).
 */
class PostController extends Controller
{
    use TracksParticipation;

    public function __construct(private NotificationService $notifications)
    {
    }

    public function index(Request $request, Topic $topic)
    {
        //pk addded
         if (! $request->user()->isMemberOf($topic->group_id)) {
            return response()->json(['message' => 'You must be a member of this topic\'s group to view its posts.'], 403);
        }


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
        // pk added
          if (! $author->isMemberOf($topic->group_id)) {
            return response()->json(['message' => 'You must be a member of this topic\'s group to post here.'], 403);
        }



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

        foreach ($request->input('exclude_user_ids', []) as $excludedUserId) {
            PostExclusion::create(['post_id' => $post->post_id, 'excluded_user_id' => $excludedUserId]);
        }

        $author->update(['last_active_at' => now()]);
        $this->recordParticipation($author, $topic->group_id, 'post');

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
    public function flag(Post $post)
    {
        $post->update(['is_flagged' => true]);

        return response()->json(['message' => 'Post flagged for moderation.', 'post' => $post]);
    }

    public function destroy(Post $post)
    {
        $post->delete();

        return response()->json(['message' => 'Post deleted.']);
    }
}
