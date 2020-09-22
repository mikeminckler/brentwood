<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

use App\Models\Blog;
use App\Models\Page;

class Tag extends Model
{
    use HasFactory;

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
