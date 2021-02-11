<?php

namespace Tests\Unit;

use Tests\TestCase;

use App\Models\Chat;
use App\Models\User;

class ChatTest extends TestCase
{
    /** @test **/
    public function a_chat_belongs_to_a_user()
    {
        $chat = Chat::factory()->create();
        $this->assertNotNull($chat->user);
        $this->assertInstanceOf(User::class, $chat->user);
    }

    /** @test **/
    public function a_chat_message_has_a_deleted_attribute()
    {
        $chat = Chat::factory()->create();
        $chat->delete();
        $chat->refresh();

        $this->assertNotNull($chat->deleted);
        $this->assertTrue($chat->deleted);
    }

    /** @test **/
    public function a_chat_can_have_many_whispers()
    {
        $user = User::factory()->create();
        $chat = Chat::factory()->create();
        $chat->whispers()->attach($user);

        $this->assertEquals(1, $chat->whispers->count());
        $this->assertTrue($chat->whispers->contains('id', $user->id));
    }

    /** @test **/
    public function a_chat_has_whisper_ids_attribute()
    {
        $user = User::factory()->create();
        $chat = Chat::factory()->create();
        $chat->whispers()->attach($user);

        $this->assertNotNull($chat->whisper_ids);
        $this->assertTrue($chat->whisper_ids->contains($user->id));
    }
}
