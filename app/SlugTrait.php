<?php

namespace App;

use Illuminate\Support\Str;

trait SlugTrait
{
    abstract public function getFullSlugAttribute();

    public function findByFullSlug($slug)
    {
        return $this->all()->filter(function ($page) use ($slug) {
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
}
