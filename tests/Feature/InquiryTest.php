<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

use App\Models\User;
use App\Models\Page;
use App\Models\Inquiry;
use App\Models\Tag;
use App\Models\TextBlock;
use App\Models\Livestream;

use App\Mail\InquiryConfirmation;
use App\Mail\EmailVerification;

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
        $this->get('/inquiry')
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

        $this->json('POST', route('pages.update', ['id' => $inquiry_page->id]), $input)
            ->assertJsonFragment([
                'success' => 'Page Saved',
            ])
            ->assertSuccessful();

        $inquiry_page->refresh();

        $this->assertEquals($inquiry_page_slug, $inquiry_page->slug);
        $this->assertEquals($inquiry_page_parent_page_id, $inquiry_page->parent_page_id);
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

        $this->json('POST', route('pages.update', ['id' => $inquiry_page->id]), $input)
            ->assertJsonFragment([
                'success' => 'Page Saved',
            ])
            ->assertSuccessful();

        $inquiry_page->refresh();

        $this->assertEquals($inquiry_page_slug, $inquiry_page->slug);
        $this->assertEquals($inquiry_page_parent_page_id, $inquiry_page->parent_page_id);
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
                //'target_grade',
                //'target_year',
                //'student_type',
             ]);

        $tag1 = Tag::factory()->create();
        $tag2 = Tag::factory()->create();
        $tag3 = Tag::factory()->create();

        $name = $this->faker->name;
        $email = $this->faker->safeEmail;

        $input = Inquiry::factory()->raw([
            'user_id' => null,
            'name' => $name,
            'email' => $email,
        ]);

        $input['tags'] = [ $tag1, $tag2, $tag3 ];

        $this->withoutExceptionHandling();
        $this->json('POST', route('inquiries.store'), $input)
            ->assertSuccessful()
            ->assertJsonFragment([
                'success' => 'Inquiry Saved',
            ]);

        $inquiry = Inquiry::all()->last();

        $this->assertNotNull($inquiry->user->name);
        $this->assertEquals(Arr::get($input, 'name'), $inquiry->user->name);
        $this->assertNotNull($inquiry->user->email);
        $this->assertEquals(Arr::get($input, 'email'), $inquiry->user->email);
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
        $inquiry = Inquiry::factory()->for(User::factory())->has(Tag::factory()->count(3))->create();
        $user = $inquiry->user;
        $tag = Tag::factory()->create();

        $this->json('POST', route('inquiries.view', ['id' => $inquiry->id]), [])
            ->assertStatus(403);

        $this->json('POST', $inquiry->url, [])
             ->assertStatus(422)
             ->assertJsonValidationErrors([
                'name',
                'email',
                //'target_grade',
                //'target_year',
                //'student_type',
             ]);
        
        $input = Inquiry::factory()->raw([
            'user_id' => $inquiry->user->id,
            'name' => $user->name,
            'email' => $user->email,
        ]);
        $input['tags'] = [ $tag ];

        $this->withoutExceptionHandling();
        $this->json('POST', $inquiry->url, $input)
            ->assertSuccessful()
            ->assertJsonFragment([
                'success' => 'Inquiry Saved',
            ]);

        $inquiry->refresh();
        $this->assertEquals(Arr::get($input, 'name'), $inquiry->user->name);
        $this->assertEquals(Arr::get($input, 'email'), $inquiry->user->email);
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
                 'name' => $inquiry->user->name,
                 'email' => $inquiry->user->email,
             ]);
    }

    /** @test **/
    public function an_inquiry_can_save_an_associated_array_of_livestream()
    {
        $livestream = Livestream::factory()->create();

        $input = Inquiry::factory()->raw([
            'user_id' => null,
            'name' => $this->faker->name,
            'email' => $this->faker->email,
        ]);
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

        $this->get($inquiry->url)
             ->assertSuccessful();
    }

    /** @test **/
    public function an_inquiry_can_save_an_associated_livestream()
    {
        $livestream = Livestream::factory()->create();

        $input = Inquiry::factory()->raw([
            'user_id' => null,
            'name' => $this->faker->name,
            'email' => $this->faker->email,
        ]);
        $input['livestream'] = $livestream;

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

        $this->get($inquiry->url)
             ->assertSuccessful();

        $this->assertNotNull($inquiry->livestreams()->first()->pivot->url);

        $this->get($inquiry->livestreams()->first()->pivot->url)
            ->assertSuccessful();
    }

    /** @test **/
    public function an_inquiry_can_be_created_with_just_a_name_and_email()
    {
        $name = $this->faker->name;
        $email = $this->faker->safeEmail;

        $input = [
            'name' => $name,
            'email' => $email,
        ];

        $this->withoutExceptionHandling();
        $this->json('POST', route('inquiries.store'), $input)
            ->assertSuccessful()
            ->assertJsonFragment([
                'success' => 'Inquiry Saved',
            ]);

        $inquiry = Inquiry::all()->last();

        $this->assertInstanceOf(Inquiry::class, $inquiry);

        $this->assertEquals($name, $inquiry->user->name);
        $this->assertEquals($email, $inquiry->user->email);
    }

    /** @test **/
    public function registering_for_a_livestream_sends_a_confimation_email()
    {
        $name = $this->faker->name;
        $email = $this->faker->safeEmail;
        $livestream = Livestream::factory()->create();

        $input = [
            'name' => $name,
            'email' => $email,
            'livestream' => $livestream,
        ];

        Mail::fake();

        $this->withoutExceptionHandling();
        $this->json('POST', route('inquiries.store'), $input)
            ->assertSuccessful()
            ->assertJsonFragment([
                'success' => 'Inquiry Saved',
            ]);

        Mail::assertQueued(InquiryConfirmation::class, function ($mail) use ($email) {
            return $mail->hasTo($email);
        });
    }

    /** @test **/
    public function a_user_can_create_a_password_when_signing_up()
    {
        $name = $this->faker->name;
        $email = $this->faker->safeEmail;
        $password = Str::random(8);

        $input = [
            'name' => $name,
            'email' => $email,
            'password' => 'short',
        ];

        $this->json('POST', route('inquiries.store'), $input)
             ->assertStatus(422)
             ->assertJsonValidationErrors([
                'password',
             ]);

        $input['password'] = $password;

        $this->json('POST', route('inquiries.store'), $input)
             ->assertStatus(422)
             ->assertJsonValidationErrors([
                'password',
             ]);

        $input['password_confirmation'] = $password;

        $this->withoutExceptionHandling();
        $this->json('POST', route('inquiries.store'), $input)
            ->assertSuccessful()
            ->assertJsonFragment([
                'success' => 'Inquiry Saved',
            ]);

        $inquiry = Inquiry::all()->last();

        $this->assertInstanceOf(Inquiry::class, $inquiry);

        $this->assertEquals($name, $inquiry->user->name);
        $this->assertEquals($email, $inquiry->user->email);

        $this->assertTrue(auth()->attempt(['email' => $email, 'password' => $password]));
    }

    /** @test **/
    public function a_user_cannot_change_a_password_for_an_existing_account_when_signing_up()
    {
        $password = Str::random(8);
        $user = User::factory()->create([
            'password' => Hash::make($password),
        ]);
        $this->assertNotNull($user->password);

        $this->assertTrue(auth()->attempt(['email' => $user->email, 'password' => $password]));

        auth()->logout();
        $this->assertFalse(auth()->check());

        $new_password = Str::random(8);

        $input = [
            'name' => $user->name,
            'email' => $user->email,
            'password' => $new_password,
            'password_confirmation' => $new_password,
        ];

        $this->withoutExceptionHandling();
        $this->json('POST', route('inquiries.store'), $input)
            ->assertSuccessful()
            ->assertJsonFragment([
                'success' => 'Inquiry Saved',
            ]);

        $inquiry = Inquiry::all()->last();

        $this->assertInstanceOf(Inquiry::class, $inquiry);

        $this->assertEquals($user->name, $inquiry->user->name);
        $this->assertEquals($user->email, $inquiry->user->email);

        $this->assertTrue(auth()->attempt(['email' => $user->email, 'password' => $password]));
    }


    /** @test **/
    public function creating_an_inquiry_with_a_password_sends_an_email_confirmation_link()
    {
        Mail::fake();

        $name = $this->faker->name;
        $email = $this->faker->safeEmail;
        $password = Str::random(8);

        $input = [
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $password,
        ];

        $this->withoutExceptionHandling();
        $this->json('POST', route('inquiries.store'), $input)
            ->assertSuccessful()
            ->assertJsonFragment([
                'success' => 'Inquiry Saved',
            ]);

        $inquiry = Inquiry::all()->last();
        $this->assertInstanceOf(Inquiry::class, $inquiry);

        $user = $inquiry->user;
        $this->assertInstanceOf(User::class, $user);

        $this->assertEquals($email, $user->email);

        $this->assertTrue(auth()->attempt(['email' => $email, 'password' => $password]));

        Mail::assertQueued(EmailVerification::class, function ($mail) use ($email) {
            return $mail->hasTo($email);
        });
    }
}
