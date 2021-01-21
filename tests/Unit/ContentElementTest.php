<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use Illuminate\Support\Arr;

use App\Models\ContentElement;
use App\Models\TextBlock;
use App\Models\Page;
use App\Models\Version;
use App\Models\Blog;

use Tests\Unit\TagsTrait;

class ContentElementTest extends TestCase
{
    use WithFaker;
    use TagsTrait;

    protected function getClassname()
    {
        return 'content-element';
    }

    protected function getModel()
    {
        return $this->createContentElement(TextBlock::factory());
    }

    /** @test **/
    public function a_content_element_can_be_created_from_the_saveContentElement_function()
    {
        $page = Page::factory()->create();
        $text_block = TextBlock::factory()->raw();
        $input = [
            'id' => 0,
            'type' => 'text-block',
            'content' => $text_block,
            'pivot' => [
                'contentable_id' => $page->id,
                'contentable_type' => get_class($page),
                'sort_order' => 1,
                'unlisted' => false,
                'expandable' => false,
            ],
        ];

        $content_element = (new ContentElement)->saveContentElement($input, null);

        $this->assertInstanceOf(ContentElement::class, $content_element);

        $page->refresh();
        $this->assertInstanceOf(ContentElement::class, $page->contentElements()->first());
        $this->assertEquals($content_element->id, $page->contentElements()->first()->id);

        $this->assertEquals($page->id, $content_element->pages->first()->id);
        $this->assertEquals(1, $content_element->pages->first()->pivot->sort_order);
        $this->assertEquals(0, $content_element->pages->first()->pivot->unlisted);
        $this->assertEquals(0, $content_element->pages->first()->pivot->expandable);
        $this->assertNotNull($content_element->content_id);
        $this->assertEquals(Arr::get($text_block, 'header'), $content_element->content->header);
        $this->assertEquals(Arr::get($text_block, 'body'), $content_element->content->body);
    }

    /** @test **/
    public function a_content_element_has_many_versions()
    {
        $content_element = $this->createContentElement(TextBlock::factory());
        $this->assertEquals(1, $content_element->versions()->count());
        $this->assertInstanceOf(Version::class, $content_element->versions->first());
    }

    /** @test **/
    public function a_content_element_belongs_to_many_pages()
    {
        $content_element = $this->createContentElement(TextBlock::factory(), Page::factory()->create());
        $this->assertInstanceOf(Page::class, $content_element->pages->first());
    }

    /** @test **/
    public function a_content_element_belongs_to_many_blogs()
    {
        $content_element = $this->createContentElement(TextBlock::factory(), Blog::factory()->create());
        $this->assertInstanceOf(Blog::class, $content_element->blogs->first());
    }

    /** @test **/
    public function a_content_element_can_have_a_text_block()
    {
        $content_element = $this->createContentElement(TextBlock::factory());
        $this->assertInstanceOf(TextBlock::class, $content_element->content);
    }

    /** @test **/
    public function a_content_element_has_a_content_type()
    {
        $content_element = $this->createContentElement(TextBlock::factory());
        $this->assertEquals('text-block', $content_element->type);
    }

    /** @test **/
    public function a_new_content_element_is_created_if_the_one_updated_has_been_published()
    {
        $page = Page::factory()->published()->create();
        $content_element = $this->createContentElement(TextBlock::factory(), $page, $page->publishedVersion);

        $content = $content_element->content;

        $this->assertNotEquals($page->getDraftVersion()->id, $page->publishedVersion->id);

        $this->assertNotNull($content_element->contentables->first()->version->published_at);

        $input = ContentElement::factory()->for(TextBlock::factory(), 'content')->raw();
        $input['type'] = 'text-block';
        $input['content'] = TextBlock::factory()->raw();
        $input['content']['id'] = $content->id;
        $input['pivot'] = [
            'contentable_id' => $page->id,
            'contentable_type' => get_class($page),
            'sort_order' => $this->faker->randomNumber(1),
            'unlisted' => false,
            'expandable' => false,
        ];

        $saved_content_element = (new ContentElement)->saveContentElement($input, $content_element->id);

        $this->assertNotEquals($content_element->id, $saved_content_element->id);
        $this->assertNotEquals($content->id, $saved_content_element->content->id);

        $this->assertEquals(1, $content_element->contentables->count());

        $page->refresh();
        $this->assertEquals($page->getDraftVersion()->id, $saved_content_element->contentables()->first()->version->id);
    }


    /** @test **/
    public function a_content_element_can_get_its_previous_version()
    {
        $page = Page::factory()->published()->create();
        $content_element1 = $this->createContentElement(TextBlock::factory(), $page, $page->publishedVersion);

        $this->assertNotNull($content_element1->contentables()->first()->version->published_at);

        $content_element2 = ContentElement::factory()->for(TextBlock::factory(), 'content')->create([
            'uuid' => $content_element1->uuid,
        ]);

        $content_element2->pages()->detach();
        $content_element2->pages()->attach($page, ['sort_order' => $this->faker->randomNumber(1), 'unlisted' => false, 'expandable' => false, 'version_id' => $page->draft_version_id]);

        $this->assertInstanceOf(ContentElement::class, $content_element2->getPreviousVersion($page));
        $this->assertEquals($content_element1->id, $content_element2->getPreviousVersion($page)->id);
        $this->assertEquals($content_element1->uuid, $content_element2->uuid);
    }

    /** @test **/
    public function saving_publish_at_persits_the_correct_value()
    {
        $content_element = $this->createContentElement(TextBlock::factory());
        $content = $content_element->content;

        $page = Page::factory()->published()->create();
        $content_element->pages()->attach($page, ['sort_order' => $this->faker->randomNumber(1), 'unlisted' => false, 'expandable' => false, 'version_id' => $page->getDraftVersion()->id]);

        $input = ContentElement::factory()->for(TextBlock::factory(), 'content')->raw();
        $input['type'] = 'text-block';
        $input['content'] = TextBlock::factory()->raw();
        $input['content']['id'] = $content->id;
        $input['publish_at'] = '2020-08-23T12:00:00.000000Z';

        $input['pivot'] = [
            'contentable_id' => $page->id,
            'contentable_type' => get_class($page),
            'sort_order' => $this->faker->randomNumber(1),
            'unlisted' => false,
            'expandable' => false,
        ];

        (new ContentElement)->saveContentElement($input, $content_element->id);

        $content_element->refresh();

        $content_element_array = $content_element->toArray();
        $this->assertEquals('2020-08-23T12:00:00.000000Z', Arr::get($content_element_array, 'publish_at'));
    }

    /** @test **/
    public function a_content_element_can_get_its_page_version()
    {
        $page = Page::factory()->create();
        $content_element = $this->createContentElement(TextBlock::factory(), $page);

        $version = $page->getDraftVersion();

        $this->assertInstanceOf(Version::class, $content_element->getPageVersion($page));
        $this->assertEquals($version->id, $content_element->getPageVersion($page)->id);

    }
}
