<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;

use App\Models\Photo;
use App\Models\Page;
use App\Models\ContentElement;
use App\Models\PhotoBlock;
use App\Models\FileUpload;
use App\Models\User;
use Tests\Feature\SoftDeletesTestTrait;

class PhotoTest extends TestCase
{
    use SoftDeletesTestTrait;

    protected function getModel()
    {
        return Photo::factory()
                    ->for(PhotoBlock::factory(), 'content')
                    ->for(FileUpload::factory()->jpg(), 'fileUpload')
                    ->create();
    }

    /** @test **/
    public function a_photo_can_be_updated()
    {
        $page = Page::factory()->create();
        $content_element = ContentElement::factory()
                                ->for(PhotoBlock::factory()->has(Photo::factory()->for(FileUpload::factory()->jpg(), 'fileUpload')), 'content')
                                ->create([
                                    'version_id' => $page->draft_version_id,
                                ]);
        $content_element->pages()->detach();
        $content_element->pages()->attach($page, ['sort_order' => 1, 'unlisted' => false, 'expandable' => false]);
        $photo_block = $content_element->content;
        $photo = $photo_block->photos->first();
        $this->assertInstanceOf(Photo::class, $photo);

        Storage::fake();
        $file_name = Str::random().'.jpg';
        $file = UploadedFile::fake()->image($file_name);
        $file_upload = (new FileUpload)->saveFile($file, 'photos', true);

        $input = Photo::factory()->raw();
        $input['file_upload_id'] = $file_upload->id;

        $this->json('POST', route('photos.update', ['id' => $photo->id]), $input)
            ->assertStatus(401);

        $this->signIn(User::factory()->create());

        $this->json('POST', route('photos.update', ['id' => $photo->id]), $input)
            ->assertStatus(403);

        $this->signInAdmin();

        $this->json('POST', route('photos.update', ['id' => $photo->id]), [])
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'file_upload_id',
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
                'id' => Arr::get($input, 'file_upload_id'),
             ]);

        $photo->refresh();

        $this->assertEquals(Arr::get($input, 'file_upload_id'), $photo->fileUpload->id);
        $this->assertEquals(Arr::get($input, 'name'), $photo->name);
        $this->assertEquals(Arr::get($input, 'alt'), $photo->alt);
        $this->assertEquals(Arr::get($input, 'sort_order'), $photo->sort_order);
        $this->assertEquals(Arr::get($input, 'span'), $photo->span);
        $this->assertEquals(Arr::get($input, 'offsetX'), $photo->offsetX);
        $this->assertEquals(Arr::get($input, 'offsetY'), $photo->offsetY);
    }
}
