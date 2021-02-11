<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

use App\Models\Livestream;
use App\Models\Inquiry;
use App\Models\User;
use App\Models\Chat;

use App\Events\ChatMessageCreated;
use App\Events\WhisperCreated;

class ChatTest extends TestCase
{
    use WithFaker;

    /** @test **/
    public function a_new_message_can_be_sent_to_chat()
    {
        $message = $this->faker->sentence;
        $livestream = Livestream::factory()->create();
        $inquiry = Inquiry::factory()->create();
        $inquiry->saveLivestreams(['livestream' => $livestream]);

        $user = $inquiry->user;
        $this->assertInstanceOf(User::class, $user);

        $input = [
            'room' => 'livestream.'.$livestream->id,
            'message' => $message,
        ];

        $this->json('POST', route('chat.send-message'), [])
             ->assertStatus(401);

        $this->signIn(User::factory()->create());

        $this->json('POST', route('chat.send-message'), $input)
             ->assertStatus(403);

        $this->signIn($user);

        $this->json('POST', route('chat.send-message'), ['room' => 'livestream.'.$livestream->id])
             ->assertStatus(422)
             ->assertJsonValidationErrors([
                'message',
             ]);

        Event::fake();

        $this->withoutExceptionHandling();
        $this->json('POST', route('chat.send-message'), $input)
             ->assertSuccessful()
             ->assertJsonFragment([
                'message' => $message,
                'name' => $user->name,
             ]);

        Event::assertDispatched(function (ChatMessageCreated $event) use ($message) {
            return $event->chat->message === $message;
        });
    }

    /** @test **/
    public function a_chat_can_be_deleted()
    {
        $chat = Chat::factory()->create();

        $this->json('POST', route('chat.delete', ['id' => $chat->id]))
             ->assertStatus(401);

        $this->signIn(User::factory()->create());

        $this->json('POST', route('chat.delete', ['id' => $chat->id]))
             ->assertStatus(403);

        $this->signInAdmin();

        $this->json('POST', route('chat.delete', ['id' => $chat->id]))
             ->assertSuccessful()
             ->assertJsonFragment([
                'success' => 'Message Deleted',
             ]);

        $this->assertFalse(Chat::all()->contains('id', $chat->id));
    }

    /** @test **/
    public function chat_can_be_loaded_for_a_specific_room()
    {
        $chat = Chat::factory()->create();
        $old_chat = Chat::factory()->create([
            'created_at' => now()->subMinutes(6),
            'updated_at' => now()->subMinutes(6),
        ]);

        $this->assertTrue($old_chat->created_at->isPast());

        $other_chat = Chat::factory()->create([
            'room' => 'fooabar.1',
        ]);

        $this->json('POST', route('chat.load'))
            ->assertStatus(401);

        $this->signIn(User::factory()->create());

        $this->json('POST', route('chat.load'))
             ->assertStatus(422)
            ->assertJsonValidationErrors([
                'room',
            ]);

        $this->withoutExceptionHandling();
        $this->json('POST', route('chat.load'), ['room' => $chat->room])
            ->assertStatus(403);

        $this->signInAdmin();

        $this->json('POST', route('chat.load'), ['room' => $chat->room])
             ->assertSuccessful()
             ->assertJsonFragment([
                'message' => $chat->message,
             ])
             ->assertJsonMissing([
                'message' => $old_chat->message,
                'message' => $other_chat->message,
             ]);
    }

    /** @test **/
    public function a_chat_room_can_be_viewed()
    {
        $livestream = Livestream::factory()->create();
        $room = $livestream->chat_room;

        $this->assertNotNull($room);

        $this->get(route('chat.view', ['room' => $room]))
            ->assertRedirect('/login');

        $this->signIn(User::factory()->create());

        $this->get(route('chat.view', ['room' => $room]))
            ->assertRedirect('/');

        $this->signInAdmin();

        $this->get(route('chat.view', ['room' => $room]))
             ->assertSuccessful()
             ->assertViewHas('room', $room);
    }

