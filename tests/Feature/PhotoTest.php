<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use Illuminate\Support\Str;
use Illuminate\Support\Arr;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
    
use App\Photo;
use App\FileUpload;
use App\User;

class PhotoTest extends TestCase
{
    /** @test **/
    public function saving_a_photo()
    {
        Storage::fake();
        $file_name = Str::random().'jpg';
        $file = UploadedFile::fake()->image($file_name);
        $file_upload = (new FileUpload)->saveFile($file, 'photos', true);

        $input = factory(Photo::class)->raw();
        $input['file_upload'] = $file_upload;

        $this->signInAdmin();

        $this->json('POST', route('photos.create'), $input)
            ->assertSuccessful()
            ->assertJsonFragment([
                'success' => $input['name'].' saved',
            ]);

        $photo = Photo::all()->last();

        Storage::assertExists('photos/'.$file->hashName());
        $this->assertInstanceOf(Photo::class, $photo);
        $this->assertEquals(Arr::get($input, 'name'), $photo->name);
        $this->assertEquals(Arr::get($input, 'description'), $photo->description);
        $this->assertEquals(Arr::get($input, 'alt'), $photo->alt);
        $this->assertEquals($photo->fileUpload->id, $file_upload->id);
    }

    /** @test **/
    public function updating_a_photo()
    {
        Storage::fake();
        $photo = factory(Photo::class)->create();
        $input = $photo->toArray();
        $input['name'] = $this->faker->name;
        $input['description'] = $this->faker->sentence;
        $input['alt'] = $this->faker->sentence;

        $this->signInAdmin();

        $this->json('POST', route('photos.update', ['id' => $photo->id]), $input)
            ->assertSuccessful()
            ->assertJsonFragment([
                'success' => $input['name'].' saved',
            ]);

        $photo->refresh();

        $this->assertEquals(Arr::get($input, 'name'), $photo->name);
        $this->assertEquals(Arr::get($input, 'description'), $photo->description);
        $this->assertEquals(Arr::get($input, 'alt'), $photo->alt);
        $this->assertEquals($photo->fileUpload->id, Arr::get($input, 'file_upload.id'));
    }

    /** @test **/
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
}
