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
    public function the_inquiry_content_page_can_be_found()
    {
        $page = Page::find(3);
        $inquiry_page = Inquiry::findPage();  
        $this->assertInstanceOf(Page::class, $inquiry_page);
        $this->assertEquals($page->id, $inquiry_page->id);
    }

    /** @test **/
    public function tags_for_the_inquiry_come_from_the_content_elements_on_the_inquiry_page()
    {
        $inquiry_page = Inquiry::findPage();
        $inquiry_page->contentElements()->delete();
        $text_block = $this->createContentElement(TextBlock::factory(), $inquiry_page);
        $text_block2 = $this->createContentElement(TextBlock::factory(), $inquiry_page);
        $photo_block = $this->createContentElement(PhotoBlock::factory(), $inquiry_page);

        $tag1 = Tag::factory()->create();
        $tag2 = Tag::factory()->create();
        $unpublished_tag = Tag::factory()->create();

        $text_block->tags()->attach($tag1);
        $text_block2->tags()->attach($tag1);
        $photo_block->tags()->attach($tag2);

        $inquiry_page->publish();

        $unpublished_ce = $this->createContentElement(TextBlock::factory(), $inquiry_page);
        $unpublished_ce->tags()->attach($unpublished_tag);

        $tags = Inquiry::getTags();

        $this->assertInstanceOf(Collection::class, $tags);

        // these two plus the default tags
        $this->assertEquals(4, $tags->count());
        $this->assertTrue($tags->contains('id', $tag1->id));
        $this->assertTrue($tags->contains('id', $tag2->id));
        $this->assertFalse($tags->contains('id', $unpublished_tag->id));

    }

    /** @test **/
    public function inquiry_tags_include_boarding_and_day()
    {
        $boarding_tag = Tag::where('name', 'Boarding Student')->first();
        $day_tag = Tag::where('name', 'Day Student')->first();

        $this->assertNotNull($boarding_tag);
        $this->assertNotNull($day_tag);

        $this->assertTrue(Inquiry::getTags()->contains('id', $boarding_tag->id));
        $this->assertTrue(Inquiry::getTags()->contains('id', $day_tag->id));
    }

    /** @test **/
    public function an_inquiries_tags_filters_out_boarding_and_day()
    {
        
        $boarding_tag = Tag::where('name', 'Boarding Student')->first();
        $day_tag = Tag::where('name', 'Day Student')->first();

        $this->assertNotNull($boarding_tag);
        $this->assertNotNull($day_tag);

        $inquiry_page = Inquiry::findPage();
        $inquiry_page->contentElements()->delete();
        $text_block = $this->createContentElement(TextBlock::factory(), $inquiry_page);

        $tag = Tag::factory()->create();

        $text_block->tags()->attach($tag);

        $inquiry_page->publish();

        $inquiry = Inquiry::factory()->create();
        $inquiry->addTag($tag);

        $inquiry->refresh();

        $this->assertNotNull($inquiry->filtered_tags);
        $this->assertEquals(1, $inquiry->filtered_tags->count());
        $this->assertTrue($inquiry->filtered_tags->contains('id', $tag->id));
        $this->assertFalse($inquiry->filtered_tags->contains('id', $boarding_tag->id));
        $this->assertFalse($inquiry->filtered_tags->contains('id', $day_tag->id));

    }
}