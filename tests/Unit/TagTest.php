<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;

use App\Models\Tag;
use App\Models\Blog;
use App\Models\Page;

class TagTest extends TestCase
{
    use WithFaker;

    /** @test **/
    public function a_tag_has_many_blogs()
    {
        $tag = Tag::factory()->create();
        $blog = Blog::factory()->create();

        $tag->blogs()->attach($blog);

        $tag->refresh();

        $this->assertEquals(1, $tag->blogs()->count());
        $this->assertInstanceOf(Blog::class, $tag->blogs()->first());
        $this->assertEquals($blog->id, $tag->blogs()->first()->id);
    }

    /** @test **/
    public function a_tag_has_many_pages()
    {
        $tag = Tag::factory()->create();
        $page = Page::factory()->create();

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
    public function tag_can_be_found_or_created_from_an_array()
    {
        $tag_data = Tag::factory()->create()->toArray();

        $tag = (new Tag)->findOrCreateTag($tag_data);

        $this->assertInstanceOf(Tag::class, $tag);
        $this->assertEquals(Arr::get($tag_data, 'name'), $tag->name);
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

    /** @test **/
    public function a_tag_has_a_heiarchy()
    {
        $parent_tag = Tag::factory()->create([
            'parent_tag_id' => null,
        ]);

        $tag = Tag::factory()->create([
            'parent_tag_id' => $parent_tag->id,
        ]);

        $parent_tag->refresh();

        $this->assertInstanceOf(Tag::class, $tag->parentTag);
        $this->assertEquals(1, $parent_tag->tags->count());
        $this->assertTrue($parent_tag->tags->contains('id', $tag->id));
    }

    /** @test **/
    public function a_tag_has_a_label_attribute()
    {
        $parent_tag = Tag::factory()->create();
        $tag = Tag::factory()->create([
            'parent_tag_id' => $parent_tag->id,
        ]);

        $this->assertNotNull($tag->label);
        $this->assertTrue(Str::contains($tag->label, $parent_tag->name));

        $data = Tag::find($tag->id)->toArray();

        $this->assertNotNull(Arr::get($data, 'search_label'));

        $this->assertTrue(Str::contains(Arr::get($data, 'search_label'), $parent_tag->name));
    }
}
