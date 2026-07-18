<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

/**
 * Central dispatcher for the Notification Module (SDD 5.10).
 *
 * Persists every notification for durability, then pushes it live to
 * connected clients over a broadcasting channel (WebSocket broker in the
 * SDD). Offline users simply see the persisted, unread row next time they
 * sync or poll GET /api/notifications.
 */
class NotificationService
{
    public function send(User $user, string $type, string $message, ?string $relatedType = null, ?int $relatedId = null): Notification
    {
        $notification = Notification::create([
            'user_id' => $user->user_id,
            'type' => $type,
            'message' => $message,
            'related_type' => $relatedType,
            'related_id' => $relatedId,
            'is_read' => false,
        ]);

        // Push to the user's private channel for online clients. Offline
        // clients pick this up later via the Sync module (SDD 5.4) or by
        // polling the notifications index endpoint.
        //
        // FIXED: this call previously had no error handling, unlike every
        // other broadcast() call in the codebase (see
        // PostController::store() / ReplyController::store()). The
        // notification row above is already safely persisted by this point
        // regardless — a broadcasting failure (invalid Pusher/Reverb
        // credentials, the socket server being offline, etc.) must not take
        // the whole request down. This was surfacing as a 500 on
        // /posts/{id}/flag and /replies/{id}/flag because those endpoints
        // call send() once per Administrator, so any broadcast failure
        // aborted the whole flag action even though the flag itself and its
        // notification rows had already saved.
        try {
            broadcast(new \App\Events\NotificationBroadcast($notification))->toOthers();
        } catch (\Throwable $e) {
            report($e);
        }

        return $notification;
    }

    public function sendToMany(iterable $users, string $type, string $message, ?string $relatedType = null, ?int $relatedId = null): void
    {
        foreach ($users as $user) {
            $this->send($user, $type, $message, $relatedType, $relatedId);
        }
    }
}
