<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Conversation;

Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
    $conv = Conversation::find($conversationId);
    if (!$conv) return false;

    // 只有参与者才能订阅
    return $conv->participants()->where('user_id', $user->id)->exists();
});

Broadcast::channel('user.{id}', function ($user, $id) {
    // 用户自己的私有通道（推未读数等）
    return (int) $user->id === (int) $id;
});
