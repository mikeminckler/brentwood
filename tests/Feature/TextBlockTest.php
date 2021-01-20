<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;

use App\Models\ContentElement;
use App\Models\User;
use App\Models\TextBlock;
use App\Models\FileUpload;
use App\Models\Photo;
use App\Models\Page;

use Tests\Feature\ContentElementsTestTrait;

class TextBlockTest extends TestCase
{
    use WithFaker;
    use ContentElementsTestTrait;

    protected function getClassname()
    {
        return 'text-block';
    }

    /** @test **/
    public function a_text_block_content_element_can_be_created()
    {
        $input = $this->createContentElement(TextBlock::factory())->toArray();
        $input['id'] = 0;
        $input['type'] = 'text-block';
        $input['content'] = TextBlock::factory()->stat()->raw();
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
                'success' => 'Text Block Saved',
                'contentable_id' => $page->id,
                'sort_order' => 1,
                'unlisted' => 0,
                'expandable' => 0,
             ]);

        $page->refresh();
        $text_block = TextBlock::all()->last();
        $this->assertEquals(Arr::get($input, 'content.header'), $text_block->header);
        $this->assertEquals(Arr::get($input, 'content.body'), $text_block->body);
        $this->assertEquals(Arr::get($input, 'content.style'), $text_block->style);
        $this->assertEquals(Arr::get($input, 'content.full_width'), $text_block->full_width);
        $this->assertEquals(Arr::get($input, 'content.stat_number'), $text_block->stat_number);
        $this->assertEquals(Arr::get($input, 'content.stat_name'), $text_block->stat_name);

        $this->assertEquals($page->id, $text_block->contentElement->pages->first()->id);
        $this->assertTrue($page->contentElements()->count() > 0);
        $this->assertTrue($page->contentElements()->get()->contains('uuid', $text_block->contentElement->uuid));
        //$this->assertTrue($page->getContentElements()->contains('uuid', $text_block->contentElement->uuid));

        $pivot = $page->contentElements()->where('content_element_id', $text_block->contentElement->id)->first()->pivot;
        $this->assertEquals(1, $pivot->sort_order);
        $this->assertEquals(0, $pivot->unlisted);
        $this->assertEquals(0, $pivot->expandable);
    }

    /** @test **/
    public function a_text_block_can_be_updated()
    {
        $content_element = $this->createContentElement(TextBlock::factory());
        $text_block = $content_element->content;
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
        $text_block_input = TextBlock::factory()->raw();
        $input['content']['header'] = Arr::get($text_block_input, 'header');
        $input['content']['body'] = Arr::get($text_block_input, 'body');
        $input['content']['style'] = Arr::get($text_block_input, 'style');
        $input['pivot'] = [
            'contentable_id' => $page->id,
            'contentable_type' => get_class($page),
            'sort_order' => 1,
            'unlisted' => false,
            'expandable' => false,
        ];

        $content = (new TextBlock);
        $content->header = Arr::get($input, 'content.header');
        $content->body = Arr::get($input, 'content.body');
        $content->style = Arr::get($input, 'content.style');

        $this->json('POST', route('content-elements.update', ['id' => $content_element->id]), $input)
             ->assertSuccessful()
             ->assertJsonFragment([
                'success' => 'Text Block Saved',
                'header' => $content->header,
                'body' => $content->body,
                'style' => $content->style,
             ]);

        $content_element->refresh();
        $text_block->refresh();
        $this->assertEquals($content->header, $text_block->header);
        $this->assertEquals($content->body, $text_block->body);
        $this->assertEquals($content->style, $text_block->style);
    }

    /** @test **/
    public function a_text_block_can_save_a_photo()
    {
        Storage::fake();
        $file_name = Str::random().'.jpg';
        $file = UploadedFile::fake()->image($file_name);
        $file_upload = (new FileUpload)->saveFile($file, 'photos', true);

        $photo_input = Photo::factory()->raw();
        $photo_input['file_upload_id'] = $file_upload->id;

        $input = $this->createContentElement(TextBlock::factory())->toArray();
        $input['type'] = 'text-block';
        $input['content'] = TextBlock::factory()->raw();
        $input['content']['photos'] = [$photo_input];
        $page = Page::factory()->create();
        $input['pivot'] = [
            'contentable_id' => $page->id,
            'contentable_type' => get_class($page),
            'sort_order' => 1,
            'unlisted' => false,
            'expandable' => false,
        ];

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

    /** @test **/
    public function a_text_block_content_element_can_be_after_another_text_block()
    {
        $input = $this->createContentElement(TextBlock::factory())->toArray();
        $input['type'] = 'text-block';
        $input['content'] = TextBlock::factory()->raw();
        $page = Page::factory()->create();
        $input['pivot'] = [
            'contentable_id' => $page->id,
            'contentable_type' => get_class($page),
            'sort_order' => 1,
            'unlisted' => false,
            'expandable' => false,
        ];

        $this->signInAdmin();

        $this->withoutExceptionHandling();
        $this->json('POST', route('content-elements.store'), $input)
             ->assertSuccessful()
             ->assertJsonFragment([
                'success' => 'Text Block Saved',
                'contentable_id' => $page->id,
                'sort_order' => 1,
                'unlisted' => 0,
                'expandable' => 0,
             ]);

        $input['pivot'] = [
            'contentable_id' => $page->id,
            'contentable_type' => get_class($page),
            'sort_order' => 2,
            'unlisted' => true,
            'expandable' => true,
        ];

        $this->withoutExceptionHandling();
        $this->json('POST', route('content-elements.store'), $input)
             ->assertSuccessful()
             ->assertJsonFragment([
                'success' => 'Text Block Saved',
                'contentable_id' => $page->id,
                'sort_order' => 2,
                'unlisted' => 1,
                'expandable' => 1,
             ]);
    }

    /** @test **/
    public function an_instanced_content_element_pushes_its_updates_to_other_pages()
    {
        // Create a content element on a page and publish the page
        $content_element = $this->createContentElement(TextBlock::factory());
        $text_block = $content_element->content;
        $this->assertInstanceOf(TextBlock::class, $text_block);

        $page = $content_element->pages->first();
        $page->publish();

        $content_element->refresh();
        $this->assertNotNull($content_element->getPageVersion($page)->published_at);

        // create a second page and instance the content element on the new page
        $page2 = Page::factory()->create();

        $this->signInAdmin();

        $input = $content_element->toArray();
        $input['instance'] = 'true';
        $input['pivot'] = [
            'contentable_id' => $page2->id,
            'contentable_type' => get_class($page2),
            'sort_order' => 1,
            'unlisted' => false,
            'expandable' => false,
        ];

        $this->json('POST', route('content-elements.update', ['id' => $content_element->id]), $input)
             ->assertSuccessful()
             ->assertJsonFragment([
                'success' => 'Text Block Saved',
             ]);

        $content_element->refresh();
        $text_block->refresh();

        $page->refresh();
        $page2->refresh();

        $this->assertEquals(2, $page->contentElements()->count());
        $this->assertEquals(1, $page2->contentElements()->count());

        $this->assertEquals($content_element->id, $page->contentElements()->first()->id);
        $page2_ce = $page2->contentElements()->first();

        $this->assertEquals($page->contentElements()->first()->uuid, $page2->contentElements()->first()->uuid);

        $this->assertNotNull($page->contentElements()->first()->getPageVersion($page)->published_at);

        // update the content element and make sure the changes are in both locations
        $body = $this->faker->sentence;

        $input['content']['body'] = $body;
        $this->json('POST', route('content-elements.update', ['id' => $content_element->id]), $input)
             ->assertSuccessful()
             ->assertJsonFragment([
                'success' => 'Text Block Saved',
             ]);

        $page2_ce->refresh();

        $this->assertEquals($body, $page2_ce->content->body);
    }
}
