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

class Blog extends Model
{
    use HasFactory;
    use SoftDeletes;
    use AppendAttributesTrait;
    use HasContentElementsTrait;
    use VersioningTrait;
    use SlugTrait;
    use TagsTrait;

    protected $dates = ['publish_at'];

    public $append_attributes = [
        'editable',
        'full_slug',
        'can_be_published',
        'content_elements',
        'preview_content_elements',
        'type',
        'resource',
        'published_at',
    ];

    protected $casts = [
        'unlisted' => 'boolean',
    ];

    public function savePage($id = null, $input)
    {
        if ($id) {
            $blog = Blog::findOrFail($id);
        } else {
            $blog = new Blog;
        }

        $blog->name = Arr::get($input, 'name');
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
}
