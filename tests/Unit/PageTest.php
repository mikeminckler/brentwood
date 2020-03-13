<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;

use App\Page;
use App\ContentElement;

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
        $page = factory(Page::class)->create();
        $content_element = factory(ContentElement::class)->create([
            'page_id' => $page->id,
        ]);

        $page->refresh();

        $this->assertNotNull($page->contentElements);
        $this->assertTrue($page->contentElements->contains('id', $content_element->id));
    }

    /** @test **/
    public function a_page_can_save_its_content_elements()
    {
        $content_element_input = factory(ContentElement::class)->states('text-block')->raw();
        
        $input = [
            'content_elements' => [json_encode($content_element)],
        ];

        $page->saveContentElements($input);
        $page->refresh();

        $this->assertEquals(1, $page->contentElements->count());
        $content_element = $page->contentElements->first();
    }

}
