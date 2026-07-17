<?php

namespace App\Events;

use App\Models\Post;
use App\Models\Reply;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageBroadcast implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public $reply;
    public $topicId;

    /**
     * Pass the newly created reply so it's sent to the frontend.
     */
    public function __construct( $reply, $topicId)
    {
        // Load the author relation so the frontend knows who typed it
        $this->reply = $reply->load('author');
        $this->topicId = $topicId;
    }

    /**
     * Broadcast on the presence channel for this specific topic.
     */
    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('topic.' . $this->topicId),
        ];
    }

    /**
     * The event name Echo will listen for.
     */
    public function broadcastAs(): string
    {
        return 'MessageBroadcast';
    }

    /**
     * Selective communication: the topic channel is shared by every group
     * member, but a post can exclude specific users. Since Presence
     * channels can't drop individual subscribers, include the excluded
     * user ids so the frontend can skip rendering for them (see
     * post_exclusions / PostController::index()).
     */
    public function broadcastWith(): array
    {
        $excludedUserIds = $this->reply instanceof \App\Models\Post
            ? $this->reply->exclusions()->pluck('excluded_user_id')->all()
            : [];

        return [
            'reply' => $this->reply,
            'topicId' => $this->topicId,
            'excluded_user_ids' => $excludedUserIds,
        ];
    }
}