<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Http\Requests\ChatSendMessageValidation;

use App\Models\Chat;

use App\Events\ChatMessageCreated;

class ChatController extends Controller
{
    public function sendMessage(ChatSendMessageValidation $request)
    {
        $chat = new Chat;
        $chat->user_id = auth()->user()->id;
        $chat->room = request('room');
        $chat->message = request('message');

        $chat->save();

        $chat->append('name');

        broadcast(new ChatMessageCreated(request('room'), $chat))->toOthers();

        return response()->json([
            'chat' => $chat,
        ]);
    }

    public function load()
    {
        Validator::make(request()->all(), [
            'room' => 'required',
        ])->validate();

        if (!Chat::canJoinRoom(request('room'))) {
            return response()->json(['error' => 'You do not have permission to load this chat'], 403);
        }

        $chats = Chat::where('room', request('room'))
            ->where('created_at', '>', now()->subMinutes(5))
            ->get()
            ->sortByDesc('created_at')
            ->values();

        return response()->json([
            'chats' => $chats,
        ]);
    }

    public function destroy($id)
    {
        $chat = Chat::findOrFail($id);

        if (!auth()->user()->can('delete', $chat)) {
            return response()->json(['error' => 'You do not have permission to delete chat messages'], 403);
        }

        $chat->delete();

        return response()->json(['success' => 'Message Deleted']);
    }

    public function view($room)
    {
        if (!Chat::canJoinRoom($room)) {
            return redirect('/')->with(['error' => 'You do not have permission to view that chat room']);
        }

        return view('chat.view', compact('room'));
    }
}
