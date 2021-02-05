<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Arr;

use App\Traits\TagsTrait;
use App\Traits\HasPermissionsTrait;

class Livestream extends Model
{
    use HasFactory;
    use TagsTrait;
    use HasPermissionsTrait;

    protected $with = ['tags'];

    protected $dates = ['start_date'];

    protected $appends = ['chat_room', 'roles', 'users'];

    public function saveLivestream($input, $id = null)
    {
        if ($id) {
            $livestream = Livestream::findOrFail($id);
        } else {
            $livestream = new Livestream;
        }

        $livestream->name = Arr::get($input, 'name');
        $livestream->video_id = Arr::get($input, 'video_id');
        $livestream->start_date = Arr::get($input, 'start_date');
        $livestream->length = Arr::get($input, 'length'); // in minutes
        $livestream->enable_chat = Arr::get($input, 'enable_chat');
        $livestream->save();

        $livestream->saveTags($input);
        $livestream->saveRoles($input);

        return $livestream;
    }

    public function inquiries()
    {
        return $this->belongsToMany(Inquiry::class)->withPivot('url');
    }

    public function getDateAttribute()
    {
        return $this->start_date->timezone('America/Vancouver')->format('l F jS g:ia');
    }

    public function getInquiryUsersAttribute()
    {
        return $this->inquiries->map(function ($inquiry) {
            return $inquiry->user;
        });
    }

    public function getChatRoomAttribute()
    {
        return 'livestream.'.$this->id;
    }
}
