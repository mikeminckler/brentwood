<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Photo;
use Illuminate\Support\Arr;

class PhotoTest extends TestCase
{

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
    public function a_photo_can_be_removed()
    {
        $photo = factory(Photo::class)->create();

        $this->signInAdmin();

        $this->json('POST', route('photos.destroy', ['id' => $photo->id]))
            ->assertSuccessful()
            ->assertJsonFragment([
                'success' => $photo->name.' removed',
            ]);
    }

}
