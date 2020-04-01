<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Quote;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use App\Photo;
use App\FileUpload;
use App\User;
use App\Page;
use App\ContentElement;

class QuoteTest extends TestCase
{

    /** @test **/
    public function a_quote_content_element_can_be_created()
    {

        Storage::fake();
        $file_name = Str::random().'.jpg';
        $file = UploadedFile::fake()->image($file_name);
        $file_upload = (new FileUpload)->saveFile($file, 'photos', true);

        $photo_input = factory(Photo::class)->raw();
        $photo_input['file_upload'] = $file_upload;

        $input = factory(ContentElement::class)->states('quote')->raw();
        $input['type'] = 'quote';
        $input['content'] = factory(Quote::class)->raw();
        $input['content']['photos'] = [$photo_input];
        $page = factory(Page::class)->create();
        $input['pivot'] = [
            'page_id' => $page->id,
            'sort_order' => 1,
            'unlisted' => false,
        ];

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

        $this->json('POST', route('content-elements.store'), ['type' => 'quote'])
             ->assertStatus(422)
             ->assertJsonValidationErrors([
                'pivot.page_id',
                'pivot.unlisted',
                'pivot.sort_order',
                //'content.author_name',
                //'content.author_details',
                //'content.body',
             ]);

        $this->withoutExceptionHandling();
        $this->json('POST', route('content-elements.store'), $input)
             ->assertSuccessful()
             ->assertJsonFragment([
                'success' => 'Quote Saved',
             ]);

        $quote = Quote::all()->last();
        $this->assertEquals(Arr::get($input, 'content.body'), $quote->body);
        $this->assertEquals(Arr::get($input, 'content.author_name'), $quote->author_name);
        $this->assertEquals(Arr::get($input, 'content.author_details'), $quote->author_details);
        $this->assertEquals(Arr::get($input, 'content.author_details'), $quote->author_details);

        $photo = $quote->photos->first();
        $this->assertInstanceOf(Photo::class, $photo);
        $this->assertEquals(Arr::get($photo_input, 'name'), $photo->name);
        $this->assertEquals(Arr::get($photo_input, 'description'), $photo->description);
        $this->assertEquals(Arr::get($photo_input, 'alt'), $photo->alt);
        $this->assertEquals($photo->fileUpload->id, $file_upload->id);
    }

    /** @test **/
    public function a_quote_can_be_updated()
    {
        $quote = factory(Quote::class)->create();
        $content_element = $quote->contentElement;

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

        $this->json('POST', route('content-elements.update', ['id' => $content_element->id]), ['type' => 'quote'])
             ->assertStatus(422)
             ->assertJsonValidationErrors([
                'pivot.page_id',
                'pivot.sort_order',
                'pivot.unlisted',
                //'content.author_name',
                //'content.author_details',
                //'content.body',
             ]);

        $input = $content_element->toArray();
        $quote_input = factory(Quote::class)->raw();
        $input['content']['body'] = Arr::get($quote_input, 'body');
        $input['content']['author_name'] = Arr::get($quote_input, 'author_name');
        $input['content']['author_details'] = Arr::get($quote_input, 'author_details');
        $page = factory(Page::class)->create();
        $input['pivot'] = [
            'page_id' => $page->id,
            'sort_order' => 1,
            'unlisted' => false,
        ];

        $this->json('POST', route('content-elements.update', ['id' => $content_element->id]), $input)
             //->assertSuccessful()
             ->assertJsonFragment([
                'success' => 'Quote Saved',
                'body' => Arr::get($input, 'content.body'),
                'author_name' => Arr::get($input, 'content.author_name'),
                'author_details' => Arr::get($input, 'content.author_details'),
             ]);

        $content_element->refresh();
        $quote->refresh();
        $this->assertEquals(Arr::get($input, 'content.body'), $quote->body);
        $this->assertEquals(Arr::get($input, 'content.author_name'), $quote->author_name);
        $this->assertEquals(Arr::get($input, 'content.author_details'), $quote->author_details);
    }
}
