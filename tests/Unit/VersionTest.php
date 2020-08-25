<?php

namespace Tests\Unit;

use Tests\TestCase;

use App\Version;
use App\Page;

class VersionTest extends TestCase
{

    /** @test **/
    public function a_version_can_be_published()
    {
        $version = factory(Version::class)->states('page')->create();
        $version->publish();

        $version->refresh();
        $this->assertNotNull($version->published_at);
    }

    /** @test **/
    public function a_version_belongs_to_a_page()
    {
        $version = factory(Version::class)->states('page')->create();
        $this->assertInstanceOf(Page::class, $version->versionable);
    }
}
