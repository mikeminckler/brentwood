<?php

namespace Tests\Unit;

use Tests\TestCase;

use Illuminate\Support\Arr;

use App\Models\TextBlock;
use App\Models\ContentElement;

use Tests\Unit\PageLinkTestTrait;

class TextBlockTest extends TestCase
{
    use PageLinkTestTrait;

    protected function getModel()
    {
        return $this->createContentElement(TextBlock::factory())->content;
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
        $input = TextBlock::factory()->raw();

        $text_block = (new TextBlock)->saveContent($input, null);

        $this->assertInstanceOf(TextBlock::class, $text_block);
        $this->assertEquals(Arr::get($input, 'header'), $text_block->header);
        $this->assertEquals(Arr::get($input, 'body'), $text_block->body);
    }

    /** @test **/
    public function a_text_block_has_a_content_element()
    {
        $text_block = $this->createContentElement(TextBlock::factory())->content;
        $this->assertInstanceOf(ContentElement::class, $text_block->contentElement);
    }
}
