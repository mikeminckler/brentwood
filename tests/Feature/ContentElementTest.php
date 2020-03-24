<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\ContentElement;
use App\User;
use App\Page;

class ContentElementTest extends TestCase
{
    protected function getModel()
    {
        return factory(ContentElement::class)->states('text-block')->create();
    }

    /** @test **/
    public function a_content_elements_draft_can_be_removed()
    {
        $page = factory(Page::class)->states('published')->create();
        $published_content_element = factory(ContentElement::class)->states('text-block')->create([
            'page_id' => $page->id,
            'version_id' => $page->published_version_id,
        ]);

        $content_element = factory(ContentElement::class)->states('text-block')->create([
            'uuid' => $published_content_element->uuid,
            'version_id' => $page->draft_version_id,
        ]);

        $this->json('POST', route('content-elements.remove', ['id' => $content_element->id]))
            ->assertStatus(401);

        $this->signIn(factory(User::class)->create());

        $this->json('POST', route('content-elements.remove', ['id' => $content_element->id]))
            ->assertStatus(403);

        $this->assertEquals(1, ContentElement::where('id', $content_element->id)->get()->count());
        $this->signInAdmin();

        $this->withoutExceptionHandling();
        $this->json('POST', route('content-elements.remove', ['id' => $content_element->id]))
            ->assertSuccessful()
            ->assertJsonFragment([
                'success' => 'Text Block Removed',
                'id' => $published_content_element->id,
                'uuid' => $content_element->uuid,
            ]);

        $this->assertEquals(0, ContentElement::where('id', $content_element->id)->get()->count());
    }

    /** @test **/
    public function a_content_element_can_be_restored()
    {
        $content_element = factory(ContentElement::class)->states('text-block')->create();
        $content_element->delete();
        $this->assertEquals(0, ContentElement::where('id', $content_element->id)->get()->count());

        $this->json('POST', route('content-elements.restore', ['id' => $content_element->id]))
            ->assertStatus(401);

        $this->signIn(factory(User::class)->create());

        $this->withoutExceptionHandling();
        $this->json('POST', route('content-elements.restore', ['id' => $content_element->id]))
            ->assertStatus(403);

        $this->signInAdmin();

        $this->json('POST', route('content-elements.restore', ['id' => $content_element->id]))
            ->assertSuccessful()
            ->assertJsonFragment([
                'success' => 'Text Block Restored',
            ]);

        $this->assertEquals(1, ContentElement::where('id', $content_element->id)->get()->count());
    }

    /** @test **/
    public function a_content_elements_can_be_completely_removed()
    {
        $page = factory(Page::class)->states('published')->create();
        $published_content_element = factory(ContentElement::class)->states('text-block')->create([
            'page_id' => $page->id,
            'version_id' => $page->published_version_id,
        ]);

        $content_element = factory(ContentElement::class)->states('text-block')->create([
            'uuid' => $published_content_element->uuid,
            'version_id' => $page->draft_version_id,
        ]);

        $this->json('POST', route('content-elements.remove', ['id' => $content_element->id]), ['remove_all' => true])
            ->assertStatus(401);

        $this->signIn(factory(User::class)->create());

        $this->json('POST', route('content-elements.remove', ['id' => $content_element->id]), ['remove_all' => true])
            ->assertStatus(403);

        $this->assertEquals(1, ContentElement::where('id', $content_element->id)->get()->count());
        $this->signInAdmin();

        $this->withoutExceptionHandling();
        $this->json('POST', route('content-elements.remove', ['id' => $content_element->id]), ['remove_all' => true])
            ->assertSuccessful()
            ->assertJsonFragment([
                'success' => 'Text Block Removed',
            ]);

        $this->assertEquals(0, ContentElement::where('id', $content_element->id)->get()->count());
        $this->assertEquals(0, ContentElement::where('id', $published_content_element->id)->get()->count());
    }

}
