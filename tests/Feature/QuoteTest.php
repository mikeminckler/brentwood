<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

use App\Models\Quote;
use App\Models\Photo;
use App\Models\FileUpload;
use App\Models\User;
use App\Models\Page;
use App\Models\ContentElement;

use Tests\Feature\ContentElementsTestTrait;

class QuoteTest extends TestCase
{
    use ContentElementsTestTrait;

    protected function getClassname()
    {
        return 'quote';
    }

    /** @test **/
    public function a_quote_content_element_can_be_created()
    {
        Storage::fake();
        $file_name = Str::random().'.jpg';
        $file = UploadedFile::fake()->image($file_name);
        $file_upload = (new FileUpload)->saveFile($file, 'photos', true);

        $photo_input = Photo::factory()->raw();
        $photo_input['file_upload'] = $file_upload;

        $input = $this->createContentElement(Quote::factory())->toArray();
        $input['id'] = 0;
        $input['type'] = 'quote';
        $input['content'] = Quote::factory()->raw();
        $input['content']['photos'] = [$photo_input];
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
        $content_element = $this->createContentElement(Quote::factory());
        $quote = $content_element->content;
        $page = $content_element->pages->first();

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
        $quote_input = Quote::factory()->raw();
        $input['content']['body'] = Arr::get($quote_input, 'body');
        $input['content']['author_name'] = Arr::get($quote_input, 'author_name');
        $input['content']['author_details'] = Arr::get($quote_input, 'author_details');
        $input['pivot'] = [
            'contentable_id' => $page->id,
            'contentable_type' => get_class($page),
            'sort_order' => 1,
            'unlisted' => false,
            'expandable' => false,
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
