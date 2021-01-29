<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\Page;
use Illuminate\Support\Arr;

class PagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $home_page = new Page;
        $home_page->name = 'Home';
        $home_page->slug = '/';
        $home_page->parent_page_id = 0;
        $home_page->sort_order = 1;
        $home_page->footer_color = '218,241,250';
        $home_page->protected = true;
        $home_page->save();
        $home_page->publish();

        $inquiry = new Page;
        $inquiry->name = 'Inquiry';
        $inquiry->slug = 'inquiry';
        $inquiry->title = 'Create an Inquiry';
        $inquiry->parent_page_id = 1;
        $inquiry->sort_order = 1;
        $inquiry->unlisted = true;
        $inquiry->protected = true;
        $inquiry->save();
        $inquiry->publish();

        $inquiry_content = new Page;
        $inquiry_content->name = 'Inquiry Content';
        $inquiry_content->slug = 'inquiry-content';
        $inquiry_content->parent_page_id = 2;
        $inquiry_content->sort_order = 1;
        $inquiry_content->unlisted = true;
        $inquiry_content->protected = true;
        $inquiry_content->save();
        $inquiry_content->publish();

        $livestream = new Page;
        $livestream->name = 'Livestream Registration';
        $livestream->slug = 'livestream-register';
        $livestream->parent_page_id = 1;
        $livestream->sort_order = 1;
        $livestream->unlisted = true;
        $livestream->protected = true;
        $livestream->save();
        $livestream->publish();
    }
}
