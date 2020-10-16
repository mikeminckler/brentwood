<?php

namespace Tests\Feature;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Event;

use App\Events\PageSaved;
use App\Events\BlogSaved;
use App\Events\ContentElementCreated;
use App\Events\ContentElementSaved;
use App\Events\ContentElementRemoved;

use App\Models\User;
use App\Models\TextBlock;
use App\Models\Tag;
use App\Models\Blog;
use App\Models\Page;
use App\Models\ContentElement;
use App\Models\Version;

trait PagesTestTrait
{
    abstract protected function getModel();
    abstract protected function getClassname();

    /** @test **/
    public function a_page_can_be_loaded_via_ajax()
    {
        $this->signInAdmin();
        session()->put('editing', true);

        $content_element = $this->createContentElement(TextBlock::factory(), $this->getModel());
        $text_block = $content_element->content;
        $page = $content_element->{Str::plural($this->getClassname())}->first();

        $this->withoutExceptionHandling();

        $this->json('GET', route('pages.load', ['page' => $page->full_slug]))
            ->assertSuccessful()
            ->assertSessionHas('editing')
            ->assertJsonFragment([
                'body' => $text_block->body,
                'type' => $page->type,
            ]);
    }

    /** @test **/
    public function a_page_can_be_set_to_unlisted_and_not_unlisted()
    {
        $page = $this->getModel();

        $this->assertFalse($page->unlisted);

        $this->json('POST', route(Str::plural($this->getClassname()).'.unlist', ['id' => $page->id]))
            ->assertStatus(401);

        $this->signIn(User::factory()->create());

        $this->withoutExceptionHandling();
        $this->json('POST', route(Str::plural($this->getClassname()).'.unlist', ['id' => $page->id]))
            ->assertStatus(403);

        $this->signInAdmin();

        $this->json('POST', route(Str::plural($this->getClassname()).'.unlist', ['id' => $page->id]))
             ->assertSuccessful()
             ->assertJsonFragment([
                'success' => Str::title($this->getClassname()).' Hidden',
             ]);

        $page->refresh();

        $this->assertTrue($page->unlisted);

        $this->json('POST', route(Str::plural($this->getClassname()).'.reveal', ['id' => $page->id]))
             ->assertSuccessful()
             ->assertJsonFragment([
                'success' => Str::title($this->getClassname()).' Revealed',
             ]);

        $page->refresh();

        $this->assertFalse($page->unlisted);
    }

    /** @test **/
    public function when_a_page_is_saved_an_event_is_broadcast()
    {
        Event::fake();

        $page = $this->getModel();
        $input = $this->getModel()->toArray();

        $this->signInAdmin();

        //$this->withoutExceptionHandling();
        $this->postJson(route(Str::plural($this->getClassname()).'.update', ['id' => $page->id]), $input)
            //->assertSuccessful()
            ->assertJsonFragment([
                'success' => Str::title($this->getClassname()).' Saved',
                'full_slug' => $page->refresh()->full_slug,
            ]);

        $page->refresh();

        if ($this->getClassname() === 'page') {
            Event::assertDispatched(function (PageSaved $event) use ($page) {
                return $event->{$this->getClassname()}->id === $page->id;
            });
        } elseif ($this->getClassname() === 'blog') {
            Event::assertDispatched(function (BlogSaved $event) use ($page) {
                return $event->{$this->getClassname()}->id === $page->id;
            });
        }
    }

    /** @test **/
    public function loading_a_page_includes_its_tags()
    {
        $this->signInAdmin();
        session()->put('editing', true);

        $content_element = $this->createContentElement(TextBlock::factory(), $this->getModel());
        $text_block = $content_element->content;
        $page = $content_element->{Str::plural($this->getClassname())}->first();

        $tag_name = $this->faker->firstName;
        $page->addTag($tag_name);
        $page->refresh();

        $this->assertEquals(1, $page->tags->count());

        $this->withoutExceptionHandling();

        $this->json('GET', route('pages.load', ['page' => $page->full_slug]))
            ->assertSuccessful()
            ->assertSessionHas('editing')
            ->assertJsonFragment([
                'body' => $text_block->body,
                'type' => $page->type,
                'name' => $tag_name,
            ]);
    }

