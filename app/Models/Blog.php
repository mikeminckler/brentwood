<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use App\Events\BlogSaved;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

use App\Traits\HasContentElementsTrait;
use App\Traits\AppendAttributesTrait;
use App\Traits\VersioningTrait;
use App\Traits\SlugTrait;
use App\Traits\TagsTrait;
use App\Traits\HasFooterTrait;
use App\Traits\HasPermissionsTrait;

use App\Models\Page;
use App\Models\Tag;

class Blog extends Model
{
    use HasFactory;
    use SoftDeletes;
    use AppendAttributesTrait;
    use HasContentElementsTrait;
    use VersioningTrait;
    use SlugTrait;
    use TagsTrait;
    use HasFooterTrait;
    use HasPermissionsTrait;

    protected $dates = ['publish_at'];

    protected $with = ['tags'];

    protected $appends = ['type'];

    public $append_attributes = [
        'editable',
        'full_slug',
        'can_be_published',
        'content_elements',
        'preview_content_elements',
        'type',
        'full_type',
        'resource',
        'published_at',
    ];

    protected $casts = [
        'unlisted' => 'boolean',
    ];

    public function savePage(array $input, $id = null)
    {
        if ($id) {
            $blog = Blog::findOrFail($id);
        } else {
            $blog = new Blog;
        }

        $blog->name = Arr::get($input, 'name');
        $blog->title = Arr::get($input, 'title');
        $blog->author = Arr::get($input, 'author');
        $blog->unlisted = Arr::get($input, 'unlisted') == true ? true : false;
        $blog->publish_at = Arr::get($input, 'publish_at');
        $blog->save();

        $blog->saveContentElements($input);
        $blog->saveTags($input);

        cache()->tags([cache_name($blog)])->flush();

        broadcast(new BlogSaved($blog))->toOthers();

        return $blog;
    }

    public function getFullSlugAttribute()
    {
        return 'blogs/'.$this->getSlug();
    }

    public static function getBlogs($tags = null)
    {
        if (session()->has('editing')) {
            $blogs = Blog::orderBy('id', 'desc')->with('versions')->get();
        } else {
            $blogs = Blog::whereNotNull('published_version_id')
                    ->orderBy('id', 'desc')
                    ->get()
                    ->filter(function ($blog) {
                        return $blog->unlisted ? false : true;
                    });
        }

        //$blogs->load('tags');

        if ($tags) {
            if ($tags instanceof Tag) {
                $tags = collect([$tags]);
            }

            if ($tags->count()) {
                $blogs = $blogs->filter(function ($blog) use ($tags) {
                    foreach ($tags as $tag) {
                        if ($blog->tags->contains('id', $tag->id)) {
                            return true;
                        }
                    }
                    return false;
                });
            }
        }

        return $blogs;
    }

    public function getNextBlogAttribute() 
    {
        if (!$this->published_at) {
            return null;
        }
        return Blog::whereHas('publishedVersion', function($query) {
            $query->where('published_at', '>', $this->published_at);
        })
        ->get()
        ->sortBy(function($blog) {
            return $blog->published_at;
        })->first();
    }

    public function getPreviousBlogAttribute() 
    {
        if (!$this->published_at) {
            return null;
        }
        return Blog::whereHas('publishedVersion', function($query) {
            $query->where('published_at', '<', $this->published_at);
        })
        ->get()
        ->sortByDesc(function($blog) {
            return $blog->published_at;
        })->first();
    }

    public function getFooterFgPhoto() 
    {
        $value = $this->footerFgPhoto;   
        if ($value) {
            return $value;
        } else {
            return Page::find(1)->footerFgPhoto;   
        }
    }

    public function getFooterBgPhoto() 
    {
        $value = $this->footerBgPhoto;   
        if ($value) {
            return $value;
        } else {
            return Page::find(1)->footerBgPhoto;   
        }
    }

    public function getFooterColorAttribute($value)
    {
        if ($value) {
            return $value;
        } else {
            return Page::find(1)->footer_color;   
        }
    }
}
