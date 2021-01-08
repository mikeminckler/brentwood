<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;
use Carbon\Carbon;

use App\Models\Page;
use App\Models\ContentElement;
use App\Models\TextBlock;
use App\Models\Version;
use App\Models\Menu;
use App\Models\FileUpload;
use App\Models\Role;
use App\Models\User;
use App\Models\Photo;

use App\Events\PageDraftCreated;

use Tests\Unit\AppendAttributesTestTrait;
use Tests\Unit\ContentElementsTestTrait;
use Tests\Unit\VersioningTestTrait;
use Tests\Unit\TagsTrait;

class PageTest extends TestCase
{
    use WithFaker;
    use AppendAttributesTestTrait;
    use ContentElementsTestTrait;
    use VersioningTestTrait;
    use TagsTrait;

    protected function getClassname()
    {
        return 'page';
    }

    /** @test **/
    public function a_page_has_a_parent()
    {
        $first_level_page = Page::factory()->create();
        $second_level_page = Page::factory()->secondLevel()->create([
            'parent_page_id' => $first_level_page->id,
        ]);

        $this->assertNotNull($first_level_page->parentPage);
        $this->assertNotNull($second_level_page->parentPage);
        $this->assertEquals($first_level_page->id, $second_level_page->parentPage->id);

        $this->assertNotNull($first_level_page->pages);
        $this->assertTrue($first_level_page->pages->contains('id', $second_level_page->id));
    }

    /** @test **/
    public function the_page_tree_can_be_created()
    {
        $first_level_page = Page::factory()->create();
        $second_level_page = Page::factory()->secondLevel()->create([
            'parent_page_id' => $first_level_page->id,
        ]);

        $home_page = Page::find(1);
        $this->assertInstanceOf(Page::class, $home_page);
        $first_level = $home_page->first()->pages;
        $this->assertTrue($first_level->contains('id', $first_level_page->id));
        $second_level = $first_level_page->pages;
        $this->assertTrue($second_level->contains('id', $second_level_page->id));
    }

    /** @test **/
    public function a_page_has_a_default_slug_attribute()
    {
        $page = Page::factory()->create([
            'name' => 'Foo Bar Baz',
        ]);

        $this->assertNotNull($page->getSlug());
        $this->assertEquals('foo-bar-baz', $page->getSlug());
    }

    /** @test **/
    public function a_page_has_a_full_slug_attribute()
    {
        $page = Page::factory()->secondLevel()->create([
            'name' => 'Jimmy Page',
            'parent_page_id' => Page::factory()->create([
                'name' => 'Led Zeppelin',
                'parent_page_id' => Page::factory()->create([
                    'name' => 'Rock N Roll',
                ]),
            ]),
        ]);

        $this->assertNotNull($page->parentPage);
        $this->assertEquals('rock-n-roll/led-zeppelin/jimmy-page', $page->full_slug);
    }

    /** @test **/
    public function a_page_can_be_found_by_its_full_slug()
    {
        $page = Page::factory()->secondLevel()->create([
            'name' => $this->faker->firstName,
            'parent_page_id' => Page::factory()->create([
                'name' => $this->faker->firstName,
                'parent_page_id' => Page::factory()->create([
                    'name' => $this->faker->firstName,
                ]),
            ]),
        ]);

        $page_slug = $page->full_slug;

        $found_page = (new Page)->findByFullSlug($page->full_slug);

        $this->assertInstanceOf(Page::class, $found_page);
        $this->assertEquals($page->id, $found_page->id);
    }

    /** @test **/
    public function a_page_can_return_its_slug()
    {
        $slug = $this->faker->name;
        $page = Page::factory()->slug()->create([
            'slug' => $slug,
        ]);

        $this->assertEquals($slug, $page->slug);
    }

    /** @test **/
    public function a_page_can_be_hidden_from_the_menu()
    {
        $page = Page::factory()->unlisted()->create();
        $this->assertEquals(1, $page->unlisted);
    }

