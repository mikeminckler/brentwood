<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class Page extends Model
{
    public function savePage($id = null, $input) 
    {
        $update = false;
        if ($id) {
            $page = Page::findOrFail($id);
            $update = true;
        } else {
            $page = new Page;
        }

        $page->name = Arr::get($input, 'name');

        $parent_page = Page::find(Arr::get($input, 'parent_page_id'));
        if ($parent_page instanceof Page) {
            $page->parent_page_id = $parent_page->id;
        } else {
            $page->parent_page_id = null;
        }

        $page->order = requestInput('order');
        $page->save();

        cache()->tags([cache_name($page)])->flush();
        return $page;    
    }
}
