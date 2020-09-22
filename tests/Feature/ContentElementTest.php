<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\ContentElement;
use App\Models\User;
use App\Models\Page;
use App\Models\TextBlock;
use App\Models\Version;

use Illuminate\Support\Facades\Event;
use App\Events\ContentElementSaved;
use App\Events\ContentElementCreated;
use App\Events\ContentElementRemoved;

class ContentElementTest extends TestCase
{
    use WithFaker;

    protected function getModel()
    {
        return factory(ContentElement::class)->states('text-block')->create();
    }

    /** @test **/
    public function a_content_elements_draft_can_be_removed()
    {
        $published_content_element = ContentElement::factory()
                                ->page()
                                ->hasAttached(Page::factory()->published(), ['sort_order' => 1, 'unlisted' => 0, 'expandable' => 0])
                                ->for(TextBlock::factory(), 'content')
                                ->create();

        /*
        $page = Page::factory()->published()->create();
        $published_content_element = ContentElement::factory()->for(TextBlock::factory(), 'content')->create([
            'version_id' => $page->published_version_id,
        ]);
         */

        $this->assertInstanceOf(Page::class, $published_content_element->pages()->first());
        $page = $published_content_element->pages()->first();
        $this->assertNotNull($page->publishedVersion);
        $this->assertInstanceOf(Version::class, $page->publishedVersion);

        $content_element = ContentElement::factory()->for(TextBlock::factory(), 'content')->create([
            'uuid' => $published_content_element->uuid,
            'version_id' => $page->draft_version_id,
        ]);

        $content_element->pages()->detach();
        $content_element->pages()->attach($page, ['sort_order' => $this->faker->randomNumber(1), 'unlisted' => false, 'expandable' => false]);

        $this->json('POST', route('content-elements.remove', ['id' => $content_element->id]))
            ->assertStatus(401);

        $this->signIn(User::factory()->create());

        $this->json('POST', route('content-elements.remove', ['id' => $content_element->id]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['page_id']);

        $this->json('POST', route('content-elements.remove', ['id' => $content_element->id]), ['page_id' => $page->id])
            ->assertStatus(403);

        $this->assertEquals(1, ContentElement::where('id', $content_element->id)->get()->count());
        $this->signInAdmin();

        $this->withoutExceptionHandling();
        $this->json('POST', route('content-elements.remove', ['id' => $content_element->id]), ['page_id' => $page->id])
            ->assertSuccessful()
            ->assertJsonFragment([
                'success' => 'Text Block Removed',
                'id' => $published_content_element->id,
                'uuid' => $content_element->uuid,
            ]);

        $this->assertEquals(0, ContentElement::where('id', $content_element->id)->get()->count());
    }

    /** @test **/
    public function a_content_element_can_be_restored()
    {
        $content_element = factory(ContentElement::class)->states('text-block')->create();
        $content_element->delete();
        $this->assertEquals(0, ContentElement::where('id', $content_element->id)->get()->count());

        $this->json('POST', route('content-elements.restore', ['id' => $content_element->id]))
            ->assertStatus(401);

        $this->signIn(factory(User::class)->create());

        $this->withoutExceptionHandling();
        $this->json('POST', route('content-elements.restore', ['id' => $content_element->id]))
            ->assertStatus(403);

        $this->signInAdmin();

        $this->json('POST', route('content-elements.restore', ['id' => $content_element->id]))
            ->assertSuccessful()
            ->assertJsonFragment([
                'success' => 'Text Block Restored',
            ]);

        $this->assertEquals(1, ContentElement::where('id', $content_element->id)->get()->count());
    }

    /** @test **/
    public function a_content_elements_can_be_completely_removed()
    {
        $page = factory(Page::class)->states('published')->create();
        $published_content_element = factory(ContentElement::class)->states('text-block')->create([
            'version_id' => $page->published_version_id,
        ]);

        $content_element = factory(ContentElement::class)->states('text-block')->create([
            'uuid' => $published_content_element->uuid,
            'version_id' => $page->draft_version_id,
        ]);

        $content_element->pages()->detach();
        $content_element->pages()->attach($page, ['sort_order' => $this->faker->randomNumber(1), 'unlisted' => false, 'expandable' => false]);

        $this->json('POST', route('content-elements.remove', ['id' => $content_element->id]), ['remove_all' => true])
            ->assertStatus(401);

        $this->signIn(factory(User::class)->create());

        $this->json('POST', route('content-elements.remove', ['id' => $content_element->id]), ['remove_all' => true])
             ->assertStatus(422)
         ->assertJsonValidationErrors(['page_id']);

        $this->json('POST', route('content-elements.remove', ['id' => $content_element->id]), ['remove_all' => true, 'page_id' => $page->id])
            ->assertStatus(403);

        $this->assertEquals(1, ContentElement::where('id', $content_element->id)->get()->count());
        $this->signInAdmin();

        $this->withoutExceptionHandling();
        $this->json('POST', route('content-elements.remove', ['id' => $content_element->id]), ['remove_all' => true, 'page_id' => $page->id])
            ->assertSuccessful()
            ->assertJsonFragment([
                'success' => 'Text Block Removed',
            ]);

        $this->assertEquals(0, ContentElement::where('id', $content_element->id)->get()->count());
        $this->assertEquals(0, ContentElement::where('id', $published_content_element->id)->get()->count());
    }

    /** @test **/
    public function a_user_with_page_editing_can_update_content_elements()
    {
        $content_element = factory(ContentElement::class)->states('page', 'text-block')->create();

        $page = $content_element->pages->first();

        $this->assertInstanceOf(Page::class, $page);

        $user = factory(User::class)->create();
        $page->createPageAccess($user);
        $user->refresh();

        $this->assertTrue($user->can('update', $page));

        $content_element['pivot'] = [
            'contentable_id' => $page->id,
            'contentable_type' => get_class($page),
            'sort_order' => $this->faker->randomNumber(1),
            'unlisted' => false,
            'expandable' => false,
        ];
        $input = $content_element->toArray();
        $input['content'] = factory(TextBlock::class)->raw();

        $this->signIn($user);
        $this->json('POST', route('content-elements.update', ['id' => $content_element->id]), $input)
             ->assertJsonFragment([
                'success' => 'Text Block Saved',
             ])
             ->assertSuccessful();
    }

    /** @test **/
    public function a_user_with_page_editing_can_create_content_elements()
    {
        $content_element = factory(ContentElement::class)->states('page', 'text-block')->create();
        $page = $content_element->pages->first();
        $this->assertInstanceOf(Page::class, $page);

        $user = factory(User::class)->create();
        $page->createPageAccess($user);
        $user->refresh();

        $this->assertTrue($user->can('update', $page));

        $content_element['pivot'] = [
            'sort_order' => $this->faker->randomNumber(1),
            'unlisted' => false,
            'expandable' => false,
        ];
        $input = $content_element->toArray();
        $input['content'] = factory(TextBlock::class)->raw();

        $this->signIn($user);

        $this->json('POST', route('content-elements.store'), $input)
             ->assertStatus(422)
            ->assertJsonValidationErrors([
                'pivot.contentable_id',
                'pivot.contentable_type',
            ]);

        $input['pivot']['contentable_id'] = $page->id;
        $input['pivot']['contentable_type'] = get_class($page);

        $this->json('POST', route('content-elements.store'), $input)
             ->assertSuccessful()
             ->assertJsonFragment([
                'success' => 'Text Block Saved',
             ]);
    }

    /** @test **/
    public function saving_a_new_content_element_when_there_is_already_a_draft_version_should_not_create_a_new_content_element()
    {
        $this->signInAdmin();

        $text_block = factory(TextBlock::class)->create();
        $content_element = $text_block->contentElement;
        $this->assertInstanceOf(ContentElement::class, $content_element);
        $content_element_id = $content_element->id;
        $page = $content_element->pages->first();
        $this->assertInstanceOf(Page::class, $page);
        $this->assertEquals(1, $content_element->pages()->count());

        $this->json('POST', route('pages.publish', ['id' => $page->id]))
             ->assertSuccessful()
             ->assertJsonFragment([
                'success' => 'Page Published',
             ]);

        $page->refresh();
        $content_element->refresh();
        $this->assertEquals($content_element->version->id, $page->publishedVersion->id);

        $content_element = ContentElement::find($content_element_id);

        $this->assertNotNull($page->published_at);
        $this->assertNotNull($content_element->published_at);
        
        $content_element['pivot'] = [
            'contentable_id' => $page->id,
            'contentable_type' => get_class($page),
            'sort_order' => $this->faker->randomNumber(1),
            'unlisted' => false,
            'expandable' => false,
        ];
        $input = $content_element->toArray();
        $input['content'] = factory(TextBlock::class)->raw();
        //$input['version_id'] = $page->getDraftVersion()->id;

        $this->withoutExceptionHandling();
        $this->json('POST', route('content-elements.update', ['id' => $content_element->id]), $input)
             ->assertSuccessful()
             ->assertJsonFragment([
                'success' => 'Text Block Saved',
                'id' => $content_element_id + 1,
             ]);

        $draft_content_element = ContentElement::all()->last();

        $this->assertNull($draft_content_element->published_at);
        $this->assertNotEquals($content_element_id, $draft_content_element->id);
        $this->assertEquals($content_element_id + 1, $draft_content_element->id);

        $this->json('POST', route('content-elements.update', ['id' => $content_element->id]), $input)
             ->assertSuccessful()
             ->assertJsonFragment([
                'success' => 'Text Block Saved',
                'id' => $draft_content_element->id,
             ]);

        $this->assertEquals($draft_content_element->id, ContentElement::all()->last()->id);
    }

    /** @test **/
    public function an_event_is_broadcast_when_a_content_element_is_saved()
    {
        $content_element = factory(ContentElement::class)->states('page', 'text-block')->create();
        $page = $content_element->pages->first();
        $this->assertInstanceOf(Page::class, $page);

        Event::fake();

        $this->signInAdmin();

        $content_element['pivot'] = [
            'contentable_id' => $page->id,
            'contentable_type' => get_class($page),
            'sort_order' => $this->faker->randomNumber(1),
            'unlisted' => false,
            'expandable' => false,
        ];
        $input = $content_element->toArray();
        $input['content'] = factory(TextBlock::class)->raw();

        $this->json('POST', route('content-elements.update', ['id' => $content_element->id]), $input)
             ->assertSuccessful()
             ->assertJsonFragment([
                'success' => 'Text Block Saved',
             ]);

        $content_element = ContentElement::all()->last();

        Event::assertDispatched(function (ContentElementSaved $event) use ($content_element) {
            return $event->content_element->id === $content_element->id;
        });
    }

    /** @test **/
    public function an_event_is_broadcast_when_a_content_element_is_created()
    {
        $content_element = factory(ContentElement::class)->states('page', 'text-block')->create();
        $page = $content_element->pages->first();
        $this->assertInstanceOf(Page::class, $page);

        Event::fake();

        $this->signInAdmin();

        $content_element['pivot'] = [
            'contentable_id' => $page->id,
            'contentable_type' => get_class($page),
            'sort_order' => $this->faker->randomNumber(1),
            'unlisted' => false,
            'expandable' => false,
        ];
        $input = $content_element->toArray();
        $input['content'] = factory(TextBlock::class)->raw();

        $this->json('POST', route('content-elements.store'), $input)
             ->assertSuccessful()
             ->assertJsonFragment([
                'success' => 'Text Block Saved',
             ]);

        $content_element = ContentElement::all()->last();

        Event::assertDispatched(function (ContentElementCreated $event) use ($content_element, $page) {
            return $event->content_element->id === $content_element->id && $event->page->id === $page->id;
        });
    }

    /** @test **/
    public function a_content_element_can_be_loaded()
    {
        $content_element = factory(ContentElement::class)->states('page', 'text-block')->create();
        $page = $content_element->pages()->first();
        $this->assertInstanceOf(Page::class, $page);
        $sort_order = $page->pivot->sort_order;
        $this->assertTrue($sort_order > 0);

        $this->json('POST', route('content-elements.load', ['id' => $content_element->id]))
            ->assertStatus(401);

        $this->signIn(factory(User::class)->create());

        $this->json('POST', route('content-elements.load', ['id' => $content_element->id]), [])
             ->assertStatus(422)
             ->assertJsonValidationErrors([
                'page_id',
             ]);

        $this->json('POST', route('content-elements.load', ['id' => $content_element->id]), ['page_id' => $page->id])
            ->assertStatus(403);

        $this->signInAdmin();

        $this->withoutExceptionHandling();
        $this->json('POST', route('content-elements.load', ['id' => $content_element->id]), ['page_id' => $page->id])
            ->assertSuccessful()
            ->assertJsonFragment([
                'id' => $content_element->id,
                'sort_order' => $sort_order,
            ]);
    }

    /** @test **/
    public function an_event_is_broadcast_when_a_content_element_is_deleted()
    {
        $content_element = factory(ContentElement::class)->states('page', 'text-block')->create();
        $page = $content_element->pages->first();
        $this->assertInstanceOf(Page::class, $page);

        Event::fake();

        $this->signInAdmin();

        $this->json('POST', route('content-elements.remove', ['id' => $content_element->id]), [])
             ->assertStatus(422)
             ->assertJsonValidationErrors(['page_id']);

        $this->withoutExceptionHandling();
        $this->json('POST', route('content-elements.remove', ['id' => $content_element->id]), ['page_id' => $page->id])
            ->assertSuccessful()
            ->assertJsonFragment([
                'success' => 'Text Block Removed',
                //'uuid' => $content_element->uuid,
            ]);

        Event::assertDispatched(function (ContentElementRemoved $event) use ($content_element, $page) {
            return $event->content_element->id === $content_element->id && $event->page->id === $page->id;
        });
    }
}
