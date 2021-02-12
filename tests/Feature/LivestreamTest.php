<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Arr;
use Tests\TestCase;
use Carbon\Carbon;

use App\Models\Livestream;
use App\Models\User;
use App\Models\Tag;
use App\Models\Inquiry;
use App\Models\Role;

use App\Mail\LivestreamReminder;

class LivestreamTest extends TestCase
{

    /** @test **/
    public function the_livestream_index_can_be_loaded()
    {
        $this->get(route('livestreams.index'))
             ->assertRedirect('/login');

        $this->signIn(User::factory()->create());

        $this->withoutExceptionHandling();
        $this->get(route('livestreams.index'))
             ->assertRedirect('/');

        $this->signInAdmin();

        $this->get(route('livestreams.index'))
             ->assertSuccessful();
    }

    /** @test **/
    public function a_livestream_can_be_created()
    {
        $input = Livestream::factory()->raw();
        $role = Role::factory()->create();
        $mod = User::factory()->create();
        $input['roles'] = [$role];
        $input['moderators'] = [$mod];

        $this->json('POST', route('livestreams.store'), [])
             ->assertStatus(401);

        $this->signIn(User::factory()->create());

        $this->json('POST', route('livestreams.store'), [])
             ->assertStatus(403);

        $this->signInAdmin();

        $this->json('POST', route('livestreams.store'), [])
             ->assertStatus(422)
             ->assertJsonValidationErrors([
                'name',
                'start_date',
                'video_id',
             ]);

        $this->withoutExceptionHandling();
        $this->json('POST', route('livestreams.store'), $input)
             ->assertSuccessful()
             ->assertJsonFragment([
                'success' => Arr::get($input, 'name').' Saved',
             ]);

        $livestream = Livestream::all()->last();

        $this->assertInstanceOf(Livestream::class, $livestream);
        $this->assertEquals(Arr::get($input, 'name'), $livestream->name);
        $this->assertEquals(Arr::get($input, 'video_id'), $livestream->video_id);
        $this->assertEquals(Arr::get($input, 'start_date'), $livestream->start_date);
        $this->assertInstanceOf(Carbon::class, $livestream->start_date);
        $this->assertEquals(Arr::get($input, 'length'), $livestream->length);
        $this->assertEquals(Arr::get($input, 'enable_chat'), $livestream->enable_chat);
        $this->assertNotNull($livestream->roles);
        $this->assertTrue($livestream->roles->contains('id', $role->id));
        $this->assertNotNull($livestream->moderators);
        $this->assertTrue($livestream->moderators->contains('id', $mod->id));
    }

    /** @test **/
    public function a_livestream_can_be_updated()
    {
        $livestream = Livestream::factory()->create();
        $role = Role::factory()->create();
        $mod1 = User::factory()->create();
        $mod2 = User::factory()->create();
        $livestream->createPermission($role);
        $livestream->moderators()->attach($mod1);

        $new_role = Role::factory()->create();

        $input = Livestream::factory()->raw();
        $input['roles'] = [$new_role];
        $input['moderators'] = [$mod2];

        $this->json('POST', route('livestreams.update', ['id' => $livestream->id]), [])
             ->assertStatus(401);

        $this->signIn(User::factory()->create());

        $this->json('POST', route('livestreams.update', ['id' => $livestream->id]), [])
             ->assertStatus(403);

        $this->signInAdmin();

        $this->json('POST', route('livestreams.update', ['id' => $livestream->id]), [])
             ->assertStatus(422)
             ->assertJsonValidationErrors([
                'name',
             ]);

        $this->withoutExceptionHandling();
        $this->json('POST', route('livestreams.update', ['id' => $livestream->id]), $input)
             ->assertSuccessful()
             ->assertJsonFragment([
                'success' => Arr::get($input, 'name').' Saved',
                'name' => Arr::get($input, 'name'),
             ]);

        $livestream->refresh();

        $this->assertEquals(Arr::get($input, 'name'), $livestream->name);
        $this->assertEquals(Arr::get($input, 'video_id'), $livestream->video_id);
        $this->assertEquals(Arr::get($input, 'start_date'), $livestream->start_date);
        $this->assertEquals(Arr::get($input, 'length'), $livestream->length);
        $this->assertEquals(Arr::get($input, 'enable_chat'), $livestream->enable_chat);
        $this->assertFalse($livestream->roles->contains('id', $role->id));
        $this->assertTrue($livestream->roles->contains('id', $new_role->id));
        $this->assertInstanceOf(Role::class, Role::find($role->id));
        $this->assertFalse($livestream->moderators->contains('id', $mod1->id));
        $this->assertTrue($livestream->moderators->contains('id', $mod2->id));
    }

