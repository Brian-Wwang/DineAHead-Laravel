<?php

namespace App\Services;

use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Message;
use Illuminate\Support\Facades\DB;

class ChatService
{
    public function findOrCreatePrivateConversation(int $userA, int $userB): Conversation
    {
        // 两人私聊按参与者集合唯一
        $conv = Conversation::where('type','private')
            ->whereHas('participants', fn($q)=>$q->where('user_id',$userA))
            ->whereHas('participants', fn($q)=>$q->where('user_id',$userB))
            ->first();

        if ($conv) return $conv;

        return DB::transaction(function() use($userA,$userB){
            $conv = Conversation::create(['type'=>'private']);
            ConversationParticipant::create([
                'conversation_id'=>$conv->id,'user_id'=>$userA
            ]);
            ConversationParticipant::create([
                'conversation_id'=>$conv->id,'user_id'=>$userB
            ]);
            return $conv;
        });
    }

    public function sendMessage(int $conversationId, int $senderId, string $body, ?array $meta=null): Message
    {
        $msg = Message::create([
            'conversation_id'=>$conversationId,
            'sender_id'=>$senderId,
            'body'=>$body,
            'meta'=>$meta,
        ]);

        // 触发事件 -> 实时 + 未读 + FCM
        event(new MessageSent($msg));

        // 发送者自己可顺带更新 last_read
        ConversationParticipant::where([
            'conversation_id'=>$conversationId,
            'user_id'=>$senderId,
        ])->update([
            'last_read_message_id'=>$msg->id,
        ]);

        return $msg;
    }

    public function markAsRead(int $conversationId, int $userId, ?int $untilMessageId=null): void
    {
        $lastId = $untilMessageId ?? Message::where('conversation_id',$conversationId)->max('id');

        ConversationParticipant::where([
            'conversation_id'=>$conversationId,
            'user_id'=>$userId,
        ])->update([
            'last_read_message_id'=>$lastId,
            'unread_count'=>0,
        ]);
    }
}
