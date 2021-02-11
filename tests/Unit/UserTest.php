<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;

use App\Models\User;
use App\Models\Role;
use App\Models\Inquiry;
use App\Models\Chat;

use Laravel\Socialite;

class UserTest extends TestCase
{
    use WithFaker;

    /** @test **/
    public function a_user_can_have_many_roles()
    {
        $user = User::factory()->create();
        $role = Role::factory()->create();

        $user->roles()->attach($role);

        $user->refresh();

        $this->assertNotNull($user->roles);
        $this->assertTrue($user->roles->contains('id', $role->id));
    }


    /** @test **/
    public function a_user_can_add_a_role()
    {
        $user = User::factory()->create();
        $role = Role::factory()->create();

        $user->addRole($role);

        $user->refresh();

        $this->assertTrue($user->roles->contains('id', $role->id));
    }

    /** @test **/
    public function a_user_can_remove_a_role()
    {
        $user = User::factory()->create();
        $role = Role::factory()->create();

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
        $user = User::factory()->create();
        $role = Role::factory()->create();

        $user->addRole($role->name);

        $user->refresh();

        $this->assertTrue($user->roles->contains('id', $role->id));
    }

    /** @test **/
    public function a_user_can_check_if_they_have_a_role()
    {
        $user = User::factory()->create();
        $role = Role::factory()->create();

        $this->assertFalse($user->hasRole($role));
        // check by name
        $this->assertFalse($user->hasRole($role->name));
        // check by id
        $this->assertFalse($user->hasRole($role->id));

        $user->addRole($role);
        $user->refresh();

        $this->assertTrue($user->hasRole($role));
        // check by name
        $this->assertTrue($user->hasRole($role->name));
        // check by id
        $this->assertTrue($user->hasRole($role->id));
    }

    /** @test **/
    public function a_role_can_be_found_by_name_with_periods_in_the_title()
    {
        $role = Role::factory()->create([
            'name' => 'foo.bar',
        ]);

        $user = User::factory()->create();
        $user->addRole($role);
        $user->refresh();

        $this->assertTrue($user->hasRole($role->name));
    }


    /** @test **/
    public function an_admin_user_has_all_roles()
    {
        $role = Role::factory()->create();
        $admin_role = Role::where('name', 'admin')->first();
        $this->assertInstanceOf(Role::class, $admin_role);

        $admin_user = User::whereHas('roles', function ($query) {
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

    /** @test **/
    public function a_user_can_be_found_or_created_from_input()
    {
        $name = $this->faker->name;
        $email = $this->faker->safeEmail;

        $input = [
            'name' => $name,
            'email' => $email,
        ];

        $user = User::findOrCreate($input);

        $this->assertInstanceOf(User::class, $user);

        $this->assertEquals($name, $user->name);
        $this->assertEquals($email, $user->email);

        $user2 = User::findOrCreate($input);

        $this->assertEquals($user->id, $user2->id);
    }

    /** @test **/
    public function a_user_has_many_inquiries()
    {
        $inquiry = Inquiry::factory()->create();
        $user = $inquiry->user;
        $this->assertInstanceOf(User::class, $user);

        $this->assertNotNull($user->inquiries);
        $this->assertEquals(1, $user->inquiries->count());
        $this->assertTrue($user->inquiries->contains('id', $inquiry->id));
    }

    /** @test **/
    public function a_user_can_have_many_whispers()
    {
        $user = User::factory()->create();
        $chat = Chat::factory()->create();
        $user->whispers()->attach($chat);

        $this->assertEquals(1, $user->whispers->count());
        $this->assertTrue($user->whispers->contains('message', $chat->message));
    }
}
