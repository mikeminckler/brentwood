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
}
