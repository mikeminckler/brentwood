<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use Illuminate\Support\Arr;

use App\Models\Role;
use App\Models\User;

class RoleTest extends TestCase
{

    /** @test **/
    public function roles_can_be_searched()
    {
        $role = Role::where('id', '!=', 1)->get()->random();

        $input = [
            'autocomplete' => true,
            'terms' => $role->name,
        ];

        $this->json('POST', route('roles.search'), $input)
            ->assertStatus(401);

        $this->signIn(User::factory()->create());

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
        $input = Role::factory()->raw();

        $this->json('POST', route('roles.store'), [])
             ->assertStatus(401);

        $this->signIn(User::factory()->create());

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
        $role = Role::factory()->create();
        $user = User::factory()->create();
        $input = Role::factory()->raw();
        $input['users'] = [
            $user->toArray(),
        ];

        $this->json('POST', route('roles.update', ['id' => $role->id]), [])
             ->assertStatus(401);

        $this->signIn(User::factory()->create());

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
                'name' => Arr::get($input, 'name'),
             ]);

        $role->refresh();
        $this->assertEquals(Arr::get($input, 'name'), $role->name);
        $this->assertNotNull($role->users);
        $this->assertTrue($role->users->contains('name', $user->name));
    }


    /** @test **/
    public function the_roles_index_can_be_loaded()
    {
        $role = Role::all()->random();
        $user = User::factory()->create();
        $user->addRole($role);

        $this->get(route('roles.index'))
             ->assertRedirect('/login');

        $this->signIn(User::factory()->create());

        $this->withoutExceptionHandling();
        $this->get(route('roles.index'))
             ->assertRedirect('/');

        $this->signInAdmin();

        $this->get(route('roles.index'))
             ->assertSuccessful();
    }

    /*
    public function a_user_can_be_removed_from_a_role()
    {
        $user = factory(User::class)->create();
        $role = factory(Role::class)->create();

        $user->addRole($role);

        $user->refresh();
        $role->refresh();

        $this->assertTrue($role->users->contains('id', $user->id));

        $this->signInAdmin();

        $input = [
            'user_id' => $user->id,
        ];

        $this->withoutExceptionHandling();

        $this->json('POST', route('roles.remove-user', ['id' => $role->id]), $input)
             ->assertSuccessful()
             ->assertJsonFragment([
                'success' => $user->name.' Removed From '.$role->name,
             ]);

        $role->refresh();

        $this->assertFalse($role->users->contains('id', $user->id));
    }
     */


    /** @test **/
    public function a_roles_user_is_removed_if_it_is_not_included_in_the_save_input()
    {
        $user = User::factory()->create();
        $role = Role::all()->random();

        $user->addRole($role);
        $user->refresh();

        $this->assertTrue($user->hasRole($role));

        $input = Role::factory()->raw();
        $input['users'] = [];

        $this->signInAdmin();

        $this->withoutExceptionHandling();

        $this->json('POST', route('roles.update', ['id' => $role->id]), $input)
             ->assertSuccessful()
             ->assertJsonFragment([
                'success' => Arr::get($input, 'name').' Saved',
             ]);

        $role->refresh();
        $this->assertFalse($role->users->contains('id', $user->id));
    }
}
