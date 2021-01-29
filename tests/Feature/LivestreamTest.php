<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Tests\TestCase;
use Carbon\Carbon;

use App\Models\Livestream;
use App\Models\User;
use App\Models\Tag;

class LivestreamTest extends TestCase
{

    /** @test **/
    public function the_livestream_index_can_be_loaded()
    {
        $this->get(route('livestreams.index'))
             ->assertRedirect('/login');

        $this->signIn(User::factory()->create());

        $this->withoutExceptionHandling();
        $this->get(route('livestreams.index'))
             ->assertRedirect('/');

        $this->signInAdmin();

        $this->get(route('livestreams.index'))
             ->assertSuccessful();
    }

    /** @test **/
    public function a_livestream_can_be_created()
    {
        $input = Livestream::factory()->raw();

        $this->json('POST', route('livestreams.store'), [])
             ->assertStatus(401);

        $this->signIn(User::factory()->create());

        $this->json('POST', route('livestreams.store'), [])
             ->assertStatus(403);

        $this->signInAdmin();

        $this->json('POST', route('livestreams.store'), [])
             ->assertStatus(422)
             ->assertJsonValidationErrors([
                'name',
                'start_date',
                'video_id',
             ]);

        $this->withoutExceptionHandling();
        $this->json('POST', route('livestreams.store'), $input)
             ->assertSuccessful()
             ->assertJsonFragment([
                'success' => Arr::get($input, 'name').' Saved',
             ]);

        $livestream = Livestream::all()->last();

        $this->assertInstanceOf(Livestream::class, $livestream);
        $this->assertEquals(Arr::get($input, 'name'), $livestream->name);
        $this->assertEquals(Arr::get($input, 'video_id'), $livestream->video_id);
        $this->assertEquals(Arr::get($input, 'start_date'), $livestream->start_date);
        $this->assertInstanceOf(Carbon::class, $livestream->start_date);
        $this->assertEquals(Arr::get($input, 'length'), $livestream->length);
    }

    /** @test **/
    public function a_livestream_can_be_updated()
    {
        $livestream = Livestream::factory()->create();
        $input = Livestream::factory()->raw();

        $this->json('POST', route('livestreams.update', ['id' => $livestream->id]), [])
             ->assertStatus(401);

        $this->signIn(User::factory()->create());

        $this->json('POST', route('livestreams.update', ['id' => $livestream->id]), [])
             ->assertStatus(403);

        $this->signInAdmin();

        $this->json('POST', route('livestreams.update', ['id' => $livestream->id]), [])
             ->assertStatus(422)
             ->assertJsonValidationErrors([
                'name',
             ]);

        $this->withoutExceptionHandling();
        $this->json('POST', route('livestreams.update', ['id' => $livestream->id]), $input)
             ->assertSuccessful()
             ->assertJsonFragment([
                'success' => Arr::get($input, 'name').' Saved',
                'name' => Arr::get($input, 'name'),
             ]);

        $livestream->refresh();

        $this->assertEquals(Arr::get($input, 'name'), $livestream->name);
        $this->assertEquals(Arr::get($input, 'video_id'), $livestream->video_id);
        $this->assertEquals(Arr::get($input, 'start_date'), $livestream->start_date);
        $this->assertEquals(Arr::get($input, 'length'), $livestream->length);
    }

    /** @test **/
    public function a_livestream_can_have_tags_that_are_used_for_filtering_on_pages()
    {
        $livestream = Livestream::factory()->create();
        $tag = Tag::factory()->create();

        $input = $livestream->toArray();
        $input['tags'] = [$tag];

        $this->signInAdmin();

        $this->withoutExceptionHandling();
        $this->json('POST', route('livestreams.update', ['id' => $livestream->id]), $input)
             ->assertSuccessful()
             ->assertJsonFragment([
                'success' => Arr::get($input, 'name').' Saved',
                'name' => Arr::get($input, 'name'),
             ]);

        $livestream->refresh();
        
        $this->assertNotNull($livestream->tags);
        $this->assertEquals(1, $livestream->tags->count());
        $this->assertTrue($livestream->tags->contains('id', $tag->id));
    }

    /** @test **/
    public function livestreams_can_be_loaded_for_pagination()
    {
        $livestream = Livestream::factory()->create();

        $this->json('GET', route('livestreams.index'))
            ->assertStatus(401);

        $this->signIn(User::factory()->create());

        $this->json('GET', route('livestreams.index'))
            ->assertStatus(403);

        $this->signInAdmin();
        session()->put('editing', true);

        $this->withoutExceptionHandling();
        $this->json('GET', route('livestreams.index'))
             ->assertSuccessful()
             ->assertJsonFragment([
                'name' => $livestream->name,
             ]);
    }

    /** @test **/
    public function a_livestream_can_be_viewed()
    {
        $livestream = Livestream::factory()->create();

        $this->withoutExceptionHandling();
        $this->get(route('livestreams.view', ['id' => $livestream->id]))
             ->assertSuccessful()
             ->assertViewHas('livestream');
    }

    /** @test **/
    public function registering_for_a_livestream()
    {
        $livestream = Livestream::factory()->create();

        $this->withoutExceptionHandling();
        $this->get(route('livestreams.register', ['id' => $livestream->id]))
             ->assertSuccessful()
             ->assertViewHas('livestream', $livestream);
    }
}
