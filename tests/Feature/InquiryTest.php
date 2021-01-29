<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

use App\Models\User;
use App\Models\Page;
use App\Models\Inquiry;
use App\Models\Tag;
use App\Models\TextBlock;
use App\Models\Livestream;

class InquiryTest extends TestCase
{
    use WithFaker;

    /** @test **/
    public function the_inquiries_index_can_be_loaded()
    {
        $this->get(route('inquiries.index'))
            ->assertStatus(302);

        $this->signIn(User::factory()->create());

        $this->get(route('inquiries.index'))
            ->assertRedirect('/');

        $this->signInAdmin();

        $this->withoutExceptionHandling();
        $this->get(route('inquiries.index'))
            ->assertSuccessful();
    }

    /** @test **/
    public function the_inquiry_page_can_be_loaded()
    {
        $this->withoutExceptionHandling();
        $this->get(route('inquiries.create'))
            ->assertSuccessful();
    }


    /** @test **/
    public function the_inquiry_page_important_fields_cannot_be_changed()
    {
        $fake_page = Page::factory()->create();
        $inquiry_page = Page::where('slug', 'inquiry')->first();
        $this->assertInstanceOf(Page::class, $inquiry_page);

        $inquiry_page_slug = $inquiry_page->slug;
        $inquiry_page_parent_page_id = $inquiry_page->parent_page_id;

        $this->signInAdmin();

        $input = [
            'name' => $this->faker->firstName,
            'slug' => $this->faker->firstName,
            'parent_page_id' => $fake_page->id,
            'sort_order' => $this->faker->numberBetween(10, 100),
        ];

        $this->assertTrue($inquiry_page->id === 2);
        $this->assertTrue(Str::contains(route('pages.update', ['id' => $inquiry_page->id]), 2));

        $this->json('POST', route('pages.update', ['id' => $inquiry_page->id]), $input)
            ->assertJsonFragment([
                'success' => 'Page Saved',
            ])
            ->assertSuccessful();

        $inquiry_page->refresh();

        $this->assertEquals($inquiry_page_slug, $inquiry_page->slug);
        $this->assertEquals($inquiry_page_parent_page_id, $inquiry_page->parent_page_id);
        $this->assertEquals(2, $inquiry_page->id);
    }

    /** @test **/
    public function the_inquiry_content_page_important_fields_cannot_be_changed()
    {
        $fake_page = Page::factory()->create();
        $inquiry_page = Page::where('slug', 'inquiry-content')->first();
        $this->assertInstanceOf(Page::class, $inquiry_page);

        $inquiry_page_slug = $inquiry_page->slug;
        $inquiry_page_parent_page_id = $inquiry_page->parent_page_id;

        $this->signInAdmin();

        $input = [
            'name' => $this->faker->firstName,
            'slug' => $this->faker->firstName,
            'parent_page_id' => $fake_page->id,
            'sort_order' => $this->faker->numberBetween(10, 100),
        ];

        $this->assertTrue($inquiry_page->id === 3);
        $this->assertTrue(Str::contains(route('pages.update', ['id' => $inquiry_page->id]), 3));

        $this->json('POST', route('pages.update', ['id' => $inquiry_page->id]), $input)
            ->assertJsonFragment([
                'success' => 'Page Saved',
            ])
            ->assertSuccessful();

        $inquiry_page->refresh();

        $this->assertEquals($inquiry_page_slug, $inquiry_page->slug);
        $this->assertEquals($inquiry_page_parent_page_id, $inquiry_page->parent_page_id);
        $this->assertEquals(3, $inquiry_page->id);
    }

