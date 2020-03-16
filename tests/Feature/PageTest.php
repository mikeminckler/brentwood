<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Page;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class PageTest extends TestCase
{

    use WithFaker;

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
                'order',
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
        $this->assertEquals( Arr::get($input, 'order'), $page->order);
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
                'order',
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
        $this->assertEquals( Arr::get($input, 'order'), $page->order);
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
            'parent_page_id' => factory(Page::class)->create(['parent_page_id' => $this->faker->numberBetween(1000,100000)])->id,
            'order' => $this->faker->numberBetween(10,100),
        ];

        $this->postJson(route('pages.update', ['id' => $home_page->id]), $input)
            ->assertSuccessful()
            ->assertJsonFragment([
                'success' => 'Page Saved',
            ]);

        $home_page->refresh();

        $this->assertEquals($home_page_slug, $home_page->slug);
        $this->assertEquals($home_page_parent_page_id, $home_page->parent_page_id);
        
    }
}
