<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\Page;
use App\Models\TextBlock;
use App\Models\User;

class ContentElementTest extends TestCase
{

    /** @test **/
    public function a_content_element_can_be_published_directly()
    {
        $page = Page::factory()->create();
        $content_element1 = $this->createContentElement(TextBlock::factory(), $page);
        $content_element2 = $this->createContentElement(TextBlock::factory(), $page);
        $content_element3 = $this->createContentElement(TextBlock::factory(), $page);
        $content_element3->publish_at = now()->subHours(1);
        $content_element3->save();

        $this->json('POST', route('content-elements.publish', ['id' => $content_element1->id]))
            ->assertStatus(401);

        $this->signIn(User::factory()->create());

        $this->json('POST', route('content-elements.publish', ['id' => $content_element1->id]))
             ->assertStatus(403);

        $this->signInAdmin();

        $this->json('POST', route('content-elements.publish', ['id' => $content_element1->id]))
             ->assertStatus(422)
             ->assertJsonValidationErrors([
                 'pivot.contentable_id',
                 'pivot.contentable_type',
             ]);

        $this->withoutExceptionHandling();
        $this->json('POST', route('content-elements.publish', ['id' => $content_element1->id]), ['pivot' => ['contentable_id' => $page->id, 'contentable_type' => 'page']])
             ->assertSuccessful()
             ->assertJsonFragment([
                 'success' => 'Text Block Published'
             ]);

        $content_element1->refresh();
        $content_element2->refresh();
        $content_element3->refresh();

        $this->assertNotNull($content_element1->getPageVersion($page)->published_at);
        $this->assertNull($content_element2->getPageVersion($page)->published_at);
        $this->assertNull($content_element3->getPageVersion($page)->published_at);

    }
}
