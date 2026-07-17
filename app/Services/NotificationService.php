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

        // FIXED: broadcast() throws a BroadcastException whenever the
        // configured WebSocket server (Pusher/soketi/Reverb) isn't
        // reachable — e.g. nothing listening on 127.0.0.1:8080 locally.
        // That exception was propagating straight out of send(), turning
        // an otherwise-successful notification save into a 500 for the
        // whole request (this is what was happening to /posts/{id}/flag
        // and /replies/{id}/flag after the type/role fixes: the row saved
        // fine, then this line blew up). Real-time push is a nice-to-have
        // on top of the persisted row — offline/non-connected clients
        // already fall back to polling GET /notifications — so a failure
        // here is logged and swallowed instead of failing the request.
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
