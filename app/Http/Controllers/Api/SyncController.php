<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Post;
use App\Models\Quiz;
use App\Models\SyncRecord;
use Illuminate\Http\Request;

/**
 * Synchronize Messages use case (SDD Table 38) for the Java desktop client.
 * Accepts queued offline activities, resolves conflicts server-wins, and
 * returns everything that happened since the client's last sync timestamp.
 */
class SyncController extends Controller
{
    public function sync(Request $request)
    {
        $request->validate([
            'device_type' => 'required|in:Web,Desktop',
            'last_synced_at' => 'nullable|date',
            'queued_actions' => 'nullable|array', // offline-composed posts/replies etc.
        ]);

        $user = $request->user();

        $record = SyncRecord::firstOrCreate(
            ['user_id' => $user->user_id, 'device_type' => $request->device_type],
            ['last_synced_at' => null]
        );

        $since = $request->last_synced_at ?? $record->last_synced_at ?? now()->subYears(10);

        // Step 3-4: server-wins resolution - replay each queued offline
        // action through the same creation path a live client would use,
        // keyed by the client's own client_uuid. That key is what makes
        // this safe to call over and over: if the app crashes before it
        // sees this response, or the same queued_actions batch gets sent
        // again on a later sync, Post::firstOrCreate() below finds the
        // already-created row instead of inserting a second one - the
        // previous version of this method just wrote queued_actions into
        // pending_actions and cleared it, without ever creating the actual
        // post, so a client had no way to know its offline messages had
        // been saved and would keep resending the same ones every sync.
        $syncedActions = [];

        if ($request->filled('queued_actions')) {
            foreach ($request->queued_actions as $action) {
                $clientUuid = $action['client_uuid'] ?? null;
                $type = $action['type'] ?? 'post';

                if (! $clientUuid || ! in_array($type, ['post', 'message'], true)) {
                    // Can't dedupe an action with no client-side id, and
                    // other action types aren't replayed yet - skip rather
                    // than guess, so we never silently drop or duplicate.
                    continue;
                }

                $post = Post::firstOrCreate(
                    ['author_id' => $user->user_id, 'client_uuid' => $clientUuid],
                    [
                        'topic_id' => $action['topic_id'] ?? null,
                        'content' => $action['content'] ?? '',
                        'attachment_url' => $action['attachment_url'] ?? null,
                        'posted_at' => $action['posted_at'] ?? now(),
                    ]
                );

                $syncedActions[] = ['client_uuid' => $clientUuid, 'post_id' => $post->post_id];
            }

            // Nothing left pending once every action above has either been
            // created or already existed - there's no unresolved state to
            // hold onto between requests.
            $record->update(['pending_actions' => null]);
        }

        // Step 5-6: pull everything new since the last sync timestamp.
        $newPosts = Post::whereHas('topic.group.members', fn ($q) => $q->where('users.user_id', $user->user_id))
            ->where('posted_at', '>', $since)
            ->with('author')
            ->get();

        $newQuizzes = Quiz::whereHas('group.members', fn ($q) => $q->where('users.user_id', $user->user_id))
            ->where('created_at', '>', $since)
            ->get();

        $pendingNotifications = Notification::where('user_id', $user->user_id)
            ->where('created_at', '>', $since)
            ->get();

        $record->update(['last_synced_at' => now()]);

        return response()->json([
            'synced_at' => $record->last_synced_at,
            // Tells the client exactly which queued_actions items were
            // (or already had been) saved server-side, by client_uuid, so
            // it knows it's safe to drop those from its local offline
            // queue - and only those. Anything it queued but doesn't see
            // acknowledged here should stay queued and be retried, rather
            // than the old behavior of trusting the whole batch worked.
            'synced_actions' => $syncedActions,
            'new_posts' => $newPosts,
            'new_quizzes' => $newQuizzes,
            'notifications' => $pendingNotifications,
        ]);
    }
}
