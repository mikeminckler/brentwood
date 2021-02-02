<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Collection;
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

        if (!$tag->protected) {
            $tag->name = Str::title(Arr::get($input, 'name'));
            $tag->parent_tag_id = Arr::get($input, 'parent_tag_id');
        }
        $tag->save();

        return $tag;
    }

    public function getLabelAttribute()
    {
        $name = '';

        if ($this->parentTag) {
            $name .= $this->parentTag->label.' - ';
        }

        return $name.$this->name;
    }

    public function getSearchLabelAttribute()
    {
        return $this->label;
    }

    public function blogs()
    {
        return $this->morphedByMany(Blog::class, 'taggable');
    }

    public function pages()
    {
        return $this->morphedByMany(Page::class, 'taggable');
    }

    public function parentTag()
    {
        return $this->belongsTo(Tag::class, 'parent_tag_id');
    }

    public function tags()
    {
        return $this->hasMany(Tag::class, 'parent_tag_id');
    }


    public static function filterWithHierarchy(Collection $filter_tags)
    {

        // this takes in an array of tags and filters out tags that aren't
        // present from all of the tags in the db, to preserve the hierarchy
        // for displaying in the frontend

        return Tag::whereNull('parent_tag_id')->get()->map(function ($tag) use ($filter_tags) {
            return Tag::filterTags($tag, $filter_tags);
        })
        ->filter()
        ->values();
    }

    public static function filterTags($tag, $filter_tags)
    {
        if ($filter_tags->contains('id', $tag->id)) {
            return $tag;
        }

        $tags = $tag->tags()->get();

        if ($tags->count()) {
            $tags = $tags->transform(function ($tag) use ($filter_tags) {
                return Tag::filterTags($tag, $filter_tags);
            });

            $tags = $tags->filter();

            if ($tags->count()) {
                $tag->tags = $tags;
                return $tag;
            } else {
                return null;
            }
        } else {
            return null;
        }
    }
}
