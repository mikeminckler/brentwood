<?php

namespace Tests\Unit;

use Tests\TestCase;

use App\Models\Page;
use App\Models\Permission;

class PermissionTest extends TestCase
{

    /** @test **/
    public function an_objectable_can_be_found()
    {
        $page = Page::factory()->create();   

        $objectable = Permission::findObjectable([
            'objectable_id' => $page->id,
            'objectable_type' => $page->type,
        ]);

        $this->assertInstanceOf(Page::class, $objectable);
        $this->assertEquals($page->id, $objectable->id);
    }
}
