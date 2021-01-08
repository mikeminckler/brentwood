<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\Page;
use Illuminate\Support\Arr;

class HomePageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $page = new Page;
        $page->name = 'Home';
        $page->slug = '/';
        $page->parent_page_id = 0;
        $page->sort_order = 1;
        $page->save();
        $page->publish();
    }
}
