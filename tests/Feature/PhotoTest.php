<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Photo;
use Tests\Feature\SoftDeletesTestTrait;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use App\FileUpload;
use App\User;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;

class PhotoTest extends TestCase
{
    use SoftDeletesTestTrait;

    protected function getModel()
    {
        return factory(Photo::class)->states('photo-block')->create();
    }

    /** @test **/
    public function a_photo_can_be_updated()
    {
        $photo = factory(Photo::class)->states('photo-block')->create();
        $photo_block = $photo->content;
        $content_element = $photo_block->contentElement;
        $page = $content_element->pages->first();

        Storage::fake();
        $file_name = Str::random().'.jpg';
        $file = UploadedFile::fake()->image($file_name);
        $file_upload = (new FileUpload)->saveFile($file, 'photos', true);

        $input = factory(Photo::class)->raw();
        $input['file_upload'] = $file_upload;

        $this->json('POST', route('photos.update', ['id' => $photo->id]), $input)
            ->assertStatus(401);

        $this->signIn( factory(User::class)->create());

        $this->json('POST', route('photos.update', ['id' => $photo->id]), $input)
            ->assertStatus(403);

        $this->signInAdmin();

        $this->json('POST', route('photos.update', ['id' => $photo->id]), [])
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'file_upload.id',
                'sort_order',
                'span',
                'offsetX',
                'offsetY',
            ]);

        $this->withoutExceptionHandling();
        $this->json('POST', route('photos.update', ['id' => $photo->id]), $input)
             ->assertSuccessful()
             ->assertJsonFragment([
                'success' => 'Photo Saved',
                'id' => $photo->id,
                'name' => Arr::get($input, 'name'),
                'id' => Arr::get($input, 'file_upload.id'),
             ]);

        $photo->refresh();

        $this->assertEquals(Arr::get($input, 'file_upload.id'), $photo->fileUpload->id);
        $this->assertEquals(Arr::get($input, 'name'), $photo->name);
        $this->assertEquals(Arr::get($input, 'alt'), $photo->alt);
        $this->assertEquals(Arr::get($input, 'sort_order'), $photo->sort_order);
        $this->assertEquals(Arr::get($input, 'span'), $photo->span);
        $this->assertEquals(Arr::get($input, 'offsetX'), $photo->offsetX);
        $this->assertEquals(Arr::get($input, 'offsetY'), $photo->offsetY);

    }
}
