<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

use App\Models\User;

class Chat extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $hidden = ['user'];
    protected $appends = ['deleted', 'name'];

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
}
