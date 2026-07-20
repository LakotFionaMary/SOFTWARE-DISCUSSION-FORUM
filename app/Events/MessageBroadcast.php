<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;


class MessageBroadcast implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The ID of the topic being broadcasted to.
     *
     * @var int
     */
    public $topicId;

    /**
     * The reply or post model/array instance.
     *
     * @var mixed
     */
    public $reply;

    /**
     * Array of user IDs excluded from viewing this message branch.
     *
     * @var array
     */
    public $excludedUserIds;

    /**
     * Create a new event instance.
     *
     * @param int $topicId
     * @param mixed $reply
     * @param array $excludedUserIds
     * @return void
     */
    public function __construct($topicId, $reply, array $excludedUserIds = [])
    {
        $this->topicId = $topicId;
        $this->reply = $reply;
        $this->excludedUserIds = $excludedUserIds;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        // Matches the frontend Echo.join(`topic.${topicId}`) expectation
        return [
            new PresenceChannel('topic.' . $this->topicId),
        ];
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs(): string
    {
        return 'MessageBroadcast';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith(): array
    {
        $excludedUserIds = $this->reply instanceof \App\Models\Post
            ? $this->reply->exclusions()->pluck('excluded_user_id')->all()
            : [];

        return [
            'reply' => $this->reply,
            'excluded_user_ids' => $this->excludedUserIds,
        ];
    }
}