    /** @test **/
    public function if_session_editing_preview_content_elements_are_appended()
    {
        $content_element = $this->createContentElement(TextBlock::factory());
        $this->assertEquals(1, $content_element->pages()->count());
        $page = $content_element->pages->first();
        $content_element->version_id = $page->getDraftVersion()->id;
        $content_element->save();

        $this->assertFalse($page->preview_content_elements->contains('id', $content_element->id));
        $this->signInAdmin();
        session()->put('editing', true);
        $this->assertTrue($page->preview_content_elements->contains('id', $content_element->id));
    }

    /** @test **/
    public function a_page_can_have_a_footer_fg()
    {
        Storage::fake();
        $page = Page::factory()->create();

        $fg_file_name = Str::random().'.jpg';
        $fg_file = UploadedFile::fake()->image($fg_file_name);
        $fg_file_upload = (new FileUpload)->saveFile($fg_file, 'photos', true);
        $input = Photo::factory()->raw();
        $input['file_upload'] = $fg_file_upload;
        $photo = (new Photo)->savePhoto($input, null, $page);

        $this->assertInstanceOf(Photo::class, $photo);

        $page->footer_fg_photo_id = $photo->id;
        $page->save();

        $this->assertNotNull($page->footerFgPhoto);
        $this->assertInstanceOf(Photo::class, $page->footerFgPhoto);
        $this->assertTrue(strpos($page->footerFgPhoto->medium, $fg_file_upload->filename) > 0);
        Storage::disk('public')->assertExists($page->footerFgPhoto->medium);
    }

    /** @test **/
    public function a_page_can_have_a_footer_bg()
    {
        Storage::fake();
        $page = Page::factory()->create();

        $bg_file_name = Str::random().'.jpg';
        $bg_file = UploadedFile::fake()->image($bg_file_name);
        $bg_file_upload = (new FileUpload)->saveFile($bg_file, 'photos', true);
        $input = Photo::factory()->raw();
        $input['file_upload'] = $bg_file_upload;
        $photo = (new Photo)->savePhoto($input, null, $page);

        $this->assertInstanceOf(Photo::class, $photo);

        $page->footer_bg_photo_id = $photo->id;
        $page->save();

        $this->assertNotNull($page->footerBgPhoto);
        $this->assertInstanceOf(Photo::class, $page->footerBgPhoto);
        $this->assertTrue(strpos($page->footerBgPhoto->medium, $bg_file_upload->filename) > 0);
        Storage::disk('public')->assertExists($page->footerBgPhoto->medium);
    }

