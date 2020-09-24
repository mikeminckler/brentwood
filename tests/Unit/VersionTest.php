<?php

namespace Tests\Unit;

use Tests\TestCase;

use App\Models\Version;
use App\Models\Page;
use App\Models\Blog;

class VersionTest extends TestCase
{

    /** @test **/
    public function a_version_can_be_published()
    {
        $version = Version::factory()->for(Page::factory(), 'versionable')->create();
        $version->publish();

        $version->refresh();
        $this->assertNotNull($version->published_at);
    }

    /** @test **/
    public function a_version_belongs_to_a_page()
    {
        $version = Version::factory()->for(Page::factory(), 'versionable')->create();
        $this->assertInstanceOf(Page::class, $version->versionable);
    }

    /** @test **/
    public function a_version_belongs_to_a_blog()
    {
        $version = Version::factory()->for(Blog::factory(), 'versionable')->create();
        $this->assertInstanceOf(Blog::class, $version->versionable);
    }
}
