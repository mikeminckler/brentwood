<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use App\ContentElementTrait;

use App\YoutubeVideo;
use Illuminate\Support\Arr;

class YoutubeVideo extends Model
{
    use ContentElementTrait;

    public function saveContent($id = null, $input) 
    {
        if ($id >= 1) {
            $youtube_video = YoutubeVideo::findOrFail($id);
        } else {
            $youtube_video = new YoutubeVideo;
        }

        $youtube_video->video_id = Arr::get($input, 'video_id');

        $youtube_video->save();

        cache()->tags([cache_name($youtube_video)])->flush();
        return $youtube_video;
    }
}
