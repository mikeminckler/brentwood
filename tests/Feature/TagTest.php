<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Tests\TestCase;

use App\Models\Tag;
use App\Models\User;

use Tests\Feature\SearchTestTrait;

class TagTest extends TestCase
{
    use SearchTestTrait;

    protected function getClassname()
    {
        return 'tag';
    }

    /** @test **/
    public function tags_can_be_search_for_autocomplete()
    {
        $tag = Tag::factory()->create();

        $this->json('POST', route('tags.search'), [])
            ->assertStatus(401);

        $this->signIn(User::factory()->create());

        $this->json('POST', route('tags.search'), [])
            ->assertStatus(403);

        $this->signInAdmin();

        $this->json('POST', route('tags.search'), [])
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'terms',
            ]);

        $this->withoutExceptionHandling();
        $this->json('POST', route('tags.search'), ['terms' => strtolower(substr($tag->name, 0, 4))])
            ->assertSuccessful()
            ->assertJsonFragment([
                'id' => $tag->id,
                'name' => $tag->name,
            ]);
    }

    /** @test **/
    public function a_tag_can_be_created()
    {
        $input = Tag::factory()->raw();

        $this->json('POST', route('tags.store'), [])
             ->assertStatus(401);

        $this->signIn(User::factory()->create());

        $this->json('POST', route('tags.store'), [])
             ->assertStatus(403);

        $this->signInAdmin();

        $this->json('POST', route('tags.store'), [])
             ->assertStatus(422)
             ->assertJsonValidationErrors([
                'name',
             ]);

        $this->withoutExceptionHandling();
        $this->json('POST', route('tags.store'), $input)
             ->assertSuccessful()
             ->assertJsonFragment([
                'success' => Arr::get($input, 'name').' Saved',
             ]);

        $tag = Tag::all()->last();

        $this->assertInstanceOf(Tag::class, $tag);
        $this->assertEquals(Arr::get($input, 'name'), $tag->name);
    }

    /** @test **/
    public function creating_a_tag_that_has_a_parent()
    {
        $parent_tag = Tag::factory()->create();

        $this->assertNull($parent_tag->parent_tag_id);

        $input = Tag::factory()->raw([
            'parent_tag_id' => $parent_tag->id,
        ]);

        $this->signInAdmin();

        $this->json('POST', route('tags.store'), $input)
             ->assertSuccessful()
             ->assertJsonFragment([
                'success' => Arr::get($input, 'name').' Saved',
             ]);

        $tag = Tag::all()->last();
        $parent_tag->refresh();

        $this->assertInstanceOf(Tag::class, $tag);
        $this->assertEquals(Arr::get($input, 'name'), $tag->name);

        $this->assertInstanceOf(Tag::class, $tag->parentTag);
        $this->assertEquals($parent_tag->id, $tag->parentTag->id);
        $this->assertEquals(1, $parent_tag->tags->count());
        $this->assertTrue($parent_tag->tags->contains('id', $tag->id));
    }
}
