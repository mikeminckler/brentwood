<?php

namespace Tests\Unit;

use Tests\TestCase;

use App\ContentElement;
use App\TextBlock;
use App\Page;
use Illuminate\Support\Arr;

class ContentElementTest extends TestCase
{
    /** @test **/
    public function a_content_element_can_be_created()
    {

        $page = factory(Page::class)->create();
        $text_block = factory(TextBlock::class)->raw();
        $input = [
            'id' => 0,
            'type' => 'text-block',
            'page_id' => $page->id,
            'content' => $text_block,
            'sort_order' => 1,
            'unlisted' => false,
        ];

        $content_element = (new ContentElement)->saveContentElement(null, $input);

        $this->assertInstanceOf(ContentElement::class, $content_element);

        $this->assertEquals($page->id, $content_element->page->id);
        $this->assertEquals(1, $content_element->sort_order);
        $this->assertEquals(0, $content_element->unlisted);
        $this->assertEquals(Arr::get($text_block, 'header'), $content_element->content->header);
        $this->assertEquals(Arr::get($text_block, 'body'), $content_element->content->body);

    }

    /** @test **/
    public function a_content_element_belongs_to_a_page()
    {
        $content_element = factory(ContentElement::class)->states('text-block')->create();
        $this->assertInstanceOf(Page::class, $content_element->page);
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

    /*
    public function a_content_element_has_an_html_attribute()
    {
        $content_element = factory(ContentElement::class)->states('text-block')->create();
        $html = view('content-elements.'.$content_element->type, ['content' => $content_element->content])->render();
        $this->assertEquals($html, $content_element->html);
    }
    */
}