    /** @test **/
    public function a_pages_tags_are_saved_on_update()
    {
        $page = $this->getModel();
        $input = $this->getFactory()->raw();

        $tag1 = Tag::factory()->create();
        $tag2 = Tag::factory()->create();
        $tag3 = Tag::factory()->create();

        $input['tags'] = [
            $tag1,
            $tag2,
        ];

        $this->signInAdmin();

        $this->withoutExceptionHandling();

        $this->postJson(route(Str::plural($this->getClassname()).'.update', ['id' => $page->id]), $input)
            ->assertSuccessful()
            ->assertJsonFragment([
                'success' => Str::title($this->getClassname()).' Saved',
            ]);

        $page->refresh();

        $this->assertTrue($page->tags->contains('id', $tag1->id));
        $this->assertTrue($page->tags->contains('id', $tag2->id));

        $input['tags'] = [
            $tag1,
            $tag3,
        ];

        $this->signInAdmin();

        $this->postJson(route(Str::plural($this->getClassname()).'.update', ['id' => $page->id]), $input)
            ->assertSuccessful()
            ->assertJsonFragment([
                'success' => Str::title($this->getClassname()).' Saved',
            ]);

        $page->refresh();

        $this->assertTrue($page->tags->contains('id', $tag1->id));
        $this->assertFalse($page->tags->contains('id', $tag2->id));
        $this->assertTrue($page->tags->contains('id', $tag3->id));

        $input = $this->getFactory()->raw();

        $this->postJson(route(Str::plural($this->getClassname()).'.update', ['id' => $page->id]), $input)
            ->assertSuccessful()
            ->assertJsonFragment([
                'success' => Str::title($this->getClassname()).' Saved',
            ]);

        $page->refresh();

        $this->assertEquals(0, $page->tags->count());
    }

    /** @test **/
    public function a_page_can_be_rendered()
    {
        $content_element = $this->createContentElement(TextBlock::factory(), $this->getModel());
        $text_block = $content_element->content;
        $page = $content_element->{Str::plural($this->getClassname())}->first();
        $page->publish();

        $this->json('GET', route('pages.load', ['page' => $page->full_slug, 'render' => 'true']))
            ->assertSuccessful();
    }

