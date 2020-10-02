<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Tests\TestCase;

use Tests\Feature\ContentElementsTestTrait;

use App\Models\BlogList;
use App\Models\User;
use App\Models\Page;
use App\Models\Tag;

class BlogListTest extends TestCase
{
    use ContentElementsTestTrait;

    protected function getClassname()
    {
        return 'blog-list';
    }

    /** @test **/
    public function a_blog_list_can_be_created()
    {
        $tag1 = Tag::factory()->create();
        $tag2 = Tag::factory()->create();

        $input = $this->createContentElement(BlogList::factory())->toArray();
        $input['id'] = 0;
        $input['content_id'] = 0;
        $input['content_type'] = null;
        $input['content.id'] = 0;
        $input['content']['tags'] = [
            $tag1,
            $tag2,
        ];

        $page = Page::factory()->create();
        $input['pivot'] = [
            'contentable_id' => $page->id,
            'contentable_type' => get_class($page),
            'sort_order' => 1,
            'unlisted' => false,
            'expandable' => false,
        ];

        $this->json('POST', route('content-elements.store'), [])
            ->assertStatus(401);

        $this->signIn(User::factory()->create());

        $this->json('POST', route('content-elements.store'), [])
             ->assertStatus(422)
             ->assertJsonValidationErrors([
                 'pivot.contentable_id',
                 'pivot.contentable_type',
             ]);

        $this->json('POST', route('content-elements.store'), ['pivot' => ['contentable_id' => $page->id, 'contentable_type' => 'page']])
             ->assertStatus(403);

        $this->signInAdmin();

        $this->json('POST', route('content-elements.store'), ['pivot' => ['contentable_id' => $page->id, 'contentable_type' => 'page']])
             ->assertStatus(422)
             ->assertJsonValidationErrors([
                 'type',
             ]);

        $this->json('POST', route('content-elements.store'), ['type' => 'blog-list', 'pivot' => ['contentable_id' => $page->id, 'contentable_type' => 'page']])
             ->assertStatus(422)
             ->assertJsonValidationErrors([
                'pivot.sort_order',
                'pivot.unlisted',
                'pivot.expandable',
             ]);


        $this->withoutExceptionHandling();
        $this->json('POST', route('content-elements.store'), $input)
             ->assertSuccessful()
             ->assertJsonFragment([
                'success' => 'Blog List Saved',
             ]);

        $blog_list = BlogList::all()->last();
        $this->assertEquals(Arr::get($input, 'content.header'), $blog_list->header);
        $this->assertTrue($blog_list->tags->contains('id', $tag1->id));
        $this->assertTrue($blog_list->tags->contains('id', $tag2->id));
    }
}
