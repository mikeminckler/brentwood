<?php

namespace App\Utilities;

use App\Models\Page;

class PageLink
{
    public static function convertLink($value)
    {
        if (!$value) {
            return null;
        }
        if (!session()->has('editing') || request('preview')) {
            preg_match('/(^\d+)/', $value, $match);

            if (count($match)) {
                $page = Page::find($match[0]);

                if ($page instanceof Page) {
                    if ($page->full_slug !== '/') {
                        $full_slug = '/'.$page->full_slug;
                    } else {
                        $full_slug = '/';
                    }
                }

                return preg_replace('/'.$match[0].'/', $full_slug, $value);
            }
        }
        return $value;
    }

    public static function convertLinkText($value)
    {
        if (!session()->has('editing') || request('preview')) {
            $replace = [];

            preg_match_all('/\<a.*?(href="(\d+)(#c-([^"]+))?").*?\>([^\<]+)\<\/a>/', $value, $match);

            $find = collect($match[1])->map(function ($string) {
                return '/'.str_replace('/', '\/', $string).'/';
            })->all();

            foreach ($match[2] as $index => $page_id) {
                $page = Page::find($page_id);
                if ($page instanceof Page) {
                    if ($page->full_slug !== '/') {
                        $full_slug = '/'.$page->full_slug;
                    } else {
                        $full_slug = '/';
                    }

                    $new_link = '';
                    if ($match[4][$index]) {
                        $new_link .= '@click="$eventer.$emit(\'toggle-expander\', \''.$match[4][$index].'\')" ';
                    }
                    $new_link .= 'href="'.$full_slug;
                    if (request('preview')) {
                        $new_link .= '?preview=true';
                    }
                    if ($match[4][$index]) {
                        $new_link .= '#c-'.$match[4][$index];
                    }
                    $new_link.= '"';
                    $replace[] = $new_link;
                }
            }

            $processed_text = preg_replace($find, $replace, $value);
            return $processed_text;
        }
        return $value;
    }
}
