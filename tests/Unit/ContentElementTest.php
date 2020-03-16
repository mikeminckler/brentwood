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

        $text_block = factory(TextBlock::class)->raw();
        $input = [
            'id' => 0,
            'type' => 'text-block',
            'content_data' => $text_block,
        ];

        $page = factory(Page::class)->create();

        $content_element = (new ContentElement)->saveContentElement(null, $input, $page);

        $this->assertInstanceOf(ContentElement::class, $content_element);

        $this->assertEquals($page->id, $content_element->page->id);
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
}
