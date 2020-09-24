<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\EmbedCode;
use App\Models\Page;
use App\Models\User;
use App\Models\ContentElement;
use Illuminate\Support\Arr;

class EmbedCodeTest extends TestCase
{
    use WithFaker;

    /** @test **/
    public function an_embed_code_content_element_can_be_created()
    {
        $input = ContentElement::factory()->for(EmbedCode::factory(), 'content')->raw();
        $input['id'] = 0;
        $input['type'] = 'embed-code';
        $input['content'] = EmbedCode::factory()->raw();
        $page = Page::factory()->create();
        $input['pivot'] = [
            'contentable_id' => $page->id,
            'contentable_type' => get_class($page),
            'sort_order' => 1,
            'unlisted' => false,
            'expandable' => false,
        ];

        $this->json('POST', route('content-elements.store'), [])
            ->assertStatus(401);

        $this->signIn(User::factory()->create());

        $this->json('POST', route('content-elements.store'), [])
             ->assertStatus(422)
             ->assertJsonValidationErrors([
                 'pivot.contentable_id',
                 'pivot.contentable_type',
             ]);

        $this->json('POST', route('content-elements.store'), ['pivot' => ['contentable_id' => $page->id, 'contentable_type' => 'page']])
             ->assertStatus(403);

        $this->signInAdmin();

        $this->json('POST', route('content-elements.store'), ['pivot' => ['contentable_id' => $page->id, 'contentable_type' => 'page']])
             ->assertStatus(422)
             ->assertJsonValidationErrors([
                 'type',
             ]);

        $this->json('POST', route('content-elements.store'), ['type' => 'banner-photo', 'pivot' => ['contentable_id' => $page->id, 'contentable_type' => 'page']])
             ->assertStatus(422)
             ->assertJsonValidationErrors([
                'pivot.sort_order',
                'pivot.unlisted',
                'pivot.expandable',
             ]);

        $this->withoutExceptionHandling();
        $this->json('POST', route('content-elements.store'), $input)
             ->assertSuccessful()
             ->assertJsonFragment([
                'success' => 'Embed Code Saved',
             ]);

        $embed_code = EmbedCode::all()->last();
        $this->assertNotNull($embed_code->code);
        $this->assertEquals(Arr::get($input, 'content.code'), $embed_code->code);
    }

    /** @test **/
    public function a_embed_code_can_be_updated()
    {
        $page = Page::factory()->create();
        $content_element = ContentElement::factory()->for(EmbedCode::factory(), 'content')->create([
            'version_id' => $page->draft_version_id,
        ]);
        $content_element->pages()->detach();
        $content_element->pages()->attach($page, ['sort_order' => $this->faker->randomNumber(1), 'unlisted' => false, 'expandable' => false]);
        $embed_code = $content_element->content;
        $this->assertInstanceOf(EmbedCode::class, $embed_code);

        $this->assertInstanceOf(ContentElement::class, $content_element);

        $this->json('POST', route('content-elements.update', ['id' => $content_element->id]), [])
            ->assertStatus(401);

        $this->signIn(User::factory()->create());

        $this->json('POST', route('content-elements.update', ['id' => $content_element->id]), [])
             ->assertStatus(422)
             ->assertJsonValidationErrors([
                 'pivot.contentable_id',
                 'pivot.contentable_type',
             ]);

        $this->json('POST', route('content-elements.update', ['id' => $content_element->id]), ['pivot' => ['contentable_id' => $page->id, 'contentable_type' => 'page']])
             ->assertStatus(403);

        $this->signInAdmin();

        $this->json('POST', route('content-elements.update', ['id' => $content_element->id]), ['pivot' => ['contentable_id' => $page->id, 'contentable_type' => 'page']])
             ->assertStatus(422)
             ->assertJsonValidationErrors([
                 'type',
             ]);

        $this->json('POST', route('content-elements.update', ['id' => $content_element->id]), ['type' => 'embed-code', 'pivot' => ['contentable_id' => $page->id, 'contentable_type' => 'page']])
             ->assertStatus(422)
             ->assertJsonValidationErrors([
                'pivot.sort_order',
                'pivot.unlisted',
                'pivot.expandable',
             ]);

        $input = $content_element->toArray();
        $embed_code_input = EmbedCode::factory()->raw();
        $input['content']['code'] = Arr::get($embed_code_input, 'code');

        $input['pivot'] = [
            'contentable_id' => $page->id,
            'contentable_type' => get_class($page),
            'sort_order' => 1,
            'unlisted' => false,
            'expandable' => false,
        ];

        $this->json('POST', route('content-elements.update', ['id' => $content_element->id]), $input)
             ->assertSuccessful()
             ->assertJsonFragment([
                'success' => 'Embed Code Saved',
                'code' => Arr::get($input, 'content.code'),
             ]);

        $content_element->refresh();
        $embed_code->refresh();
        $this->assertNotNull($embed_code->code);
        $this->assertEquals(Arr::get($input, 'content.code'), $embed_code->code);
    }
}
