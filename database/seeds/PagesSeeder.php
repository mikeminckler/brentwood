<?php

use Illuminate\Database\Seeder;

use App\Page;

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
            'Home',
        ];

        foreach ($pages as $name) {
            $page = new Page;
            $page->name = $name;
            $page->save();
        }
    }
}