    /** @test **/
    public function a_page_has_footer_images_and_a_footer_color_that_can_be_inherited()
    {
        Storage::fake();
        $fg_file_name = Str::random().'.jpg';
        $fg_file = UploadedFile::fake()->image($fg_file_name);
        $fg_file_upload = (new FileUpload)->saveFile($fg_file, 'photos', true);
        $bg_file_name = Str::random().'.jpg';
        $bg_file = UploadedFile::fake()->image($bg_file_name);
        $bg_file_upload = (new FileUpload)->saveFile($bg_file, 'photos', true);
        $footer_color = $this->faker->hexcolor;

        $home_page = Page::find(1);

        $input = Photo::factory()->raw();
        $input['file_upload'] = $fg_file_upload;
        $fg_photo = (new Photo)->savePhoto($input, null, $home_page);

        $input = Photo::factory()->raw();
        $input['file_upload'] = $bg_file_upload;
        $bg_photo = (new Photo)->savePhoto($input, null, $home_page);

        $home_page->footer_fg_photo_id = $fg_photo->id;
        $home_page->footer_bg_photo_id = $bg_photo->id;
        $home_page->footer_color = $footer_color;
        $home_page->save();

        $home_page->refresh();

        $page = Page::factory()->create([
            'parent_page_id' => $home_page->id,
        ]);

        $this->assertEquals($fg_file_upload->id, $home_page->getFooterFgPhoto()->fileUpload->id);
        $this->assertEquals($bg_file_upload->id, $home_page->getFooterBgPhoto()->fileUpload->id);
        $this->assertEquals($footer_color, $page->footer_color);

        $fg_file_name2 = Str::random().'.jpg';
        $fg_file2 = UploadedFile::fake()->image($fg_file_name2);
        $fg_file_upload2 = (new FileUpload)->saveFile($fg_file2, 'photos', true);
        $bg_file_name2 = Str::random().'.jpg';
        $bg_file2 = UploadedFile::fake()->image($bg_file_name2);
        $bg_file_upload2 = (new FileUpload)->saveFile($bg_file2, 'photos', true);
        $footer_color2 = $this->faker->hexcolor;
        
        $sub_page = Page::factory()->create([
            'parent_page_id' => $page->id,
        ]);

        $input = Photo::factory()->raw();
        $input['file_upload'] = $fg_file_upload2;
        $fg_photo2 = (new Photo)->savePhoto($input, null, $sub_page);

        $input = Photo::factory()->raw();
        $input['file_upload'] = $bg_file_upload2;
        $bg_photo2 = (new Photo)->savePhoto($input, null, $sub_page);

        $this->assertEquals($fg_file_upload->id, $sub_page->getFooterFgPhoto()->fileUpload->id);
        $this->assertEquals($bg_file_upload->id, $sub_page->getFooterBgPhoto()->fileUpload->id);
        $this->assertEquals($footer_color, $sub_page->footer_color);

        $page->footer_fg_photo_id = $fg_photo2->id;
        $page->footer_bg_photo_id = $bg_photo2->id;
        $page->footer_color = $footer_color2;
        $page->save();
        $page->refresh();
        $sub_page->refresh();

        $this->assertEquals($fg_file_upload2->id, $sub_page->getFooterFgPhoto()->fileUpload->id);
        $this->assertEquals($bg_file_upload2->id, $sub_page->getFooterBgPhoto()->fileUpload->id);
        $this->assertEquals($footer_color2, $sub_page->footer_color);
    }


    /** @test **/
    public function a_page_has_a_sub_menu()
    {
        $page = Page::factory()->create();
        $sub_page = Page::factory()->create([
            'parent_page_id' => $page->id,
        ]);

        $this->assertInstanceOf(Collection::class, $page->sub_menu);
        $this->assertTrue($page->sub_menu->contains('name', $sub_page->name));
    }

    /** @test **/
    public function a_page_can_recursively_append_attributes()
    {
        $page1 = Page::factory()->create();
        $page2 = Page::factory()->create([
            'parent_page_id' => $page1->id,
        ]);
        $page3 = Page::factory()->create([
            'parent_page_id' => $page2->id,
        ]);

        $page1->appendRecursive(['full_slug']);
        $page_array = $page1->toArray();

        $this->assertNotNull(Arr::get($page_array, 'full_slug'));
        $this->assertEquals($page1->full_slug, Arr::get($page_array, 'full_slug'));

        $this->assertNotNull(Arr::get($page_array, 'pages'));
        $this->assertNotNull(Arr::get($page_array['pages'][0], 'full_slug'));
        $this->assertEquals($page2->full_slug, Arr::get($page_array['pages'][0], 'full_slug'));

        $this->assertNotNull(Arr::get($page_array['pages'][0]['pages'][0], 'full_slug'));
        $this->assertEquals($page3->full_slug, Arr::get($page_array['pages'][0]['pages'][0], 'full_slug'));
    }

    /** @test **/
    public function a_pages_full_slug_removes_any_non_word_characters()
    {
        $unsanitized_name = 'FOOBAR, baz, "Foo"';

        $page = Page::factory()->create([
            'name' => $unsanitized_name,
        ]);

        $this->assertEquals('foobar-baz-foo', $page->full_slug);
    }

    /** @test **/
    public function a_page_has_a_footer_text_color()
    {
        $page = Page::factory()->create();

        $page->footer_color = '50,50,50';
        $page->save();

        $page->refresh();

        $this->assertNotNull($page->footer_text_color);
        $this->assertEquals('text-gray-200', $page->footer_text_color);
    }
}
