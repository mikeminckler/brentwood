<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Arr;

use App\Http\Requests\ChatSendMessageValidation;

use App\Models\Chat;
use App\Models\User;

use App\Events\ChatMessageCreated;
use App\Events\WhisperCreated;

class ChatController extends Controller
{
    public function sendMessage(ChatSendMessageValidation $request)
    {
        $input = requestInput();
        $chat = (new Chat)->saveChat($input);

        $whispers = collect();

        if (Arr::get($input, 'whisper_id')) {
            $whispers->push(User::findOrFail(Arr::get($input, 'whisper_id')));
        }

        if ($whispers->count()) {
            foreach ($whispers as $user) {
                $chat->whispers()->attach($user);
                broadcast(new WhisperCreated($chat, $user));
            }
        } else {
            broadcast(new ChatMessageCreated($chat))->toOthers();
        }

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
            ->where('created_at', '>', now()->subHours(1))
            ->where(function ($query) {
                $query->whereHas('whispers', function ($query) {
                    $query->where('user_id', auth()->user()->id);
                })
                ->orWhereDoesntHave('whispers')
                ->orWhere('user_id', auth()->user()->id);
            })
            ->with('whispers')
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
