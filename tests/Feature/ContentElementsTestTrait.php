<?php

namespace Tests\Feature;

use App\Models\Page;

trait ContentElementsTestTrait
{
    abstract protected function getClassname();

    /** @test **/
    public function a_content_element_can_be_created_and_viewed_on_a_page()
    {
        $page = Page::factory()->create();
        $content_element = $this->createContentElement($this->getFactory(), $page);

        $page = $content_element->pages->first();
        $page->publish();

        $this->assertInstanceOf(Page::class, $page);

        $this->withoutExceptionHandling();
        $this->get($page->full_slug)
            ->assertSuccessful();
    }
}