    /** @test **/
    public function a_livestream_can_have_tags_that_are_used_for_filtering_on_pages()
    {
        $livestream = Livestream::factory()->create();
        $tag = Tag::factory()->create();

        $input = $livestream->toArray();
        $input['tags'] = [$tag];

        $this->signInAdmin();

        $this->withoutExceptionHandling();
        $this->json('POST', route('livestreams.update', ['id' => $livestream->id]), $input)
             ->assertSuccessful()
             ->assertJsonFragment([
                'success' => Arr::get($input, 'name').' Saved',
                'name' => Arr::get($input, 'name'),
             ]);

        $livestream->refresh();
        
        $this->assertNotNull($livestream->tags);
        $this->assertEquals(1, $livestream->tags->count());
        $this->assertTrue($livestream->tags->contains('id', $tag->id));
    }

    /** @test **/
    public function livestreams_can_be_loaded_for_pagination()
    {
        $livestream = Livestream::factory()->create();
        $inquiry = Inquiry::factory()->create();

        $inquiry->saveLivestreams(['livestream' => $livestream]);

        $this->json('GET', route('livestreams.index'))
            ->assertStatus(401);

        $this->signIn(User::factory()->create());

        $this->json('GET', route('livestreams.index'))
            ->assertStatus(403);

        $this->signInAdmin();
        session()->put('editing', true);

        $this->withoutExceptionHandling();
        $this->json('GET', route('livestreams.index'))
             ->assertSuccessful()
             ->assertJsonFragment([
                'name' => $livestream->name,
                'name' => $inquiry->user->name,
             ]);
    }

    /** @test **/
    public function a_livestream_can_be_viewed()
    {
        $livestream = Livestream::factory()->create();

        $this->withoutExceptionHandling();
        $this->get(route('livestreams.view', ['id' => $livestream->id]))
             ->assertSuccessful()
             ->assertViewHas('livestream');
    }

    /** @test **/
    public function registering_for_a_livestream()
    {
        $livestream = Livestream::factory()->create();

        $this->withoutExceptionHandling();
        $this->get(route('livestreams.register', ['id' => $livestream->id]))
             ->assertSuccessful()
             ->assertViewHas('livestream', $livestream);
    }

    /** @test **/
    public function a_livestream_can_load_an_inquiry()
    {
        $livestream = Livestream::factory()->create();
        $inquiry = Inquiry::factory()->create();

        $inquiry->saveLivestreams([
            'livestream' => $livestream,
        ]);

        $inquiry->refresh();
        $livestream->refresh();

        $this->assertTrue($inquiry->livestreams->contains('id', $livestream->id));
        $this->assertTrue($livestream->inquiries->contains('id', $inquiry->id));

        $this->assertNotNull($livestream->inquiries->first()->pivot->url);

        $this->get(route('livestreams.view', ['id' => $livestream]))
             ->assertSuccessful()
            ->assertViewMissing('inquiry', $inquiry);

        $this->get($livestream->inquiries->first()->pivot->url)
             ->assertSuccessful()
            ->assertViewHas('inquiry', $inquiry);

        $this->assertTrue(auth()->check());
        $this->assertEquals($inquiry->user->id, auth()->user()->id);
    }

