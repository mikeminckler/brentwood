<?php

namespace App\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

use App\Models\PageAccess;
use App\Models\ContentElement;

trait HasContentElementsTrait
{
    public function contentElements()
    {
        return $this->morphToMany(ContentElement::class, 'contentable')->withPivot('sort_order', 'unlisted', 'expandable');
    }

    public function getTypeAttribute()
    {
        return Str::kebab(class_basename($this));
    }

    public function getResourceAttribute()
    {
        return Str::kebab(Str::plural(class_basename($this)));
    }

    public function saveContentElements($input)
    {
        if (Arr::get($input, 'content_elements')) {
            foreach (Arr::get($input, 'content_elements') as $content_element) {
                $content_element = (new ContentElement)->saveContentElement($content_element, Arr::get($content_element, 'id'));
            }
        }

        return $this;
    }

    public function getContentElements()
    {
        $version_id = requestInput('version_id');
        
        if ($version_id > 0) {
            return $this->contentElements()
                ->where('version_id', '<=', $version_id)
                ->get();
        } else {
            return $this->contentElements()->get();
        }
    }

    public function getContentElementsAttribute()
    {
        //return cache()->tags([cache_name($this)])->rememberForever(cache_name($this).'-content-elements', function() {
        $content_elements = $this->getContentElements()
                         ->groupBy('uuid')
                         ->map(function ($uuid) {
                             return $uuid->sortByDesc(function ($content_element) {
                                 return $content_element->version_id;
                             })->first();
                         })
                         ->sortBy(function ($content_element) {
                             return $content_element->pivot->sort_order;
                         })
                         ->values();

        return $this->addContentableAttributes($content_elements);
        //});
    }

    public function getPublishedContentElementsAttribute()
    {
        $content_elements = $this->getContentElements()
                     ->groupBy('uuid')
                     ->map(function ($uuid) {
                         return $uuid->filter(function ($content_element) {
                             return $content_element->published_at ? true : false;
                         })
                        ->sortByDesc(function ($content_element) {
                            return $content_element->version_id;
                        })->first();
                     })
                     ->filter()
                     ->filter(function ($content_element) {
                         return $content_element->pivot->unlisted ? false : true;
                     })
                     ->sortBy(function ($content_element) {
                         return $content_element->pivot->sort_order;
                     })->values();

        return $this->addContentableAttributes($content_elements);
    }

    public function getPreviewContentElementsAttribute()
    {
        if (!session()->get('editing')) {
            return collect();
        }
        $content_elements = $this->getContentElements()
                     ->groupBy('uuid')
                     ->map(function ($uuid) {
                         return $uuid->sortByDesc(function ($content_element) {
                             return $content_element->version_id;
                         })->first();
                     })
                     ->filter(function ($content_element) {
                         return $content_element->pivot->unlisted ? false : true;
                     })
                     ->sortBy(function ($content_element) {
                         return $content_element->pivot->sort_order;
                     })->values();

        return $this->addContentableAttributes($content_elements);
    }

    protected function addContentableAttributes(Collection $content_elements)
    {
        return $content_elements->map(function ($content_element) {
            $content_element->contentable_id = $this->id;
            $content_element->contentable_type = $this->type;
            return $content_element;
        });
    }

    public function getEditableAttribute()
    {
        if (!auth()->check()) {
            return false;
        }

        if (!session()->has('editing')) {
            return false;
        }

        return auth()->user()->can('update', $this);
    }

    public function createPageAccess($pageable)
    {
        $page_access = (new PageAccess)->savePageAccess($this, $pageable);
        return $this;
    }

    public function removePageAccess($pageable)
    {
        $page_access = (new PageAccess)->removePageAccess($this, $pageable);
        return $this;
    }

    public function pageAccesses()
    {
        return $this->morphMany(PageAccess::class, 'pageable');
    }
}
