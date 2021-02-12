<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

use Illuminate\Support\Arr;

use App\Models\User;
use App\Models\Role;
use App\Models\Livestream;
use App\Models\Inquiry;
use App\Models\Chat;

use App\Events\UserBanned;

class UserTest extends TestCase
{
    use WithFaker;

    /** @test **/
    public function the_users_index_can_be_loaded()
    {
        $this->get(route('users.index'))
            ->assertStatus(302);

        $this->signIn(User::factory()->create());

        $this->withoutExceptionHandling();
        $this->get(route('users.index'))
            ->assertRedirect('/');

        $this->signInAdmin();

        $this->get(route('users.index'))
            ->assertSuccessful();
    }

    /** @test **/
    public function all_users_can_be_loaded()
    {
        $this->json('GET', route('users.load'))
            ->assertStatus(401);

        $this->signIn(User::factory()->create());

        $this->json('GET', route('users.load'))
            ->assertStatus(403);

        $this->signInAdmin();

        $user = User::factory()->create();
        $role = Role::all()->random();
        $user->addRole($role);

        $this->json('GET', route('users.load'))
             ->assertSuccessful()
             ->assertJsonFragment([
                'name' => $user->name,
                'name' => $role->name,
             ]);
    }

    /** @test **/
    public function a_user_can_be_save_with_their_roles()
    {
        $user = User::factory()->create();
        $role = Role::all()->random();
        $input = User::factory()->raw();
        $input['roles'] = [$role];

        $this->assertFalse($user->hasRole($role));

        $this->json('POST', route('users.update', ['id' => $user->id]), $input)
            ->assertStatus(401);

        $this->signIn(User::factory()->create());

        $this->json('POST', route('users.update', ['id' => $user->id]), $input)
            ->assertStatus(403);

        $this->signInAdmin();

        $this->json('POST', route('users.update', ['id' => $user->id]), [])
             ->assertStatus(422)
             ->assertJsonValidationErrors([
                'name',
                'email',
             ]);

        $this->withoutExceptionHandling();
        $this->json('POST', route('users.update', ['id' => $user->id]), $input)
             ->assertSuccessful()
             ->assertJsonFragment([
                'success' => Arr::get($input, 'name').' Saved',
             ]);

        $user->refresh();

        $this->assertEquals(Arr::get($input, 'name'), $user->name);
        $this->assertEquals(Arr::get($input, 'email'), $user->email);
        $this->assertTrue($user->hasRole($role));
    }

    /** @test **/
    public function a_users_role_is_removed_if_it_is_not_included_in_the_save_input()
    {
        $user = User::factory()->create();
        $role = Role::all()->random();

        $user->addRole($role);
        $user->refresh();

        $this->assertTrue($user->hasRole($role));

        $input = User::factory()->raw();
        $input['roles'] = [];

        $this->signInAdmin();

        $this->withoutExceptionHandling();

        $this->json('POST', route('users.update', ['id' => $user->id]), $input)
             ->assertSuccessful()
             ->assertJsonFragment([
                'success' => Arr::get($input, 'name').' Saved',
             ]);

        $user->refresh();
        $this->assertFalse($user->hasRole($role));
    }

    /** @test **/
    public function users_can_be_searched()
    {
        $user = User::factory()->create();
        $input = [
            'autocomplete' => true,
            'terms' => $user->name,
        ];

        $this->json('POST', route('users.search'), $input)
            ->assertStatus(401);

        $this->signIn(User::factory()->create());

        $this->withoutExceptionHandling();
        $this->json('POST', route('users.search'), $input)
            ->assertStatus(403);

        $this->signInAdmin();

        $this->withoutExceptionHandling();
        $this->json('POST', route('users.search'), $input)
             ->assertSuccessful()
             ->assertJsonFragment([
                 'name' => $user->name,
                 'selected' => false,
             ]);
    }

    /** @test **/
    public function a_user_can_get_banned_from_chatting()
    {
        $livestream = Livestream::factory()->create();
        $room = $livestream->chat_room;
        $message = $this->faker->sentence;
        $inquiry = Inquiry::factory()->create();
        $inquiry->saveLivestreams(['livestream' => $livestream]);
        $user = $inquiry->user;

        $this->json('POST', route('users.ban', ['id' => $user->id]))
            ->assertStatus(401);

        $this->signIn($user);

        // create message

        $input = [
            'room' => $room,
            'message' => $message,
        ];

        $this->json('POST', route('chat.send-message'), $input)
             ->assertSuccessful()
             ->assertJsonFragment([
                'message' => $message,
             ]);

        $chat = Chat::all()->last();
        $this->assertEquals($message, $chat->message);

        $this->json('POST', route('chat.load'), ['room' => $room])
             ->assertJsonFragment([
                 'id' => $chat->id,
                 'message' => $chat->message,
             ]);

        // ban

        $this->json('POST', route('users.ban', ['id' => $user->id]))
            ->assertStatus(403);

        $this->signInAdmin();

        Event::fake();

        $this->json('POST', route('users.ban', ['id' => $user->id]))
             ->assertSuccessful()
             ->assertJsonFragment([
                'success' => $user->name.' Banned',
             ]);

        $user->refresh();
        $this->assertNotNull($user->banned_at);
        
        Event::assertDispatched(function (UserBanned $event) use ($user) {
            return $event->user->id === $user->id;
        });

        // banned messages are soft deleted
        $this->assertFalse(Chat::all()->contains('message', $message));

        $this->signIn($user);

        // create message

        $input = [
            'room' => $room,
            'message' => $message,
        ];

        $this->json('POST', route('chat.send-message'), $input)
             ->assertStatus(403);
    }

    /** @test **/
    public function a_mod_can_ban_a_user_from_chatting()
    {
        $livestream = Livestream::factory()->create();
        $room = $livestream->chat_room;
        $message = $this->faker->sentence;
        $inquiry = Inquiry::factory()->create();
        $inquiry->saveLivestreams(['livestream' => $livestream]);
        $user = $inquiry->user;
        $moderator = User::factory()->create();
        $livestream->moderators()->attach($moderator);

        $this->json('POST', route('users.ban', ['id' => $user->id]))
            ->assertStatus(401);

        $this->signIn($user);

        // create message

        $input = [
            'room' => $room,
            'message' => $message,
        ];

        $this->json('POST', route('chat.send-message'), $input)
             ->assertSuccessful()
             ->assertJsonFragment([
                'message' => $message,
             ]);

        $chat = Chat::all()->last();
        $this->assertEquals($message, $chat->message);

        $this->json('POST', route('chat.load'), ['room' => $room])
             ->assertJsonFragment([
                 'id' => $chat->id,
                 'message' => $chat->message,
             ]);

        // ban

        $this->json('POST', route('users.ban', ['id' => $user->id]), ['room' => $room])
            ->assertStatus(403);

        $this->signIn($moderator);

        Event::fake();

        $this->withoutExceptionHandling();
        $this->json('POST', route('users.ban', ['id' => $user->id]), ['room' => $room])
             ->assertSuccessful()
             ->assertJsonFragment([
                'success' => $user->name.' Banned',
             ]);

        $user->refresh();
        $this->assertNotNull($user->banned_at);
        
        Event::assertDispatched(function (UserBanned $event) use ($user) {
            return $event->user->id === $user->id;
        });

        // banned messages are soft deleted
        $this->assertFalse(Chat::all()->contains('message', $message));
    }
}
