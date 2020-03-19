<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

use App\Photo;
use App\PhotoBlock;
use App\FileUpload;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;

class PhotoTest extends TestCase
{
    /** @test **/
    public function a_photo_has_a_file_upload()
    {
        $photo = factory(Photo::class)->create();
        $this->assertInstanceOf(FileUpload::class, $photo->fileUpload);
    }

    /** @test **/
    public function a_photo_can_be_saved()
    {
        Storage::fake();
        $file_name = Str::random().'jpg';
        $file = UploadedFile::fake()->image($file_name);
        $file_upload = (new FileUpload)->saveFile($file, 'photos', true);

        $input = factory(Photo::class)->raw();
        $input['file_upload'] = $file_upload;

        $photo = (new Photo)->savePhoto(null, $input);
        $this->assertInstanceOf(Photo::class, $photo);

        $this->assertEquals(Arr::get($input, 'photo_block_id'), $photo->photoBlock->id);
        $this->assertEquals(Arr::get($input, 'name'), $photo->name);
        $this->assertEquals(Arr::get($input, 'description'), $photo->description);
        $this->assertEquals(Arr::get($input, 'alt'), $photo->alt);
        $this->assertEquals(Arr::get($input, 'sort_order'), $photo->sort_order);
        $this->assertEquals(Arr::get($input, 'span'), $photo->span);
        $this->assertEquals(Arr::get($input, 'offsetX'), $photo->offsetX);
        $this->assertEquals(Arr::get($input, 'offsetY'), $photo->offsetY);

    }

    /** @test **/
    public function a_photo_belongs_to_a_photo_block()
    {
        $photo = factory(Photo::class)->create();   
        $this->assertInstanceOf(PhotoBlock::class, $photo->photoBlock);
    }

    /** @test **/
    public function a_photo_can_have_a_small()
    {
        $photo = factory(Photo::class)->create();
        $small = $photo->small;
        $this->assertTrue(strpos($photo->small, $photo->fileUpload->filename) > 0);
        Storage::disk('public')->assertExists($small);
    }

    /** @test **/
    public function a_photo_small_can_be_removed()
    {
        $photo = factory(Photo::class)->create();
        $small = $photo->small;
        Storage::disk('public')->assertExists($small);
        $photo->removeSmall();
        Storage::disk('public')->assertMissing($small);
    }

    /** @test **/
    public function a_photo_can_have_a_medium_image()
    {
        $photo = factory(Photo::class)->create();
        $medium = $photo->medium;
        $this->assertTrue(strpos($photo->medium, $photo->fileUpload->filename) > 0);
        Storage::disk('public')->assertExists($medium);
    }

    /** @test **/
    public function a_photos_medium_image_can_be_removed()
    {
        $photo = factory(Photo::class)->create();
        $medium = $photo->medium;
        Storage::disk('public')->assertExists($medium);
        $photo->removeMedium();
        Storage::disk('public')->assertMissing($medium);
    }

    /** @test **/
    public function a_photo_can_have_a_large_image()
    {
        $photo = factory(Photo::class)->create();
        $large = $photo->large;
        $this->assertTrue(strpos($photo->large, $photo->fileUpload->filename) > 0);
        Storage::disk('public')->assertExists($large);
    }

    /** @test **/
    public function a_photos_large_image_can_be_removed()
    {
        $photo = factory(Photo::class)->create();
        $large = $photo->large;
        Storage::disk('public')->assertExists($large);
        $photo->removeLarge();
        Storage::disk('public')->assertMissing($large);
    }

    /*
    public function a_photo_name_can_be_found_from_the_upload_file()
    {
        $this->withoutExceptionHandling();
        $input = [];
        $file_upload = factory(FileUpload::class)->create();

        $input['file_upload'] = $file_upload;

        $this->signInAdmin();

        $this->json('POST', route('photos.create'), $input)
            ->assertSuccessful()
            ->assertJsonFragment([
                'success' => $file_upload->name.' saved',
            ]);

        $photo = Photo::all()->last();

        $this->assertInstanceOf(Photo::class, $photo);
        $this->assertEquals($photo->name, $file_upload->name);
    }
     */
}
