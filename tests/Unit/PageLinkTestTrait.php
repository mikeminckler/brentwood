<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;

use App\Models\Page;
use App\Models\ContentElement;

trait PageLinkTestTrait
{
    abstract protected function getModel();
    abstract protected function getLinkFields();

    use WithFaker;

    /** @test **/
    public function if_a_page_is_displayed_in_the_front_end_we_convert_page_id_links_to_full_slugs()
    {
        $content = $this->getModel();
        $page1 = Page::factory()->create();
        $page2 = Page::factory()->create();
        $page3 = Page::factory()->create();
        $this->assertNotNull($page1->full_slug);

        foreach ($this->getLinkFields() as $link_field) {
            $body = '<p>'.$this->faker->sentence.' <a href="'.$page1->id.'" >'.$page1->name.'</a></p>';
            $body .= '<p>'.$this->faker->sentence.' <a href="'.$page2->id.'" >'.$page2->name.'</a></p>';
            $body .= '<p>'.$this->faker->sentence.' <a href="'.$page3->id.'" >'.$page3->name.'</a></p>';

            $content->{$link_field} = $body;
            $content->save();

            $content->refresh();

            session()->put('editing', true);

            $this->assertTrue(Str::contains($content->{$link_field}, 'href="'.$page1->id.'"'));
            $this->assertFalse(Str::contains($content->{$link_field}, 'href="/'.$page1->full_slug.'"'));
            $this->assertTrue(Str::contains($content->{$link_field}, 'href="'.$page2->id.'"'));
            $this->assertFalse(Str::contains($content->{$link_field}, 'href="/'.$page2->full_slug.'"'));
            $this->assertTrue(Str::contains($content->{$link_field}, 'href="'.$page3->id.'"'));
            $this->assertFalse(Str::contains($content->{$link_field}, 'href="/'.$page3->full_slug.'"'));

            session()->pull('editing');

            // if not editing, the links should be parsed for the frontend
            $this->assertFalse(Str::contains($content->{$link_field}, 'href="'.$page1->id.'"'));
            $this->assertTrue(Str::contains($content->{$link_field}, 'href="/'.$page1->full_slug.'"'));
            $this->assertFalse(Str::contains($content->{$link_field}, 'href="'.$page2->id.'"'));
            $this->assertTrue(Str::contains($content->{$link_field}, 'href="/'.$page2->full_slug.'"'));
            $this->assertFalse(Str::contains($content->{$link_field}, 'href="'.$page3->id.'"'));
            $this->assertTrue(Str::contains($content->{$link_field}, 'href="/'.$page3->full_slug.'"'));
        }
    }

    /** @test **/
    public function if_a_page_is_displayed_in_the_front_end_we_convert_page_id_links_with_content_links()
    {
        $content = $this->getModel();
        $content_element = $content->contentElement;
        $this->assertInstanceOf(ContentElement::class, $content_element);
        $page = Page::factory()->create();
        $this->assertNotNull($page->full_slug);
        $page->contentElements()->attach($content_element, ['sort_order' => 1, 'unlisted' => false, 'expandable' => false, 'version_id' => $page->getDraftVersion()->id]);

        $content2 = $this->getModel();
        $content_element2 = $content2->contentElement;
        $content_element2->pages()->detach();
        $page->contentElements()->attach($content_element2, ['sort_order' => 1, 'unlisted' => false, 'expandable' => false, 'version_id' => $page->getDraftVersion()->id]);

        foreach ($this->getLinkFields() as $link_field) {
            $body = '<p>'.$this->faker->sentence.' <a class="button float-right" href="'.$page->id.'#c-'.$content_element->uuid.'" target="__blank" rel="noopener noreferrer nofollow">'.$page->name.'</a></p>';
            $body .= '<p>'.$this->faker->sentence.' <a class="button float-right" href="'.$page->id.'#c-'.$content_element2->uuid.'" target="__blank" rel="noopener noreferrer nofollow">'.$page->name.'</a></p>';

            $content->{$link_field} = $body;
            $content->save();

            $content->refresh();

            session()->put('editing', true);

            $this->assertTrue(Str::contains($content->{$link_field}, 'href="'.$page->id.'#c-'.$content_element->uuid.'"'));
            $this->assertFalse(Str::contains($content->{$link_field}, 'href="/'.$page->full_slug.'#c-'.$content_element->uuid.'"'));

            $this->assertTrue(Str::contains($content->{$link_field}, 'href="'.$page->id.'#c-'.$content_element2->uuid.'"'));
            $this->assertFalse(Str::contains($content->{$link_field}, 'href="/'.$page->full_slug.'#c-'.$content_element2->uuid.'"'));

            session()->pull('editing');

            // if not editing, the links should be parsed for the frontend
            $this->assertFalse(Str::contains($content->{$link_field}, 'href="'.$page->id.'#c-'.$content_element->uuid.'"'));
            $this->assertTrue(Str::contains($content->{$link_field}, 'href="/'.$page->full_slug.'#c-'.$content_element->uuid.'"'));

            $this->assertFalse(Str::contains($content->{$link_field}, 'href="'.$page->id.'#c-'.$content_element2->uuid.'"'));
            $this->assertTrue(Str::contains($content->{$link_field}, 'href="/'.$page->full_slug.'#c-'.$content_element2->uuid.'"'));
        }
    }

    /** @test **/
    public function page_links_have_a_click_event_for_vue_to_expand_any_expanders()
    {
        $content = $this->getModel();
        $content_element1 = $content->contentElement;
        $this->assertInstanceOf(ContentElement::class, $content_element1);
        $page = Page::factory()->create();
        $this->assertNotNull($page->full_slug);
        $page->contentElements()->attach($content_element1, ['sort_order' => 1, 'unlisted' => false, 'expandable' => false, 'version_id' => $page->getDraftVersion()->id]);

        $content2 = $this->getModel();
        $content_element2 = $content2->contentElement;
        $content_element2->pages()->detach();
        $page->contentElements()->attach($content_element2, ['sort_order' => 1, 'unlisted' => false, 'expandable' => false, 'version_id' => $page->getDraftVersion()->id]);

        $page->refresh();

        $this->assertEquals(2, $page->contentElements->count());

        foreach ($this->getLinkFields() as $link_field) {
            $body = '<p>'.$this->faker->sentence.' <a class="button float-right" href="'.$page->id.'#c-'.$content_element1->uuid.'" rel="noopener noreferrer nofollow">'.$page->name.'</a></p>';
            $body .= '<p>'.$this->faker->sentence.' <a class="button float-right" href="'.$page->id.'#c-'.$content_element2->uuid.'" rel="noopener noreferrer nofollow">'.$page->name.'</a></p>';

            $content->{$link_field} = $body;
            $content->save();

            $content->refresh();

            session()->put('editing', true);

            $this->assertFalse(Str::contains($content->{$link_field}, '@click="$eventer.$emit(\'toggle-expander\', \''.$content_element1->uuid.'\')"'));
            $this->assertFalse(Str::contains($content->{$link_field}, '@click="$eventer.$emit(\'toggle-expander\', \''.$content_element2->uuid.'\')"'));

            session()->pull('editing');

            // if not editing, the links should be parsed for the frontend
            $this->assertTrue(Str::contains($content->{$link_field}, '@click="$eventer.$emit(\'toggle-expander\', \''.$content_element1->uuid.'\')"'));
            $this->assertTrue(Str::contains($content->{$link_field}, '@click="$eventer.$emit(\'toggle-expander\', \''.$content_element2->uuid.'\')"'));
        }
    }
}
