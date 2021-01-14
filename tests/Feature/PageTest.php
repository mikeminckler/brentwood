<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Event;

use App\Models\Page;
use App\Models\ContentElement;
use App\Models\User;
use App\Models\TextBlock;
use App\Models\FileUpload;
use App\Models\Photo;
use App\Models\Version;

use App\Events\PagePublished;
use App\Events\PageSaved;

use Tests\Feature\SoftDeletesTestTrait;
use Tests\Feature\VersioningTestTrait;
use Tests\Feature\PagesTestTrait;

class PageTest extends TestCase
{
    use WithFaker;
    use SoftDeletesTestTrait;
    use VersioningTestTrait;
    use PagesTestTrait;

    protected function getModel()
    {
        return Page::factory()->create();
    }

    protected function getClassname()
    {
        return 'page';
    }

    /** @test **/
    public function a_page_can_be_created()
    {
        $input = Page::factory()->raw();

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

        $this->assertNotNull($page->name);
        $this->assertNotNull($page->title);
        $this->assertNotNull($page->parent_page_id);
        $this->assertNotNull($page->sort_order);

        $this->assertEquals(Arr::get($input, 'name'), $page->name);
        $this->assertEquals(Arr::get($input, 'title'), $page->title);
        $this->assertEquals(Arr::get($input, 'parent_page_id'), $page->parent_page_id);
        $this->assertEquals(Arr::get($input, 'sort_order'), $page->sort_order);
    }

