<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Quote;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use App\Photo;
use App\FileUpload;
use App\User;
use App\Page;
use App\ContentElement;
use App\FeaturedPhoto;

class FeaturedPhotoTest extends TestCase
{

    /** @test **/
    public function a_featured_photo_content_element_can_be_created()
    {

        Storage::fake();
        $file_name = Str::random().'.jpg';
        $file = UploadedFile::fake()->image($file_name);
        $file_upload = (new FileUpload)->saveFile($file, 'photos', true);

        $photo_input = factory(Photo::class)->raw();
        $photo_input['file_upload'] = $file_upload;

        $input = factory(ContentElement::class)->states('featured-photo')->raw();
        $input['type'] = 'featured-photo';
        $input['content'] = factory(FeaturedPhoto::class)->raw();
        $input['content']['photos'] = [$photo_input];
        $page = factory(Page::class)->create();
        $input['pivot'] = [
            'page_id' => $page->id,
            'sort_order' => 1,
            'unlisted' => false,
            'expandable' => false,
        ];

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

        $this->json('POST', route('content-elements.store'), ['type' => 'featured-photo'])
             ->assertStatus(422)
             ->assertJsonValidationErrors([
                'pivot.page_id',
                'pivot.sort_order',
                'pivot.unlisted',
                'pivot.expandable',
             ]);

        $this->withoutExceptionHandling();
        $this->json('POST', route('content-elements.store'), $input)
             ->assertSuccessful()
             ->assertJsonFragment([
                'success' => 'Featured Photo Saved',
             ]);

        $featured_photo = FeaturedPhoto::all()->last();
        $this->assertEquals(Arr::get($input, 'content.body'), $featured_photo->body);
        $this->assertEquals(Arr::get($input, 'content.header'), $featured_photo->header);

        $photo = $featured_photo->photos->first();
        $this->assertInstanceOf(Photo::class, $photo);
        $this->assertEquals(Arr::get($photo_input, 'name'), $photo->name);
        $this->assertEquals(Arr::get($photo_input, 'description'), $photo->description);
        $this->assertEquals(Arr::get($photo_input, 'alt'), $photo->alt);
        $this->assertEquals($photo->fileUpload->id, $file_upload->id);
    }

}
