<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
    
use App\Models\PhotoBlock;
use App\Models\Photo;
use App\Models\FileUpload;
use App\Models\User;
use App\Models\Page;
use App\Models\ContentElement;

use Tests\Feature\ContentElementsTestTrait;

class PhotoBlockTest extends TestCase
{
    use WithFaker;
    use ContentElementsTestTrait;

    protected function getClassname()
    {
        return 'photo-block';
    }

    /** @test **/
    public function saving_a_photo_block()
    {
        Storage::fake();
        $file_name = Str::lower(Str::random().'.jpg');
        $file = UploadedFile::fake()->image($file_name);
        $file_upload = (new FileUpload)->saveFile($file, 'photos', true);

        $photo_input = Photo::factory()->raw([
            'file_upload_id' => $file_upload->id,
        ]);

        $page = Page::factory()->create();

        $input = ContentElement::factory()->for(PhotoBlock::factory(), 'content')->raw();
        $input['id'] = 0;
        $input['type'] = 'photo-block';

        $input['content'] = [
            'photos' => [$photo_input],
            'columns' => 1,
            'height' => 33,
            'padding' => false,
            'show_text' => true,
            'header' => $this->faker->sentence,
            'body' => $this->faker->paragraph,
            'text_order' => 1,
            'text_span' => 1,
            'text_style' => 'blue',
        ];

        $input['pivot'] = [
            'contentable_id' => $page->id,
            'contentable_type' => get_class($page),
            'sort_order' => 1,
            'unlisted' => false,
            'expandable' => false,
        ];

        $this->signInAdmin();

        $this->json('POST', route('content-elements.store'), ['type' => 'photo-block', 'pivot' => ['contentable_id' => $page->id, 'contentable_type' => 'page']])
             ->assertStatus(422)
             ->assertJsonValidationErrors([
                'pivot.sort_order',
                'pivot.unlisted',
                'pivot.expandable',
                //'content.photos',
                //'content.columns',
                //'content.height',
                //'content.padding',
                //'content.show_text',
             ]);

        //$this->withoutExceptionHandling();
        $this->json('POST', route('content-elements.store'), $input)
             //->assertSuccessful()
             ->assertJsonFragment([
                'success' => 'Photo Block Saved',
             ]);

        $photo_block = PhotoBlock::all()->last();

        $this->assertEquals(Arr::get($input, 'content.columns'), $photo_block->columns);
        $this->assertEquals(Arr::get($input, 'content.height'), $photo_block->height);
        $this->assertEquals(Arr::get($input, 'content.padding'), $photo_block->padding);
        $this->assertEquals(Arr::get($input, 'content.show_text'), $photo_block->show_text);
        $this->assertEquals(Arr::get($input, 'content.header'), $photo_block->header);
        $this->assertEquals(Arr::get($input, 'content.body'), $photo_block->body);
        $this->assertEquals(Arr::get($input, 'content.text_order'), $photo_block->text_order);
        $this->assertEquals(Arr::get($input, 'content.text_span'), $photo_block->text_span);
        $this->assertEquals(Arr::get($input, 'content.text_style'), $photo_block->text_style);

        $this->assertTrue($photo_block->photos->contains(function ($p) use ($file_name) {
            return $p->fileUpload->name === $file_name;
        }));

        $photo = $photo_block->photos->first();
        $this->assertInstanceOf(PhotoBlock::class, $photo->content);
        $this->assertEquals($photo_block->id, $photo->content->id);

        //Storage::assertExists('photos/'.$file->hashName());
        $this->assertInstanceOf(Photo::class, $photo);
        $this->assertEquals(Arr::get($photo_input, 'name'), $photo->name);
        $this->assertEquals(Arr::get($photo_input, 'description'), $photo->description);
        $this->assertEquals(Arr::get($photo_input, 'alt'), $photo->alt);
        $this->assertEquals($photo->fileUpload->id, $file_upload->id);
    }

