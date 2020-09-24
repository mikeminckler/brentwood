<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\Tag;
use App\Models\User;

class TagTest extends TestCase
{

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
}
