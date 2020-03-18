<?php

use Illuminate\Database\Seeder;

use App\Page;
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
        $pages = [
            ['name' => 'Home', 'slug' => '/', 'parent_name' => ''],
        ];

        $count = 0;
        foreach ($pages as $page_data) {
            $parent = Page::where('name', Arr::get($page_data, 'parent_name'))->first();

            $page = new Page;
            $page->name = Arr::get($page_data, 'name');
            $page->slug = Arr::get($page_data, 'slug');
            $page->parent_page_id = $parent ? $parent->id : 0;
            $page->sort_order = $count;
            $page->save();
            $count++;
        }
    }
}
