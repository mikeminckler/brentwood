<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

use App\ContentElement;

class Page extends Model
{
    use SoftDeletes;

    protected $with = ['pages'];
    protected $appends = ['full_slug', 'can_be_published', 'content_elements'];

    public function savePage($id = null, $input) 
    {
        $home_page = false;
        if ($id) {
            $page = Page::findOrFail($id);
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

        $page->sort_order = Arr::get($input, 'sort_order');
        $page->unlisted = Arr::get($input, 'unlisted') == true ? true : false;
        $page->save();

        $page->saveContentElements($input);

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
            
            if ($parent_page instanceof Page) {
                while ($parent_page->id > 1) {
                    $slug = Str::kebab($parent_page->name).'/'.$slug;
                    $parent_page = Page::find($parent_page->parent_page_id);
                }
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
        return $this->belongsToMany(ContentElement::class)->withPivot('sort_order', 'unlisted');
    }

    public function saveContentElements($input) 
    {
        if (Arr::get($input, 'content_elements')) {
            foreach (Arr::get($input, 'content_elements') as $content_element) {
                $content_element = (new ContentElement)->saveContentElement(Arr::get($content_element, 'id'), $content_element);
            }
        }

        return $this;
    }

    public function versions() 
    {
        return $this->hasMany(Version::class);   
    }

    public function publishedVersion() 
    {
        return $this->belongsTo(Version::class, 'published_version_id');   
    }

    public function getPublishedAtAttribute() 
    {
        return optional($this->publishedVersion)->published_at;   
    }

    public function publish() 
    {
        $draft_version = $this->getDraftVersion();
        $draft_version->publish();
        $this->published_version_id = $draft_version->id;
        $this->save();

        cache()->tags([cache_name($this), cache_name($draft_version)])->flush();
        return $this;
    }

    public function getDraftVersion() 
    {
        $draft_version = $this->versions()->whereNull('published_at')->first();
        if ($draft_version) {
            return $draft_version;
        } else {
            return (new Version)->saveVersion(null, [
                'name' => $this->versions()->count() + 1,
                'page_id' => $this->id,
            ]);
        }
    }

    public function getDraftVersionIdAttribute() 
    {
        return $this->getDraftVersion()->id; 
    }

    public function getContentElementsAttribute() 
    {
        return $this->contentElements()
                     ->get()
                     ->groupBy('uuid')
                     ->map(function($uuid) {
                        return $uuid->sortByDesc( function( $content_element) {
                            return $content_element->version_id;
                        })->first();
                     })
                     ->sortBy(function($content_element) {
                        return $content_element->sort_order;
                     })->values();
    }

    public function getPublishedContentElementsAttribute() 
    {
        return $this->contentElements()
                     ->get()
                     ->groupBy('uuid')
                     ->map(function($uuid) {
                        return $uuid->filter( function($content_element) {
                            return $content_element->published_at ? true : false;
                        })
                        ->sortByDesc( function( $content_element) {
                            return $content_element->version_id;
                        })->first();
                     })
                     ->filter()
                     ->filter( function($content_element) {
                        return $content_element->unlisted ? false : true;
                     })
                     ->sortBy(function($content_element) {
                        return $content_element->sort_order;
                     })->values();
    }

    public function getCanBePublishedAttribute() 
    {
        if (!$this->published_version_id && $this->contentElements->count()) {
            return true;
        }

        return $this->content_elements->filter(function($content_element) {
                return $content_element->published_at ? false : true;
            })->count() ? true : false;
    }
}
