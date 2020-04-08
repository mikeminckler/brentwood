<?php

namespace Tests\Unit;

use Tests\TestCase;

use App\TextBlock;
use Illuminate\Support\Arr;

use App\ContentElement;
use Tests\Unit\PageLinkTestTrait;

class TextBlockTest extends TestCase
{

    use PageLinkTestTrait;

    protected function getModel()
    {
        return factory(TextBlock::class)->create();
    }

    protected function getLinkFields()
    {
        return [
            'body',
        ];
    }

    /** @test **/
    public function a_text_block_can_be_created()
    {
        $input = factory(TextBlock::class)->raw();

        $text_block = (new TextBlock)->saveContent(null, $input);

        $this->assertInstanceOf(TextBlock::class, $text_block);
        $this->assertEquals(Arr::get($input, 'header'), $text_block->header);
        $this->assertEquals(Arr::get($input, 'body'), $text_block->body);
    }

    /** @test **/
    public function a_text_block_has_a_content_element()
    {
        $text_block = factory(TextBlock::class)->create();
        $this->assertInstanceOf(ContentElement::class, $text_block->contentElement);
    }

}
