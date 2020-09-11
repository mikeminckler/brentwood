<?php

namespace Tests\Feature;

use Illuminate\Support\Str;
use App\User;

trait PagesTestTrait
{
    abstract protected function getModel();
    abstract protected function getClassname();

    /** @test **/
    public function a_page_can_be_set_to_unlisted_and_not_unlisted()
    {
        $page = $this->getModel();

        $this->assertFalse($page->unlisted);

        $this->json('POST', route(Str::plural($this->getClassname()).'.unlist', ['id' => $page->id]))
            ->assertStatus(401);

        $this->signIn(factory(User::class)->create());

        $this->withoutExceptionHandling();
        $this->json('POST', route(Str::plural($this->getClassname()).'.unlist', ['id' => $page->id]))
            ->assertStatus(403);

        $this->signInAdmin();

        $this->json('POST', route(Str::plural($this->getClassname()).'.unlist', ['id' => $page->id]))
             ->assertSuccessful()
             ->assertJsonFragment([
                'success' => Str::title($this->getClassname()).' Hidden',
             ]);

        $page->refresh();

        $this->assertTrue($page->unlisted);

        $this->json('POST', route(Str::plural($this->getClassname()).'.reveal', ['id' => $page->id]))
             ->assertSuccessful()
             ->assertJsonFragment([
                'success' => Str::title($this->getClassname()).' Revealed',
             ]);

        $page->refresh();

        $this->assertFalse($page->unlisted);
    }
}
