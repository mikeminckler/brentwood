<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

use Tests\Unit\TagsTrait;

use App\Models\Tag;
use App\Models\BlogList;
use App\Models\Blog;

class BlogListTest extends TestCase
{
    use TagsTrait;

    public function getClassname()
    {
        return 'blog-list';
    }

    /** @test **/
    public function a_blog_lists_tags_are_always_included()
    {
        $content_element = $this->createContentElement(BlogList::factory());
        $tag = Tag::factory()->create();
        $blog_list = $content_element->content;

        $this->assertInstanceOf(BlogList::class, $blog_list);

        $blog_list->addTag($tag);
        $blog_list->refresh();

        $this->assertEquals(1, $blog_list->tags->count());
        $tag = $blog_list->tags->first();

        $content_element->refresh();
        $blog_array = $content_element->content->toArray();

        $this->assertNotNull(Arr::get($blog_array, 'tags'));
        $this->assertEquals($tag->id, Arr::get($blog_array, 'tags')[0]['id']);
    }

    /** @test **/
    public function a_blog_list_has_a_blogs_attribute()
    {
        $blog = Blog::factory()->create();
        $tag = Tag::factory()->create();

        $blog->addTag($tag);

        $blog->publish();

        $content_element = $this->createContentElement(BlogList::factory());
        $blog_list = $content_element->content;

        $blog_list->addTag($tag);
        $blog_list->refresh();

        $this->assertNotNull($blog_list->blogs);
        $this->assertInstanceOf(LengthAwarePaginator::class, $blog_list->blogs);
        $this->assertEquals(1, count($blog_list->blogs->items()));
        $this->assertTrue(collect($blog_list->blogs->items())->contains('id', $blog->id));
    }
}
