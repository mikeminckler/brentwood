<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use App\Blog;
use App\Page;
use Illuminate\Support\Str;

class Tag extends Model
{
    public function findOrCreateTag($id_or_name)
    {
        $tag = Tag::find($id_or_name);

        if (!$tag) {
            $tag = Tag::where('name', Str::title($id_or_name))->first();
        }

        if (!$tag) {
            $tag = new Tag;
            $tag->name = Str::title($id_or_name);
            $tag->save();
        }

        return $tag;
    }

    public function blogs()
    {
        return $this->morphedByMany(Blog::class, 'taggable');
    }

    public function pages()
    {
        return $this->morphedByMany(Page::class, 'taggable');
    }
}
