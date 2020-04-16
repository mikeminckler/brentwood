<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;

use App\User;
use App\Role;
use Illuminate\Support\Arr;
use Laravel\Socialite;

class UserTest extends TestCase
{
    use WithFaker;

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


    /** @test **/
    public function a_user_can_be_created_from_google()
    {
        $id = '113984752438530878390';
        $google_data = new Socialite\Two\User;
        $google_data->token = $this->faker->sha256;
        $google_data->expiresIn = 3599;
        $google_data->id = $id;
        $google_data->nickname = null;
        $google_data->name = 'Mike Minckler';
        $google_data->email = 'mike.minckler@brentwood.ca';
        $google_data->avatar = 'https://lh3.googleusercontent.com/a-/AOh14GhW2zbSQ4LIdT_pDThpx4MzSX9BnE4YlR-Ewib0EEc';
        $google_data->user =  [
                'sub' => $id,
                'name' => 'Mike Minckler',
                'given_name' => 'Mike',
                'family_name' => 'Minckler',
                'profile' => 'https://plus.google.com/113984752438530878390',
                'picture' => 'https://lh3.googleusercontent.com/a-/AOh14GhW2zbSQ4LIdT_pDThpx4MzSX9BnE4YlR-Ewib0EEc',
                'email' => 'mike.minckler@brentwood.ca',
                'email_verified' => true,
                'locale' => 'en',
                'hd' => 'brentwood.ca',
                'id' => $id,
                'verified_email' => true,
                'link' => 'https://plus.google.com/113984752438530878390',
            ];
        $google_data->avatar_original = 'https://lh3.googleusercontent.com/a-/AOh14GhW2zbSQ4LIdT_pDThpx4MzSX9BnE4YlR-Ewib0EEc';

        $user = User::createOrUpdateFromGoogle($google_data);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($google_data->getId(), $user->oauth_id);
        $this->assertEquals($google_data->getEmail(), $user->email);
        $this->assertEquals($google_data->getName(), $user->name);
        $this->assertEquals($google_data->getAvatar(), $user->avatar);

    }
}
