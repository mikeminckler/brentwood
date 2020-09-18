<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;

use App\Tag;
use App\Blog;
use App\Page;
use Illuminate\Support\Str;

class TagTest extends TestCase
{
    use WithFaker;

    /** @test **/
    public function a_tag_has_many_blogs()
    {
        $tag = factory(Tag::class)->create();
        $blog = factory(Blog::class)->create();

        $tag->blogs()->attach($blog);

        $tag->refresh();

        $this->assertEquals(1, $tag->blogs()->count());
        $this->assertInstanceOf(Blog::class, $tag->blogs()->first());
        $this->assertEquals($blog->id, $tag->blogs()->first()->id);
    }

    /** @test **/
    public function a_tag_has_many_pages()
    {
        $tag = factory(Tag::class)->create();
        $page = factory(Page::class)->create();

        $tag->pages()->attach($page);

        $tag->refresh();

        $this->assertEquals(1, $tag->pages()->count());
        $this->assertInstanceOf(Page::class, $tag->pages()->first());
        $this->assertEquals($page->id, $tag->pages()->first()->id);
    }

    /** @test **/
    public function a_tag_can_be_found_or_created()
    {
        $name = $this->faker->firstName.$this->faker->randomNumber(3);

        $tag = (new Tag)->findOrCreateTag($name);

        $this->assertInstanceOf(Tag::class, $tag);

        $this->assertEquals($name, $tag->name);

        $this->assertEquals($tag->id, (new Tag)->findOrCreateTag($name)->id);

        $this->assertEquals($tag->id, (new Tag)->findOrCreateTag($tag->id)->id);
    }

    /** @test **/
    public function a_tag_is_always_returned_with_title_case()
    {
        $name = $this->faker->firstName.' '.$this->faker->lastName;

        $tag = (new Tag)->findOrCreateTag(strtolower($name));

        $this->assertEquals(Str::title($name), $tag->name);

        $tag2 = (new Tag)->findOrCreateTag(strtoupper($name));

        $this->assertEquals(Str::title($name), $tag2->name);

        $this->assertEquals($tag->id, $tag->id);
    }
}
