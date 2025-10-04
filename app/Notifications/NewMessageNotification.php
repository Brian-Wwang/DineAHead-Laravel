<?php

namespace App\Notifications;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;

class NewMessageNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Message $message, public array $tokens) {}

    public function via($notifiable): array
    {
        return [FcmChannel::class];
    }

    public function toFcm($notifiable): FcmMessage
    {
        $title  = 'New message';
        $body   = $this->message->body;
        $convId = (string) $this->message->conversation_id;

        return FcmMessage::create()
            ->setNotification(
                // ✅ v5.x 写法：直接用构造函数
                new FcmNotification($title, $body)
            )
            ->setData([
                'type'           => 'message',
                'conversation_id'=> $convId,
                'message_id'     => (string) $this->message->id,
                'sender_id'      => (string) $this->message->sender_id,
            ])
            ->setTokens($this->tokens); // 批量 tokens
    }
}
