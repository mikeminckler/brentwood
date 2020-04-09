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
use Tests\Unit\PageLinkTestTrait;
use App\TextBlock;
use App\Page;

class PhotoTest extends TestCase
{

    /** @test **/
    public function a_photo_has_a_file_upload()
    {
        cache()->flush();
        $photo = factory(Photo::class)->states('photo-block')->create();
        $this->assertInstanceOf(FileUpload::class, $photo->fileUpload);
    }

    /** @test **/
    public function a_photo_can_be_saved()
    {
        Storage::fake();
        $file_name = Str::random().'jpg';
        $file = UploadedFile::fake()->image($file_name);
        $file_upload = (new FileUpload)->saveFile($file, 'photos', true);

        $input = factory(Photo::class)->states('stat', 'link')->raw();
        $input['file_upload'] = $file_upload;

        $photo_block = factory(PhotoBlock::class)->create();
        $photo = (new Photo)->savePhoto(null, $input, $photo_block);
        $this->assertInstanceOf(Photo::class, $photo);

        $this->assertEquals($photo_block->id, $photo->content->id);
        $this->assertEquals(get_class($photo_block), get_class($photo->content));
        $this->assertEquals(Arr::get($input, 'name'), $photo->name);
        $this->assertEquals(Arr::get($input, 'description'), $photo->description);
        $this->assertEquals(Arr::get($input, 'alt'), $photo->alt);
        $this->assertEquals(Arr::get($input, 'sort_order'), $photo->sort_order);
        $this->assertEquals(Arr::get($input, 'span'), $photo->span);
        $this->assertEquals(Arr::get($input, 'offsetX'), $photo->offsetX);
        $this->assertEquals(Arr::get($input, 'offsetY'), $photo->offsetY);
        $this->assertEquals(Arr::get($input, 'fill'), $photo->fill);
        $this->assertEquals(Arr::get($input, 'stat_number'), $photo->stat_number);
        $this->assertEquals(Arr::get($input, 'stat_name'), $photo->stat_name);
        $this->assertEquals(Arr::get($input, 'link'), $photo->link);

    }

    /** @test **/
    public function a_photo_belongs_to_a_content_item()
    {
        $photo = factory(Photo::class)->states('photo-block')->create();   
        $this->assertInstanceOf(PhotoBlock::class, $photo->content);
    }

    /** @test **/
    public function a_photo_can_have_a_small()
    {
        $photo = factory(Photo::class)->states('photo-block')->create();
        $small = $photo->small;
        $this->assertTrue(strpos($photo->small, $photo->fileUpload->filename) > 0);
        Storage::disk('public')->assertExists($small);
    }

    /** @test **/
    public function a_photo_small_can_be_removed()
    {
        $photo = factory(Photo::class)->states('photo-block')->create();
        $small = $photo->small;
        Storage::disk('public')->assertExists($small);
        $photo->removeSmall();
        Storage::disk('public')->assertMissing($small);
    }

    /** @test **/
    public function a_photo_can_have_a_medium_image()
    {
        $photo = factory(Photo::class)->states('photo-block')->create();
        $medium = $photo->medium;
        $this->assertTrue(strpos($photo->medium, $photo->fileUpload->filename) > 0);
        Storage::disk('public')->assertExists($medium);
    }

    /** @test **/
    public function a_photos_medium_image_can_be_removed()
    {
        $photo = factory(Photo::class)->states('photo-block')->create();
        $medium = $photo->medium;
        Storage::disk('public')->assertExists($medium);
        $photo->removeMedium();
        Storage::disk('public')->assertMissing($medium);
    }

    /** @test **/
    public function a_photo_can_have_a_large_image()
    {
        $photo = factory(Photo::class)->states('photo-block')->create();
        $large = $photo->large;
        $this->assertTrue(strpos($photo->large, $photo->fileUpload->filename) > 0);
        Storage::disk('public')->assertExists($large);
    }

    /** @test **/
    public function a_photos_large_image_can_be_removed()
    {
        $photo = factory(Photo::class)->states('photo-block')->create();
        $large = $photo->large;
        Storage::disk('public')->assertExists($large);
        $photo->removeLarge();
        Storage::disk('public')->assertMissing($large);
    }


    /** @test **/
    public function updating_a_file_upload_clears_the_generated_images()
    {
        Storage::fake();
        $file_name = Str::random().'jpg';
        $file = UploadedFile::fake()->image($file_name);
        $file_upload = (new FileUpload)->saveFile($file, 'photos', true);

        $input = factory(Photo::class)->raw();
        $input['file_upload'] = $file_upload;

        $photo_block = factory(PhotoBlock::class)->create();
        $photo = (new Photo)->savePhoto(null, $input, $photo_block);
        $this->assertInstanceOf(Photo::class, $photo);

        $this->assertTrue(Str::contains($photo->small, $file_name));
        $this->assertTrue(Str::contains($photo->medium, $file_name));
        $this->assertTrue(Str::contains($photo->large, $file_name));

        $file_name2 = Str::random().'jpg';
        $file2 = UploadedFile::fake()->image($file_name2);
        $file_upload2 = (new FileUpload)->saveFile($file2, 'photos', true);

        $input['file_upload'] = $file_upload2;

        $photo = (new Photo)->savePhoto($photo->id, $input, $photo_block);

        $photo->refresh();

        $this->assertEquals($file_upload2->id, $photo->fileUpload->id);
        $this->assertTrue(Str::contains($photo->small, $file_name2));
        $this->assertTrue(Str::contains($photo->medium, $file_name2));
        $this->assertTrue(Str::contains($photo->large, $file_name2));
    }

    /** @test **/
    public function a_photo_link_is_converted_to_a_slug()
    {
        $text_block = factory(TextBlock::class)->create();
        $content_element = $text_block->contentElement;
        $page = $content_element->pages()->first();

        $link = $page->id.'#c-'.$content_element->uuid;

        $this->assertInstanceOf(Page::class, $page);
        $photo = factory(Photo::class)->states('photo-block')->create([
            'link' => $link,
        ]);

        $this->assertNotNull($photo->link);

        $this->assertEquals('/'.$page->full_slug.'#c-'.$content_element->uuid, $photo->link);
    }
}
