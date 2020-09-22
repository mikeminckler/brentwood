<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

use App\Models\User;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function signIn($user = null)
    {
        if (!$user instanceof User) {
            $user = User::factory()->create();
        }

        $this->actingAs($user);
        $this->assertEquals($user->id, auth()->user()->id);

        return $this;
    }

    protected function signInAdmin()
    {
        $user = User::find(1);
        //$user->addRole('admin');
        return $this->signIn($user);
    }
}
