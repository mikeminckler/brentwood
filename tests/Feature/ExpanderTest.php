<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ExpanderTest extends TestCase
{

    /** @test **/
    public function a_expander_content_element_can_be_created()
    {
        $input = factory(ContentElement::class)->states('expander')->raw();
        $input['type'] = 'expander';
        $input['content'] = factory(Expander::class)->raw();

        $this->json('POST', route('content-elements.store'), [])
            ->assertStatus(401);

        $this->signIn( factory(User::class)->create());

        $this->json('POST', route('content-elements.store'), [])
             ->assertStatus(403);

        $this->signInAdmin();

        $this->json('POST', route('content-elements.store'), [])
             ->assertStatus(422)
             ->assertJsonValidationErrors([
                 'type',
             ]);

        $this->json('POST', route('content-elements.store'), ['type' => 'expander'])
             ->assertStatus(422)
             ->assertJsonValidationErrors([
                'page_id',
             ]);

        $this->withoutExceptionHandling();
        $this->json('POST', route('content-elements.store'), $input)
             ->assertSuccessful()
             ->assertJsonFragment([
                'success' => 'Expander Saved',
             ]);

        $expander = Expander::all()->last();
        $this->assertEquals(Arr::get($input, 'content.name'), $expander->name);
        $this->assertEquals(Arr::get($input, 'content.body'), $expander->body);
    }

}
