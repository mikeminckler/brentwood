<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Arr;

use App\Traits\PhotosTrait;
use App\Traits\ContentElementTrait;
use App\Utilities\PageLink;

class YoutubeVideo extends Model
{
    use HasFactory;
    use ContentElementTrait;
    use PhotosTrait;

    protected $with = ['photos'];
    protected $appends = ['banner'];

    public function saveContent(array $input, $id = null)
    {
        if ($id >= 1) {
            $youtube_video = YoutubeVideo::findOrFail($id);
        } else {
            $youtube_video = new YoutubeVideo;
        }

        $youtube_video->video_id = Arr::get($input, 'video_id');
        $youtube_video->title = Arr::get($input, 'title');
        $youtube_video->full_width = Arr::get($input, 'full_width');
        $youtube_video->header = Arr::get($input, 'header');
        $youtube_video->body = Arr::get($input, 'body');

        $youtube_video->save();

        $youtube_video->saveSinglePhoto($input);

        cache()->tags([cache_name($youtube_video)])->flush();
        return $youtube_video;
    }

    public function getBannerAttribute()
    {
        return $this->photos->first();
    }

    public function getBodyAttribute($value)
    {
        return PageLink::convertLinkText($value);
    }
}
