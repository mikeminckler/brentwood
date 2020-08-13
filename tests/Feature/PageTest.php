<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Page;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

use App\ContentElement;
use App\User;
use App\TextBlock;
use Tests\Feature\SoftDeletesTestTrait;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use App\FileUpload;
use App\Photo;
use App\Version;

use Illuminate\Support\Facades\Event;
use App\Events\PagePublished;

class PageTest extends TestCase
{

    use WithFaker;
    use SoftDeletesTestTrait;

    protected function getModel()
    {
        return factory(Page::class)->create();
    }

    /** @test **/
    public function a_page_can_be_created()
    {
        $input = factory(Page::class)->raw();   

        $this->postJson(route('pages.store'), [])
            ->assertStatus(401);

        $this->signInAdmin();

        $this->json('POST', route('pages.store'), [])
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'name',
                'parent_page_id',
                'sort_order',
            ]);

        $this->withoutExceptionHandling();
        $this->postJson(route('pages.store'), $input)
            ->assertSuccessful()
            ->assertJsonFragment([
                'success' => 'Page Saved',
                'full_slug' => Page::all()->last()->full_slug,
            ]);

        $page = Page::all()->last();

        $this->assertEquals( Arr::get($input, 'name'), $page->name);
        $this->assertEquals( Arr::get($input, 'parent_page_id'), $page->parent_page_id);
        $this->assertEquals( Arr::get($input, 'sort_order'), $page->sort_order);
    }

    /** @test **/
    public function the_page_tree_can_be_loaded()
    {
        $first_level_page = factory(Page::class)->create();
        $second_level_page = factory(Page::class)->states('secondLevel')->create([
            'parent_page_id' => $first_level_page->id,
        ]);

        $this->withoutExceptionHandling();
        $this->json('GET', route('pages.index'))
             ->assertSuccessful()
             ->assertJsonFragment([
                'name' => $first_level_page->name,
                'name' => $second_level_page->name,
             ]);
    }

    /** @test **/
    public function the_home_page_can_be_loaded()
    {
        $home_page = Page::find(1);
        $this->assertInstanceOf(Page::class, $home_page);

        $this->withoutExceptionHandling();
        $this->get('/')
             ->assertSuccessful()
             ->assertViewHas([
                'page' => $home_page,
             ]);
    }

    /** @test **/
    public function a_page_can_be_updated()
    {
        $page = factory(Page::class)->create();
        $input = factory(Page::class)->raw();   

        $this->postJson(route('pages.update', ['id' => $page->id]), [])
            ->assertStatus(401);

        $this->signInAdmin();

        $this->postJson(route('pages.update', ['id' => $page->id]), [])
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'name',
                'parent_page_id',
                'sort_order',
            ]);

        $this->withoutExceptionHandling();
        $this->postJson(route('pages.update', ['id' => $page->id]), $input)
            ->assertSuccessful()
            ->assertJsonFragment([
                'success' => 'Page Saved',
                'full_slug' => $page->refresh()->full_slug,
            ]);

        $page->refresh();

        $this->assertEquals( Arr::get($input, 'name'), $page->name);
        $this->assertEquals( Arr::get($input, 'parent_page_id'), $page->parent_page_id);
        $this->assertEquals( Arr::get($input, 'sort_order'), $page->sort_order);
    }

    /** @test **/
    public function a_page_can_be_unlisted()
    {
        $page = factory(Page::class)->create();
        $input = factory(Page::class)->raw([
            'unlisted' => true,
        ]);

        $this->signInAdmin();

        $this->withoutExceptionHandling();
        $this->postJson(route('pages.update', ['id' => $page->id]), $input)
            ->assertSuccessful()
            ->assertJsonFragment([
                'success' => 'Page Saved',
                'full_slug' => $page->refresh()->full_slug,
            ]);

        $page->refresh();

        $this->assertEquals(1, $page->unlisted);

        $input = factory(Page::class)->raw([
            'unlisted' => false,
        ]);

        $this->signInAdmin();

        $this->withoutExceptionHandling();
        $this->postJson(route('pages.update', ['id' => $page->id]), $input)
            ->assertSuccessful()
            ->assertJsonFragment([
                'success' => 'Page Saved',
                'full_slug' => $page->refresh()->full_slug,
            ]);

        $page->refresh();

        $this->assertEquals(0, $page->unlisted);
    }

    /** @test **/
    public function a_page_gets_404_response_if_no_page_can_be_found()
    {
        $slug = Str::random(8);   

        //$this->withoutExceptionHandling();
        $this->get( $slug )
            ->assertStatus(404);
    }

    /** @test **/
    public function the_home_page_important_fields_cannot_be_changed()
    {
        $home_page = Page::where('slug', '/')->first();
        $this->assertInstanceOf(Page::class, $home_page);

        $home_page_slug = $home_page->slug;
        $home_page_parent_page_id = $home_page->parent_page_id;

        $this->signInAdmin();

        $input = [
            'name' => $this->faker->firstName,
            'slug' => $this->faker->firstName,
            'parent_page_id' => 0,
            'sort_order' => $this->faker->numberBetween(10,100),
        ];

        $this->postJson(route('pages.update', ['id' => $home_page->id]), $input)
            //->assertSuccessful()
            ->assertJsonFragment([
                'success' => 'Page Saved',
            ]);

        $home_page->refresh();

        $this->assertEquals($home_page_slug, $home_page->slug);
        $this->assertEquals($home_page_parent_page_id, $home_page->parent_page_id);
        
    }

    /** @test **/
    public function a_page_can_be_published()
    {
        $content_element = factory(ContentElement::class)->states('text-block')->create([
        ]);
        $page = $content_element->pages->first();

        $content_element->version_id = $page->getDraftVersion()->id;
        $content_element->save();
        $content_element->refresh();

        $this->assertNull($page->published_at);

        $this->json('POST', route('pages.publish', ['id' => $page->id]))
            ->assertStatus(401);

        $this->signIn( factory(User::class)->create());

        $this->json('POST', route('pages.publish', ['id' => $page->id]))
            ->assertStatus(403);

        $this->signInAdmin();

        $this->json('POST', route('pages.publish', ['id' => $page->id]))
             ->assertSuccessful()
             ->assertJsonFragment([
                'success' => 'Page Published',
             ]);

        $page->refresh();
        $content_element->refresh();

        $this->assertNotNull($page->published_version_id);
        $this->assertEquals($page->published_version_id, $content_element->version_id);
    }

    /** @test **/
    public function a_published_page_can_be_updated()
    {
        $page = factory(Page::class)->states('published')->create();

        $content_element = factory(ContentElement::class)->states('text-block')->create([
            'version_id' => $page->published_version_id,
        ]);
        $content_element['pivot'] = [
            'page_id' => $page->id,
            'sort_order' => $this->faker->randomNumber(1),
            'unlisted' => false,
            'expandable' => false,
        ];

        $this->signInAdmin();

        $input = $content_element->toArray();
        $input['content'] = factory(TextBlock::class)->raw();

        $this->json('POST', route('content-elements.update', ['id' => $content_element->id]), $input)
             ->assertSuccessful();

        $new_content_element = ContentElement::all()->last();

        $this->assertNotEquals($content_element->id, $new_content_element->id);
        $this->assertEquals($page->getDraftVersion()->id, $new_content_element->version_id);

        $this->assertEquals(Arr::get($input, 'header'), $new_content_element->header);
        $this->assertEquals(Arr::get($input, 'body'), $new_content_element->body);
        $this->assertEquals($page->getDraftVersion()->id, $new_content_element->version_id);

        $this->json('POST', route('pages.publish', ['id' => $page->id]))
             ->assertSuccessful()
             ->assertJsonFragment([
                'success' => 'Page Published',
             ]);

        $page->refresh();
        $content_element->refresh();

        $this->assertNotNull($page->published_version_id);
        $this->assertEquals($page->published_version_id, $new_content_element->version_id);

    }


    /** @test **/
    public function a_page_returns_a_404_if_it_has_not_been_published()
    {
        $page = factory(Page::class)->states('unpublished')->create();

        $this->get( $page->full_slug )
            ->assertStatus(404);
        
        $page->publish();

        $this->withoutExceptionHandling();
        $this->get( $page->full_slug )
            ->assertSuccessful();
    }

    /** @test **/
    public function the_home_page_cannot_be_deleted()
    {
        $home_page = Page::findOrFail(1);

        $this->signInAdmin();

        $this->json('POST', route('pages.remove', ['id' => $home_page->id]))
             ->assertStatus(403)
             ->assertJsonFragment([
                'error' => 'The home page cannot be deleted',
             ]);
    }

    /** @test **/
    public function a_page_can_save_a_footer_image()
    {
        Storage::fake();
        $fg_file_name = Str::random().'.jpg';
        $fg_file = UploadedFile::fake()->image($fg_file_name);
        $fg_file_upload = (new FileUpload)->saveFile($fg_file, 'photos', true);

        $bg_file_name = Str::random().'.jpg';
        $bg_file = UploadedFile::fake()->image($bg_file_name);
        $bg_file_upload = (new FileUpload)->saveFile($bg_file, 'photos', true);

        $page = factory(Page::class)->create();
        $input = factory(Page::class)->raw();   
        $input['footer_fg_file_upload'] = $fg_file_upload;
        $input['footer_bg_file_upload'] = $bg_file_upload;
        $input['footer_color'] = $this->faker->hexcolor;

        $this->signInAdmin();

        $this->withoutExceptionHandling();
        $this->postJson(route('pages.update', ['id' => $page->id]), $input)
            ->assertSuccessful()
            ->assertJsonFragment([
                'success' => 'Page Saved',
                'full_slug' => $page->refresh()->full_slug,
            ]);

        $page->refresh();

        $this->assertEquals( Arr::get($input, 'name'), $page->name);
        $this->assertEquals( Arr::get($input, 'parent_page_id'), $page->parent_page_id);
        $this->assertEquals( Arr::get($input, 'sort_order'), $page->sort_order);
        $this->assertEquals( Arr::get($input, 'footer_color'), $page->footer_color);
        $this->assertEquals( Arr::get($input, 'footer_fg_file_upload.id'), $page->footer_fg_file_upload_id);
        $this->assertEquals( Arr::get($input, 'footer_bg_file_upload.id'), $page->footer_bg_file_upload_id);

        $fg_photo = $page->footerFgFileUpload;
        $bg_photo = $page->footerBgFileUpload;

        $this->assertInstanceOf(FileUpload::class, $fg_photo);
        $this->assertInstanceOf(FileUpload::class, $bg_photo);

        $this->assertEquals($fg_photo->id, $fg_file_upload->id);
        $this->assertEquals($bg_photo->id, $bg_file_upload->id);
    }

    /** @test **/
    public function a_user_without_update_permisson_cannot_save_a_page()
    {
        $page = factory(Page::class)->create();
        $user = factory(User::class)->create();

        $input = factory(Page::class)->raw();   

        $this->postJson(route('pages.update', ['id' => $page->id]), $input)
             ->assertStatus(401);

        $this->signIn($user);

        $this->postJson(route('pages.update', ['id' => $page->id]), $input)
             ->assertStatus(403);

        $page->createPageAccess($user);

        $user->refresh();

        $this->postJson(route('pages.update', ['id' => $page->id]), $input)
            ->assertSuccessful();
        
    }


    /** @test **/
    public function sorting_a_page_below_reorders_pages()
    {
        $parent_page = factory(Page::class)->create();
        $page = factory(Page::class)->create([
            'parent_page_id' => $parent_page->id,
            'sort_order' => 2
        ]);

        $this->assertInstanceOf(Page::class, $parent_page);

        $page_above = factory(Page::class)->create([
            'parent_page_id' => $parent_page->id,
            'sort_order' => 1,
        ]);

        $page_below = factory(Page::class)->create([
            'parent_page_id' => $parent_page->id,
            'sort_order' => 3,
        ]);

        $page_last = factory(Page::class)->create([
            'parent_page_id' => $parent_page->id,
            'sort_order' => 4,
        ]);

        $this->signInAdmin();

        $input = [
            // This sets the position, after the below page
            'sort_order' => $page_below->sort_order + .5,
            'parent_page_id' => $parent_page->id,
        ];

        //dump('ABOVE: '.$page_above->id);
        //dump('BELOW: '.$page_below->id);
        //dump('PAGE: '.$page->id);
        //dump('LAST: '.$page_last->id);

        $this->withoutExceptionHandling();
        $this->json('POST', route('pages.sort', ['id' => $page->id]), $input)
             ->assertSuccessful()
            ->assertJsonFragment([
                'success' => 'Page Saved',
            ]);

        $page->refresh();
        $page_above->refresh();
        $page_below->refresh();
        $page_last->refresh();

        $this->assertEquals(1, $page_above->sort_order);
        $this->assertEquals(2, $page_below->sort_order);
        $this->assertEquals(3, $page->sort_order);
        $this->assertEquals(4, $page_last->sort_order);

    }

    /** @test **/
    public function sorting_a_page_above_reorders_pages()
    {
        $parent_page = factory(Page::class)->create();
        $page = factory(Page::class)->create([
            'parent_page_id' => $parent_page->id,
            'sort_order' => 2
        ]);

        $this->assertInstanceOf(Page::class, $parent_page);

        $page_above = factory(Page::class)->create([
            'parent_page_id' => $parent_page->id,
            'sort_order' => 1,
        ]);

        $page_below = factory(Page::class)->create([
            'parent_page_id' => $parent_page->id,
            'sort_order' => 3,
        ]);

        $page_last = factory(Page::class)->create([
            'parent_page_id' => $parent_page->id,
            'sort_order' => 4,
        ]);

        $this->signInAdmin();

        $input = [
            // This sets the position, after the below page
            'sort_order' => .5,
            'parent_page_id' => $parent_page->id,
        ];

        $this->withoutExceptionHandling();
        $this->json('POST', route('pages.sort', ['id' => $page->id]), $input)
             ->assertSuccessful()
            ->assertJsonFragment([
                'success' => 'Page Saved',
            ]);

        $page->refresh();
        $page_above->refresh();
        $page_below->refresh();
        $page_last->refresh();

        $this->assertEquals(1, $page->sort_order);
        $this->assertEquals(2, $page_above->sort_order);
        $this->assertEquals(3, $page_below->sort_order);
        $this->assertEquals(4, $page_last->sort_order);

    }

    /** @test **/
    public function sorting_a_page_into_a_new_parent_sorts_the_old_parent_pages()
    {
        $parent_old = factory(Page::class)->create();
        $parent_new = factory(Page::class)->create();
        $page = factory(Page::class)->create([
            'parent_page_id' => $parent_old->id,
            'sort_order' => 2
        ]);

        $old_page1 = factory(Page::class)->create([
            'parent_page_id' => $parent_old->id,
            'sort_order' => 1
        ]);

        $old_page2 = factory(Page::class)->create([
            'parent_page_id' => $parent_old->id,
            'sort_order' => 3
        ]);

        $new_page1 = factory(Page::class)->create([
            'parent_page_id' => $parent_new->id,
            'sort_order' => 1
        ]);

        $new_page2 = factory(Page::class)->create([
            'parent_page_id' => $parent_new->id,
            'sort_order' => 2
        ]);

        $this->signInAdmin();

        $input = [
            // This sets the position, after the below page
            'sort_order' => 1.5,
            'parent_page_id' => $parent_new->id,
        ];

        $this->withoutExceptionHandling();
        $this->json('POST', route('pages.sort', ['id' => $page->id]), $input)
             ->assertSuccessful()
            ->assertJsonFragment([
                'success' => 'Page Saved',
            ]);

        $page->refresh();
        $old_page1->refresh();
        $old_page2->refresh();
        $new_page1->refresh();
        $new_page2->refresh();

        $this->assertEquals(2, $page->sort_order);
        $this->assertEquals(1, $new_page1->sort_order);
        $this->assertEquals(3, $new_page2->sort_order);
        $this->assertEquals(1, $old_page1->sort_order);
        $this->assertEquals(2, $old_page2->sort_order);
        
    }

    /** @test **/
    public function sorting_a_first_level_page()
    {
        $page = factory(Page::class)->create([
            'parent_page_id' => 1,
            'sort_order' => 2,
        ]);

        $this->signInAdmin();

        $input = [
            // This sets the position, after the below page
            'sort_order' => .5,
            'parent_page_id' => 1,
        ];

        $this->withoutExceptionHandling();
        $this->json('POST', route('pages.sort', ['id' => $page->id]), $input)
             ->assertSuccessful()
            ->assertJsonFragment([
                'success' => 'Page Saved',
            ]);

        $page->refresh();

        $this->assertEquals(1, $page->parentPage->id);
        $this->assertEquals(1, $page->sort_order);

    }

    /** @test **/
    public function a_previous_version_of_a_page_can_be_loaded()
    {

        $this->signInAdmin();
        session()->put('editing', true);

        $text_block = factory(TextBlock::class)->create();
        $old_text = $text_block->body;
        $content_element = $text_block->contentElement;
        $this->assertInstanceOf(ContentElement::class, $content_element);

        $page = $content_element->pages->first();

        $this->assertInstanceOf(Page::class, $page);

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
            'page_id' => $page->id,
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

        $route  = route('pages.load', ['page' => $page->full_slug, 'version_id' => $draft_version->id]);
        $this->assertTrue(Str::contains($route, 'version_id'));
        $this->get($route)
            ->assertSessionHas('editing')
            ->assertSuccessful()
            ->assertDontSee($new_text)
            ->assertSee($old_text);

    }

    /** @test **/
    public function a_page_can_be_loaded_via_ajax()
    {
        $this->signInAdmin();
        session()->put('editing', true);

        $text_block = factory(TextBlock::class)->create();
        $content_element = $text_block->contentElement;
        $page = $content_element->pages->first();

        $this->assertInstanceOf(Page::class, $page);

        $this->withoutExceptionHandling();
        $this->json('GET', route('pages.load', ['page' => $page->full_slug]))
            ->assertSuccessful()
            ->assertSessionHas('editing')
            ->assertJsonFragment([
                'body' => $text_block->body,
            ]);

    }

    /** @test **/
    public function the_home_page_can_be_saved()
    {
        $input = [
            "content_elements" => [],
            "footer_bg_file_upload" => null,
            "footer_color" => null,
            "footer_fg_file_upload" => null,
            "name" => "d",
            "parent_page_id" => 0,
            "sort_order" => 0,
            "unlisted" => false,
        ];

        $this->signInAdmin();

        $this->json('POST', route('pages.update', ['id' => 1]), $input)
            //->assertSuccessful()
            ->assertJsonFragment([
                'success' => 'Page Saved',
                'full_slug' => '/',
            ]);
            
    }


    // when rolling back we will copy any old CEs and make new version numbers for them if we they dont have a draft
    // individual content elements can be published, the other draft items become a new version


    /** @test **/
    public function individual_content_elements_can_be_published()
    {
        $text_block = factory(TextBlock::class)->create();
        $content_element1 = $text_block->contentElement;
        $page = $content_element1->pages->first();
        
        $this->assertInstanceOf(Page::class, $page);
        $this->assertInstanceOf(ContentElement::class, $content_element1);

        $content_element2 = factory(ContentElement::class)->states('text-block')->create();

        $content_element2->pages()->detach();

        $content_element2->pages()->attach($page, [
            'sort_order' => 2,
            'unlisted' => false,
            'expandable' => false,
        ]);

        $this->assertEquals(2, $page->contentElements->count());

        $page->publish();
        $page->refresh();

        $this->signInAdmin();

        $content_element1['pivot'] = [
            'page_id' => $page->id,
            'sort_order' => $this->faker->randomNumber(1),
            'unlisted' => false,
            'expandable' => false,
        ];
        $input = $content_element1->toArray();
        $text_block_content = factory(TextBlock::class)->raw();
        $input['content'] = $text_block_content;

        $this->json('POST', route('content-elements.update', ['id' => $content_element1->id]), $input)
             ->assertSuccessful()
             ->assertJsonFragment([
                'success' => 'Text Block Saved',
             ]);

        $content_element1->refresh();
        $content_element1_version_id = $content_element1->version->id;
        $page->refresh();

        $this->assertEquals($page->draft_version_id, $content_element1->version->id);

        $content_element2['pivot'] = [
            'page_id' => $page->id,
            'sort_order' => $this->faker->randomNumber(1),
            'unlisted' => false,
            'expandable' => false,
        ];
        $input = $content_element2->toArray();
        $input['publish_at'] = now()->subMinutes(5);
        $text_block_content2 = factory(TextBlock::class)->raw();
        $input['content'] = $text_block_content2;

        $this->withoutExceptionHandling();
        $this->json('POST', route('content-elements.update', ['id' => $content_element2->id]), $input)
             ->assertSuccessful()
             ->assertJsonFragment([
                'success' => 'Text Block Saved',
             ]);

        $content_element2->refresh();
        $this->assertNotNull($content_element2->publish_at);
        $this->assertTrue($content_element2->publish_at->isPast());
        $this->assertEquals($page->draft_version_id, $content_element2->version->id);

        // publish command
        // find pages where the page needs to be publish OR the content elements need to be published

        $page->refresh();
        $this->assertNull($page->publish_at);

        Page::publishScheduledContent();

        $page->refresh();
        $content_element1->refresh();

        $this->assertNotEquals($content_element1_version_id, $content_element1->version->id);
        $this->assertEquals($page->getDraftVersion()->id, $content_element1->version->id);
        $this->assertEquals($page->publishedVersion->id, $content_element2->version->id);

    }

    /** @test **/
    public function when_a_page_is_published_an_event_is_broadcast()
    {
        Event::fake();
        $page = factory(Page::class)->states('unpublished')->create();

        $this->signInAdmin();

        $this->withoutExceptionHandling();
        $this->json('POST', route('pages.publish', ['id' => $page->id]))
             ->assertSuccessful()
             ->assertJsonFragment([
                'success' => 'Page Published',
             ]);

        $page->refresh();
        $this->assertNotNull($page->published_version_id);

        Event::assertDispatched(function (PagePublished $event) use ($page) {
            return $event->page->id === $page->id;
        });
    }

}
