<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\TextBlock;
use App\Models\Page;
use App\Utilities\PageResponse;

use Illuminate\View\View;

class PageResponseTest extends TestCase
{
    /** @test **/
    public function a_page_response_can_be_created()
    {
        $content_element = $this->createContentElement(TextBlock::factory(), Page::factory()->create());
        $page = $content_element->pages()->first();

        $this->assertInstanceOf(Page::class, $page);

        $page->publish();

        $response = (new PageResponse)->view($page, 'pages.view');

        $this->assertInstanceOf(View::class, $response);

        $this->withoutExceptionHandling();
        $this->get($page->full_slug)
             ->assertSuccessful()
             ->assertViewIs('pages.view')
             ->assertViewHas('page', $page)
            ->assertViewHas('content_elements');

        $this->signInAdmin();
    }
}
