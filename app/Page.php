<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

use App\ContentElement;

class Page extends Model
{
    protected $with = ['pages'];
    protected $appends = ['full_slug'];

    public function savePage($id = null, $input) 
    {
        $update = false;
        $home_page = false;
        if ($id) {
            $page = Page::findOrFail($id);
            $update = true;
            if ($page->slug === '/') {
                $home_page = true;
            }
        } else {
            $page = new Page;
        }

        $page->name = Arr::get($input, 'name');
        if (!$home_page) {
            $page->slug = Arr::get($input, 'slug');
        }

        if (!$home_page) {
            $parent_page = Page::find(Arr::get($input, 'parent_page_id'));
            if ($parent_page instanceof Page) {
                $page->parent_page_id = $parent_page->id;
            } else {
                $page->parent_page_id = null;
            }
        }

        $page->order = requestInput('order');
        $page->save();

        cache()->tags([cache_name($page)])->flush();
        return $page;    
    }

    public function parentPage()
    {
        if ($this->parent_page_id > 0) {
            return $this->belongsTo(Page::class, 'parent_page_id');
        } else {
            return null;
        }
    }

    public function pages() 
    {
        return $this->hasMany(Page::class, 'parent_page_id');   
    }

    public function getFullSlugAttribute() 
    {
        if (!$this->slug) {
            $slug = Str::kebab($this->name);   
        } else {
            $slug = $this->slug;
        }

        if ($this->parent_page_id > 0) {
            $parent_page = Page::find($this->parent_page_id);
            
            while ($parent_page->id != 1) {
                $slug = Str::kebab($parent_page->name).'/'.$slug;
                $parent_page = Page::find($parent_page->parent_page_id);
            }
        }

        return $slug;
    }

    public static function findByFullSlug($slug) 
    {
        return Page::all()->filter(function($page) use($slug) {
            return $page->full_slug === $slug;
        })->last();
    }

    public function getSlugAttribute($value) 
    {
        if ($value) {
            return $value;
        }   

        return Str::kebab($this->name);
    }

    public function contentElements() 
    {
        return $this->hasMany(ContentElement::class);
    }
}
