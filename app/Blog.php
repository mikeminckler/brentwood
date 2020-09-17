<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Events\BlogSaved;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\HasContentElementsTrait;
use App\AppendAttributesTrait;
use App\VersioningTrait;
use App\SlugTrait;

class Blog extends Model
{
    use SoftDeletes;
    use AppendAttributesTrait;
    use HasContentElementsTrait;
    use VersioningTrait;
    use SlugTrait;

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

        cache()->tags([cache_name($blog)])->flush();

        broadcast(new BlogSaved($blog))->toOthers();

        return $blog;
    }

    public function getFullSlugAttribute()
    {
        return 'blogs/'.$this->getSlug();
    }
}
