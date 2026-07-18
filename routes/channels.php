<?php
 
use App\Models\Topic;
use Illuminate\Support\Facades\Broadcast;

/**
 * A user may only listen on their own private notification channel — this
 * is what App\Events\NotificationBroadcast actually broadcasts on
 * (PrivateChannel('user.'.$notification->user_id)). This authorization
 * callback was missing entirely, which is why subscribing to it from the
 * frontend (Echo/Pusher) failed with "Authentication signature invalid":
 * there was no route for the broadcasting auth endpoint to authorize the
 * channel against, so it never returned a valid signature.
 */
Broadcast::channel('user.{userId}', function ($user, int $userId) {
    return (int) $user->user_id === $userId;
}, ['guards' => ['sanctum']]);

/**
 * Only members of a topic's group may listen on its channel — mirrors the
 * same isMemberOf() check used in PostController/ReplyController.
 */
Broadcast::channel('topic.{topicId}', function ($user, int $topicId) {
    $topic = Topic::find($topicId);

    if (! $topic) {
        return false;
    }

    // Check membership
    if ($user->isMemberOf($topic->group_id)) {
        // MUST return an array of user details for Presence Channels
        return [
            'user_id'   => $user->user_id,
            'full_name' => $user->full_name ?? $user->name,
        ];
    }

    return false;
},['guards' => ['sanctum']]);