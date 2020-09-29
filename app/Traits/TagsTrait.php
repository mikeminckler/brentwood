<?php

namespace App\Traits;

use Illuminate\Support\Arr;
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

    public function saveTags($input)
    {
        $this->tags()->detach();

        if (is_array(Arr::get($input, 'tags'))) {
            foreach (Arr::get($input, 'tags') as $tag_data) {
                $this->addTag($tag_data);
            }
        }

        return $this;
    }
}
