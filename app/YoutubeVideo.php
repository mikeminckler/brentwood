<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use App\YoutubeVideo;
use Illuminate\Support\Arr;
use App\PhotosTrait;
use App\ContentElementTrait;
use App\PageLink;

class YoutubeVideo extends Model
{
    use ContentElementTrait;
    use PhotosTrait;

    protected $with = ['photos'];
    protected $appends = ['banner'];

    public function saveContent($id = null, $input) 
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
