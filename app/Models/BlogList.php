<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

use App\Traits\ContentElementTrait;
use App\Traits\TagsTrait;

class BlogList extends Model
{
    use HasFactory;
    use ContentElementTrait;
    use TagsTrait;

    protected $with = ['tags'];

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
}
