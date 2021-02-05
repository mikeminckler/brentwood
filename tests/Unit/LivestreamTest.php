<?php

namespace Tests\Unit;

use Tests\TestCase;

use App\Models\Inquiry;
use App\Models\Livestream;
use App\Models\User;
use App\Models\Role;

class LivestreamTest extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_example()
    {
        $this->assertTrue(true);
    }

    /** @test **/
    public function a_livestream_can_belong_to_many_inquiries()
    {
        $inquiry = Inquiry::factory()->create();
        $livestream = Livestream::factory()->create();
        $inquiry->saveLivestreams(['livestream' => $livestream]);

        $livestream->refresh();
        $this->assertEquals(1, $livestream->inquiries()->count());
        $this->assertEquals($inquiry->id, $livestream->inquiries()->first()->id);
    }

    /** @test **/
    public function a_livestream_has_a_date_attribute()
    {
        $livestream = Livestream::factory()->create();
        $this->assertNotNull($livestream->date);
        $this->assertEquals($livestream->start_date->timezone('America/Vancouver')->format('l F jS g:ia'), $livestream->date);
    }

    /** @test **/
    public function a_livestream_has_many_users_through_inquires()
    {
        $inquiry = Inquiry::factory()->create();
        $user = $inquiry->user;
        $this->assertInstanceOf(User::class, $user);
        $livestream = Livestream::factory()->create();
        $inquiry->saveLivestreams(['livestream' => $livestream]);

        $this->assertNotNull($livestream->inquiry_users);
        $this->assertTrue($livestream->inquiry_users->contains('id', $user->id));
    }

    /** @test **/
    public function a_livestream_can_have_many_roles()
    {
        $livestream = Livestream::factory()->create();
        $role = Role::factory()->create();

        $livestream->createPermission($role);

        $livestream->refresh();

        $this->assertNotNull($livestream->roles);
        $this->assertEquals(1, $livestream->roles->count());
        $this->assertTrue($livestream->roles->contains('id', $role->id));
    }

    /** @test **/
    public function a_livestream_can_have_many_users()
    {
        $livestream = Livestream::factory()->create();
        $user = User::factory()->create();

        $livestream->createPermission($user);

        $livestream->refresh();

        $this->assertNotNull($livestream->users);
        $this->assertEquals(1, $livestream->users->count());
        $this->assertTrue($livestream->users->contains('id', $user->id));
    }
}
