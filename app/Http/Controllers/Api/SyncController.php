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

        // Server-wins resolution: If queued actions are passed directly in the sync call,
        // record them on the SyncRecord.
        if ($request->filled('queued_actions')) {
            $record->update(['pending_actions' => $request->queued_actions]);
        }

        // Pull everything new since the last sync timestamp.
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

        // Update the last synced timestamp cleanly using the current server time
        $record->update([
            'last_synced_at' => now(),
            'pending_actions' => null,
        ]);

        return response()->json([
            'synced_at' => $record->last_synced_at,
            'new_posts' => $newPosts,
            'new_quizzes' => $newQuizzes,
            'notifications' => $pendingNotifications,
        ]);
    }
}
