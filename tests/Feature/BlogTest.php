<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use Illuminate\Support\Arr;

use App\Models\Blog;
use App\Models\User;
use App\Models\ContentElement;
use App\Models\TextBlock;
use App\Models\Version;
use App\Models\Tag;

use Tests\Feature\SoftDeletesTestTrait;
use Tests\Feature\VersioningTestTrait;
use Tests\Feature\PagesTestTrait;

class BlogTest extends TestCase
{
    use WithFaker;
    use SoftDeletesTestTrait;
    use VersioningTestTrait;
    use PagesTestTrait;

    protected function getModel()
    {
        return Blog::factory()->create();
    }

    protected function getClassname()
    {
        return 'blog';
    }

    /** @test **/
    public function the_blog_index_can_be_loaded()
    {
        $this->get(route('blogs.index'))
             ->assertRedirect('/login');

        $this->signIn(User::factory()->create());

        $this->withoutExceptionHandling();
        $this->get(route('blogs.index'))
             ->assertRedirect('/');

        $this->signInAdmin();

        $this->get(route('blogs.index'))
             ->assertSuccessful();
    }

    /** @test **/
    public function a_blog_article_can_be_created()
    {
        $input = Blog::factory()->raw();

        $this->postJson(route('blogs.store'), [])
            ->assertStatus(401);

        $this->signInAdmin();

        $this->json('POST', route('blogs.store'), [])
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'name',
            ]);

        $this->withoutExceptionHandling();
        $this->postJson(route('blogs.store'), $input)
            ->assertSuccessful()
            ->assertJsonFragment([
                'success' => 'Blog Saved',
            ]);

        $blog = Blog::all()->last();

        $this->assertNotNull($blog->name);
        $this->assertNotNull($blog->title);
        $this->assertNotNull($blog->author);

        $this->assertEquals(Arr::get($input, 'name'), $blog->name);
        $this->assertEquals(Arr::get($input, 'title'), $blog->title);
        $this->assertEquals(Arr::get($input, 'author'), $blog->author);
    }

    /** @test **/
    public function a_blog_can_be_updated()
    {
        $blog = Blog::factory()->create();
        $input = Blog::factory()->raw();

        $this->postJson(route('blogs.update', ['id' => $blog->id]), [])
            ->assertStatus(401);

        $this->signInAdmin();

        $this->postJson(route('blogs.update', ['id' => $blog->id]), [])
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'name',
            ]);

        $this->withoutExceptionHandling();
        $this->postJson(route('blogs.update', ['id' => $blog->id]), $input)
            ->assertSuccessful()
            ->assertJsonFragment([
                'success' => 'Blog Saved',
            ]);

        $blog->refresh();

        $this->assertEquals(Arr::get($input, 'name'), $blog->name);
        $this->assertEquals(Arr::get($input, 'author'), $blog->author);
    }

    /** @test **/
    public function blogs_can_be_loaded_for_pagination()
    {
        $blog = Blog::factory()->create();
        $content_element = $this->createContentElement(TextBlock::factory(), $blog);

        $this->assertInstanceOf(Blog::class, $blog);
        $this->assertTrue($blog->contentElements()->get()->contains('id', $content_element->id));

        $this->json('GET', route('blogs.index'))
            ->assertStatus(401);

        $this->signIn(User::factory()->create());

        $this->json('GET', route('blogs.index'))
            ->assertStatus(403);

        $this->signInAdmin();
        session()->put('editing', true);

        $this->json('GET', route('blogs.index'))
             ->assertSuccessful()
             ->assertJsonFragment([
                'body' => $content_element->content->body,
             ]);
    }

    /** @test **/
    public function a_list_of_blogs_can_be_loaded()
    {
        $blog = Blog::factory()->create();
        $blog->publish();
        $blog->refresh();
        $blog_unlisted = Blog::factory()->unlisted()->create();
        $blog_unpublished = Blog::factory()->unpublished()->create();

        $this->assertNotNull($blog->published_version_id);

        $this->withoutExceptionHandling();
        $this->json('POST', route('blogs.index'), [])
            ->assertSuccessful()
            ->assertJsonFragment([
                'name' => $blog->name,
            ])
            ->assertJsonMissing([
                'name' => $blog_unlisted->name,
                'name' => $blog_unpublished->name,
            ]);
    }

    /** @test **/
    public function blog_listings_can_be_fitlered_by_tags()
    {
        $blog1 = Blog::factory()->create();
        $tag1 = Tag::factory()->create();
        $blog1->addTag($tag1);
        $blog1->publish();
        $blog1->refresh();

        $blog2 = Blog::factory()->create();
        $tag2 = Tag::factory()->create();
        $blog2->addTag($tag2);
        $blog2->publish();
        $blog2->refresh();

        $blog3 = Blog::factory()->create();
        $tag3 = Tag::factory()->create();
        $blog3->addTag($tag3);
        $blog3->publish();
        $blog3->refresh();

        $this->withoutExceptionHandling();
        $this->json('POST', route('blogs.index'), [])
            ->assertSuccessful()
            ->assertJsonFragment([
                'name' => $blog1->name,
                'name' => $blog2->name,
                'name' => $blog3->name,
            ]);

        $this->json('POST', route('blogs.index'), ['tags' => [$tag1]])
            ->assertSuccessful()
            ->assertJsonFragment([
                'name' => $blog1->name,
            ])
            ->assertJsonMissing([
                'name' => $blog2->name,
                'name' => $blog3->name,
            ]);

        $this->json('POST', route('blogs.index'), ['tags' => [$tag1, $tag2]])
            ->assertSuccessful()
            ->assertJsonFragment([
                'name' => $blog1->name,
                'name' => $blog2->name,
            ])
            ->assertJsonMissing([
                'name' => $blog3->name,
            ]);
    }

    /** @test **/
    public function a_blog_can_be_viewed()
    {
        $blog = Blog::factory()->create();
        $content_element = $this->createContentElement(TextBlock::factory(), $blog);

        $blog->publish();

        $this->withoutExceptionHandling();
        $this->get( $blog->full_slug)
            ->assertSuccessful();
        
    }
}
