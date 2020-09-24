<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

use App\Models\YoutubeVideo;
use App\Models\ContentElement;
use App\Models\User;
use App\Models\FileUpload;
use App\Models\Photo;
use App\Models\Page;

class YoutubeVideoTest extends TestCase
{

    /** @test **/
    public function a_youtube_video_content_element_can_be_created()
    {
        $input = $this->createContentElement(YoutubeVideo::factory())->toArray();
        $input['id'] = 0;
        $input['type'] = 'youtube-video';
        $input['content'] = YoutubeVideo::factory()->text()->raw();
        $page = Page::factory()->create();
        $input['pivot'] = [
            'contentable_id' => $page->id,
            'contentable_type' => get_class($page),
            'sort_order' => 1,
            'unlisted' => false,
            'expandable' => false,
        ];

        $this->json('POST', route('content-elements.store'), [])
            ->assertStatus(401);

        $this->signIn(User::factory()->create());

        $this->json('POST', route('content-elements.store'), [])
             ->assertStatus(422)
             ->assertJsonValidationErrors([
                 'pivot.contentable_id',
                 'pivot.contentable_type',
             ]);

        $this->json('POST', route('content-elements.store'), ['pivot' => ['contentable_id' => $page->id, 'contentable_type' => 'page']])
             ->assertStatus(403);

        $this->signInAdmin();

        $this->json('POST', route('content-elements.store'), ['pivot' => ['contentable_id' => $page->id, 'contentable_type' => 'page']])
             ->assertStatus(422)
             ->assertJsonValidationErrors([
                 'type',
             ]);

        $this->json('POST', route('content-elements.store'), ['type' => 'youtube-video', 'pivot' => ['contentable_id' => $page->id, 'contentable_type' => 'page']])
             ->assertStatus(422)
             ->assertJsonValidationErrors([
                'pivot.sort_order',
                'pivot.unlisted',
                'pivot.expandable',
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
        $this->assertEquals(Arr::get($input, 'content.full_width'), $youtube_video->full_width);
        $this->assertEquals(Arr::get($input, 'content.header'), $youtube_video->header);
        $this->assertEquals(Arr::get($input, 'content.body'), $youtube_video->body);
    }

    /** @test **/
    public function a_youtube_video_can_be_updated()
    {
        $content_element = $this->createContentElement(YoutubeVideo::factory());
        $youtube_video = $content_element->content;
        $page = $content_element->pages->first();

        $this->assertInstanceOf(ContentElement::class, $content_element);

        $this->json('POST', route('content-elements.update', ['id' => $content_element->id]), [])
            ->assertStatus(401);

        $this->signIn(User::factory()->create());

        $this->json('POST', route('content-elements.update', ['id' => $content_element->id]), [])
             ->assertStatus(422)
             ->assertJsonValidationErrors([
                 'pivot.contentable_id',
                 'pivot.contentable_type',
             ]);

        $this->json('POST', route('content-elements.update', ['id' => $content_element->id]), ['pivot' => ['contentable_id' => $page->id, 'contentable_type' => 'page']])
             ->assertStatus(403);

        $this->signInAdmin();

        $this->json('POST', route('content-elements.update', ['id' => $content_element->id]), ['pivot' => ['contentable_id' => $page->id, 'contentable_type' => 'page']])
             ->assertStatus(422)
             ->assertJsonValidationErrors([
                 'type',
             ]);

        $this->json('POST', route('content-elements.update', ['id' => $content_element->id]), ['type' => 'youtube-video', 'pivot' => ['contentable_id' => $page->id, 'contentable_type' => 'page']])
             ->assertStatus(422)
             ->assertJsonValidationErrors([
                'pivot.sort_order',
                'pivot.unlisted',
                'pivot.expandable',
             ]);

        $input = $content_element->toArray();
        $youtube_video_input = YoutubeVideo::factory()->raw();
        $input['content']['video_id'] = Arr::get($youtube_video_input, 'video_id');
        $input['content']['title'] = Arr::get($youtube_video_input, 'title');
        $input['pivot'] = [
            'contentable_id' => $page->id,
            'contentable_type' => get_class($page),
            'sort_order' => 1,
            'unlisted' => false,
            'expandable' => false,
        ];

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
        $content_element = $this->createContentElement(YoutubeVideo::factory());
        $youtube_video = $content_element->content;

        Storage::fake();
        $file_name = Str::lower(Str::random().'.jpg');
        $file = UploadedFile::fake()->image($file_name);
        $file_upload = (new FileUpload)->saveFile($file, 'photos', true);

        $photo_input = Photo::factory()->raw();
        $photo_input['file_upload'] = $file_upload;

        $input = $content_element->toArray();
        $youtube_video_input = YoutubeVideo::factory()->raw();
        $input['content']['video_id'] = Arr::get($youtube_video_input, 'video_id');
        $input['content']['title'] = Arr::get($youtube_video_input, 'title');
        $input['content']['photos'] = [$photo_input];
        $page = Page::factory()->create();
        $input['pivot'] = [
            'contentable_id' => $page->id,
            'contentable_type' => get_class($page),
            'sort_order' => 1,
            'unlisted' => false,
            'expandable' => false,
        ];

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

        $this->assertTrue($youtube_video->photos->contains(function ($p) use ($file_name) {
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

        $photo_input = Photo::factory()->raw();
        $photo_input['file_upload'] = $file_upload;

        $input = $content_element->toArray();
        $youtube_video_input = YoutubeVideo::factory()->raw();
        $input['content']['video_id'] = Arr::get($youtube_video_input, 'video_id');
        $input['content']['photos'] = [$photo_input];
        $page = Page::factory()->create();
        $input['pivot'] = [
            'contentable_id' => $page->id,
            'contentable_type' => get_class($page),
            'sort_order' => 1,
            'unlisted' => false,
            'expandable' => false,
        ];

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
