<?php

namespace App\Listeners;

use App\Events\BroadcastMessageCreated;
use App\Events\MessageSent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Broadcast;

class SendRealtimeBroadcast implements ShouldQueue
{
    public function handle(MessageSent $event): void
    {
        $message = $event->message;

        // 广播到会话私有频道
        broadcast(new BroadcastMessageCreated($message))->toOthers();
    }
}
