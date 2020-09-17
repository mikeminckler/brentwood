<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;

use Tests\Unit\AppendAttributesTestTrait;
use Tests\Unit\ContentElementsTestTrait;
use Tests\Unit\VersioningTestTrait;

use App\Blog;
use Illuminate\Support\Str;

class BlogTest extends TestCase
{
    use WithFaker;
    use AppendAttributesTestTrait;
    use ContentElementsTestTrait;
    use VersioningTestTrait;

    protected function getModel()
    {
        return factory(Blog::class)->create();
    }

    protected function getClassname()
    {
        return 'blog';
    }

    /** @test **/
    public function a_blog_has_a_full_slug_attribute()
    {
        $blog = factory(Blog::class)->create([
            'name' => 'Jimmy Page',
        ]);

        $this->assertEquals('blogs/jimmy-page', $blog->full_slug);
    }

    /** @test **/
    public function a_blog_can_be_found_by_its_full_slug()
    {
        $blog = factory(Blog::class)->create([
            'name' => $this->faker->firstName,
        ]);

        $blog_slug = $blog->full_slug;

        $found_blog = (new Blog)->findByFullSlug($blog->full_slug);

        $this->assertInstanceOf(Blog::class, $found_blog);
        $this->assertEquals($blog->id, $found_blog->id);
    }

    /** @test **/
    public function a_blog_has_a_slug_attribute()
    {
        $name = $this->faker->firstName;
        $blog = factory(Blog::class)->create([
            'name' => $name,
        ]);

        $this->assertNotNull($blog->getSlug());
        $this->assertEquals(Str::kebab($name), $blog->getSlug());
    }
}
