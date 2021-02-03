<?php

namespace Tests\Unit;

use Tests\TestCase;

use Illuminate\Support\Collection;

use App\Models\Page;
use App\Models\TextBlock;
use App\Models\PhotoBlock;
use App\Models\Tag;
use App\Models\Inquiry;
use App\Models\Livestream;
use App\Models\User;

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

        // these two plus the default admissions group
        $this->assertEquals(3, $tags->count());
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
        // admissions -> tags ->
        $this->assertTrue(Inquiry::getTags()->first()->tags->contains('id', $boarding_tag->id));
        $this->assertTrue(Inquiry::getTags()->first()->tags->contains('id', $day_tag->id));
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

    /** @test **/
    public function inquiry_tags_have_heiarchy()
    {
        $inquiry_page = Inquiry::findPage();
        $inquiry_page->contentElements()->delete();
        $text_block = $this->createContentElement(TextBlock::factory(), $inquiry_page);

        $parent_tag = Tag::factory()->create();
        $tag = Tag::factory()->create([
            'parent_tag_id' => $parent_tag->id,
        ]);
        $text_block->tags()->attach($tag);

        $inquiry_page->publish();

        //dump('CHECKING FOR: '.$tag->name);

        $tags = Inquiry::getTags();

        // we include the two default tags
        $this->assertEquals(2, $tags->count());
        $this->assertTrue($tags->contains('id', $parent_tag->id));
        $this->assertEquals($parent_tag->id, $tags->last()->id);
        $this->assertEquals(1, $tags->last()->tags()->count());
        $this->assertEquals($tag->id, $tags->last()->tags()->first()->id);
    }

    /** @test **/
    public function an_inquiry_can_belong_to_many_livestreams()
    {
        $inquiry = Inquiry::factory()->create();
        $livestream = Livestream::factory()->create();

        $inquiry->saveLivestreams(['livestream' => $livestream]);

        $inquiry->refresh();
        $this->assertEquals(1, $inquiry->livestreams()->count());
        $this->assertEquals($livestream->id, $inquiry->livestreams()->first()->id);
    }

    /** @test **/
    public function an_inquiry_belongs_to_a_user() 
    {
        $user = User::factory()->create();
        $inquiry = Inquiry::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->assertNotNull($inquiry->user);
        $this->assertInstanceOf(User::class, $inquiry->user);
        $this->assertEquals($user->id, $inquiry->user->id);
    }

}
