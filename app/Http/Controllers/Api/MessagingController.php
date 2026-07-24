<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Events\MessageBroadcast;
use App\Models\Group;
use App\Models\Post;
use App\Models\PostExclusion;
use Illuminate\Http\Request;
/**
 * Messaging And Synchronization Module (SDD 5.4) - "Real-Time Messaging"
 * use case (Table 37). Wraps post creation with an immediate WebSocket
 * broadcast, honoring selective-communication exclusions and running the
 * message through the same content-moderation flagging rules.
 */
class MessagingController extends Controller
{
    private const FLOOD_LIMIT_PER_MINUTE = 10;
    public function send(Request $request, Group $group)
    {
        $request->validate([
            'topic_id' => 'required|integer|exists:topics,topic_id',
            'content' => 'required|string',
            'exclude_user_ids' => 'nullable|array',
            'client_uuid' => 'nullable|string|max:255',
        ]);
        $sender = $request->user();
        if ($sender->isBlacklistedIn($group->group_id)) {
            return response()->json(['message' => 'You are blacklisted from messaging in this group.'], 403);
        }
        // Already-synced retry: the client resent a message we've already
        // saved. Return the existing post instead of creating a duplicate.
        if ($request->filled('client_uuid')) {
            $existing = Post::where('author_id', $sender->user_id)
                ->where('client_uuid', $request->client_uuid)
                ->first();
            if ($existing) {
                return response()->json($existing->load('author'), 200);
            }
        }
        // Content moderation failure: automated flooding rule.
        $recentCount = Post::where('author_id', $sender->user_id)
            ->where('posted_at', '>=', now()->subMinute())
            ->count();
        if ($recentCount >= self::FLOOD_LIMIT_PER_MINUTE) {
            return response()->json(['message' => 'Message blocked: flooding rule triggered.'], 429);
        }
        $post = Post::create([
            'topic_id' => $request->topic_id,
            'author_id' => $sender->user_id,
            'content' => $request->content,
            'client_uuid' => $request->client_uuid,
            'posted_at' => now(),
        ]);
        foreach ($request->input('exclude_user_ids', []) as $excludedUserId) {
            PostExclusion::create(['post_id' => $post->post_id, 'excluded_user_id' => $excludedUserId]);
        }
        $sender->update(['last_active_at' => now()]);
        // Step 7-8: broadcast to the group channel; excluded members are
        // filtered out inside the broadcast event's broadcastToOthers logic.
        // Wrapped like PostController/ReplyController's broadcasts: this is
        // a nice-to-have on top of the post above, which is already safely
        // saved by this point either way.
        try {
            broadcast(new MessageBroadcast($post->topic_id, $post))->toOthers();
        } catch (\Throwable $e) {
            report($e);
        }
        return response()->json($post->load('author'), 201);
    }
}
