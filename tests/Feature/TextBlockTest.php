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
                //'content.header',
                'content.body',
             ]);

        $content = (new TextBlock);
        $content->header = Arr::get($input, 'content.header');
        $content->body = Arr::get($input, 'content.body');

        $this->json('POST', route('content-elements.store'), $input)
             ->assertSuccessful()
             ->assertJsonFragment([
                'success' => 'Text Block Saved',
                //'html' => view('content-elements.text-block', ['content' => $content])->render(),
             ]);

        $text_block = TextBlock::all()->last();
        $this->assertEquals($content->header, $text_block->header);
        $this->assertEquals($content->body, $text_block->body);
    }

    /** @test **/
    public function a_text_block_can_be_updated()
    {
        $text_block = factory(TextBlock::class)->create();
        $content_element = $text_block->contentElement;

        $this->assertInstanceOf(ContentElement::class, $content_element);

        $this->json('POST', route('content-elements.update', ['id' => $content_element->id]), [])
            ->assertStatus(401);

        $this->signIn( factory(User::class)->create());

        $this->json('POST', route('content-elements.update', ['id' => $content_element->id]), [])
             ->assertStatus(403);

        $this->signInAdmin();

        $this->json('POST', route('content-elements.update', ['id' => $content_element->id]), [])
             ->assertStatus(422)
             ->assertJsonValidationErrors([
                 'type',
             ]);

        $this->json('POST', route('content-elements.update', ['id' => $content_element->id]), ['type' => 'text-block'])
             ->assertStatus(422)
             ->assertJsonValidationErrors([
                'page_id',
                //'content.header',
                'content.body',
             ]);

        $input = $content_element->toArray();
        $text_block_input = factory(TextBlock::class)->raw();
        $input['content']['header'] = Arr::get($text_block_input, 'header');
        $input['content']['body'] = Arr::get($text_block_input, 'body');

        $content = (new TextBlock);
        $content->header = Arr::get($input, 'content.header');
        $content->body = Arr::get($input, 'content.body');

        $this->json('POST', route('content-elements.update', ['id' => $content_element->id]), $input)
             ->assertSuccessful()
             ->assertJsonFragment([
                'success' => 'Text Block Saved',
                'header' => $content->header,
                'body' => $content->body,
             ]);

        $content_element->refresh();
        $text_block->refresh();
        $this->assertEquals($content->header, $text_block->header);
        $this->assertEquals($content->body, $text_block->body);
    }
}
