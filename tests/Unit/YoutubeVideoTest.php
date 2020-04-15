<?php

namespace Tests\Unit;

use Tests\TestCase;

use App\Photo;
use App\YoutubeVideo;
use Tests\Unit\PageLinkTestTrait;

class YoutubeVideoTest extends TestCase
{

    use PageLinkTestTrait;

    protected function getModel()
    {
        return factory(YoutubeVideo::class)->create();
    }

    protected function getLinkFields()
    {
        return [
            'body',
        ];
    }


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
