<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\YoutubeVideo;
use App\ContentElement;
use App\User;
use Illuminate\Support\Arr;

class YoutubeVideoTest extends TestCase
{

    /** @test **/
    public function a_youtube_video_content_element_can_be_created()
    {

        $input = factory(ContentElement::class)->states('youtube-video')->raw();
        $input['type'] = 'youtube-video';
        $input['content'] = factory(YoutubeVideo::class)->raw();

        $this->json('POST', route('content-elements.store'), [])
            ->assertStatus(401);

        $this->signIn( factory(User::class)->create());

        $this->json('POST', route('content-elements.store'), [])
             ->assertStatus(403);

        $this->signInAdmin();

        $this->json('POST', route('content-elements.store'), [])
             ->assertStatus(422)
             ->assertJsonValidationErrors([
                 'type',
             ]);

        $this->json('POST', route('content-elements.store'), ['type' => 'youtube-video'])
             ->assertStatus(422)
             ->assertJsonValidationErrors([
                'page_id',
                //'content.video_id',
             ]);

        $this->withoutExceptionHandling();
        $this->json('POST', route('content-elements.store'), $input)
             ->assertSuccessful()
             ->assertJsonFragment([
                'success' => 'Youtube Video Saved',
             ]);

        $youtube_video = YoutubeVideo::all()->last();
        $this->assertEquals(Arr::get($input, 'content.video_id'), $youtube_video->video_id);

    }

    /** @test **/
    public function a_youtube_video_can_be_updated()
    {
        $youtube_video = factory(YoutubeVideo::class)->create();
        $content_element = $youtube_video->contentElement;

        $this->assertInstanceOf(ContentElement::class, $content_element);

        $this->json('POST', route('content-elements.update', ['id' => $content_element->id]), [])
            ->assertStatus(401);

        $this->signIn( factory(User::class)->create());

        $this->json('POST', route('content-elements.update', ['id' => $content_element->id]), [])
             ->assertStatus(403);

        $this->signInAdmin();

        $this->json('POST', route('content-elements.update', ['id' => $content_element->id]), [])
             ->assertStatus(422)
             ->assertJsonValidationErrors([
                 'type',
             ]);

        $this->json('POST', route('content-elements.update', ['id' => $content_element->id]), ['type' => 'youtube-video'])
             ->assertStatus(422)
             ->assertJsonValidationErrors([
                'page_id',
                'content.video_id',
             ]);

        $input = $content_element->toArray();
        $youtube_video_input = factory(YoutubeVideo::class)->raw();
        $input['content']['video_id'] = Arr::get($youtube_video_input, 'video_id');

        $this->json('POST', route('content-elements.update', ['id' => $content_element->id]), $input)
             ->assertSuccessful()
             ->assertJsonFragment([
                'success' => 'Youtube Video Saved',
                'video_id' => Arr::get($input, 'content.video_id'),
             ]);

        $content_element->refresh();
        $youtube_video->refresh();
        $this->assertEquals(Arr::get($input, 'content.video_id'), $youtube_video->video_id);
    }

}
