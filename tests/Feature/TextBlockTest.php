<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use Illuminate\Support\Arr;
use App\ContentElement;
use App\User;
use App\TextBlock;

class TextBlockTest extends TestCase
{

    /** @test **/
    public function a_text_block_content_element_can_be_created()
    {
        $input = factory(ContentElement::class)->states('text-block')->raw();
        $input['type'] = 'text-block';
        $input['content'] = factory(TextBlock::class)->raw();

        $this->json('POST', route('content-elements.store'), [])
            ->assertStatus(401);

        $this->signIn( factory(User::class)->create());

        $this->json('POST', route('content-elements.store'), [])
             ->assertStatus(403);

        $this->signInAdmin();

        $this->json('POST', route('content-elements.store'), [])
             ->assertStatus(422)
             ->assertJsonValidationErrors([
                 'type',
             ]);

        $this->json('POST', route('content-elements.store'), ['type' => 'text-block'])
             ->assertStatus(422)
             ->assertJsonValidationErrors([
                'page_id',
                'content.header',
                'content.body',
             ]);

        $content = (new TextBlock);
        $content->header = Arr::get($input, 'content.header');
        $content->body = Arr::get($input, 'content.body');

        $this->json('POST', route('content-elements.store'), $input)
             ->assertSuccessful()
             ->assertJsonFragment([
                'success' => 'Text Block Saved',
                'html' => view('content-elements.text-block', ['content' => $content])->render(),
             ]);
    }

    /** @test **/
    public function a_text_block_can_be_updated()
    {
        $this->fail('write this test');
    }
}