    /** @test **/
    public function a_message_can_be_whispered()
    {
        $message = $this->faker->sentence;
        $livestream = Livestream::factory()->create();
        $inquiry = Inquiry::factory()->create();
        $inquiry->saveLivestreams(['livestream' => $livestream]);

        $user = $inquiry->user;
        $this->assertInstanceOf(User::class, $user);

        $input = [
            'room' => 'livestream.'.$livestream->id,
            'message' => $message,
            'whisper_id' => $user->id,
        ];

        $this->json('POST', route('chat.send-message'), [])
             ->assertStatus(401);

        $this->signIn(User::factory()->create());

        $this->json('POST', route('chat.send-message'), $input)
             ->assertStatus(403);

        $this->signInAdmin();

        $this->json('POST', route('chat.send-message'), ['room' => 'livestream.'.$livestream->id])
             ->assertStatus(422)
             ->assertJsonValidationErrors([
                'message',
             ]);

        Event::fake();

        $this->withoutExceptionHandling();
        $this->json('POST', route('chat.send-message'), $input)
             ->assertSuccessful()
             ->assertJsonFragment([
                'message' => $message,
             ]);

        Event::assertDispatched(function (WhisperCreated $event) use ($message, $user) {
            return $event->chat->message === $message && $event->user->id === $user->id;
        });

        $this->assertTrue($user->whispers()->get()->contains('message', $message));
    }

    /** @test **/
    public function a_whisper_is_not_loaded_into_other_peoples_chat()
    {
        $message = $this->faker->sentence;
        $whisper = $this->faker->sentence;
        $livestream = Livestream::factory()->create();
        $inquiry = Inquiry::factory()->create();
        $inquiry2 = Inquiry::factory()->create();
        $inquiry->saveLivestreams(['livestream' => $livestream]);
        $inquiry2->saveLivestreams(['livestream' => $livestream]);
        $room = 'livestream.'.$livestream->id;

        $user = $inquiry->user;
        $this->assertInstanceOf(User::class, $user);

        $input = [
            'room' => $room,
            'message' => $message,
        ];

        $whisper_input = [
            'room' => $room,
            'message' => $whisper,
            'whisper_id' => $user->id,
        ];

        $admin = User::find(1);
        $this->assertTrue($admin->hasRole('admin'));
        $this->signIn($admin);

        Event::fake();

        $this->json('POST', route('chat.send-message'), $input)
             ->assertSuccessful()
             ->assertJsonFragment([
                'message' => $message,
             ]);


        $this->json('POST', route('chat.send-message'), $whisper_input)
        ->assertSuccessful()
        ->assertJsonFragment([
            'message' => $whisper,
        ]);

        $this->assertTrue($user->whispers()->get()->contains('message', $whisper));

        $global_chat = Chat::where('message', $message)->first();
        $this->assertInstanceOf(Chat::class, $global_chat);
        $whisper_chat = Chat::where('message', $whisper)->first();
        $this->assertInstanceOf(Chat::class, $whisper_chat);

        Event::assertDispatched(function (WhisperCreated $event) use ($whisper, $user) {
            return $event->chat->message === $whisper && $event->user->id === $user->id;
        });

        $this->signIn($user);

        $this->json('POST', route('chat.load'), ['room' => $room])
             ->assertJsonFragment([
                 'id' => $whisper_chat->id,
                 'message' => $whisper_chat->message,
             ]);

        $this->json('POST', route('chat.load'), ['room' => $room])
             ->assertJsonFragment([
                 'id' => $global_chat->id,
                 'message' => $global_chat->message,
             ]);

        $this->signIn($inquiry2->user);

        $this->json('POST', route('chat.load'), ['room' => $room])
             ->assertJsonMissing([
                 'id' => $whisper_chat->id,
                 'message' => $whisper_chat->message,
             ]);

        $this->json('POST', route('chat.load'), ['room' => $room])
             ->assertJsonFragment([
                 'id' => $global_chat->id,
                 'message' => $global_chat->message,
             ]);

        $this->signIn($admin);

        $this->json('POST', route('chat.load'), ['room' => $room])
             ->assertJsonFragment([
                 'id' => $whisper_chat->id,
                 'message' => $whisper_chat->message,
             ]);

        $this->json('POST', route('chat.load'), ['room' => $room])
             ->assertJsonMissing([
                 'email' => $global_chat->user->email,
            ])
             ->assertJsonFragment([
                 'id' => $global_chat->id,
                 'message' => $global_chat->message,
             ]);
    }
}
