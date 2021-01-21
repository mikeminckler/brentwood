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

class InquiryTest extends TestCase
{

    use WithFaker;

    /** @test **/
    public function the_inquiry_index_can_be_loaded()
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

    // an inquiry can be viewed

    /** @test **/
    public function an_inquiry_can_be_viewed()
    {
        $inquiry = Inquiry::factory()->has(Tag::factory()->count(3))->create();

        $this->assertInstanceOf(Inquiry::class, $inquiry);
        $this->assertEquals(3, $inquiry->tags->count());

        $this->get( route('inquiries.view', ['id' => $inquiry->id]))
            ->assertStatus(401);

        $this->get( $inquiry->url)
             ->assertSuccessful()
            ->assertViewHas('inquiry', $inquiry);
    }

    /** @test **/
    public function untagged_content_will_always_display_on_the_inquiry_page()
    {
        $inquiry = Inquiry::factory()->has(Tag::factory()->count(3))->create();

        $this->assertInstanceOf(Inquiry::class, $inquiry);
        $this->assertEquals(3, $inquiry->tags->count());

        $this->get( route('inquiries.view', ['id' => $inquiry->id]))
            ->assertStatus(401);

        $this->get( $inquiry->url)
             ->assertSuccessful()
            ->assertViewHas('inquiry', $inquiry);
        
    }

    // an inquiry has a user....

    // global inquiry content always show
    // tagged content can be filtered by selection
}
