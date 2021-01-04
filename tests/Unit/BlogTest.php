<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;

use Tests\Unit\AppendAttributesTestTrait;
use Tests\Unit\ContentElementsTestTrait;
use Tests\Unit\VersioningTestTrait;
use Tests\Unit\TagsTrait;

use App\Models\Blog;
use App\Models\Tag;

class BlogTest extends TestCase
{
    use WithFaker;
    use AppendAttributesTestTrait;
    use ContentElementsTestTrait;
    use VersioningTestTrait;
    use TagsTrait;

    protected function getModel()
    {
        return Blog::factory()->create();
    }

    protected function getClassname()
    {
        return 'blog';
    }

    /** @test **/
    public function a_blog_has_a_full_slug_attribute()
    {
        $blog = Blog::factory()->create([
            'name' => 'Jimmy Page',
        ]);

        $this->assertEquals('blogs/jimmy-page', $blog->full_slug);
    }

    /** @test **/
    public function a_blog_can_be_found_by_its_full_slug()
    {
        $blog = Blog::factory()->create([
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
        $blog = Blog::factory()->create([
            'name' => $name,
        ]);

        $this->assertNotNull($blog->getSlug());
        $this->assertEquals(Str::kebab($name), $blog->getSlug());
    }

    /** @test **/
    public function blogs_can_be_got()
    {
        $blog = Blog::factory()->create();
        $blog->publish();

        $blogs = Blog::getBlogs();

        $this->assertTrue($blogs->contains('id', $blog->id));
    }

    /** @test **/
    public function blogs_get_can_be_filtered_by_tags()
    {
        $blog1 = Blog::factory()->create();
        $blog2 = Blog::factory()->create();

        $tag = Tag::factory()->create();

        $blog1->addTag($tag);

        $blog1->publish();
        $blog2->publish();

        $blogs = Blog::getBlogs();
        $this->assertTrue($blogs->contains('id', $blog1->id));
        $this->assertTrue($blogs->contains('id', $blog2->id));

        $blogs = Blog::getBlogs($tag);
        $this->assertTrue($blogs->contains('id', $blog1->id));
        $this->assertFalse($blogs->contains('id', $blog2->id));

        $blogs = Blog::getBlogs(collect([$tag]));
        $this->assertTrue($blogs->contains('id', $blog1->id));
        $this->assertFalse($blogs->contains('id', $blog2->id));
    }

    /** @test **/
    public function a_blog_can_find_the_next_oldest_and_prev_oldest_blogs()
    {
        $blog = Blog::factory()->create();
        $prev_blog = Blog::factory()->create();
        $next_blog = Blog::factory()->create();

        $blog->publish();
        $prev_blog->publish();
        $next_blog->publish();

        $blog->publishedVersion->published_at = now()->addMinutes(60);
        $blog->publishedVersion->save();

        $prev_blog->publishedVersion->published_at = now()->addMinutes(30);
        $prev_blog->publishedVersion->save();

        $next_blog->publishedVersion->published_at = now()->addMinutes(90);
        $next_blog->publishedVersion->save();

        $blog->refresh();

        $this->assertTrue($next_blog->published_at > $blog->published_at);
        $this->assertTrue($next_blog->published_at > $prev_blog->published_at);
        $this->assertTrue($prev_blog->published_at < $blog->published_at);
        $this->assertTrue($prev_blog->published_at < $next_blog->published_at);

        $this->assertEquals($next_blog->id, $blog->next_blog->id);
        $this->assertEquals($prev_blog->id, $blog->previous_blog->id);

    }
}
