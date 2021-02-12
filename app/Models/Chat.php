<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

use App\Models\User;

class Chat extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $hidden = ['user', 'whispers'];
    protected $appends = ['deleted', 'name', 'whisper_ids'];

    public function saveChat($input)
    {
        $chat = new Chat;
        $chat->user_id = auth()->user()->id;
        $chat->room = Arr::get($input, 'room');
        $chat->message = Arr::get($input, 'message');

        $chat->save();

        $chat->append('name');

        return $chat;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getNameAttribute()
    {
        return $this->user->name;
    }

    public function getDeletedAttribute()
    {
        return $this->deleted_at ? true : false;
    }

    public function getWhisperIdsAttribute()
    {
        if ($this->whispers->count()) {
            return $this->whispers->map->id;
        } else {
            return null;
        }
    }

    public static function canJoinRoom($room)
    {
        $room = explode('.', $room);

        $class_name = 'App\\Models\\'.Str::studly($room[0]);

        $object = resolve($class_name)->find($room[1]);

        if (!$object) {
            return false;
        }

        return auth()->user()->can('chat', $object);
    }

    public static function canModerateRoom($room)
    {
        $room = explode('.', $room);

        $class_name = 'App\\Models\\'.Str::studly($room[0]);

        $object = resolve($class_name)->find($room[1]);

        if (!$object) {
            return false;
        }

        return auth()->user()->can('moderate', $object);
    }

    public function whispers()
    {
        return $this->belongsToMany(User::class, 'whispers');
    }
}
