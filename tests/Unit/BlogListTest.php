<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Support\Arr;

use Tests\Unit\TagsTrait;

use App\Models\Tag;
use App\Models\BlogList;

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
}
