<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\BannerPhoto;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use App\Photo;
use App\FileUpload;
use App\User;
use App\Page;
use App\ContentElement;

class BannerPhotoTest extends TestCase
{

    /** @test **/
    public function a_banner_photo_content_element_can_be_created()
    {

        Storage::fake();
        $file_name = Str::random().'.jpg';
        $file = UploadedFile::fake()->image($file_name);
        $file_upload = (new FileUpload)->saveFile($file, 'photos', true);

        $photo_input = factory(Photo::class)->raw();
        $photo_input['file_upload'] = $file_upload;

        $input = factory(ContentElement::class)->states('banner-photo')->raw();
        $input['type'] = 'banner-photo';
        $input['content'] = factory(BannerPhoto::class)->raw();
        $input['content']['photos'] = [$photo_input];
        $page = factory(Page::class)->create();
        $input['pivot'] = [
            'contentable_id' => $page->id,
            'contentable_type' => get_class($page),
            'sort_order' => 1,
            'unlisted' => false,
            'expandable' => false,
        ];

        $this->json('POST', route('content-elements.store'), [])
            ->assertStatus(401);

        $this->signIn( factory(User::class)->create());

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

        $this->json('POST', route('content-elements.store'), ['type' => 'banner-photo', 'pivot' => ['contentable_id' => $page->id, 'contentable_type' => 'page']])
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
                'success' => 'Banner Photo Saved',
             ]);

        $banner_photo = BannerPhoto::all()->last();
        $this->assertEquals(Arr::get($input, 'content.body'), $banner_photo->body);
        $this->assertEquals(Arr::get($input, 'content.header'), $banner_photo->header);

        $photo = $banner_photo->photos->first();
        $this->assertInstanceOf(Photo::class, $photo);
        $this->assertEquals(Arr::get($photo_input, 'name'), $photo->name);
        $this->assertEquals(Arr::get($photo_input, 'description'), $photo->description);
        $this->assertEquals(Arr::get($photo_input, 'alt'), $photo->alt);
        $this->assertEquals($photo->fileUpload->id, $file_upload->id);
    }
}
