<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\User;
use App\Models\Page;
use App\Models\Role;
use App\Models\Permission;

class PermissionTest extends TestCase
{

    /** @test **/
    public function the_permissions_index_can_be_loaded()
    {
        $this->get(route('permissions.index'))
             ->assertRedirect(route('login'));

        $this->signIn(User::factory()->create());

        $this->withoutExceptionHandling();
        $this->get(route('permissions.index'))
             ->assertRedirect('/');

        $this->signInAdmin();

        $this->get(route('permissions.index'))
            ->assertSuccessful();
    }

    /** @test **/
    public function permission_can_be_created_for_a_user()
    {
        $user = User::factory()->create();
        $page = Page::factory()->create();

        $this->json('POST', route('permissions.store'), [])
            ->assertStatus(401);

        $this->signIn(User::factory()->create());

        $this->json('POST', route('permissions.store'), [])
            ->assertStatus(403);

        $this->signInAdmin();

        $this->json('POST', route('permissions.store'), [])
             ->assertStatus(422)
             ->assertJsonValidationErrors([
                 'objectable_id',
                 'objectable_type',
                 'users',
                 'roles',
             ]);

        $input = [
            'objectable_id' => $page->id,
            'objectable_type' => $page->type,
            'users' => [
                $user->toArray(),
            ],
        ];

        $this->withoutExceptionHandling();

        $this->json('POST', route('permissions.store'), $input)
            ->assertOK()
            ->assertJsonFragment([
                'success' => 'Permission Created',
                'name' => $page->name,
            ]);

        $this->assertTrue($user->canEditPage($page));
    }

    /** @test **/
    public function permission_can_be_created_for_a_role()
    {
        $this->withoutExceptionHandling();
        $role = Role::factory()->create();
        $page = Page::factory()->create();

        $this->signInAdmin();

        $input = [
            'objectable_id' => $page->id,
            'objectable_type' => $page->type,
            'roles' => [
                $role->toArray(),
            ],
        ];

        $this->json('POST', route('permissions.store'), $input)
            ->assertOK()
            ->assertJsonFragment([
                'success' => 'Permission Created',
            ]);

        $this->assertTrue($role->canEditPage($page));
    }

    /** @test **/
    public function a_pages_permissions_can_be_loaded()
    {
        $page = Page::factory()->create();
        $role = Role::factory()->create();

        $page->createPermission($role);

        $this->assertTrue($role->canEditPage($page));

        $page->refresh();
        $permission = $page->permissions->last();

        $this->assertInstanceOf(Permission::class, $permission);
        $this->assertEquals($page->id, $permission->objectable_id);
        $this->assertInstanceOf(get_class($page), $permission->objectable);
        $this->assertEquals($role->id, $permission->accessable->id);

        $this->json('POST', route('permissions.load'), ['objectable_id' => $page->id, 'objectable_type' => $page->type])
            ->assertStatus(401);

        $this->signIn(User::factory()->create());

        $this->withoutExceptionHandling();
        $this->json('POST', route('permissions.load'), ['objectable_id' => $page->id, 'objectable_type' => $page->type])
            ->assertStatus(403);

        $this->signInAdmin();

        $this->json('POST', route('permissions.load'), ['objectable_id' => $page->id, 'objectable_type' => $page->type])
             ->assertSuccessful()
             ->assertJsonFragment([
                'id' => $permission->id,
                'accessable_id' => $role->id,
                'name' => $role->name,
             ]);
    }

    /** @test **/
    public function permission_can_be_removed()
    {
        $page = Page::factory()->create();
        $role = Role::factory()->create();

        $page->createPermission($role);

        $this->assertTrue($role->canEditPage($page));

        $page->refresh();
        $permission = $page->permissions->last();

        $this->assertInstanceOf(Permission::class, $permission);
        $this->assertEquals($page->id, $permission->objectable->id);
        $this->assertEquals($role->id, $permission->accessable->id);

        $this->json('POST', route('permissions.destroy', ['id' => $permission->id]))
            ->assertStatus(401);

        $this->signIn(User::factory()->create());

        $this->json('POST', route('permissions.destroy', ['id' => $permission->id]))
            ->assertStatus(403);

        $this->signInAdmin();

        $this->json('POST', route('permissions.destroy', ['id' => $permission->id]))
             ->assertSuccessful()
             ->assertJsonFragment(['success' => 'Permission Removed']);

        $this->assertNull(Permission::find($permission->id));
    }
}