    // an inquiry can be created
    /** @test **/
    public function an_inquiry_can_be_created()
    {
        $this->json('POST', route('inquiries.store'), [])
             ->assertStatus(422)
             ->assertJsonValidationErrors([
                'name',
                'email',
                'target_grade',
                'target_year',
                'student_type',
             ]);

        $tag1 = Tag::factory()->create();
        $tag2 = Tag::factory()->create();
        $tag3 = Tag::factory()->create();

        $input = Inquiry::factory()->raw();
        $input['tags'] = [ $tag1, $tag2, $tag3 ];

        $this->withoutExceptionHandling();
        $this->json('POST', route('inquiries.store'), $input)
            ->assertSuccessful()
            ->assertJsonFragment([
                'success' => 'Inquiry Saved',
            ]);

        $inquiry = Inquiry::all()->last();

        $this->assertNotNull($inquiry->name);
        $this->assertEquals(Arr::get($input, 'name'), $inquiry->name);
        $this->assertNotNull($inquiry->email);
        $this->assertEquals(Arr::get($input, 'email'), $inquiry->email);
        $this->assertNotNull($inquiry->phone);
        $this->assertEquals(Arr::get($input, 'phone'), $inquiry->phone);
        $this->assertNotNull($inquiry->target_grade);
        $this->assertEquals(Arr::get($input, 'target_grade'), $inquiry->target_grade);
        $this->assertNotNull($inquiry->target_year);
        $this->assertEquals(Arr::get($input, 'target_year'), $inquiry->target_year);
        $this->assertNotNull($inquiry->student_type);
        $this->assertEquals(Arr::get($input, 'student_type'), $inquiry->student_type);

        $this->assertTrue($inquiry->tags->contains('id', $tag1->id));
        $this->assertTrue($inquiry->tags->contains('id', $tag2->id));
        $this->assertTrue($inquiry->tags->contains('id', $tag3->id));

        $this->assertNotNull($inquiry->url);
    }

    /** @test **/
    public function an_inquiry_can_be_updated()
    {
        $inquiry = Inquiry::factory()->has(Tag::factory()->count(3))->create();
        $tag = Tag::factory()->create();

        $this->json('POST', route('inquiries.view', ['id' => $inquiry->id]), [])
            ->assertStatus(403);

        $this->json('POST', $inquiry->url, [])
             ->assertStatus(422)
             ->assertJsonValidationErrors([
                'name',
                'email',
                'target_grade',
                'target_year',
                'student_type',
             ]);
        
        $input = Inquiry::factory()->raw();
        $input['tags'] = [ $tag ];

        $this->json('POST', $inquiry->url, $input)
            ->assertSuccessful()
            ->assertJsonFragment([
                'success' => 'Inquiry Saved',
            ]);

        $inquiry->refresh();
        $this->assertEquals(Arr::get($input, 'name'), $inquiry->name);
        $this->assertEquals(Arr::get($input, 'email'), $inquiry->email);
        $this->assertEquals(Arr::get($input, 'phone'), $inquiry->phone);
        $this->assertEquals(Arr::get($input, 'target_grade'), $inquiry->target_grade);
        $this->assertEquals(Arr::get($input, 'target_year'), $inquiry->target_year);
        $this->assertEquals(Arr::get($input, 'student_type'), $inquiry->student_type);

        $this->assertEquals(1, $inquiry->tags->count());
        $this->assertTrue($inquiry->tags->contains('id', $tag->id));
    }

    // an inquiry can be viewed

    /** @test **/
    public function an_inquiry_can_be_viewed()
    {
        $inquiry = Inquiry::factory()->has(Tag::factory()->count(3))->create();

        $this->assertInstanceOf(Inquiry::class, $inquiry);
        $this->assertEquals(3, $inquiry->tags->count());

        $this->get(route('inquiries.view', ['id' => $inquiry->id]))
            ->assertStatus(401);

        $this->get($inquiry->url)
             ->assertSuccessful()
            ->assertViewHas('inquiry', $inquiry);
    }

    /** @test **/
    public function untagged_content_will_always_display_on_the_inquiry_page()
    {
        $inquiry = Inquiry::factory()->has(Tag::factory()->count(3))->create();

        $this->assertInstanceOf(Inquiry::class, $inquiry);
        $this->assertEquals(3, $inquiry->tags->count());

        $this->get(route('inquiries.view', ['id' => $inquiry->id]))
            ->assertStatus(401);

        $this->get($inquiry->url)
             ->assertSuccessful()
            ->assertViewHas('inquiry', $inquiry);
    }

