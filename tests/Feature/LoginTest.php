<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\User;

class LoginTest extends TestCase
{

    /** @test **/
    public function a_user_is_redirected_to_google_auth_when_trying_to_login()
    {
        $this->get('/login')
         ->assertRedirect();
    }
}
