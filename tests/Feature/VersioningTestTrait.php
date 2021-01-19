<?php

namespace Tests\Feature;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;
use Carbon\Carbon;

use App\Events\PageSaved;
use App\Events\BlogSaved;

use App\Models\Page;
use App\Models\Version;
use App\Models\ContentElement;
use App\Models\User;
use App\Models\TextBlock;

trait VersioningTestTrait
{
    abstract protected function getModel();
    abstract protected function getClassname();

    /** @test **/
    public function a_page_can_be_published()
    {
        $content_element = $this->createContentElement(TextBlock::factory(), $this->getModel());
        $page = $content_element->{Str::plural($this->getClassname())}()->first();

        $this->assertNull($page->published_at);

        $this->json('POST', route(Str::plural($this->getClassname()).'.publish', ['id' => $page->id]))
            ->assertStatus(401);

        $this->signIn(User::factory()->create());

        $this->withoutExceptionHandling();
        $this->json('POST', route(Str::plural($this->getClassname()).'.publish', ['id' => $page->id]))
            ->assertStatus(403);

        $this->signInAdmin();

        $this->json('POST', route(Str::plural($this->getClassname()).'.publish', ['id' => $page->id]))
             ->assertSuccessful()
             ->assertJsonFragment([
                'success' => Str::title($this->getClassname()).' Published',
                'name' => $page->name,
                'id' => $page->id,
                'id' => $content_element->id
             ]);

        $page->refresh();
        $content_element->refresh();

        $this->assertNotNull($page->published_version_id);
        $this->assertEquals($page->published_version_id, $content_element->contentables()->first()->version_id);
    }

    /** @test **/
    public function a_previous_version_of_a_page_can_be_loaded()
    {
        $this->signInAdmin();
        session()->put('editing', true);

        $content_element = $this->createContentElement(TextBlock::factory(), $this->getModel());
        $text_block = $content_element->content;
        $old_text = $text_block->body;

        $page = $content_element->{Str::plural($this->getClassname())}()->first();

        $this->assertInstanceOf(get_class($this->getModel()), $page);

        $draft_version = $page->getDraftVersion();

        $this->assertInstanceOf(Version::class, $draft_version);

        $this->assertEquals($page->draft_version_id, $content_element->getPageVersion($page)->id);

        $page->publish();
        $page->refresh();
        $content_element->refresh();
        $content = $content_element->content;

        $this->assertEquals($page->published_version_id, $content_element->getPageVersion($page)->id);

        $new_text_block = TextBlock::factory()->raw();
        $new_text = Arr::get($new_text_block, 'body');
        $this->assertNotNull($new_text);
        $input = $this->createContentElement(TextBlock::factory())->toArray();
        $input['type'] = 'text-block';
        $input['content'] = $new_text_block;
        $input['content']['id'] = $content->id;

        $input['pivot'] = [
            'contentable_id' => $page->id,
            'contentable_type' => get_class($page),
            'sort_order' => $this->faker->randomNumber(1),
            'unlisted' => false,
            'expandable' => false,
        ];

        $saved_content_element = (new ContentElement)->saveContentElement($input, $content_element->id);

        $this->assertNotEquals($page->getDraftVersion()->id, $page->publishedVersion->id);
        $this->assertNotEquals($content_element->id, $saved_content_element->id);
        $this->assertNotEquals($content->id, $saved_content_element->content->id);

        $this->withoutExceptionHandling();
        $this->assertEquals($page->getDraftVersion()->id, $saved_content_element->getPageVersion($page)->id);
        $page->publish();

        $page->refresh();

        $this->assertNotNull($page->getSlug());

        $route  = route(Str::plural($this->getClassname()).'.load', ['page' => $page->getSlug(), 'version_id' => $draft_version->id]);
        
        $this->assertTrue(Str::contains($route, 'version_id'));
        $this->get($route)
            ->assertSessionHas('editing')
            ->assertSuccessful()
            ->assertDontSee($new_text)
            ->assertSee($old_text);
    }

    /** @test **/
    public function a_page_with_more_than_one_version_displays_the_latest_published_at_date()
    {
        $content_element = $this->createContentElement(TextBlock::factory(), $this->getModel());
        $page = $content_element->{Str::plural($this->getClassname())}()->first();

        $page->publish();
        $page->refresh();

        $this->assertEquals(1, $page->versions()->count());
        $old_version = $page->versions()->first();
        $old_version->published_at = now()->subMinutes(5);
        $old_version->save();

        $page->refresh();

        $this->assertNotNull($page->published_at);

        $published_at = $page->published_at;

        $this->assertInstanceOf(Carbon::class, $published_at);

        $input = Page::factory()->raw();

        $this->signInAdmin();

        $content = $content_element->content;
        $new_text_block = TextBlock::factory()->raw();
        $new_text = Arr::get($new_text_block, 'body');
        $this->assertNotNull($new_text);
        $input = $this->createContentElement(TextBlock::factory())->toArray();
        $input['type'] = 'text-block';
        $input['content'] = $new_text_block;
        $input['content']['id'] = $content->id;

        $input['pivot'] = [
            'contentable_id' => $page->id,
            'contentable_type' => get_class($page),
            'sort_order' => $this->faker->randomNumber(1),
            'unlisted' => false,
            'expandable' => false,
        ];

        $saved_content_element = (new ContentElement)->saveContentElement($input, $content_element->id);

        $page->refresh();

        $this->assertTrue($page->can_be_published);

        $this->json('POST', route(Str::plural($this->getClassname()).'.publish', ['id' => $page->id]))
             ->assertSuccessful()
             ->assertJsonFragment([
                'success' => Str::title($this->getClassname()).' Published',
             ]);

        $page->refresh();

        $this->assertEquals(2, $page->versions()->count());

        $new_version = $page->versions()->get()->last();

        $this->assertNotEquals($old_version->id, $new_version->id);

        $this->assertNotEquals($old_version->published_at, $page->published_at);
        $this->assertEquals($new_version->published_at, $page->published_at);
    }

    /** @test **/
    public function when_a_page_is_published_an_event_is_broadcast()
    {
        Event::fake();
        $page = $this->getModel();

        $this->signInAdmin();

        $this->withoutExceptionHandling();
        $this->json('POST', route(Str::plural($this->getClassname()).'.publish', ['id' => $page->id]))
             ->assertSuccessful()
             ->assertJsonFragment([
                'success' => Str::title($this->getClassname()).' Published',
             ]);

        $page->refresh();
        $this->assertNotNull($page->published_version_id);

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
}
