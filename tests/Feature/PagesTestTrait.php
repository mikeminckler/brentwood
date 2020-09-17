<?php

namespace Tests\Feature;

use Illuminate\Support\Str;
use App\User;

use Illuminate\Support\Facades\Event;
use App\Events\PageSaved;
use App\Events\BlogSaved;

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

    /** @test **/
    public function when_a_page_is_saved_an_event_is_broadcast()
    {
        Event::fake();

        $page = $this->getModel();
        $input = $this->getModel()->toArray();

        $this->signInAdmin();

        //$this->withoutExceptionHandling();
        $this->postJson(route(Str::plural($this->getClassname()).'.update', ['id' => $page->id]), $input)
            //->assertSuccessful()
            ->assertJsonFragment([
                'success' => Str::title($this->getClassname()).' Saved',
                'full_slug' => $page->refresh()->full_slug,
            ]);

        $page->refresh();

        if ($this->getClassname() === 'page') {
            Event::assertDispatched(function (PageSaved $event) use ($page) {
                return $event->{$this->getClassname()}->id === $page->id;
            });
        } elseif ($this->getClassname() === 'blog') {
            Event::assertDispatched(function (BlogSaved $event) use ($page) {
                return $event->{$this->getClassname()}->id === $page->id;
            });
        }
    }
}
