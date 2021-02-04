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
}