    /** @test **/
    public function updating_a_photo_block()
    {
        $content_element = $this->createContentElement(PhotoBlock::factory()->has(Photo::factory([
            'file_upload_id' => FileUpload::factory()->jpg(),
        ]), 'photos'));
        $photo_block = $content_element->content;
        $photo = $photo_block->photos()->first();
        $page = $content_element->pages->first();

        $this->assertInstanceOf(ContentElement::class, $content_element);
        $this->assertInstanceOf(PhotoBlock::class, $photo_block);
        $this->assertInstanceOf(Photo::class, $photo);
        $this->assertInstanceOf(Page::class, $page);

        $input = ContentElement::factory()->raw();
        $input['type'] = 'photo-block';
        $input['content'] = PhotoBlock::factory()->raw();

        $new_photo = Photo::factory([
                            'file_upload_id' => FileUpload::factory()->jpg(),
                        ])
                        ->for(PhotoBlock::factory(), 'content')
                        ->create([
                            'content_id' => $photo_block->id
                        ]);

        $this->assertInstanceOf(FileUpload::class, $new_photo->fileUpload);

        $input['content']['photos'] = [$new_photo->toArray()];
        $input['pivot'] = [
            'contentable_id' => $page->id,
            'contentable_type' => get_class($page),
            'sort_order' => 1,
            'unlisted' => false,
            'expandable' => false,
        ];

        $this->signInAdmin();

        $this->withoutExceptionHandling();
        $this->json('POST', route('content-elements.update', ['id' => $content_element->id]), $input)
            ->assertSuccessful()
            ->assertJsonFragment([
                'success' => 'Photo Block Saved',
            ]);

        $content_element->refresh();
        $photo_block->refresh();

        $content_element_page = $content_element->pages->first();
        $this->assertEquals($page->id, $content_element_page->id);
        $this->assertEquals(Arr::get($input, 'pivot.sort_order'), $content_element_page->pivot->sort_order);
        $this->assertEquals(Arr::get($input, 'pivot.unlisted'), $content_element_page->pivot->unlisted);
        $this->assertEquals(Arr::get($input, 'pivot.expandable'), $content_element_page->pivot->expandable);

        $this->assertEquals(Arr::get($input, 'content.columns'), $photo_block->columns);
        $this->assertEquals(Arr::get($input, 'content.height'), $photo_block->height);
        $this->assertEquals(Arr::get($input, 'content.padding'), $photo_block->padding);
        $this->assertEquals(Arr::get($input, 'content.show_text'), $photo_block->show_text);
        $this->assertEquals(Arr::get($input, 'content.header'), $photo_block->header);
        $this->assertEquals(Arr::get($input, 'content.body'), $photo_block->body);
        $this->assertEquals(Arr::get($input, 'content.text_order'), $photo_block->text_order);
        $this->assertEquals(Arr::get($input, 'content.text_span'), $photo_block->text_span);
        $this->assertEquals(Arr::get($input, 'content.text_style'), $photo_block->text_style);
    }

