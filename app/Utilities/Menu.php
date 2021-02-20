<?php

namespace App\Utilities;

use App\Models\Page;
use App\Models\Livestream;
use App\Models\Blog;
use App\Models\Inquiry;

class Menu
{
    public static function getMenu()
    {
        return cache()->tags(['menu'])->rememberForever('menu', function () {
            $home_page = Page::where('slug', '/')->first();
            if ($home_page instanceof Page) {
                return Page::where('parent_page_id', $home_page->id)->get();
            } else {
                return null;
            }
        });
    }

    public static function getModules()
    {
        $pages = collect();

        if (!auth()->check()) {
            return $pages;
        }

        if (auth()->user()->can('manage', Livestream::class)) {
            $pages->push([
                'name' => 'Livestreams',
                'icon' => 'fab fa-youtube',
                'url' => '/livestreams',
            ]);
        }

        if (auth()->user()->can('manage', Blog::class)) {
            $pages->push([
                'name' => 'Blogs',
                'icon' => 'fas fa-blog',
                'url' => '/blogs',
            ]);
        }

        if (auth()->user()->can('manage', Inquiry::class)) {
            $pages->push([
                'name' => 'Inquiries',
                'icon' => 'fas fa-question-circle',
                'url' => '/inquiries',
            ]);
        }

        if (auth()->user()->hasRole('admin')) {
            $pages->push([
                'name' => 'User Management',
                'icon' => 'fas fa-users',
                'url' => '/users',
            ]);

            $pages->push([
                'name' => 'Page Permissions',
                'icon' => 'fas fa-user-lock',
                'url' => '/permissions',
            ]);

            $pages->push([
                'name' => 'Role Management',
                'icon' => 'fas fa-user-tag',
                'url' => '/roles',
            ]);

            $pages->push([
                'name' => 'Queue Monitor',
                'icon' => 'fas fa-cog',
                'url' => '/horizon',
            ]);
        }

        return $pages;
    }
}
