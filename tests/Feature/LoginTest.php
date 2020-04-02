<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\User;

class LoginTest extends TestCase
{

    /** @test **/
    public function if_an_editor_logs_in_editing_is_set_in_the_session()
    {
        $user = factory(User::class)->create([
            'password' => bcrypt('password'),
        ]); 
        $user->addRole('editor');
        $user->refresh();

        $this->assertTrue($user->hasRole('editor'));

        $this->post( route('login'), ['email' => $user->email, 'password' => 'password'])
             ->assertRedirect();

        $this->assertTrue(auth()->check());

        $this->assertTrue(session()->get('editing'));
    }
}