    /** @test **/
    public function the_page_tree_can_be_loaded()
    {
        $first_level_page = Page::factory()->create();
        $second_level_page = Page::factory()->secondLevel()->create([
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
    public function the_page_tree_preview_can_be_loaded()
    {
        $first_level_page = Page::factory()->create();
        $second_level_page = Page::factory()->secondLevel()->create([
            'parent_page_id' => $first_level_page->id,
        ]);

        $this->withoutExceptionHandling();
        $this->json('GET', route('pages.index', ['preview' => 'true']))
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
        $page = Page::factory()->create();
        $input = Page::factory()->raw();

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

        $this->assertEquals(Arr::get($input, 'name'), $page->name);
        $this->assertEquals(Arr::get($input, 'parent_page_id'), $page->parent_page_id);
        $this->assertEquals(Arr::get($input, 'sort_order'), $page->sort_order);
    }

    /** @test **/
    public function a_page_can_be_hidden()
    {
        $page = Page::factory()->create();
        $input = Page::factory()->raw([
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

        $input = Page::factory()->raw([
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
        $this->get($slug)
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
            'sort_order' => $this->faker->numberBetween(10, 100),
        ];

        $this->assertTrue($home_page->id === 1);
        $this->assertTrue(Str::contains(route('pages.update', ['id' => $home_page->id]), 1));
        $this->json('POST', route('pages.update', ['id' => $home_page->id]), $input)
            ->assertJsonFragment([
                'success' => 'Page Saved',
            ])
            ->assertSuccessful();

        $home_page->refresh();

        $this->assertEquals($home_page_slug, $home_page->slug);
        $this->assertEquals($home_page_parent_page_id, $home_page->parent_page_id);
    }

    /** @test **/
    public function a_published_page_can_be_updated()
    {
        $page = Page::factory()->published()->create();

        $content_element = $this->createContentElement(TextBlock::factory());
        $content_element->version_id = $page->published_version_id;
        $content_element->save();
        $content_element->refresh();

        $content_element['pivot'] = [
            'contentable_id' => $page->id,
            'contentable_type' => get_class($page),
            'sort_order' => $this->faker->randomNumber(1),
            'unlisted' => false,
            'expandable' => false,
        ];

        $this->signInAdmin();

        $input = $content_element->toArray();
        $input['content'] = TextBlock::factory()->raw();

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
        $page = Page::factory()->unpublished()->create();

        $this->get($page->full_slug)
            ->assertStatus(404);
        
        $page->publish();

        $this->withoutExceptionHandling();
        $this->get($page->full_slug)
            ->assertSuccessful();
    }

    /** @test **/
    public function the_home_page_cannot_be_deleted()
    {
        $home_page = Page::findOrFail(1);

        $this->signInAdmin();

        $this->withoutExceptionHandling();
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

        $page = Page::factory()->create();
        $input = Page::factory()->raw();

        $fg_photo_input = Photo::factory()->raw();
        $fg_photo_input['file_upload'] = $fg_file_upload;

        $bg_photo_input = Photo::factory()->raw();
        $bg_photo_input['file_upload'] = $bg_file_upload;

        $input['footer_fg_photo'] = $fg_photo_input;
        $input['footer_bg_photo'] = $bg_photo_input;

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

        $this->assertEquals(Arr::get($input, 'name'), $page->name);
        $this->assertEquals(Arr::get($input, 'parent_page_id'), $page->parent_page_id);
        $this->assertEquals(Arr::get($input, 'sort_order'), $page->sort_order);
        $this->assertEquals(Arr::get($input, 'footer_color'), $page->footer_color);

        $this->assertNotNull(Arr::get($input, 'footer_fg_photo.file_upload.id'));
        $this->assertNotNull(Arr::get($input, 'footer_bg_photo.file_upload.id'));

        $this->assertEquals(Arr::get($input, 'footer_fg_photo.file_upload.id'), $page->getFooterFgPhoto()->fileUpload->id);
        $this->assertEquals(Arr::get($input, 'footer_bg_photo.file_upload.id'), $page->getFooterBgPhoto()->fileUpload->id);

        $page_fg_photo = $page->getFooterFgPhoto();
        $page_bg_photo = $page->getFooterBgPhoto();

        $this->assertInstanceOf(Photo::class, $page_fg_photo);
        $this->assertInstanceOf(Photo::class, $page_bg_photo);
    }

    /** @test **/
    public function a_user_without_update_permisson_cannot_save_a_page()
    {
        $page = Page::factory()->create();
        $user = User::factory()->create();

        $input = Page::factory()->raw();

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
        $parent_page = Page::factory()->create();
        $page = Page::factory()->create([
            'parent_page_id' => $parent_page->id,
            'sort_order' => 2
        ]);

        $this->assertInstanceOf(Page::class, $parent_page);

        $page_above = Page::factory()->create([
            'parent_page_id' => $parent_page->id,
            'sort_order' => 1,
        ]);

        $page_below = Page::factory()->create([
            'parent_page_id' => $parent_page->id,
            'sort_order' => 3,
        ]);

        $page_last = Page::factory()->create([
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
        $parent_page = Page::factory()->create();
        $page = Page::factory()->create([
            'parent_page_id' => $parent_page->id,
            'sort_order' => 2
        ]);

        $this->assertInstanceOf(Page::class, $parent_page);

        $page_above = Page::factory()->create([
            'parent_page_id' => $parent_page->id,
            'sort_order' => 1,
        ]);

        $page_below = Page::factory()->create([
            'parent_page_id' => $parent_page->id,
            'sort_order' => 3,
        ]);

        $page_last = Page::factory()->create([
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
        $parent_old = Page::factory()->create();
        $parent_new = Page::factory()->create();
        $page = Page::factory()->create([
            'parent_page_id' => $parent_old->id,
            'sort_order' => 2
        ]);

        $old_page1 = Page::factory()->create([
            'parent_page_id' => $parent_old->id,
            'sort_order' => 1
        ]);

        $old_page2 = Page::factory()->create([
            'parent_page_id' => $parent_old->id,
            'sort_order' => 3
        ]);

        $new_page1 = Page::factory()->create([
            'parent_page_id' => $parent_new->id,
            'sort_order' => 1
        ]);

        $new_page2 = Page::factory()->create([
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

    /*
    public function sorting_a_first_level_page()
    {
        $page = factory(Page::class)->create([
            'parent_page_id' => 1,
            'sort_order' => 2,
        ]);

        $this->signInAdmin();

        $input = [
            // This sets the position, after the below page
            'sort_order' => 1.5,
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
        dump($page->parentPage->pages()->get()->pluck('sort_order')->toArray());
        $this->assertEquals(1, $page->sort_order);

    }
     */



    /** @test **/
    public function the_home_page_can_be_saved()
    {
        $input = [
            "content_elements" => [],
            "footer_bg_photo" => null,
            "footer_color" => null,
            "footer_fg_photo" => null,
            "name" => "d",
            "parent_page_id" => 0,
            "sort_order" => 0,
            "unlisted" => false,
        ];

        $this->signInAdmin();

        $this->json('POST', route('pages.update', ['id' => 1]), $input)
            ->assertJsonFragment([
                'success' => 'Page Saved',
                'full_slug' => '/',
            ])
            ->assertSuccessful();
    }


    /** @test **/
    public function individual_content_elements_can_be_published()
    {
        $content_element1 = $this->createContentElement(TextBlock::factory());
        $text_block = $content_element1->content;
        $page = $content_element1->pages->first();
        $content_element1->version_id = $page->getDraftVersion()->id;
        $content_element1->save();
        $content_element1->refresh();
        
        $this->assertInstanceOf(Page::class, $page);
        $this->assertInstanceOf(ContentElement::class, $content_element1);

        $content_element2 = $this->createContentElement(TextBlock::factory());
        $text_block2 = $content_element2->content;

        $content_element2->pages()->detach();

        $content_element2->pages()->attach($page, [
            'sort_order' => 2,
            'unlisted' => false,
            'expandable' => false,
        ]);

        $content_element2->version_id = $page->getDraftVersion()->id;
        $content_element2->save();
        $content_element2->refresh();

        $this->assertEquals(2, $page->contentElements->count());

        //$page->publish();
        $page->refresh();

        $this->signInAdmin();

        $content_element1['pivot'] = [
            'contentable_id' => $page->id,
            'contentable_type' => get_class($page),
            'sort_order' => $this->faker->randomNumber(1),
            'unlisted' => false,
            'expandable' => false,
        ];
        $input = $content_element1->toArray();
        $text_block_content = TextBlock::factory()->raw();
        $input['content'] = $text_block_content;

        $this->json('POST', route('content-elements.update', ['id' => $content_element1->id]), $input)
             ->assertSuccessful()
             ->assertJsonFragment([
                'success' => 'Text Block Saved',
             ]);

        $content_element1->refresh();
        $content_element1_version_id = $content_element1->version->id;
        $page->refresh();

        $this->assertEquals($page->getDraftVersion()->id, $content_element1->version->id);

        $content_element2['pivot'] = [
            'contentable_id' => $page->id,
            'contentable_type' => get_class($page),
            'sort_order' => $this->faker->randomNumber(1),
            'unlisted' => false,
            'expandable' => false,
        ];
        $input = $content_element2->toArray();
        $input['publish_at'] = now()->subMinutes(5);
        $text_block_content2 = TextBlock::factory()->raw();
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
    public function loading_a_pages_content_elements_includes_the_contentable_property()
    {
        $content_element = $this->createContentElement(TextBlock::factory());
        $page = $content_element->pages->first();

        $this->assertInstanceOf(Page::class, $page);

        $this->signInAdmin();

        $this->json('GET', route('pages.load', ['page' => $page->full_slug]))
            ->assertSuccessful()
            ->assertJsonFragment([
                'contentable_id' => $page->id,
                'contentable_type' => 'page',
            ]);
    }
}
