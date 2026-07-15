<?php
 
use App\Models\Topic;
use Illuminate\Support\Facades\Broadcast;

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