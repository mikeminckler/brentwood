<?php

namespace Tests\Feature;

use Illuminate\Support\Arr;

use App\Version;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;
use App\Events\PageDraftCreated;
use App\Page;
use Illuminate\Support\Str;

use App\ContentElement;
use App\User;
use App\TextBlock;

trait VersioningTestTrait
{
    abstract protected function getModel();
    abstract protected function getClassname();

    /** @test **/
    public function a_page_can_be_published()
    {
        $content_element = factory(ContentElement::class)->states($this->getClassname(), 'text-block')->create();
        $page = $content_element->{Str::plural($this->getClassname())}()->first();

        $content_element->version_id = $page->getDraftVersion()->id;
        $content_element->save();
        $content_element->refresh();

        $this->assertNull($page->published_at);

        $this->json('POST', route(Str::plural($this->getClassname()).'.publish', ['id' => $page->id]))
            ->assertStatus(401);

        $this->signIn(factory(User::class)->create());

        $this->withoutExceptionHandling();
        $this->json('POST', route(Str::plural($this->getClassname()).'.publish', ['id' => $page->id]))
            ->assertStatus(403);

        $this->signInAdmin();

        $this->json('POST', route(Str::plural($this->getClassname()).'.publish', ['id' => $page->id]))
             ->assertSuccessful()
             ->assertJsonFragment([
                'success' => 'Page Published',
                'name' => $page->name,
                'id' => $page->id,
                'id' => $content_element->id
             ]);

        $page->refresh();
        $content_element->refresh();

        $this->assertNotNull($page->published_version_id);
        $this->assertEquals($page->published_version_id, $content_element->version_id);
    }

    /** @test **/
    public function a_previous_version_of_a_page_can_be_loaded()
    {
        $this->signInAdmin();
        session()->put('editing', true);


        $content_element = factory(ContentElement::class)->states($this->getClassname(), 'text-block')->create();
        $text_block = $content_element->content;
        $old_text = $text_block->body;

        $page = $content_element->{Str::plural($this->getClassname())}()->first();

        $this->assertInstanceOf(get_class($this->getModel()), $page);

        $draft_version = $page->getDraftVersion();

        $this->assertInstanceOf(Version::class, $draft_version);

        $content_element->version_id = $draft_version->id;
        $content_element->save();
        $content_element->refresh();

        $this->assertEquals($page->draft_version_id, $content_element->version_id);

        $page->publish();
        $page->refresh();
        $content_element->refresh();
        $content = $content_element->content;

        $this->assertEquals($page->published_version_id, $content_element->version_id);

        $new_text_block = factory(TextBlock::class)->raw();
        $new_text = Arr::get($new_text_block, 'body');
        $this->assertNotNull($new_text);
        $input = factory(ContentElement::class)->states('text-block')->raw();
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

        $saved_content_element = (new ContentElement)->saveContentElement($content_element->id, $input);

        $this->assertNotEquals($page->getDraftVersion()->id, $page->publishedVersion->id);
        $this->assertNotEquals($content_element->id, $saved_content_element->id);
        $this->assertNotEquals($content->id, $saved_content_element->content->id);

        $this->assertEquals($page->getDraftVersion()->id, $saved_content_element->version_id);
        $page->publish();

        $page->refresh();

        $route  = route(Str::plural($this->getClassname()).'.load', ['page' => $page->slug, 'version_id' => $draft_version->id]);
        
        $this->assertTrue(Str::contains($route, 'version_id'));
        $this->get($route)
            ->assertSessionHas('editing')
            ->assertSuccessful()
            ->assertDontSee($new_text)
            ->assertSee($old_text);
    }
}
