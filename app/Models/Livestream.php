<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Arr;

use App\Traits\TagsTrait;

class Livestream extends Model
{
    use HasFactory;
    use TagsTrait;

    protected $with = ['tags'];

    protected $dates = ['start_date'];

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
        $livestream->save();

        $livestream->saveTags($input);

        return $livestream;
    }

    public function inquiries() 
    {
        return $this->belongsToMany(Inquiry::class)->withPivot('url');
    }
}
