<?php

namespace Tests\Unit;

use Tests\TestCase;

use App\Models\Page;
use App\Models\User;
use App\Utilities\Menu;
use Illuminate\Support\Collection;

class MenuTest extends TestCase
{

    /** @test **/
    public function the_page_tree_menu_can_be_loaded()
    {
        $home_page = Page::where('slug', '/')->first();
        $menu = Menu::getMenu();
        $this->assertTrue($menu->contains('id', $home_page->pages->first()->id));
    }

    /** @test **/
    public function module_pages_can_be_loaded()
    {
        $modules = Menu::getModules();

        $this->assertInstanceOf(Collection::class, $modules);

        $this->assertEquals(0, $modules->count());

        $user = User::factory()->create();
        $this->signIn($user);

        $modules = Menu::getModules();
        $this->assertEquals(0, $modules->count());

        $user->addRole('livestreams-manager');
        $user->refresh();
        $this->assertTrue($user->hasRole('livestreams-manager'));
        $this->signIn($user);
        $modules = Menu::getModules();
        $this->assertEquals(1, $modules->count());
        $this->assertTrue($modules->contains('name', 'Livestreams'));

        $user->addRole('blogs-manager');
        $user->refresh();
        $this->signIn($user);
        $modules = Menu::getModules();
        $this->assertTrue($modules->contains('name', 'Blogs'));

        $user->addRole('inquiries-manager');
        $user->refresh();
        $this->signIn($user);
        $modules = Menu::getModules();
        $this->assertTrue($modules->contains('name', 'Inquiries'));

        $this->signInAdmin();
        $modules = Menu::getModules();
        $this->assertTrue($modules->contains('name', 'Livestreams'));
        $this->assertTrue($modules->contains('name', 'Blogs'));
        $this->assertTrue($modules->contains('name', 'Inquiries'));
        $this->assertTrue($modules->contains('name', 'User Management'));
        $this->assertTrue($modules->contains('name', 'Page Permissions'));
        $this->assertTrue($modules->contains('name', 'Role Management'));
        $this->assertTrue($modules->contains('name', 'Queue Monitor'));
    }
}
