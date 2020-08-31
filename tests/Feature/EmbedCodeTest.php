<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\EmbedCode;
use App\Page;
use App\User;
use App\ContentElement;
use Illuminate\Support\Arr;

class EmbedCodeTest extends TestCase
{

    /** @test **/
    public function an_embed_code_content_element_can_be_created()
    {
        $input = factory(ContentElement::class)->states('embed-code')->raw();
        $input['type'] = 'embed-code';
        $input['content'] = factory(EmbedCode::class)->raw();
        $page = factory(Page::class)->create();
        $input['pivot'] = [
            'contentable_id' => $page->id,
            'contentable_type' => get_class($page),
            'sort_order' => 1,
            'unlisted' => false,
            'expandable' => false,
        ];

        $this->json('POST', route('content-elements.store'), [])
            ->assertStatus(401);

        $this->signIn( factory(User::class)->create());

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
        $this->assertEquals(Arr::get($input, 'content.code'), $embed_code->code);
    }

    /** @test **/
    public function a_embed_code_can_be_updated()
    {
        $embed_code = factory(EmbedCode::class)->create();
        $content_element = $embed_code->contentElement;
        $page = factory(Page::class)->create();

        $this->assertInstanceOf(ContentElement::class, $content_element);

        $this->json('POST', route('content-elements.update', ['id' => $content_element->id]), [])
            ->assertStatus(401);

        $this->signIn( factory(User::class)->create());

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
        $embed_code_input = factory(EmbedCode::class)->raw();
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
        $this->assertEquals(Arr::get($input, 'content.code'), $embed_code->code);
    }
}