    /** @test **/
    public function untagged_content_elements_always_show_on_the_inquiry_page()
    {
        $inquiry_page = Inquiry::findPage();
        // clear out old content elements
        $inquiry_page->contentElements()->delete();
        $inquiry_page->refresh();
        $this->assertEquals(0, $inquiry_page->contentElements->count());

        // create new content elements
        $content_element_start = $this->createContentElement(TextBlock::factory(), $inquiry_page);
        $tagged_content_element = $this->createContentElement(TextBlock::factory(), $inquiry_page);
        $tag = Tag::factory()->create();
        $tagged_content_element->addTag($tag);
        $content_element_end = $this->createContentElement(TextBlock::factory(), $inquiry_page);

        $this->assertEquals(1, $tagged_content_element->tags()->count());

        $inquiry_page->publish();

        $this->assertEquals(3, $inquiry_page->published_content_elements->count());

        $inquiry = Inquiry::factory()->create();

        $this->get($inquiry->url)
            ->assertSee($content_element_start->content->body)
            ->assertDontSee($tagged_content_element->content->body)
            ->assertSee($content_element_end->content->body);

        $inquiry->addTag($tag);

        $inquiry->refresh();

        $this->get($inquiry->url)
            ->assertSee($content_element_start->content->body)
            ->assertSee($tagged_content_element->content->body)
            ->assertSee($content_element_end->content->body);
    }
    
    /** @test **/
    public function inquiry_tags_can_be_loaded()
    {
        $inquiry_page = Inquiry::findPage();
        $tag = Tag::factory()->create();
        $content_element = $this->createContentElement(TextBlock::factory(), $inquiry_page);
        $content_element->addTag($tag);

        $inquiry_page->publish();
        $this->assertTrue(Inquiry::getTags()->contains('id', $tag->id));

        $this->json('GET', route('inquiries.tags'))
             ->assertSuccessful()
             ->assertJsonFragment([
                'name' => $tag->name,
                'id' => $tag->id,
             ]);
    }

    /** @test **/
    public function inquiries_can_be_loaded_for_pagination()
    {
        $inquiry = Inquiry::factory()->create();

        $this->assertInstanceOf(Inquiry::class, $inquiry);

        $this->json('GET', route('inquiries.index'))
            ->assertStatus(401);

        $this->signIn(User::factory()->create());

        $this->json('GET', route('inquiries.index'))
            ->assertStatus(403);

        $this->signInAdmin();
        session()->put('editing', true);

        $this->withoutExceptionHandling();
        $this->json('GET', route('inquiries.index'))
             ->assertSuccessful()
             ->assertJsonFragment([
                 'name' => $inquiry->name,
                 'email' => $inquiry->email,
             ]);
    }

    /** @test **/
    public function an_inquiry_can_save_an_associated_livestream()
    {
        $livestream = Livestream::factory()->create();

        $input = Inquiry::factory()->raw();
        $input['livestreams'] = [
            $livestream
        ];

        $this->withoutExceptionHandling();
        $this->json('POST', route('inquiries.store'), $input)
            ->assertSuccessful()
            ->assertJsonFragment([
                'success' => 'Inquiry Saved',
            ]);

        $inquiry = Inquiry::all()->last();

        $this->assertInstanceOf(Inquiry::class, $inquiry);

        $this->assertEquals(1, $inquiry->livestreams->count());
        $this->assertEquals($livestream->id, $inquiry->livestreams->first()->id);
    }

    /** @test **/
    public function inquiry_livestreams_can_be_loaded()
    {
        $inquiry_page = Inquiry::findPage();

        $livestream = Livestream::factory()->create();
        $open_house_tag = Tag::where('name', 'Open House')->first();
        $livestream->tags()->attach($open_house_tag);

        $livestreams = Inquiry::getLivestreams();

        $this->assertTrue($livestreams->contains('id', $livestream->id));

        $this->withoutExceptionHandling();
        $this->json('GET', route('inquiries.livestreams'))
             ->assertSuccessful()
             ->assertJsonFragment([
                'name' => $livestream->name,
                'id' => $livestream->id,
             ]);
    }

    /** @test **/
    public function a_livestream_can_be_loaded_into_the_inquiry_form()
    {
        $livestream = Livestream::factory()->create();

        $open_house_tag = Tag::where('name', 'Open House')->first();

        $livestream->tags()->attach($open_house_tag);

        $this->assertTrue(Inquiry::getLivestreams()->contains('id', $livestream->id));

        $this->withoutExceptionHandling();
        $this->get(route('inquiries.create', ['livestream_id' => $livestream->id]))
             ->assertSuccessful()
             ->assertViewHas('livestream', $livestream);
    }
}
