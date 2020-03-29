<?php

namespace Tests\Unit;

use Tests\TestCase;

use App\Photo;
use App\YoutubeVideo;

class YoutubeVideoTest extends TestCase
{

    /** @test **/
    public function a_youtube_video_has_a_banner_image()
    {
        $photo = factory(Photo::class)->states('youtube-video')->create();
        $youtube_video = $photo->content;
        $this->assertInstanceOf(YoutubeVideo::class, $youtube_video);
        $this->assertInstanceOf(Photo::class, $youtube_video->photos->first());
        $this->assertTrue($youtube_video->photos->contains('id', $photo->id));
        $this->assertEquals($youtube_video->banner->id, $photo->id);
    }
}
