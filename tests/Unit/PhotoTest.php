<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;

use App\Models\Photo;
use App\Models\PhotoBlock;
use App\Models\FileUpload;
use App\Models\TextBlock;
use App\Models\Page;
use App\Utilities\PageLink;

use Tests\Unit\PageLinkTestTrait;

class PhotoTest extends TestCase
{
    protected function createPhoto()
    {
        Storage::fake();
        $file_name = Str::random().'jpg';
        $file = UploadedFile::fake()->image($file_name);
        $file_upload = (new FileUpload)->saveFile($file, 'photos', true);

        $input = Photo::factory()->stat()->link()->raw();
        $input['file_upload'] = $file_upload;

        $photo_block = PhotoBlock::factory()->create();
        $photo = (new Photo)->savePhoto($input, null, $photo_block);
        $this->assertInstanceOf(Photo::class, $photo);
        return $photo;
    }

    /** @test **/
    public function a_photo_has_a_file_upload()
    {
        cache()->flush();
        $photo = $this->createPhoto();
        $this->assertInstanceOf(FileUpload::class, $photo->fileUpload);
    }

    /** @test **/
    public function a_photo_can_be_saved()
    {
        Storage::fake();
        $file_name = Str::random().'jpg';
        $file = UploadedFile::fake()->image($file_name);
        $file_upload = (new FileUpload)->saveFile($file, 'photos', true);

        $input = Photo::factory()->stat()->link()->raw();
        $input['file_upload'] = $file_upload;

        $photo_block = PhotoBlock::factory()->create();
        $photo = (new Photo)->savePhoto($input, null, $photo_block);
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
        $this->assertEquals(PageLink::convertLink(Arr::get($input, 'link')), $photo->link);
    }

    /** @test **/
    public function a_photo_belongs_to_a_content_item()
    {
        $photo = Photo::factory()->for(PhotoBlock::factory(), 'content')->create();
        $this->assertInstanceOf(PhotoBlock::class, $photo->content);
    }

    /** @test **/
    public function a_photo_can_have_a_small()
    {
        $photo = $this->createPhoto();
        $small = $photo->small;
        $this->assertNotNull($photo->small);
        $this->assertInstanceOf(FileUpload::class, $photo->fileUpload);
        $photo->refresh();
        $this->assertNotEquals('/public/images/default.png', $photo->small);
        $this->assertTrue(strpos($photo->small, $photo->fileUpload->filename) > 0);
        //$this->assertTrue(Str::endsWith($small, '.jpg'));
        Storage::disk('public')->assertExists($small);
    }

    /** @test **/
    public function a_photo_small_can_be_removed()
    {
        $photo = $this->createPhoto();
        $small = $photo->small;
        Storage::disk('public')->assertExists($small);
        $photo->removeSmall();
        Storage::disk('public')->assertMissing($small);
    }

    /** @test **/
    public function a_photo_can_have_a_medium_image()
    {
        $photo = $this->createPhoto();
        $medium = $photo->medium;
        $this->assertTrue(strpos($photo->medium, $photo->fileUpload->filename) > 0);
        //$this->assertTrue(Str::endsWith($medium, '.jpg'));
        Storage::disk('public')->assertExists($medium);
    }

    /** @test **/
    public function a_photos_medium_image_can_be_removed()
    {
        $photo = $this->createPhoto();
        $medium = $photo->medium;
        Storage::disk('public')->assertExists($medium);
        $photo->removeMedium();
        Storage::disk('public')->assertMissing($medium);
    }

    /** @test **/
    public function a_photo_can_have_a_large_image()
    {
        $photo = $this->createPhoto();
        $large = $photo->large;
        $this->assertTrue(strpos($photo->large, $photo->fileUpload->filename) > 0);
        //$this->assertTrue(Str::endsWith($large, '.jpg'));
        Storage::disk('public')->assertExists($large);
    }

    /** @test **/
    public function a_photos_large_image_can_be_removed()
    {
        $photo = $this->createPhoto();
        $large = $photo->large;
        Storage::disk('public')->assertExists($large);
        $photo->removeLarge();
        Storage::disk('public')->assertMissing($large);
    }


    /** @test **/
    public function updating_a_file_upload_clears_the_generated_images()
    {
        Storage::fake();
        $file_name = Str::lower(Str::random().'jpg');
        $file = UploadedFile::fake()->image($file_name);
        $file_upload = (new FileUpload)->saveFile($file, 'photos', true);

        $input = Photo::factory()->raw();
        $input['file_upload'] = $file_upload;

        $photo_block = PhotoBlock::factory()->create();
        $photo = (new Photo)->savePhoto($input, null, $photo_block);
        $this->assertInstanceOf(Photo::class, $photo);

        $this->assertTrue(Str::contains($photo->small, $file_name));
        $this->assertTrue(Str::contains($photo->medium, $file_name));
        $this->assertTrue(Str::contains($photo->large, $file_name));

        $file_name2 = Str::lower(Str::random().'jpg');
        $file2 = UploadedFile::fake()->image($file_name2);
        $file_upload2 = (new FileUpload)->saveFile($file2, 'photos', true);

        $input['file_upload'] = $file_upload2;

        $photo = (new Photo)->savePhoto($input, $photo->id, $photo_block);

        $photo->refresh();

        $this->assertEquals($file_upload2->id, $photo->fileUpload->id);
        $this->assertTrue(Str::contains($photo->small, $file_name2));
        $this->assertTrue(Str::contains($photo->medium, $file_name2));
        $this->assertTrue(Str::contains($photo->large, $file_name2));
    }

    /** @test **/
    public function a_photo_link_is_converted_to_a_slug()
    {
        $content_element = $this->createContentElement(TextBlock::factory());
        $text_block = $content_element->content;
        $page = $content_element->pages()->first();

        $link = $page->id.'#c-'.$content_element->uuid;

        $this->assertInstanceOf(Page::class, $page);
        $photo = Photo::factory()->for(PhotoBlock::factory(), 'content')->create([
            'link' => $link,
        ]);

        $this->assertNotNull($photo->link);

        $this->assertEquals('/'.$page->full_slug.'#c-'.$content_element->uuid, $photo->link);
    }

    /** @test **/
    public function a_photo_can_be_duplicated_but_use_the_same_file_upload()
    {
        Storage::fake();
        $file_name = Str::random().'jpg';
        $file = UploadedFile::fake()->image($file_name);
        $file_upload = (new FileUpload)->saveFile($file, 'photos', true);

        $input = Photo::factory()->stat()->link()->raw();
        $input['file_upload'] = $file_upload;

        $photo_block = PhotoBlock::factory()->create();
        $photo = (new Photo)->savePhoto($input, null, $photo_block);
        $this->assertInstanceOf(Photo::class, $photo);

        $new_photo = (new Photo)->savePhoto($input, $photo->id, $photo_block, true);
        $this->assertInstanceOf(Photo::class, $new_photo);

        $this->assertEquals($photo->fileUpload->id, $new_photo->fileUpload->id);
        $this->assertNotEquals($photo->id, $new_photo->id);
    }
}
