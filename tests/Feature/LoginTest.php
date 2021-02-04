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

    /** @test **/
    public function an_intended_url_can_be_set() 
    {
        $url = route('livestreams.index');
        $this->assertNull(session()->get('url.intended'));
        $this->json('POST', route('intended-url'), ['url' => $url])
             ->assertSuccessful();
        $this->assertNotNull(session()->get('url.intended'));
        $this->assertEquals($url, session()->get('url.intended'));
    }
}
