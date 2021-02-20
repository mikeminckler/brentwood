<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use Illuminate\Support\Arr;

use App\Models\Page;
use App\Models\TextBlock;
use App\Models\User;
use App\Models\Tag;
use App\Models\ContentElement;

class ContentElementTest extends TestCase
{
    use WithFaker;

    /** @test **/
    public function a_content_element_can_be_published_directly()
    {
        $page = Page::factory()->create();
        $content_element1 = $this->createContentElement(TextBlock::factory(), $page);
        $content_element2 = $this->createContentElement(TextBlock::factory(), $page);
        $content_element3 = $this->createContentElement(TextBlock::factory(), $page);
        $content_element3->publish_at = now()->subHours(1);
        $content_element3->save();

        $this->json('POST', route('content-elements.publish', ['id' => $content_element1->id]))
            ->assertStatus(401);

        $this->signIn(User::factory()->create());

        $this->json('POST', route('content-elements.publish', ['id' => $content_element1->id]))
             ->assertStatus(403);

        $this->signInAdmin();

        $this->json('POST', route('content-elements.publish', ['id' => $content_element1->id]), ['pivot.contentable_type' => $page->type])
             ->assertStatus(422)
             ->assertJsonValidationErrors([
                 'pivot.contentable_id',
                 //'pivot.contentable_type',
             ]);

        $this->withoutExceptionHandling();
        $this->json('POST', route('content-elements.publish', ['id' => $content_element1->id]), ['pivot' => ['contentable_id' => $page->id, 'contentable_type' => 'page']])
             ->assertSuccessful()
             ->assertJsonFragment([
                 'success' => 'Text Block Published'
             ]);

        $content_element1->refresh();
        $content_element2->refresh();
        $content_element3->refresh();

        $this->assertNotNull($content_element1->getPageVersion($page)->published_at);
        $this->assertNull($content_element2->getPageVersion($page)->published_at);
        $this->assertNull($content_element3->getPageVersion($page)->published_at);
    }

    /** @test **/
    public function a_content_element_loads_its_latest_version_from_its_uuid()
    {
        $content_element = $this->createContentElement(TextBlock::factory());
        $uuid = $content_element->uuid;
        $page = $content_element->pages()->first();

        $page->publish();

        $content_element->refresh();

        $this->assertNotNull($content_element->getPageVersion($page)->published_at);

        $this->signInAdmin();

        $input = $content_element->toArray();

        $this->assertEquals(1, $page->contentElements()->count());

        $new_page = Page::factory()->create();

        $input['pivot'] = [
            'contentable_id' => $new_page->id,
            'contentable_type' => get_class($new_page),
            'sort_order' => 1,
            'unlisted' => false,
            'expandable' => false,
        ];

        $paragraph = $this->faker->paragraph;

        $input['content']['body'] = $paragraph;

        $this->json('POST', route('content-elements.update', ['id' => $content_element->id]), $input)
             ->assertSuccessful()
             ->assertJsonFragment([
                'success' => 'Text Block Saved',
             ]);

        $page->refresh();
        $this->assertEquals(2, $page->contentElements()->count());

        $new_content_element = $page->contentElements()->get()->last();

        $this->assertEquals($uuid, $new_content_element->uuid);

        $this->assertNotEquals($content_element->id, $new_content_element->id);

        $this->withoutExceptionHandling();

        $this->json('POST', route('content-elements.load', ['id' => $content_element->id]), ['pivot' => ['contentable_id' => $page->id, 'contentable_type' => $page->type]])
             ->assertSuccessful()
             ->assertJsonFragment([
                 'uuid' => $uuid,
                 'id' => $new_content_element->id,
                 'contentable_id' => $page->id,
                 'body' => $paragraph,
             ]);
    }

    /** @test **/
    public function a_content_element_can_save_tags()
    {
        $tag = Tag::factory()->create();

        $input = [];
        $input['id'] = '0.0';
        $input['type'] = 'text-block';
        $input['content'] = TextBlock::factory()->raw();
        $input['content']['id'] = 0;

        $page = Page::factory()->create();

        $input['pivot'] = [
            'contentable_id' => $page->id,
            'contentable_type' => get_class($page),
            'sort_order' => 1,
            'unlisted' => false,
            'expandable' => false,
        ];

        $input['tags'] = [
            $tag->toArray(),
        ];

        $this->signInAdmin();

        $this->withoutExceptionHandling();

        $this->json('POST', route('content-elements.store'), $input)
             ->assertSuccessful()
             ->assertJsonFragment([
                'success' => 'Text Block Saved',
                'contentable_id' => $page->id,
                'sort_order' => 1,
                'unlisted' => 0,
                'expandable' => 0,
             ]);

        $page->refresh();
        $content_element = ContentElement::all()->last();

        $this->assertEquals(1, $page->contentElements()->count());
        $this->assertEquals($content_element->id, $page->contentElements()->first()->id);

        $this->assertNotNull($content_element->tags);
        $this->assertEquals(1, $content_element->tags->count());
        $this->assertTrue($content_element->tags->contains('id', $tag->id));

        // make sure that json encoding includes the tags
        $data = ContentElement::find($content_element->id)->toArray();

        $this->assertNotNull(Arr::get($data, 'tags'));
        $this->assertTrue(collect(Arr::get($data, 'tags'))->contains('id', $tag->id));
    }
}