    /** @test **/
    public function a_livestream_with_permissions_can_only_be_viewed_by_those_users()
    {
        $user = User::factory()->create();

        $role = Role::factory()->create();
        $role_user = User::factory()->create();
        $role_user->addRole($role);
        $role_user->refresh();

        $livestream = Livestream::factory()->create();

        $this->get(route('livestreams.view', ['id' => $livestream->id]))
            ->assertSuccessful();

        $livestream->createPermission($user);
        $livestream->refresh();
        $this->assertTrue($livestream->users->contains('id', $user->id));

        $this->get(route('livestreams.view', ['id' => $livestream->id]))
             ->assertRedirect('/login')
            ->assertSessionHas('url.intended', route('livestreams.view', ['id' => $livestream->id]));

        $this->signIn(User::factory()->create());

        $this->get(route('livestreams.view', ['id' => $livestream->id]))
             ->assertRedirect('/');

        $this->signIn($user);

        $this->get(route('livestreams.view', ['id' => $livestream->id]))
             ->assertSuccessful()
             ->assertViewIs('livestreams.view');

        $this->signIn($role_user);

        $this->get(route('livestreams.view', ['id' => $livestream->id]))
             ->assertRedirect('/');

        $livestream->refresh();
        $livestream->createPermission($role);
        $this->assertTrue($livestream->roles->contains('id', $role->id));

        $this->get(route('livestreams.view', ['id' => $livestream->id]))
             ->assertSuccessful()
             ->assertViewIs('livestreams.view');

        $inquiry = Inquiry::factory()->create();
        $inquiry_user = $inquiry->user;

        $this->signIn($inquiry_user);

        $this->get(route('livestreams.view', ['id' => $livestream->id]))
             ->assertRedirect('/');

        $inquiry->saveLivestreams([
            'livestream' => $livestream,
        ]);

        $this->get(route('livestreams.view', ['id' => $livestream->id]))
             ->assertSuccessful()
             ->assertViewIs('livestreams.view');
    }

    /** @test **/
    public function a_reminder_email_can_be_sent_to_inquiry_users_for_a_livestream()
    {
        $inquiry = Inquiry::factory()->create();
        $livestream = Livestream::factory()->create();

        $inquiry->saveLivestreams([
            'livestream' => $livestream,
        ]);

        $this->json('POST', route('livestreams.reminder-emails', ['id' => $livestream->id]))
            ->assertStatus(401);

        $this->signIn(User::factory()->create());

        $this->json('POST', route('livestreams.reminder-emails', ['id' => $livestream->id]))
            ->assertStatus(403);

        $this->signInAdmin();

        Mail::fake();

        $this->withoutExceptionHandling();
        $this->json('POST', route('livestreams.reminder-emails', ['id' => $livestream->id]))
             ->assertSuccessful()
             ->assertJsonFragment([
                'success' => $livestream->inquiry_users->count().' Reminder Emails Queued To Send',
             ]);

        Mail::assertQueued(LivestreamReminder::class, function ($mail) use ($inquiry) {
            return $mail->hasTo($inquiry->user->email);
        });

        $livestream->refresh();
        $this->assertEquals(1, $livestream->inquiry_users->count());
        $inquiry = $livestream->inquiries()->where('inquiry_id', $inquiry->id)->first();

        $this->assertNotNull($inquiry->pivot->reminder_email_sent_at);
    }


    /** @test **/
    public function an_individual_reminder_can_be_sent_to_a_livestream_user()
    {
        $inquiry1 = Inquiry::factory()->create();
        $inquiry2 = Inquiry::factory()->create();
        $livestream = Livestream::factory()->create();

        $inquiry1->saveLivestreams([
            'livestream' => $livestream,
        ]);

        $inquiry2->saveLivestreams([
            'livestream' => $livestream,
        ]);

        $this->signInAdmin();

        Mail::fake();

        $this->withoutExceptionHandling();
        $this->json('POST', route('livestreams.reminder-emails', ['id' => $livestream->id]), ['user_ids' => [$inquiry1->user->id]])
             ->assertSuccessful()
             ->assertJsonFragment([
                'success' => '1 Reminder Emails Queued To Send',
             ]);

        Mail::assertQueued(LivestreamReminder::class, function ($mail) use ($inquiry1) {
            return $mail->hasTo($inquiry1->user->email);
        });

        Mail::assertNotQueued(LivestreamReminder::class, function ($mail) use ($inquiry2) {
            return $mail->hasTo($inquiry2->user->email);
        });
    }
}
