<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Page;
use App\Menu;

class MenuTest extends TestCase
{

    /** @test **/
    public function the_menu_is_loaded_on_every_page()
    {
        $page = factory(Page::class)->create([
            'parent_page_id' => 1, // the home page id
        ]);

        $menu = Menu::getMenu();
        $this->assertTrue($menu->contains('id', $page->id));

        $this->withoutExceptionHandling();
        $this->get('/')
             ->assertSuccessful()
             ->assertViewHas('menu');
    }
}