    /** @test **/
    public function a_content_elements_draft_can_be_removed_from_a_page()
    {
        $page = $this->getModel();
        $page->publish();

        $published_content_element = ContentElement::factory()->for(TextBlock::factory(), 'content')->create([
            'version_id' => $page->published_version_id,
        ]);

        $published_content_element->{Str::plural($this->getClassname())}()->detach();
        $published_content_element->{Str::plural($this->getClassname())}()->attach($page, ['sort_order' => $this->faker->randomNumber(1), 'unlisted' => false, 'expandable' => false]);

        $this->assertInstanceOf(get_class($page), $published_content_element->{Str::plural($this->getClassname())}()->first());
        $page = $published_content_element->{Str::plural($this->getClassname())}()->first();

        $this->assertNotNull($page->publishedVersion);
        $this->assertInstanceOf(Version::class, $page->publishedVersion);

        $content_element = ContentElement::factory()->for(TextBlock::factory(), 'content')->create([
            'uuid' => $published_content_element->uuid,
            'version_id' => $page->draft_version_id,
        ]);

        $content_element->{Str::plural($this->getClassname())}()->detach();
        $content_element->{Str::plural($this->getClassname())}()->attach($page, ['sort_order' => $this->faker->randomNumber(1), 'unlisted' => false, 'expandable' => false]);

        $page->refresh();

        $this->assertEquals(2, $page->contentElements()->count());

        $this->json('POST', route('content-elements.remove', ['id' => $content_element->id]))
            ->assertStatus(401);

        $this->signIn(User::factory()->create());

        $this->json('POST', route('content-elements.remove', ['id' => $content_element->id]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['pivot.contentable_id', 'pivot.contentable_type']);

        $input = [];
        $input['pivot'] = [
            'contentable_id' => $page->id,
            'contentable_type' => get_class($page),
        ];

        $this->json('POST', route('content-elements.remove', ['id' => $content_element->id]), $input)
            ->assertStatus(403);

        $this->assertEquals(1, ContentElement::where('id', $content_element->id)->get()->count());
        $this->signInAdmin();

        $this->assertNotNull($content_element->getPreviousVersion());
        $this->assertEquals($published_content_element->id, $content_element->getPreviousVersion()->id);

        $this->withoutExceptionHandling();
        $this->json('POST', route('content-elements.remove', ['id' => $content_element->id]), $input)
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
        $content_element = $this->createContentElement(TextBlock::factory());
        $content_element->delete();
        $this->assertEquals(0, ContentElement::where('id', $content_element->id)->get()->count());

        $this->json('POST', route('content-elements.restore', ['id' => $content_element->id]))
            ->assertStatus(401);

        $this->signIn(User::factory()->create());

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
    public function a_content_element_can_be_completely_removed()
    {
        $page = $this->getModel();
        $page->publish();
        $published_content_element = ContentElement::factory()->for(TextBlock::factory(), 'content')->create([
            'version_id' => $page->published_version_id,
        ]);

        $content_element = ContentElement::factory()->for(TextBlock::factory(), 'content')->create([
            'uuid' => $published_content_element->uuid,
            'version_id' => $page->draft_version_id,
        ]);

        $content_element->pages()->detach();
        $content_element->pages()->attach($page, ['sort_order' => $this->faker->randomNumber(1), 'unlisted' => false, 'expandable' => false]);

        $this->json('POST', route('content-elements.remove', ['id' => $content_element->id]), ['remove_all' => true])
            ->assertStatus(401);

        $this->signIn(User::factory()->create());

        $this->json('POST', route('content-elements.remove', ['id' => $content_element->id]), ['remove_all' => true])
             ->assertStatus(422)
             ->assertJsonValidationErrors(['pivot.contentable_id', 'pivot.contentable_type']);

        $input = ['remove_all' => true];
        $input['pivot'] = [
            'contentable_id' => $page->id,
            'contentable_type' => get_class($page),
        ];

        $this->json('POST', route('content-elements.remove', ['id' => $content_element->id]), $input)
            ->assertStatus(403);

        $this->assertEquals(1, ContentElement::where('id', $content_element->id)->get()->count());
        $this->signInAdmin();

        $this->withoutExceptionHandling();
        $this->json('POST', route('content-elements.remove', ['id' => $content_element->id]), $input)
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
        $content_element = $this->createContentElement(TextBlock::factory(), $this->getModel());

        $page = $content_element->{Str::plural($this->getClassname())}->first();

        $this->assertInstanceOf(get_class($this->getModel()), $page);

        $user = User::factory()->create();
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
        $input['content'] = TextBlock::factory()->raw();

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
        $content_element = $this->createContentElement(TextBlock::factory(), $this->getModel());
        $page = $content_element->{Str::plural($this->getClassname())}->first();
        $this->assertInstanceOf(get_class($this->getModel()), $page);

        $user = User::factory()->create();
        $page->createPageAccess($user);
        $user->refresh();

        $this->assertTrue($user->can('update', $page));

        $content_element['pivot'] = [
            'sort_order' => $this->faker->randomNumber(1),
            'unlisted' => false,
            'expandable' => false,
        ];
        $input = $content_element->toArray();
        $input['content'] = TextBlock::factory()->raw();

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

        $content_element = $this->createContentElement(TextBlock::factory(), $this->getModel());
        $text_block = $content_element->content;
        $content_element_id = $content_element->id;
        $page = $content_element->{Str::plural($this->getClassname())}->first();
        $this->assertInstanceOf(get_class($this->getModel()), $page);
        $this->assertEquals(1, $content_element->{Str::plural($this->getClassname())}()->count());

        $this->json('POST', route(Str::plural($this->getClassname()).'.publish', ['id' => $page->id]))
             ->assertSuccessful()
             ->assertJsonFragment([
                'success' => Str::title($this->getClassname()).' Published',
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
        $input['content'] = TextBlock::factory()->raw();
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
        $content_element = $this->createContentElement(TextBlock::factory(), $this->getModel());
        $page = $content_element->{Str::plural($this->getClassname())}->first();
        $this->assertInstanceOf(get_class($this->getModel()), $page);

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
        $input['content'] = TextBlock::factory()->raw();

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
        $content_element = $this->createContentElement(TextBlock::factory());
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
        $input['content'] = TextBlock::factory()->raw();

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
        $content_element = $this->createContentElement(TextBlock::factory(), $this->getModel());
        $page = $content_element->{Str::plural($this->getClassname())}->first();
        $this->assertInstanceOf(get_class($this->getModel()), $page);
        $sort_order = $page->pivot->sort_order;
        $this->assertTrue($sort_order > 0);

        $this->json('POST', route('content-elements.load', ['id' => $content_element->id]))
            ->assertStatus(401);

        $this->signIn(User::factory()->create());

        $this->json('POST', route('content-elements.load', ['id' => $content_element->id]), [])
             ->assertStatus(422)
             ->assertJsonValidationErrors([
                'pivot.contentable_id',
                'pivot.contentable_type',
             ]);

        $input = [];
        $input['pivot']['contentable_id'] = $page->id;
        $input['pivot']['contentable_type'] = get_class($page);

        $this->json('POST', route('content-elements.load', ['id' => $content_element->id]), $input)
            ->assertStatus(403);

        $this->signInAdmin();

        $this->withoutExceptionHandling();
        $this->json('POST', route('content-elements.load', ['id' => $content_element->id]), $input)
            ->assertSuccessful()
            ->assertJsonFragment([
                'id' => $content_element->id,
                'sort_order' => $sort_order,
            ]);
    }

    /** @test **/
    public function an_event_is_broadcast_when_a_content_element_is_deleted()
    {
        $content_element = $this->createContentElement(TextBlock::factory(), $this->getModel());
        $page = $content_element->{Str::plural($this->getClassname())}->first();
        $this->assertInstanceOf(get_class($this->getModel()), $page);

        Event::fake();

        $this->signInAdmin();

        $this->json('POST', route('content-elements.remove', ['id' => $content_element->id]), [])
             ->assertStatus(422)
             ->assertJsonValidationErrors([
                'pivot.contentable_id',
                'pivot.contentable_type',
             ]);

        $input = [];
        $input['pivot']['contentable_id'] = $page->id;
        $input['pivot']['contentable_type'] = get_class($page);

        $this->withoutExceptionHandling();
        $this->json('POST', route('content-elements.remove', ['id' => $content_element->id]), $input)
            ->assertSuccessful()
            ->assertJsonFragment([
                'success' => 'Text Block Removed',
                //'uuid' => $content_element->uuid,
            ]);

        Event::assertDispatched(function (ContentElementRemoved $event) use ($content_element, $page) {
            return $event->content_element->id === $content_element->id && $event->page->id === $page->id;
        });
    }

    /** @test **/
    public function a_content_element_can_be_removed_from_one_page_but_stay_on_another()
    {
        $this->fail('write this test');
    }

    /** @test **/
    public function a_content_element_can_be_restored_to_one_page_but_not_the_other()
    {
        $this->fail('write this test');
    }
}
