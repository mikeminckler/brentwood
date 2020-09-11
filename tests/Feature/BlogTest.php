<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use Illuminate\Support\Arr;

use App\Blog;
use App\User;
use App\ContentElement;

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
        return factory(Blog::class)->create();
    }
    protected function getClassname()
    {
        return 'blog';
    }

    // taxonomy for blog
    // publish date
    // we need a form component
    // load a certain category


    /** @test **/
    public function the_blog_index_can_be_loaded()
    {
        $this->get(route('blogs.index'))
             ->assertRedirect('/login');

        $this->signIn(factory(User::class)->create());

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
        $input = factory(Blog::class)->raw();

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

        $this->assertEquals(Arr::get($input, 'name'), $blog->name);
    }

    /** @test **/
    public function a_blog_can_be_updated()
    {
        $blog = factory(Blog::class)->create();
        $input = factory(Blog::class)->raw();

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
    }

    /** @test **/
    public function blogs_can_be_loaded_for_pagination()
    {
        $content_element = factory(ContentElement::class)->states('blog', 'text-block')->create();
        $blog = $content_element->blogs->first();

        $this->json('GET', route('blogs.index'))
            ->assertStatus(401);

        $this->signIn(factory(User::class)->create());

        $this->json('GET', route('blogs.index'))
            ->assertStatus(403);

        $this->signInAdmin();

        $this->json('GET', route('blogs.index'))
             ->assertSuccessful()
             ->assertJsonFragment([
                'body' => $content_element->content->body,
             ]);
    }
}
