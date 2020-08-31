<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Events\BlogSaved;
use Illuminate\Support\Arr;
use App\HasContentElementsTrait;
use App\AppendAttributesTrait;
use App\VersioningTrait;

class Blog extends Model
{

    use SoftDeletes;
    use AppendAttributesTrait;
    use HasContentElementsTrait;
    use VersioningTrait;

    protected $dates = ['publish_at'];

    public $append_attributes = [
        //'full_slug', 
        //'can_be_published', 
        'content_elements', 
        'preview_content_elements',
        'type',
    ];

    public function savePage($id = null, $input) 
    {
        if ($id) {
            $blog = Blog::findOrFail($id);
        } else {
            $blog = new Blog;
        }

        $blog->name = Arr::get($input, 'name');
        $blog->unlisted = Arr::get($input, 'unlisted') == true ? true : false;
        $blog->publish_at = Arr::get($input, 'publish_at');
        $blog->save();

        $blog->saveContentElements($input);

        cache()->tags([cache_name($blog)])->flush();

        broadcast(new BlogSaved($blog))->toOthers();

        return $blog;
    }
}
