<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\User;
use App\Role;
use Illuminate\Support\Arr;

class UserTest extends TestCase
{

    /** @test **/
    public function the_users_index_can_be_loaded()
    {
        $this->get( route('users.index'))
            ->assertStatus(302);

        $this->signIn( factory(User::class)->create());

        $this->withoutExceptionHandling();
        $this->get( route('users.index'))
            ->assertRedirect('/');

        $this->signInAdmin();

        $this->get( route('users.index'))
            ->assertSuccessful();
    }

    /** @test **/
    public function all_users_can_be_loaded()
    {
        $this->json('GET', route('users.load'))
            ->assertStatus(401);

        $this->signIn( factory(User::class)->create());

        $this->json('GET', route('users.load'))
            ->assertStatus(403);

        $this->signInAdmin();

        $user = factory(User::class)->create();
        $role = Role::all()->random();
        $user->addRole($role);

        $this->json('GET', route('users.load'))
             ->assertSuccessful()
             ->assertJsonFragment([
                'name' => $user->name,
                'name' => $role->name,
             ]);

    }

    /** @test **/
    public function a_user_can_be_save_with_their_roles()
    {

        $user = factory(User::class)->create();
        $role = Role::all()->random();
        $input = factory(User::class)->raw();
        $input['roles'] = [$role];

        $this->assertFalse($user->hasRole($role));

        $this->json('POST', route('users.update', ['id' => $user->id]), $input)
            ->assertStatus(401);

        $this->signIn( factory(User::class)->create());

        $this->json('POST', route('users.update', ['id' => $user->id]), $input)
            ->assertStatus(403);

        $this->signInAdmin();

        $this->json('POST', route('users.update', ['id' => $user->id]), [])
             ->assertStatus(422)
             ->assertJsonValidationErrors([
                'name',
                'email',
             ]);

        $this->withoutExceptionHandling();
        $this->json('POST', route('users.update', ['id' => $user->id]), $input)
             ->assertSuccessful()
             ->assertJsonFragment([
                'success' => Arr::get($input, 'name').' Saved',
             ]);

        $user->refresh();

        $this->assertEquals( Arr::get($input, 'name'), $user->name);
        $this->assertEquals( Arr::get($input, 'email'), $user->email);
        $this->assertTrue($user->hasRole($role));
    }

    /** @test **/
    public function a_users_role_is_removed_if_it_is_not_included_in_the_save_input()
    {
        $user = factory(User::class)->create();   
        $role = Role::all()->random();

        $user->addRole($role);
        $user->refresh();

        $this->assertTrue($user->hasRole($role));

        $input = factory(User::class)->raw();
        $input['roles'] = [];

        $this->signInAdmin();

        $this->withoutExceptionHandling();

        $this->json('POST', route('users.update', ['id' => $user->id]), $input)
             ->assertSuccessful()
             ->assertJsonFragment([
                'success' => Arr::get($input, 'name').' Saved',
             ]);

        $user->refresh();
        $this->assertFalse($user->hasRole($role));
    }

    /** @test **/
    public function users_can_be_searched()
    {
        
        $user = factory(User::class)->create();
        $input = [
            'autocomplete' => true,
            'terms' => $user->name,
        ];

        $this->json('POST', route('users.search'), $input)
            ->assertStatus(401);

        $this->signIn( factory(User::class)->create());

        $this->withoutExceptionHandling();
        $this->json('POST', route('users.search'), $input)
            ->assertStatus(403);

        $this->signInAdmin();

        $this->withoutExceptionHandling();
        $this->json('POST', route('users.search'), $input)
             ->assertSuccessful()
             ->assertJsonFragment([
                 'name' => $user->name,
                 'selected' => false,
             ]);

    }
}
