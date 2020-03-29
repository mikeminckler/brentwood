<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use App\YoutubeVideo;
use App\ContentElement;
use App\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\FileUpload;
use App\Photo;

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
        $this->assertEquals(Arr::get($input, 'content.title'), $youtube_video->title);

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
                //'content.video_id',
             ]);

        $input = $content_element->toArray();
        $youtube_video_input = factory(YoutubeVideo::class)->raw();
        $input['content']['video_id'] = Arr::get($youtube_video_input, 'video_id');
        $input['content']['title'] = Arr::get($youtube_video_input, 'title');

        $this->json('POST', route('content-elements.update', ['id' => $content_element->id]), $input)
             ->assertSuccessful()
             ->assertJsonFragment([
                'success' => 'Youtube Video Saved',
                'video_id' => Arr::get($input, 'content.video_id'),
                'title' => Arr::get($input, 'content.title'),
             ]);

        $content_element->refresh();
        $youtube_video->refresh();
        $this->assertEquals(Arr::get($input, 'content.video_id'), $youtube_video->video_id);
        $this->assertEquals(Arr::get($input, 'content.title'), $youtube_video->title);
    }

    /** @test **/
    public function a_banner_image_can_be_saved_for_a_youtube_video()
    {
        $this->signInAdmin();
        $youtube_video = factory(YoutubeVideo::class)->create();
        $content_element = $youtube_video->contentElement;

        Storage::fake();
        $file_name = Str::random().'.jpg';
        $file = UploadedFile::fake()->image($file_name);
        $file_upload = (new FileUpload)->saveFile($file, 'photos', true);

        $photo_input = factory(Photo::class)->raw();
        $photo_input['file_upload'] = $file_upload;

        $input = $content_element->toArray();
        $youtube_video_input = factory(YoutubeVideo::class)->raw();
        $input['content']['video_id'] = Arr::get($youtube_video_input, 'video_id');
        $input['content']['title'] = Arr::get($youtube_video_input, 'title');
        $input['content']['photos'] = [$photo_input];

        $this->withoutExceptionHandling();
        $this->json('POST', route('content-elements.update', ['id' => $content_element->id]), $input)
             ->assertSuccessful()
             ->assertJsonFragment([
                'success' => 'Youtube Video Saved',
                'video_id' => Arr::get($input, 'content.video_id'),
                'title' => Arr::get($input, 'content.title'),
             ]);

        $content_element->refresh();
        $youtube_video->refresh();
        $this->assertEquals(Arr::get($input, 'content.video_id'), $youtube_video->video_id);
        $this->assertEquals(Arr::get($input, 'content.title'), $youtube_video->title);
        $this->assertEquals(1, $youtube_video->photos->count());

        $this->assertTrue($youtube_video->photos->contains(function($p) use($file_name) {
            return $p->fileUpload->name === $file_name;
        }));

        $photo = $youtube_video->photos->first();
        $this->assertInstanceOf(YoutubeVideo::class, $photo->content);
        $this->assertEquals($youtube_video->id, $photo->content->id);

        //Storage::assertExists('photos/'.$file->hashName());
        $this->assertInstanceOf(Photo::class, $photo);
        $this->assertEquals(Arr::get($photo_input, 'name'), $photo->name);
        $this->assertEquals(Arr::get($photo_input, 'description'), $photo->description);
        $this->assertEquals(Arr::get($photo_input, 'alt'), $photo->alt);
        $this->assertEquals($photo->fileUpload->id, $file_upload->id);

        $file_name = Str::random().'.jpg';
        // check that saving a new photo removes the old photo
        $file = UploadedFile::fake()->image($file_name);
        $file_upload = (new FileUpload)->saveFile($file, 'photos', true);

        $photo_input = factory(Photo::class)->raw();
        $photo_input['file_upload'] = $file_upload;

        $input = $content_element->toArray();
        $youtube_video_input = factory(YoutubeVideo::class)->raw();
        $input['content']['video_id'] = Arr::get($youtube_video_input, 'video_id');
        $input['content']['photos'] = [$photo_input];

        $this->json('POST', route('content-elements.update', ['id' => $content_element->id]), $input)
             ->assertSuccessful()
             ->assertJsonFragment([
                'success' => 'Youtube Video Saved',
                'video_id' => Arr::get($input, 'content.video_id'),
                'title' => Arr::get($input, 'content.title'),
             ]);

        $content_element->refresh();
        $youtube_video->refresh();
        $this->assertEquals(1, $youtube_video->photos->count());
    }

}
