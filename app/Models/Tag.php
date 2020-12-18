<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;

use App\Models\Blog;
use App\Models\Page;

use App\Traits\SearchTrait;

class Tag extends Model
{
    use HasFactory;
    use SearchTrait;

    protected $appends = ['search_label'];

    public function findOrCreateTag($input)
    {
        if (is_int($input)) {
            $tag = Tag::find($input);
        }

        if (is_string($input)) {
            $tag = Tag::where('name', Str::title($input))->first();
        }

        if (is_array($input)) {
            $tag = Tag::find(Arr::get($input, 'id'));

            if (!$tag) {
                $tag = Tag::where('name', Str::title(Arr::get($input, 'name')))->first();
            }
        }

        if (!$tag) {
            if (is_array($input)) {
                $name = Arr::get($input, 'name');
            } else {
                $name = $input;
            }

            $tag = new Tag;
            $tag->name = Str::title($input);
            $tag->save();
        }

        return $tag;
    }

    public function saveTag(array $input, $id = null)
    {
        if ($id) {
            $tag = Tag::findOrFail($id);
        } else {
            $tag = new Tag;
        }

        $tag->name = Str::title(Arr::get($input, 'name'));
        $tag->save();

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
