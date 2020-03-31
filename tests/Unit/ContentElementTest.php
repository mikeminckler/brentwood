<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\ContentElement;
use App\TextBlock;
use App\Page;
use App\Version;
use Illuminate\Support\Arr;

class ContentElementTest extends TestCase
{
    use WithFaker;

    /** @test **/
    public function a_content_element_can_be_created()
    {

        $page = factory(Page::class)->create();
        $text_block = factory(TextBlock::class)->raw();
        $input = [
            'id' => 0,
            'type' => 'text-block',
            'content' => $text_block,
            'page_id' => $page->id,
            'sort_order' => 1,
            'unlisted' => false,
        ];

        $content_element = (new ContentElement)->saveContentElement(null, $input);

        $this->assertInstanceOf(ContentElement::class, $content_element);

        $this->assertEquals($page->id, $content_element->pages->first()->id);
        $this->assertEquals(1, $content_element->pages->first()->pivot->sort_order);
        $this->assertEquals(0, $content_element->pages->first()->pivot->unlisted);
        $this->assertNotNull($content_element->content_id);
        $this->assertEquals(Arr::get($text_block, 'header'), $content_element->content->header);
        $this->assertEquals(Arr::get($text_block, 'body'), $content_element->content->body);

    }

    /** @test **/
    public function a_content_element_belongs_to_a_version()
    {
        $content_element = factory(ContentElement::class)->states('text-block')->create();
        $this->assertInstanceOf(Version::class, $content_element->version);
    }

    /** @test **/
    public function a_content_element_belongs_to_many_page()
    {
        $content_element = factory(ContentElement::class)->states('text-block')->create();
        $this->assertInstanceOf(Page::class, $content_element->pages->first());
    }

    /** @test **/
    public function a_content_element_can_have_a_text_block()
    {
        $content_element = factory(ContentElement::class)->states('text-block')->create();
        $this->assertInstanceOf(TextBlock::class, $content_element->content);
    }

    /** @test **/
    public function a_content_element_has_a_content_type()
    {
        $content_element = factory(ContentElement::class)->states('text-block')->create();
        $this->assertEquals('text-block', $content_element->type);
    }

    /** @test **/
    public function a_new_content_element_is_created_if_the_one_updated_has_been_published()
    {
        $page = factory(Page::class)->states('published')->create();
        $content_element = factory(ContentElement::class)->states('text-block')->create([
            'version_id' => $page->published_version_id,
        ]);

        $content_element->pages()->attach($page, ['sort_order' => $this->faker->randomNumber(1), 'unlisted' => false]);

        $content = $content_element->content;

        $this->assertNotEquals($page->getDraftVersion()->id, $page->publishedVersion->id);

        $this->assertNotNull($content_element->published_at);

        $input = factory(ContentElement::class)->states('text-block')->raw([
            'page_id' => $page->id,
            'sort_order' => $this->faker->randomNumber(1),
            'unlisted' => false,
        ]);
        $input['type'] = 'text-block';
        $input['content'] = factory(TextBlock::class)->raw();
        $input['content']['id'] = $content->id;

        $saved_content_element = (new ContentElement)->saveContentElement($content_element->id, $input);

        $this->assertNotEquals($content_element->id, $saved_content_element->id);
        $this->assertNotEquals($content->id, $saved_content_element->content->id);

        $page->refresh();
        $this->assertEquals($page->getDraftVersion()->id, $saved_content_element->version_id);
    }


    /** @test **/
    public function a_content_element_can_get_its_previous_version()
    {
        $page = factory(Page::class)->states('published')->create();
        $content_element1 = factory(ContentElement::class)->states('text-block')->create([
            'page_id' => $page->id,
            'version_id' => $page->published_version_id,
        ]);

        $this->assertNotNull($content_element1->published_at);

        $content_element2 = factory(ContentElement::class)->states('text-block')->create([
            'uuid' => $content_element1->uuid,
            'page_id' => $page->id,
            'version_id' => $page->draft_version_id,
        ]);

        $this->assertInstanceOf(ContentElement::class, $content_element2->getPreviousVersion());
        $this->assertEquals($content_element1->id, $content_element2->getPreviousVersion()->id);
        $this->assertEquals($content_element1->uuid, $content_element2->uuid);
    }
}
