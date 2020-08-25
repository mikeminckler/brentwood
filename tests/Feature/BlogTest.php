<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use Illuminate\Support\Arr;

use App\Blog;

class BlogTest extends TestCase
{

    // need a page_id to load the blog article, a simple 1 to 1 record
    // taxonomy for blog
    // publish date
    // we need a frontend content element
    // we need a form component
    // load a certain category

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
}
