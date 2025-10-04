<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Services\ChatService;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function __construct(private ChatService $chat) {}

    public function list(Request $request, int $id) {
        $conv = Conversation::findOrFail($id);
        abort_unless($conv->participants()->where('user_id',$request->user()->id)->exists(), 403);

        $messages = Message::where('conversation_id', $id)
            ->orderBy('id','desc')
            ->paginate(30);

        return response()->json($messages);
    }

    public function send(Request $request, int $id) {
        $conv = Conversation::findOrFail($id);
        abort_unless($conv->participants()->where('user_id',$request->user()->id)->exists(), 403);

        $data = $request->validate([
            'body'=>'required|string|max:5000',
            'meta'=>'nullable|array',
        ]);

        $msg = $this->chat->sendMessage($id, $request->user()->id, $data['body'], $data['meta'] ?? null);
        return response()->json($msg, 201);
    }
}
