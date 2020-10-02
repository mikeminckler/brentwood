<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

use App\Utilities\Paginate;

use App\Traits\ContentElementTrait;
use App\Traits\TagsTrait;
use App\Traits\AppendAttributesTrait;

class BlogList extends Model
{
    use HasFactory;
    use ContentElementTrait;
    use TagsTrait;
    use AppendAttributesTrait;

    protected $with = ['tags'];
    protected $appends = ['blogs'];

    public function saveContent($id = null, $input)
    {
        if ($id >= 1) {
            $blog_list = BlogList::findOrFail($id);
        } else {
            $blog_list = new BlogList;
        }

        $blog_list->header = Arr::get($input, 'header');
        $blog_list->save();

        $blog_list->saveTags($input);

        cache()->tags([cache_name($blog_list)])->flush();
        return $blog_list;
    }

    public function getBlogsAttribute()
    {
        return Paginate::create(Blog::getBlogs($this->tags));
    }
}
