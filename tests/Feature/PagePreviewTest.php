<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\ContentElement;

class PagePreviewTest extends TestCase
{
    /** @test **/
    public function draft_content_elements_are_loaded_for_a_preview()
    {
        $content_element = factory(ContentElement::class)->states('text-block')->create();
        $this->assertEquals(1, $content_element->pages()->count());
        $page = $content_element->pages->first();
        $content_element->version_id = $page->getDraftVersion()->id;
        $content_element->save();

        $this->assertTrue($page->contentElements->contains('id', $content_element->id));
        $this->assertTrue($page->content_elements->contains('id', $content_element->id));
        $this->assertEquals($page->getDraftVersion()->id, $content_element->version_id);

        $this->signInAdmin();

        $this->post(route('editing-toggle'))
             ->assertSuccessful()
             ->assertJsonFragment([
                'success' => 'Editing Enabled',
                'editing' => true,
             ]);

        $this->assertTrue(session()->has('editing'));

        $response = $this->get( $page->full_slug.'?preview=true')
                         ->assertSuccessful()
                         ->assertViewHas('content_elements');
    }
}
