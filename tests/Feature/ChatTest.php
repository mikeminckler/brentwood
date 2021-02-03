<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\Livestream;
use App\Models\Inquiry;
use App\Models\User;

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

        $this->json('POST', route('chat'), ['room' => 'livestream.'.$livestream->id])
             ->assertStatus(401);

        $this->signIn( User::factory()->create());

        $this->json('POST', route('chat'), ['room' => 'livestream.'.$livestream->id])
             ->assertStatus(403);

        
        
    }


}
