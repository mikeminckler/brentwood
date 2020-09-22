<?php

namespace App\Utilities;

use App\Models\Page;

class Menu
{
    public static function getMenu()
    {
        $home_page = Page::where('slug', '/')->first();
        if ($home_page instanceof Page) {
            return Page::where('parent_page_id', $home_page->id)->get();
        } else {
            return null;
        }
    }
}
