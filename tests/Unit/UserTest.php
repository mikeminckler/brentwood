<?php

namespace Tests\Unit;

use Tests\TestCase;

use App\User;
use App\Role;

class UserTest extends TestCase
{
    /** @test **/
    public function a_user_can_have_many_roles()
    {
        $user = factory(User::class)->create();
        $role = factory(Role::class)->create();

        $user->roles()->attach($role);

        $user->refresh();

        $this->assertNotNull($user->roles);
        $this->assertTrue($user->roles->contains('id', $role->id));
    }


    /** @test **/
    public function a_user_can_add_a_role()
    {
        $user = factory(User::class)->create();
        $role = factory(Role::class)->create();

        $user->addRole($role);

        $user->refresh();

        $this->assertTrue($user->roles->contains('id', $role->id));
    }

    /** @test **/
    public function a_user_can_remove_a_role()
    {
        $user = factory(User::class)->create();
        $role = factory(Role::class)->create();

        $user->addRole($role);

        $user->refresh();
        $this->assertTrue($user->roles->contains('id', $role->id));

        $user->removeRole($role);

        $user->refresh();
        $this->assertFalse($user->roles->contains('id', $role->id));
    }

    /** @test **/
    public function a_user_can_add_role_by_name()
    {
        $user = factory(User::class)->create();
        $role = factory(Role::class)->create();

        $user->addRole($role->name);

        $user->refresh();

        $this->assertTrue($user->roles->contains('id', $role->id));
    }

    /** @test **/
    public function a_user_can_check_if_they_have_a_role()
    {
        $user = factory(User::class)->create();
        $role = factory(Role::class)->create();

        $this->assertFalse($user->hasRole($role));
        // check by name
        $this->assertFalse($user->hasRole($role->name));

        $user->addRole($role);
        $user->refresh();

        $this->assertTrue($user->hasRole($role));
        // check by name
        $this->assertTrue($user->hasRole($role->name));

    }


    /** @test **/
    public function an_admin_user_has_all_roles()
    {
        $role = factory(Role::class)->create();
        $admin_role = Role::where('name', 'admin')->first();
        $this->assertInstanceOf(Role::class, $admin_role);

        $admin_user = User::whereHas('roles', function($query) {
            $query->where('name', 'admin');
        })->first();

        $this->assertInstanceOf(User::class, $admin_user);

        $this->assertTrue($admin_user->hasRole('admin'));
        $this->assertTrue($admin_user->hasRole($role));
    }


}
