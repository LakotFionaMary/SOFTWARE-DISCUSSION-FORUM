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
}