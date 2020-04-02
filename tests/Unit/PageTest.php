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

class PageTest extends TestCase
{

    use WithFaker;

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
        $published_content_element->pages()->attach($page, ['sort_order' => 1, 'unlisted' => false]);

        $unpublished_content_element = factory(ContentElement::class)->states('text-block')->create([
            'version_id' => $page->draft_version_id,
        ]);

        $unpublished_content_element->pages()->detach();
        $unpublished_content_element->pages()->attach($page, ['sort_order' => 1, 'unlisted' => false]);

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
        $this->assertTrue($page->can_be_published);
        $page->publish();
        $page->refresh();
        $this->assertFalse($page->can_be_published);

        $content_element = factory(ContentElement::class)->states('text-block')->create([
            'version_id' => $page->draft_version_id,
        ]);

        $content_element->pages()->detach();
        $content_element->pages()->attach($page, ['sort_order' => 1, 'unlisted' => true]);
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

        $page->contentElements()->attach($unlisted_content_element, ['sort_order' => 1, 'unlisted' => true]);

        $unpublished_content_element = factory(ContentElement::class)->states('text-block')->create([
            'version_id' => $page->draft_version_id,
        ]);

        $page->contentElements()->attach($unpublished_content_element, ['sort_order' => 2, 'unlisted' => false]);

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
        $this->assertTrue($page->preview_content_elements->contains('id', $content_element->id));

        $unlisted_content_element = factory(ContentElement::class)->states('unlisted', 'text-block')->create([
            'version_id' => $page->published_version_id,
        ]);

        $page->contentElements()->attach($unlisted_content_element, ['sort_order' => 1, 'unlisted' => true]);

        $unpublished_content_element = factory(ContentElement::class)->states('text-block')->create([
            'version_id' => $page->draft_version_id,
        ]);

        $page->contentElements()->attach($unpublished_content_element, ['sort_order' => 2, 'unlisted' => false]);

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
}
