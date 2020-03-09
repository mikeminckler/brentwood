<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\User;

class SessionTest extends TestCase
{

    /** @test **/
    public function editing_can_be_toggled_in_the_session()
    {
        $this->postJson(route('editing-toggle'))
             ->assertStatus(401);

        $this->signIn( factory(User::class)->create());

        $this->withoutExceptionHandling();
        $this->post(route('editing-toggle'))
             ->assertStatus(403);

        $this->signInAdmin();

        $this->post(route('editing-toggle'))
             ->assertSuccessful()
             ->assertJsonFragment([
                'success' => 'Editing Enabled',
             ]);

        $this->assertTrue(session()->has('editing'));

    }
}
