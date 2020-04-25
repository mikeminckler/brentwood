<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Role;
use App\User;
use Illuminate\Support\Arr;

class RoleTest extends TestCase
{

    /** @test **/
    public function roles_can_be_searched()
    {

        $role = Role::all()->random();

        $input = [
            'autocomplete' => true,
            'terms' => $role->name,
        ];

        $this->json('POST', route('roles.search'), $input)
            ->assertStatus(401);

        $this->signIn( factory(User::class)->create());

        $this->json('POST', route('roles.search'), $input)
            ->assertStatus(403);

        $this->signInAdmin();

        $this->json('POST', route('roles.search'), $input)
             ->assertSuccessful()
             ->assertJsonFragment([
                'name' => $role->name,
             ]);
    }

    /** @test **/
    public function a_role_can_be_created()
    {
        $input = factory(Role::class)->raw();

        $this->json('POST', route('roles.store'), [])
             ->assertStatus(401);

        $this->signIn(factory(User::class)->create());

        $this->json('POST', route('roles.store'), [])
             ->assertStatus(403);

        $this->signInAdmin();

        $this->json('POST', route('roles.store'), [])
             ->assertStatus(422)
             ->assertJsonValidationErrors([
                'name',
             ]);

        $this->withoutExceptionHandling();
        $this->json('POST', route('roles.store'), $input)
             ->assertSuccessful()
             ->assertJsonFragment([
                'success' => Arr::get($input, 'name').' Saved',
             ]);

        $role = Role::all()->last();

        $this->assertInstanceOf(Role::class, $role);
        $this->assertEquals(Arr::get($input, 'name'), $role->name);
    }

    /** @test **/
    public function a_role_can_be_edited()
    {
        $role = factory(Role::class)->create();
        $input = factory(Role::class)->raw();

        $this->json('POST', route('roles.update', ['id' => $role->id]), [])
             ->assertStatus(401);

        $this->signIn(factory(User::class)->create());

        $this->json('POST', route('roles.update', ['id' => $role->id]), [])
             ->assertStatus(403);

        $this->signInAdmin();

        $this->json('POST', route('roles.update', ['id' => $role->id]), [])
             ->assertStatus(422)
             ->assertJsonValidationErrors([
                'name',
             ]);

        $this->withoutExceptionHandling();
        $this->json('POST', route('roles.update', ['id' => $role->id]), $input)
             ->assertSuccessful()
             ->assertJsonFragment([
                'success' => Arr::get($input, 'name').' Saved',
             ]);

        $role->refresh();
        $this->assertEquals(Arr::get($input, 'name'), $role->name);
    }


    /** @test **/
    public function the_roles_index_can_be_loaded()
    {
        $role = Role::all()->random();

        $this->get( route('roles.index'))
             ->assertRedirect('/login');

        $this->signIn( factory(User::class)->create());

        $this->withoutExceptionHandling();
        $this->get( route('roles.index'))
             ->assertRedirect('/');

        $this->signInAdmin();

        $this->get( route('roles.index'))
            ->assertSuccessful();

    }

}
