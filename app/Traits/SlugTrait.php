<?php

namespace App\Traits;

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

    public function getSlug()
    {
        if ($this->slug === '/') {
            return $this->slug;
        }

        if ($this->slug) {
            $slug = $this->slug;
        } else {
            $slug = $this->name;
        }

        $slug = Str::lower($slug);

        $slug = preg_replace("/[^A-Za-z0-9 ]/", '', $slug);
        $slug = Str::kebab($slug);

        return $slug;
    }
}
