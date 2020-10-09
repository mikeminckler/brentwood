<?php

namespace Tests\Feature;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Event;

use App\Events\PageSaved;
use App\Events\BlogSaved;

use App\Models\User;
use App\Models\TextBlock;
use App\Models\Tag;
use App\Models\Blog;
use App\Models\Page;

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
}
