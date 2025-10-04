<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class BroadcastMessageCreated implements ShouldBroadcast
{
    use InteractsWithSockets;

    public function __construct(public Message $message) {}

    /**
     * 指定广播频道
     */
    public function broadcastOn(): array
    {
        return ['private-conversation.' . $this->message->conversation_id];
    }

    /**
     * 指定广播事件名（前端 Echo 监听的事件名）
     */
    public function broadcastAs(): string
    {
        return 'message.created';
    }

    /**
     * 广播数据内容（前端接收 payload）
     */
    public function broadcastWith(): array
    {
        return [
            'id'             => $this->message->id,
            'conversation_id'=> $this->message->conversation_id,
            'sender_id'      => $this->message->sender_id,
            'body'           => $this->message->body,
            'created_at'     => $this->message->created_at?->toISOString(),
        ];
    }
}
