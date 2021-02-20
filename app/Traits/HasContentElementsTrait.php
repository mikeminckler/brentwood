<?php

namespace App\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

use App\Models\PageAccess;
use App\Models\ContentElement;
use App\Models\Version;

trait HasContentElementsTrait
{
    public function contentElements()
    {
        return $this->morphToMany(ContentElement::class, 'contentable')->withPivot('sort_order', 'unlisted', 'expandable', 'version_id');
    }

    public function getTypeAttribute()
    {
        return Str::kebab(class_basename($this));
    }

    public function getFullTypeAttribute()
    {
        return get_class($this);
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
            return cache()->tags([cache_name($this)])->rememberForever(cache_name($this).'-content-elements-'.$version_id, function () use ($version_id) {
                return $this->contentElements()
                        ->wherePivot('version_id', '<=', $version_id)
                        ->get();
            });
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
                                 return $content_element->pivot->version_id;
                             })->first();
                         })
                         ->sortBy(function ($content_element) {
                             return $content_element->pivot->sort_order;
                         })
                         ->values();

        return $content_elements;
        //return $this->addContentableAttributes($content_elements);
        //});
    }

    public function getPublishedContentElementsAttribute()
    {
        $content_elements = $this->getContentElements()
                     ->groupBy('uuid')
                     ->map(function ($uuid) {
                         return $uuid->filter(function ($content_element) {
                             return Version::find($content_element->pivot->version_id)->published_at ? true : false;
                         })
                        ->sortByDesc(function ($content_element) {
                            return $content_element->pivot->version_id;
                        })->first();
                     })
                     ->filter()
                     ->filter(function ($content_element) {
                         return $content_element->pivot->unlisted ? false : true;
                     })
                     ->sortBy(function ($content_element) {
                         return $content_element->pivot->sort_order;
                     })->values();

        return $content_elements;
        //return $this->addContentableAttributes($content_elements);
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
                             return $content_element->pivot->version_id;
                         })->first();
                     })
                     ->filter(function ($content_element) {
                         return $content_element->pivot->unlisted ? false : true;
                     })
                     ->sortBy(function ($content_element) {
                         return $content_element->pivot->sort_order;
                     })->values();

        return $content_elements;
        //return $this->addContentableAttributes($content_elements);
    }

    /*
     * We were using this to add attributes for the front end but the attributes
     * are already available in the pivot
    protected function addContentableAttributes(Collection $content_elements)
    {
        return $content_elements->map(function ($content_element) {
            $content_element->contentable_id = $this->id;
            $content_element->contentable_type = $this->type;
            return $content_element;
        });
    }
     */

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
}
