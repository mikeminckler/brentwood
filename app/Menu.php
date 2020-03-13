<?php

namespace App;

use App\Page;

class Menu
{
    public static function getMenu() 
    {
        $home_page = Page::where('slug', '/')->first();
        return Page::where('parent_page_id', $home_page->id)->get();
    }
}
