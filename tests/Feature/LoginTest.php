<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\User;

class LoginTest extends TestCase
{

    /** @test **/
    public function the_login_view_can_be_loaded()
    {
        $this->get(route('login'))
            ->assertSuccessful();
    }

    /** @test **/
    public function a_user_can_login()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password'),
        ]);

        $this->json('POST', route('login'), ['email' => $user->email, 'password' => 'password'])
             ->assertJsonFragment([
                'success' => 'Login Complete',
                'redirect' => '/',
             ])
            ->assertSessionHas('timeout');
    }

    /** @test **/
    public function a_user_is_redirected_to_google_auth_when_trying_to_login_to_google()
    {
        $this->get('/login/google')
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

    /** @test **/
    public function a_login_check_can_be_made()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password'),
        ]);

        $this->json('GET', route('session-timeout'))
             ->assertStatus(401);

        $this->json('POST', route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ])
        ->assertSuccessful()
        ->assertSessionHas('timeout');

        $this->withoutExceptionHandling();
        $this->json('GET', route('session-timeout'))
             ->assertSuccessful()
             ->assertSessionHas('timeout');

        session()->put('timeout', now()->subSeconds(1));

        $this->json('GET', route('session-timeout'))
            ->assertStatus(419);
    }

    /** @test **/
    public function an_update_can_be_made_for_the_timeout_check()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password'),
        ]);

        $this->json('POST', route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ])
        ->assertSuccessful()
        ->assertSessionHas('timeout');

        $timeout = session()->get('timeout');

        sleep(2);

        $this->withoutExceptionHandling();
        $this->post(route('session-timeout'))
             ->assertSuccessful()
             ->assertJsonFragment([
                'success' => 'Session Updated',
             ]);

        $this->assertGreaterThan($timeout, session()->get('timeout'));
    }
}
