<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class NewsTest extends TestCase
{

    // need a page_id to load the news article, a simple 1 to 1 record
    // taxonomy for news
    // publish date
    // we need a frontend content element
    // we neoed a form component
    // loada certain category

    /** @test **/
    public function a_news_article_can_be_created()
    {


        $input = [

        ];

        
        $this->json('POST', route('news.store'), [])
             ->assertStatus(422)
             ->assertJsonValidationErrors([
             ]);

        $this->withoutExceptionHandling();
        $this->json('POST', route('news.store'), $input)
             ->assertSuccessful()
             ->assertJsonFragment([
                'success' => 'News Saved',
             ]);

        $news = News::all()->last();


    }
}
