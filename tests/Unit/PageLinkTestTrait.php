<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\WithFaker;
use App\Page;
use Illuminate\Support\Str;

trait PageLinkTestTrait
{

    abstract protected function getModel();
    abstract protected function getLinkFields();

    use WithFaker;

    /** @test **/
    public function if_a_page_is_displayed_in_the_front_end_we_convert_page_id_links_to_full_slugs()
    {

        $content = $this->getModel();
        $page1 = factory(Page::class)->create();
        $page2 = factory(Page::class)->create();
        $page3 = factory(Page::class)->create();
        $this->assertNotNull($page1->full_slug);

        foreach ($this->getLinkFields() as $link_field) {

            $body = '<p>'.$this->faker->paragraph.'</p>';
            $body .= '<p>'.$this->faker->sentence.' <a href="'.$page1->id.'" />'.$page1->name.'</p>';
            $body .= '<p>'.$this->faker->sentence.' <a href="'.$page2->id.'" />'.$page2->name.'</p>';
            $body .= '<p>'.$this->faker->sentence.' <a href="'.$page3->id.'" />'.$page3->name.'</p>';
            $body .= '<p>'.$this->faker->paragraph.'</p>';

            $content->{$link_field} = $body;
            $content->save();

            $content->refresh();

            session()->put('editing', true);

            $this->assertTrue(Str::contains($content->{$link_field}, 'href="'.$page1->id.'"'));
            $this->assertFalse(Str::contains($content->{$link_field}, 'href="/'.$page1->full_slug.'"'));
            $this->assertTrue(Str::contains($content->{$link_field}, 'href="'.$page2->id.'"'));
            $this->assertFalse(Str::contains($content->{$link_field}, 'href="/'.$page2->full_slug.'"'));
            $this->assertTrue(Str::contains($content->{$link_field}, 'href="'.$page3->id.'"'));
            $this->assertFalse(Str::contains($content->{$link_field}, 'href="/'.$page3->full_slug.'"'));

            session()->pull('editing');

            // if not editing, the links should be parsed for the frontend
            $this->assertFalse(Str::contains($content->{$link_field}, 'href="'.$page1->id.'"'));
            $this->assertTrue(Str::contains($content->{$link_field}, 'href="/'.$page1->full_slug.'"'));
            $this->assertFalse(Str::contains($content->{$link_field}, 'href="'.$page2->id.'"'));
            $this->assertTrue(Str::contains($content->{$link_field}, 'href="/'.$page2->full_slug.'"'));
            $this->assertFalse(Str::contains($content->{$link_field}, 'href="'.$page3->id.'"'));
            $this->assertTrue(Str::contains($content->{$link_field}, 'href="/'.$page3->full_slug.'"'));
        }
    }

}
