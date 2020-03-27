<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use Illuminate\Support\Arr;
use App\ContentElement;
use App\User;
use App\TextBlock;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use App\FileUpload;
use App\Photo;

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
                //'content.body',
             ]);

        $this->json('POST', route('content-elements.store'), $input)
             ->assertSuccessful()
             ->assertJsonFragment([
                'success' => 'Text Block Saved',
             ]);

        $text_block = TextBlock::all()->last();
        $this->assertEquals(Arr::get($input, 'content.header'), $text_block->header);
        $this->assertEquals(Arr::get($input, 'content.body'), $text_block->body);
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
                //'content.body',
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

    /** @test **/
    public function a_text_block_can_save_a_photo()
    {
        
        Storage::fake();
        $file_name = Str::random().'.jpg';
        $file = UploadedFile::fake()->image($file_name);
        $file_upload = (new FileUpload)->saveFile($file, 'photos', true);

        $photo_input = factory(Photo::class)->raw();
        $photo_input['file_upload'] = $file_upload;

        $input = factory(ContentElement::class)->states('text-block')->raw();
        $input['type'] = 'text-block';
        $input['content'] = factory(TextBlock::class)->raw();
        $input['content']['photos'] = [$photo_input];

        $this->signInAdmin();

        $this->withoutExceptionHandling();
        $this->json('POST', route('content-elements.store'), $input)
             ->assertSuccessful()
             ->assertJsonFragment([
                'success' => 'Text Block Saved',
             ]);

        $content_element = ContentElement::all()->last();

        $text_block = $content_element->content;

        $this->assertEquals(1, $text_block->photos->count());

        $photo = $text_block->photos->first();
        $this->assertInstanceOf(Photo::class, $photo);
        $this->assertEquals(Arr::get($photo_input, 'name'), $photo->name);
        $this->assertEquals(Arr::get($photo_input, 'description'), $photo->description);
        $this->assertEquals(Arr::get($photo_input, 'alt'), $photo->alt);
        $this->assertEquals($photo->fileUpload->id, $file_upload->id);
    }
}
