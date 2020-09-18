<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Tag;
use App\User;

class TagTest extends TestCase
{

    /** @test **/
    public function tags_can_be_search_for_autocomplete()
    {
        $tag = factory(Tag::class)->create();

        $this->json('POST', route('tags.search'), [])
            ->assertStatus(401);

        $this->signIn(factory(User::class)->create());

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
}
