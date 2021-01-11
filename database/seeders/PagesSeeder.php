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
        $home_page->save();
        $home_page->publish();

        $inquiry = new Page;
        $inquiry->name = 'Inquiry';
        $inquiry->parent_page_id = 1;
        $inquiry->sort_order = 1;
        $inquiry->unlisted = true;
        $inquiry->save();
        $inquiry->publish();
    }
}
