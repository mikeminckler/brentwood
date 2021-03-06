<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Arr;
use App\Models\User;

use App\Traits\TagsTrait;
use App\Traits\HasPermissionsTrait;
use App\Traits\AppendAttributesTrait;

class Livestream extends Model
{
    use HasFactory;
    use TagsTrait;
    use HasPermissionsTrait;
    use AppendAttributesTrait;

    protected $with = ['tags', 'moderators'];

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
        $livestream->saveModerators($input);

        return $livestream;
    }

    public function saveModerators($input)
    {
        $moderators = collect(Arr::get($input, 'moderators'))->pluck('id')->toArray();
        $this->moderators()->sync($moderators);
        return $this;
    }

    public function moderators()
    {
        return $this->belongsToMany(User::class, 'livestream_moderator');
    }

    public function inquiries()
    {
        return $this->belongsToMany(Inquiry::class)->withPivot('url', 'reminder_email_sent_at');
    }

    public function getDateAttribute()
    {
        return $this->start_date->timezone('America/Vancouver')->format('l F jS g:ia');
    }

    public function getInquiryUsersAttribute()
    {
        return $this->inquiries->map(function ($inquiry) {
            $user = $inquiry->user;
            $user->pivot = $inquiry->pivot;
            return $user;
        });
    }

    public function getChatRoomAttribute()
    {
        return 'livestream.'.$this->id;
    }
}
