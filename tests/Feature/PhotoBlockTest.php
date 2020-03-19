<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use Illuminate\Support\Str;
use Illuminate\Support\Arr;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
    
use App\PhotoBlock;
use App\Photo;
use App\FileUpload;
use App\User;
use App\Page;

class PhotoBlockTest extends TestCase
{

    use WithFaker;

    /** @test **/
    public function saving_a_photo_block()
    {
        Storage::fake();
        $file_name = Str::random().'jpg';
        $file = UploadedFile::fake()->image($file_name);
        $file_upload = (new FileUpload)->saveFile($file, 'photos', true);

        $photo_input = factory(Photo::class)->raw();
        $photo_input['file_upload'] = $file_upload;

        $page = factory(Page::class)->create();

        $input = [
            'page_id' => $page->id,
            'sort_order' => 1,
            'type' => 'photo-block',
            'content' => [
                'photos' => [$photo_input],
                'columns' => 1,
                'height' => 33,
                'padding' => false,
                'show_text' => true,
                'header' => $this->faker->sentence,
                'body' => $this->faker->paragraph,
                'text_order' => 1,
                'text_span' => 1,
            ],
        ];

        $this->signInAdmin();

        $this->json('POST', route('content-elements.store'), ['type' => 'photo-block'])
             ->assertStatus(422)
             ->assertJsonValidationErrors([
                'page_id',
                'content.photos',
                'content.columns',
                'content.height',
                'content.padding',
                'content.show_text',
             ]);

        $this->withoutExceptionHandling();
        $this->json('POST', route('content-elements.store'), $input)
             ->assertSuccessful()
             ->assertJsonFragment([
                'success' => 'Photo Block Saved',
             ]);

        $photo_block = PhotoBlock::all()->last();

        $this->assertEquals(Arr::get($input, 'content.columns'), $photo_block->columns);
        $this->assertEquals(Arr::get($input, 'content.height'), $photo_block->height);
        $this->assertEquals(Arr::get($input, 'content.padding'), $photo_block->padding);
        $this->assertEquals(Arr::get($input, 'content.show_text'), $photo_block->show_text);
        $this->assertEquals(Arr::get($input, 'content.header'), $photo_block->header);
        $this->assertEquals(Arr::get($input, 'content.body'), $photo_block->body);
        $this->assertEquals(Arr::get($input, 'content.text_order'), $photo_block->text_order);
        $this->assertEquals(Arr::get($input, 'content.text_span'), $photo_block->text_span);

        $photo = Photo::all()->last();

        $this->assertTrue($photo_block->photos->contains('id', $photo->id));

        $this->assertInstanceOf(PhotoBlock::class, $photo->photoBlock);
        $this->assertEquals($photo_block->id, $photo->photoBlock->id);

        Storage::assertExists('photos/'.$file->hashName());
        $this->assertInstanceOf(Photo::class, $photo);
        $this->assertEquals(Arr::get($photo_input, 'name'), $photo->name);
        $this->assertEquals(Arr::get($photo_input, 'description'), $photo->description);
        $this->assertEquals(Arr::get($photo_input, 'alt'), $photo->alt);
        $this->assertEquals($photo->fileUpload->id, $file_upload->id);
    }

}
