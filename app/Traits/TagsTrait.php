<?php

namespace App\Traits;

use App\Models\Tag;

trait TagsTrait
{
    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function addTag($tag)
    {
        if (!$tag instanceof Tag) {
            $tag = (new Tag)->findOrCreateTag($tag);
        }

        if (!$this->tags->contains('id', $tag->id)) {
            $this->tags()->attach($tag);
        }
    }
}
