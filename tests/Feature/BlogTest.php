<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use Illuminate\Support\Arr;

use App\Blog;

use Tests\Feature\SoftDeletesTestTrait;
use Tests\Feature\VersioningTestTrait;

class BlogTest extends TestCase
{

    use WithFaker;
    use SoftDeletesTestTrait;
    use VersioningTestTrait;

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
        $blog = factory(Blog::class)->create();

        $this->visit( route('blogs.index'))
            ->assertStatus(401);

        $this->signIn( factory(User::class)->create());

        $this->visit( route('blogs.index'))
            ->assertStatus(403);

        $this->signInAdmin();

        $this->visit( route('blogs.index'))
            ->assertSucessful();
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

        $this->assertEquals( Arr::get($input, 'name'), $blog->name);
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

        $this->assertEquals( Arr::get($input, 'name'), $blog->name);
    }
}