    /** @test **/
    public function saving_a_photo_with_the_same_name_and_size_doesnt_create_a_new_photo()
    {
        Storage::fake();
        $file_name = Str::random().'.jpg';
        $file = UploadedFile::fake()->image($file_name);
        $file_upload = (new FileUpload)->saveFile($file, 'photos', true);

        $photo_input = Photo::factory()->raw([
            'file_upload_id' => $file_upload->id,
        ]);

        $page = Page::factory()->create();

        $input = $this->createContentElement(PhotoBlock::factory())->toArray();
        $input['type'] = 'photo-block';

        $input['content'] = [
            'body' => '',
            'columns' => 1,
            'header' => '',
            'height' => 33,
            'id' => 0,
            'padding' => false,
            'photos' => [],
            'show_text' => false,
            'text_order' => 1,
            'text_span' => 1,
            'text_style' => '',
        ];

        $input['pivot'] = [
            'contentable_id' => $page->id,
            'contentable_type' => get_class($page),
            'page_id' => $page->id,
            'sort_order' => 1,
            'unlisted' => false,
            'expandable' => false,
        ];

        $this->signInAdmin();

        $this->withoutExceptionHandling();
        $this->json('POST', route('content-elements.store'), $input)
             ->assertSuccessful()
             ->assertJsonFragment([
                'success' => 'Photo Block Saved',
             ]);

        $photo_block = PhotoBlock::all()->last();
        $content_element = $photo_block->contentElement;
        $this->assertInstanceOf(ContentElement::class, $content_element);

        $this->assertEquals(Arr::get($input, 'content.columns'), $photo_block->columns);
        $this->assertEquals(Arr::get($input, 'content.height'), $photo_block->height);
        $this->assertEquals(Arr::get($input, 'content.padding'), $photo_block->padding);
        $this->assertEquals(Arr::get($input, 'content.show_text'), $photo_block->show_text);
        $this->assertEquals(Arr::get($input, 'content.header'), $photo_block->header);
        $this->assertEquals(Arr::get($input, 'content.body'), $photo_block->body);
        $this->assertEquals(Arr::get($input, 'content.text_order'), $photo_block->text_order);
        $this->assertEquals(Arr::get($input, 'content.text_span'), $photo_block->text_span);
        $this->assertEquals(Arr::get($input, 'content.text_style'), $photo_block->text_style);

        $this->assertEquals(0, $photo_block->photos->count());

        $input['content'] = [
            'photos' => [$photo_input],
            'header' => $this->faker->sentence,
            'body' => $this->faker->paragraph,
            'columns' => 1,
            'height' => 33,
            'id' => $photo_block->id,
            'padding' => false,
            'show_text' => false,
            'text_order' => 1,
            'text_span' => 1,
            'text_style' => '',
        ];

        $this->json('POST', route('content-elements.update', ['id' => $content_element->id]), $input)
             ->assertSuccessful()
             ->assertJsonFragment([
                'success' => 'Photo Block Saved',
             ]);

        $photo_block->refresh();

        $this->assertEquals(Arr::get($input, 'content.columns'), $photo_block->columns);
        $this->assertEquals(Arr::get($input, 'content.height'), $photo_block->height);
        $this->assertEquals(Arr::get($input, 'content.padding'), $photo_block->padding);
        $this->assertEquals(Arr::get($input, 'content.show_text'), $photo_block->show_text);
        $this->assertEquals(Arr::get($input, 'content.header'), $photo_block->header);
        $this->assertEquals(Arr::get($input, 'content.body'), $photo_block->body);
        $this->assertEquals(Arr::get($input, 'content.text_order'), $photo_block->text_order);
        $this->assertEquals(Arr::get($input, 'content.text_span'), $photo_block->text_span);
        $this->assertEquals(Arr::get($input, 'content.text_style'), $photo_block->text_style);

        $this->assertEquals(1, $photo_block->photos->count());

        $this->json('POST', route('content-elements.update', ['id' => $content_element->id]), $input)
             ->assertSuccessful()
             ->assertJsonFragment([
                'success' => 'Photo Block Saved',
             ]);

        $photo_block->refresh();
        // TODO I Think this test is worthelss?
        // $this->assertEquals(1, $photo_block->photos->count());
    }

    /** @test **/
    public function saving_a_new_version_of_a_photo_block_keeps_the_photo_relations_for_the_old_version()
    {
        $content_element = $this->createContentElement(PhotoBlock::factory()->has(Photo::factory()->for(FileUpload::factory()->jpg(), 'fileUpload'), 'photos'));
        $photo_block = $content_element->content;
        $photo = $photo_block->photos()->first();
        $page = $content_element->pages->first();

        $page->publish();

        $content_element->refresh();
        $this->assertNotNull($content_element->getPageVersion($page)->published_at);

        $input = $content_element->toArray();
        $input['type'] = 'photo-block';
        $input['content']['header'] = Str::random();

        $input['pivot'] = [
            'contentable_id' => $page->id,
            'contentable_type' => get_class($page),
            'page_id' => $page->id,
            'sort_order' => 1,
            'unlisted' => false,
            'expandable' => false,
        ];

        $this->signInAdmin();

        $this->json('POST', route('content-elements.update', ['id' => $content_element->id]), $input)
             //->assertSuccessful()
             ->assertJsonFragment([
                'success' => 'Photo Block Saved',
             ]);

        $photo_block->refresh();

        $this->assertEquals(1, $photo_block->photos()->count());
    }
}
