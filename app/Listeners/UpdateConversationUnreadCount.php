<?php

namespace App\Listeners;

use App\Events\MessageSent;
use App\Models\ConversationParticipant;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateConversationUnreadCount implements ShouldQueue
{
    public function handle(MessageSent $event): void
    {
        $msg = $event->message;

        ConversationParticipant::where('conversation_id', $msg->conversation_id)
            ->where('user_id', '!=', $msg->sender_id)
            ->increment('unread_count');
    }
}
