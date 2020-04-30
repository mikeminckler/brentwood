<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\User;
use App\Page;
use App\Role;
use App\PageAccess;

class PageAccessTest extends TestCase
{

    /** @test **/
    public function the_page_access_index_can_be_loaded()
    {
        $this->get( route('page-accesses.index'))
             ->assertRedirect( route('login'));

        $this->signIn( factory(User::class)->create());

        $this->withoutExceptionHandling();
        $this->get( route('page-accesses.index'))
             ->assertRedirect('/');

        $this->signInAdmin();

        $this->get( route('page-accesses.index'))
            ->assertSuccessful();

    }

    /** @test **/
    public function page_access_can_be_created_for_a_user()
    {
        $user = factory(User::class)->create();
        $page = factory(Page::class)->create();

        $this->json('POST', route('page-accesses.store'), [])
            ->assertStatus(401);

        $this->signIn(factory(User::class)->create());

        $this->json('POST', route('page-accesses.store'), [])
            ->assertStatus(403);

        $this->signInAdmin();

        $this->json('POST', route('page-accesses.store'), [])
             ->assertStatus(422)
             ->assertJsonValidationErrors([
                 'page_id',
                 'users',
                 'roles',
             ]);

        $input = [
            'page_id' => $page->id,
            'users' => [
                $user->toArray(),
            ],
        ];

        $this->withoutExceptionHandling();

        $this->json('POST', route('page-accesses.store'), $input)
            ->assertOK()
            ->assertJsonFragment([
                'success' => 'Page Access Created',
                'name' => $page->name,
            ]);

        $this->assertTrue($user->canEditPage($page));
    }

    /** @test **/
    public function page_access_can_be_created_for_a_role()
    {
        $this->withoutExceptionHandling();
        $role = factory(Role::class)->create();
        $page = factory(Page::class)->create();

        $this->signInAdmin();

        $input = [
            'page_id' => $page->id,
            'roles' => [
                $role->toArray(),
            ],
        ];

        $this->json('POST', route('page-accesses.store'), $input)
            ->assertOK()
            ->assertJsonFragment([
                'success' => 'Page Access Created',
            ]);

        $this->assertTrue($role->canEditPage($page));
    }

    /** @test **/
    public function a_pages_page_accesses_can_be_loaded()
    {
        $page = factory(Page::class)->create();
        $role = factory(Role::class)->create();

        $page->createPageAccess($role);

        $this->assertTrue($role->canEditPage($page));

        $page->refresh();
        $page_access = $page->pageAccesses->last();

        $this->assertInstanceOf(PageAccess::class, $page_access);
        $this->assertEquals($page->id, $page_access->page_id);
        $this->assertEquals($role->id, $page_access->accessable->id);

        $this->json('GET', route('page-accesses.page', ['id' => $page->id]))
            ->assertStatus(401);

        $this->signIn( factory(User::class)->create());

        $this->json('GET', route('page-accesses.page', ['id' => $page->id]))
            ->assertStatus(403);

        $this->signInAdmin();

        $this->json('GET', route('page-accesses.page', ['id' => $page->id]))
             ->assertSuccessful()
             ->assertJsonFragment([
                'id' => $page_access->id,
                'accessable_id' => $role->id,
                'name' => $role->name,
             ]);


    }

    /** @test **/
    public function page_access_can_be_removed()
    {

        $page = factory(Page::class)->create();
        $role = factory(Role::class)->create();

        $page->createPageAccess($role);

        $this->assertTrue($role->canEditPage($page));

        $page->refresh();
        $page_access = $page->pageAccesses->last();

        $this->assertInstanceOf(PageAccess::class, $page_access);
        $this->assertEquals($page->id, $page_access->page_id);
        $this->assertEquals($role->id, $page_access->accessable->id);

        $this->json('POST', route('page-accesses.destroy', ['id' => $page_access->id]))
            ->assertStatus(401);

        $this->signIn( factory(User::class)->create());

        $this->json('POST', route('page-accesses.destroy', ['id' => $page_access->id]))
            ->assertStatus(403);

        $this->signInAdmin();

        $this->json('POST', route('page-accesses.destroy', ['id' => $page_access->id]))
             ->assertSuccessful()
             ->assertJsonFragment(['success' => 'Page Access Removed']);

        $this->assertNull(PageAccess::find($page_access->id));
        
    }
}
