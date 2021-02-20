<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\User;

class SessionTest extends TestCase
{

    /** @test **/
    public function editing_can_be_toggled_in_the_session()
    {
        $this->postJson(route('editing-toggle', ['type' => 'page']))
             ->assertStatus(401);

        $this->signIn(User::factory()->create());

        $this->withoutExceptionHandling();
        $this->post(route('editing-toggle', ['type' => 'page']))
             ->assertStatus(403);

        $this->signInAdmin();

        $this->post(route('editing-toggle', ['type' => 'page']))
             ->assertSuccessful()
             ->assertJsonFragment([
                'success' => 'Editing Enabled',
                'editing' => true,
             ]);

        $this->assertTrue(session()->has('editing'));

        $this->post(route('editing-toggle', ['type' => 'page']))
             ->assertSuccessful()
             ->assertJsonFragment([
                'success' => 'Editing Disabled',
                'editing' => false,
             ]);

        $this->assertFalse(session()->has('editing'));
    }
}
