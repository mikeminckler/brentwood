<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;

use App\Page;
use App\ContentElement;
use App\TextBlock;
use App\Version;
use App\Menu;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use App\FileUpload;
use Illuminate\Support\Str;
use App\Role;
use App\User;
use Tests\Unit\AppendAttributesTestTrait;
use Illuminate\Support\Arr;

class PageTest extends TestCase
{

    use WithFaker;
    use AppendAttributesTestTrait;


    protected function getModel()
    {
        return factory(Page::class)->create();
    }

    /** @test **/
    public function a_page_has_a_parent()
    {
        $first_level_page = factory(Page::class)->create();
        $second_level_page = factory(Page::class)->states('secondLevel')->create([
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

        $first_level_page = factory(Page::class)->create();
        $second_level_page = factory(Page::class)->states('secondLevel')->create([
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
        $page = factory(Page::class)->create([
            'name' => 'Foo Bar Baz',
        ]);

        $this->assertNotNull($page->slug);
        $this->assertEquals('foo-bar-baz', $page->slug);
    }

    /** @test **/
    public function a_page_has_a_full_slug_attribute()
    {
        $page = factory(Page::class)->states('secondLevel')->create([
            'name' => 'Jimmy Page',
            'parent_page_id' => factory(Page::class)->create([
                'name' => 'Led Zeppelin',
                'parent_page_id' => factory(Page::class)->create([
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
        $page = factory(Page::class)->states('secondLevel')->create([
            'name' => $this->faker->firstName,
            'parent_page_id' => factory(Page::class)->create([
                'name' => $this->faker->firstName,
                'parent_page_id' => factory(Page::class)->create([
                    'name' => $this->faker->firstName,
                ]),
            ]),
        ]);   

        $page_slug = $page->full_slug;

        $found_page = Page::findByFullSlug($page->full_slug);

        $this->assertInstanceOf(Page::class, $found_page);
        $this->assertEquals($page->id, $found_page->id);
    }

    /** @test **/
    public function a_page_can_return_its_slug()
    {
        $slug = $this->faker->name;
        $page = factory(Page::class)->states('slug')->create([
            'slug' => $slug,
        ]);

        $this->assertEquals($slug, $page->slug);
    }

    /** @test **/
    public function a_page_can_have_many_content_elements()
    {
        $content_element = factory(ContentElement::class)->states('text-block')->create();
        $page = $content_element->pages->first();

        $page->refresh();

        $this->assertNotNull($page->contentElements);
        $this->assertTrue($page->contentElements->contains('id', $content_element->id));
    }

    /** @test **/
    public function a_page_can_save_its_content_elements()
    {
        $page = factory(Page::class)->create();
        $content_element_input = factory(ContentElement::class)->states('text-block')->raw();
        $content_element_input['type'] = 'text-block';
        $content_element_input['content'] = factory(TextBlock::class)->raw();
        $content_element_input['pivot'] = [
            'page_id' => $page->id,
            'sort_order' => 1,
            'unlisted' => false,
            'expandable' => false,
        ];
        $input = [
            'content_elements' => [
                $content_element_input,
            ],
        ];

        $page->saveContentElements($input);
        $page->refresh();

        $this->assertEquals(1, $page->contentElements->count());
        $content_element = $page->contentElements->first();
    }

    /** @test **/
    public function a_page_can_get_its_draft_version()
    {
        $page = factory(Page::class)->create();

        $this->assertInstanceOf(Page::class, $page);
        $draft_version = $page->getDraftVersion();
        $this->assertInstanceOf(Version::class, $draft_version);
    }

    /** @test **/
    public function a_page_has_a_published_version()
    {
        $page = factory(Page::class)->states('published')->create();   
        $this->assertNotNull($page->published_version_id);
        $this->assertNotNull($page->publishedVersion);
        $this->assertInstanceOf(Version::class, $page->publishedVersion);
    }

    /** @test **/
    public function a_page_can_be_published()
    {
        $page = factory(Page::class)->create();   
        $page->publish();
        $this->assertNotNull($page->published_version_id);
        $this->assertNotNull($page->publishedVersion);
        $this->assertInstanceOf(Version::class, $page->publishedVersion);
        $this->assertNotNull($page->publishedVersion->published_at);
        $this->assertNotNull($page->published_at);
    }

    /** @test **/
    public function a_page_has_many_versions()
    {
        $page = factory(Page::class)->create();
        $version = factory(Version::class)->create([
            'page_id' => $page->id,
        ]);
        $page->refresh();
        $this->assertTrue($page->versions->contains('id', $version->id));
    }

    /** @test **/
    public function if_a_page_doesnt_have_a_draft_version_one_is_created()
    {
        $page = factory(Page::class)->create();
        $this->assertInstanceOf(Version::class, $page->getDraftVersion());
    }

    /** @test **/
    public function a_page_can_be_unlisted_from_the_menu()
    {
        $page = factory(Page::class)->states('unlisted')->create();
        $this->assertEquals(1, $page->unlisted);
    }

    /** @test **/
    public function a_page_can_get_its_content_elements()
    {
        // this checks for the proper grouping of content elements by UUID
        $page = factory(Page::class)->states('published')->create();

        $published_content_element = factory(ContentElement::class)->states('text-block')->create([
            'version_id' => $page->published_version_id,
        ]);

        $published_content_element->pages()->detach();
        $published_content_element->pages()->attach($page, ['sort_order' => 1, 'unlisted' => false, 'expandable' => false]);

        $unpublished_content_element = factory(ContentElement::class)->states('text-block')->create([
            'version_id' => $page->draft_version_id,
        ]);

        $unpublished_content_element->pages()->detach();
        $unpublished_content_element->pages()->attach($page, ['sort_order' => 1, 'unlisted' => false, 'expandable' => false]);

        $page->refresh();
        $this->assertNotNull($page->content_elements);
        $this->assertInstanceOf(Collection::class, $page->content_elements);
        $this->assertTrue($page->content_elements->contains('id', $unpublished_content_element->id));
        $this->assertTrue($page->content_elements->contains('id', $published_content_element->id));
    }

    /** @test **/
    public function a_page_has_a_draft_version_id_attribute()
    {
        $page = factory(Page::class)->create();   
        $this->assertNotNull($page->draft_version_id);
        $this->assertEquals($page->getDraftVersion()->id, $page->draft_version_id);
    }

    /** @test **/
    public function a_page_has_a_can_be_published_attribute()
    {
        $content_element = factory(ContentElement::class)->states('text-block')->create();
        $page = $content_element->pages->first();
        $content_element->version_id = $page->draft_version_id;
        $content_element->save();
        $content_element->refresh();

        $page->refresh();

        $this->assertFalse($page->can_be_published);
        $user = factory(User::class)->create();
        $this->signIn($user);
        $this->assertFalse($page->can_be_published);

        $user->addRole('publisher');
        $user->refresh();
        $page->refresh();

        $this->assertTrue($page->can_be_published);
        $page->publish();
        $page->refresh();
        $this->assertFalse($page->can_be_published);

        $content_element = factory(ContentElement::class)->states('text-block')->create([
            'version_id' => $page->draft_version_id,
        ]);

        $content_element->pages()->detach();
        $content_element->pages()->attach($page, ['sort_order' => 1, 'unlisted' => true, 'expandable' => false]);
        $content_element->version_id = $page->draft_version_id;
        $content_element->save();
        
        $page->refresh();
        $this->assertTrue($page->can_be_published);

    }

    /** @test **/
    public function a_page_can_get_its_published_content_elements()
    {
        $content_element = factory(ContentElement::class)->states('text-block')->create();
        $this->assertEquals(1, $content_element->pages()->count());
        $page = $content_element->pages->first();
        $content_element->version_id = $page->getDraftVersion()->id;
        $content_element->save();

        $this->assertTrue($page->contentElements->contains('id', $content_element->id));
        $this->assertTrue($page->content_elements->contains('id', $content_element->id));
        $this->assertEquals($page->getDraftVersion()->id, $content_element->version_id);

        $page->publish();
        $page->refresh();

        $content_element->refresh();
        $this->assertNotNull($content_element->published_at);
        $this->assertTrue($page->published_content_elements->contains('id', $content_element->id));

        $unlisted_content_element = factory(ContentElement::class)->states('unlisted', 'text-block')->create([
            'version_id' => $page->published_version_id,
        ]);

        $page->contentElements()->attach($unlisted_content_element, ['sort_order' => 1, 'unlisted' => true, 'expandable' => false]);

        $unpublished_content_element = factory(ContentElement::class)->states('text-block')->create([
            'version_id' => $page->draft_version_id,
        ]);

        $page->contentElements()->attach($unpublished_content_element, ['sort_order' => 2, 'unlisted' => false, 'expandable' => false,]);

        $this->assertTrue($page->contentElements->contains('id', $content_element->id));
        $this->assertTrue($page->contentElements->contains('id', $unpublished_content_element->id));
        $this->assertTrue($page->contentElements->contains('id', $unlisted_content_element->id));

        $page->refresh();
        $this->assertFalse($page->published_content_elements->contains('id', $unlisted_content_element->id));
        $this->assertFalse($page->published_content_elements->contains('id', $unpublished_content_element->id));
    }

    /** @test **/
    public function a_page_can_get_its_preview_content_elements()
    {
        $this->signInAdmin();
        session()->put('editing', true);
        $content_element = factory(ContentElement::class)->states('text-block')->create();
        $this->assertEquals(1, $content_element->pages()->count());
        $page = $content_element->pages->first();
        $content_element->version_id = $page->getDraftVersion()->id;
        $content_element->save();

        $this->assertTrue($page->contentElements->contains('id', $content_element->id));
        $this->assertTrue($page->content_elements->contains('id', $content_element->id));
        $this->assertEquals($page->getDraftVersion()->id, $content_element->version_id);
        $this->assertEquals(0, $page->pivot->unlisted);

        $page->publish();
        $page->refresh();
        $content_element->refresh();

        $this->assertNotNull($content_element->published_at);
        $this->assertEquals(1, $page->contentElements->count());
        $this->assertTrue(session()->get('editing'));
        $this->assertTrue($page->contentElements->contains('id', $content_element->id));
        $this->assertTrue($page->preview_content_elements->contains('id', $content_element->id));

        $unlisted_content_element = factory(ContentElement::class)->states('unlisted', 'text-block')->create([
            'version_id' => $page->published_version_id,
        ]);

        $page->contentElements()->attach($unlisted_content_element, ['sort_order' => 1, 'unlisted' => true, 'expandable' => false]);

        $unpublished_content_element = factory(ContentElement::class)->states('text-block')->create([
            'version_id' => $page->draft_version_id,
        ]);

        $page->contentElements()->attach($unpublished_content_element, ['sort_order' => 2, 'unlisted' => false, 'expandable' => false]);

        $this->assertTrue($page->contentElements->contains('id', $content_element->id));
        $this->assertTrue($page->contentElements->contains('id', $unpublished_content_element->id));
        $this->assertTrue($page->contentElements->contains('id', $unlisted_content_element->id));

        $page->refresh();
        $this->assertFalse($page->preview_content_elements->contains('id', $unlisted_content_element->id));
        $this->assertTrue($page->preview_content_elements->contains('id', $content_element->id));
        $this->assertTrue($page->preview_content_elements->contains('id', $unpublished_content_element->id));
    }

    /** @test **/
    public function if_session_editing_preview_content_elements_are_appended()
    {
        $content_element = factory(ContentElement::class)->states('text-block')->create();
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
        $home_page->footer_fg_file_upload_id = $fg_file_upload->id;
        $home_page->footer_bg_file_upload_id = $bg_file_upload->id;
        $home_page->footer_color = $footer_color;
        $home_page->save();

        $page = factory(Page::class)->create([
            'parent_page_id' => $home_page->id,
        ]);

        $this->assertEquals($fg_file_upload->id, $page->footer_fg->id);
        $this->assertEquals($bg_file_upload->id, $page->footer_bg->id);
        $this->assertEquals($footer_color, $page->footer_color);

        $fg_file_name2 = Str::random().'.jpg';
        $fg_file2 = UploadedFile::fake()->image($fg_file_name2);
        $fg_file_upload2 = (new FileUpload)->saveFile($fg_file2, 'photos', true);
        $bg_file_name2 = Str::random().'.jpg';
        $bg_file2 = UploadedFile::fake()->image($bg_file_name2);
        $bg_file_upload2 = (new FileUpload)->saveFile($bg_file2, 'photos', true);
        $footer_color2 = $this->faker->hexcolor;
        
        $sub_page = factory(Page::class)->create([
            'parent_page_id' => $page->id,
        ]);

        $this->assertEquals($fg_file_upload->id, $sub_page->footer_fg->id);
        $this->assertEquals($bg_file_upload->id, $sub_page->footer_bg->id);
        $this->assertEquals($footer_color, $sub_page->footer_color);

        $page->footer_fg_file_upload_id = $fg_file_upload2->id;
        $page->footer_bg_file_upload_id = $bg_file_upload2->id;
        $page->footer_color = $footer_color2;
        $page->save();
        $page->refresh();
        $sub_page->refresh();

        $this->assertEquals($fg_file_upload2->id, $sub_page->footer_fg->id);
        $this->assertEquals($bg_file_upload2->id, $sub_page->footer_bg->id);
        $this->assertEquals($footer_color2, $sub_page->footer_color);
    }

    /** @test **/
    public function a_page_can_have_a_footer_fg()
    {
        Storage::fake();
        $fg_file_name = Str::random().'.jpg';
        $fg_file = UploadedFile::fake()->image($fg_file_name);
        $fg_file_upload = (new FileUpload)->saveFile($fg_file, 'photos', true);

        $page = factory(Page::class)->create();
        $page->footer_fg_file_upload_id = $fg_file_upload->id;
        $page->save();

        $footer_fg_image = $page->footer_fg_image;
        $this->assertNotNull($footer_fg_image);
        $this->assertTrue(strpos($footer_fg_image, $fg_file_upload->filename) > 0);
        Storage::disk('public')->assertExists($footer_fg_image);
    }

    /** @test **/
    public function a_page_can_have_a_footer_bg()
    {
        Storage::fake();
        $bg_file_name = Str::random().'.jpg';
        $bg_file = UploadedFile::fake()->image($bg_file_name);
        $bg_file_upload = (new FileUpload)->saveFile($bg_file, 'photos', true);

        $page = factory(Page::class)->create();
        $page->footer_bg_file_upload_id = $bg_file_upload->id;
        $page->save();

        $footer_bg_image = $page->footer_bg_image;
        $this->assertNotNull($footer_bg_image);
        $this->assertTrue(strpos($footer_bg_image, $bg_file_upload->filename) > 0);
        Storage::disk('public')->assertExists($footer_bg_image);
    }

    /** @test **/
    public function a_page_has_a_sub_menu()
    {
        $page = factory(Page::class)->create();
        $sub_page = factory(Page::class)->create([
            'parent_page_id' => $page->id,
        ]);

        $this->assertInstanceOf(Collection::class, $page->sub_menu);
        $this->assertTrue($page->sub_menu->contains('name', $sub_page->name));
    }

    /** @test **/
    public function a_page_has_many_page_accesses()
    {
        $page = factory(Page::class)->create();
        $role = factory(Role::class)->create();

        $page->createPageAccess($role);

        $this->assertInstanceOf(Collection::class, $page->pageAccesses()->get());
    }

    /** @test **/
    public function a_page_can_grant_access_to_a_role()
    {
        $page = factory(Page::class)->create();
        $role = factory(Role::class)->create();

        $page->createPageAccess($role);

        $this->assertTrue($role->canEditPage($page));
    }

    /** @test **/
    public function a_page_can_grant_access_to_a_user()
    {
        $page = factory(Page::class)->create();
        $user = factory(User::class)->create();

        $page->createPageAccess($user);

        $this->assertTrue($user->canEditPage($page));
    }

    /** @test **/
    public function a_page_can_check_if_it_is_editable()
    {
        $page = factory(Page::class)->create();

        $this->assertNotNull($page->editable);
        $this->assertFalse($page->editable);

        $user1 = factory(User::class)->create();
        $this->signIn($user1);

        $this->assertFalse($page->editable);

        $role = factory(Role::class)->create();
        $page->createPageAccess($role);
        $user1->addRole($role);

        $user1->refresh();
        $this->assertTrue($user1->hasRole($role));

        $this->assertTrue($user1->canEditPage($page));
        $this->assertFalse($page->editable);

        session()->put('editing', true);
        $this->assertTrue($page->editable);

        $user2 = factory(User::class)->create();
        $this->signIn($user2);

        $this->assertFalse($page->editable);
        $page->createPageAccess($user2);
        $user2->refresh();
        $this->assertTrue($page->editable);

        $this->signInAdmin();
        $this->assertTrue($page->editable);

    }

    /** @test **/
    public function a_page_can_recursively_append_attributes()
    {
        $page1 = factory(Page::class)->create();
        $page2 = factory(Page::class)->create([
            'parent_page_id' => $page1->id,
        ]);
        $page3 = factory(Page::class)->create([
            'parent_page_id' => $page2->id,
        ]);

        $page1->appendRecursive(['full_slug']);
        $page_array = $page1->toArray();

        $this->assertNotNull( Arr::get($page_array, 'full_slug'));
        $this->assertEquals($page1->full_slug, Arr::get($page_array, 'full_slug'));

        $this->assertNotNull( Arr::get($page_array, 'pages'));
        $this->assertNotNull( Arr::get($page_array['pages'][0], 'full_slug'));
        $this->assertEquals($page2->full_slug, Arr::get($page_array['pages'][0], 'full_slug'));

        $this->assertNotNull( Arr::get($page_array['pages'][0]['pages'][0], 'full_slug'));
        $this->assertEquals($page3->full_slug, Arr::get($page_array['pages'][0]['pages'][0], 'full_slug'));

    }
}
