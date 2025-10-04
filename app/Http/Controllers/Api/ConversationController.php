<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Services\ChatService;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    public function __construct(private ChatService $chat) {}

    public function getOrCreatePrivate(Request $request) {
        $data = $request->validate(['peer_id'=>'required|integer|exists:users,id']);
        $conv = $this->chat->findOrCreatePrivateConversation($request->user()->id, $data['peer_id']);
        return response()->json($conv->load('participants'));
    }

    public function show(Request $request, int $id) {
        $conv = Conversation::with(['participants.user:id,name'])->findOrFail($id);
        // 鉴权：必须是参与者
        abort_unless($conv->participants()->where('user_id',$request->user()->id)->exists(), 403);
        return response()->json($conv);
    }

    public function markAsRead(Request $request, int $id) {
        $data = $request->validate(['until_message_id'=>'nullable|integer']);
        $this->chat->markAsRead($id, $request->user()->id, $data['until_message_id'] ?? null);
        return response()->json(['success'=>true]);
    }
}
