<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Page;

class PageTest extends TestCase
{

    /** @test **/
    public function a_page_can_be_created()
    {
        $input = factory(Page::class)->states('child')->raw();   

        $this->postJson(route('pages.store'), [])
            ->assertStatus(401);

        $this->signInAdmin();

        $this->json('POST', route('pages.store'), [])
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'name',
            ]);

        $this->withoutExceptionHandling();
        $this->postJson(route('pages.store'), $input)
            ->assertSuccessful()
            ->assertJsonFragment([
                'success' => 'Page Saved',
            ]);
    }
}
