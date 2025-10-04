<?php

namespace App\Listeners;

use App\Events\MessageSent;
use App\Models\ConversationParticipant;
use App\Models\DeviceToken;
use App\Notifications\NewMessageNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendFcmIfOffline implements ShouldQueue
{
    public function handle(MessageSent $event): void
    {
        $msg = $event->message;

        $recipients = ConversationParticipant::where('conversation_id', $msg->conversation_id)
            ->where('user_id', '!=', $msg->sender_id)
            ->pluck('user_id');

        foreach ($recipients as $userId) {
            if (!is_user_online($userId)) {
                // 给该用户的所有设备发
                $tokens = DeviceToken::where('user_id', $userId)->pluck('token')->all();
                if (!empty($tokens)) {
                    // 触发通知（FCM 渠道）
                    (new \App\Models\User(['id'=>$userId])) // 虚拟实例仅用于通知路由
                        ->notify(new NewMessageNotification($msg, $tokens));
                }
            }
        }
    }
}
