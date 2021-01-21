<?php

namespace Tests\Unit;

use Tests\TestCase;

use Illuminate\Support\Collection;

use App\Models\Page;
use App\Models\TextBlock;
use App\Models\PhotoBlock;
use App\Models\Tag;
use App\Models\Inquiry;

use Tests\Unit\TagsTrait;

class InquiryTest extends TestCase
{

    use TagsTrait;

    protected function getClassname()
    {
        return 'inquiry';
    }

    /** @test **/
    public function the_inquiry_page_can_be_found()
    {
        $page = Page::find(2);
        $inquiry_page = Inquiry::findPage();  
        $this->assertInstanceOf(Page::class, $inquiry_page);
        $this->assertEquals($page->id, $inquiry_page->id);
    }

    /** @test **/
    public function tags_for_the_inquiry_come_from_the_content_elements_on_the_inquiry_page()
    {
        $inquiry_page = Page::find(2);
        $text_block = $this->createContentElement(TextBlock::factory(), $inquiry_page);
        $photo_block = $this->createContentElement(PhotoBlock::factory(), $inquiry_page);

        $tag1 = Tag::factory()->create();
        $tag2 = Tag::factory()->create();
        $unpublished_tag = Tag::factory()->create();

        $text_block->tags()->attach($tag1);
        $photo_block->tags()->attach($tag2);

        $inquiry_page->publish();

        $unpublished_ce = $this->createContentElement(TextBlock::factory(), $inquiry_page);
        $unpublished_ce->tags()->attach($unpublished_tag);

        $tags = Inquiry::getTags();

        $this->assertInstanceOf(Collection::class, $tags);

        $this->assertTrue($tags->contains('id', $tag1->id));
        $this->assertTrue($tags->contains('id', $tag2->id));
        $this->assertFalse($tags->contains('id', $unpublished_tag->id));

    }
}
