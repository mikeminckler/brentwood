<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;

use App\Http\Requests\BlogValidation;

use App\Utilities\Paginate;

use App\Traits\PagesControllerTrait;

use App\Models\Blog;
use App\Models\Tag;

class BlogsController extends Controller
{
    use PagesControllerTrait;

    protected function getModel()
    {
        return new Blog;
    }

    protected function getValidation()
    {
        return (new BlogValidation);
    }

    protected function findPage($path)
    {
        return (new Blog)->findByFullSlug($path);
    }

    public function index()
    {
        if (auth()->user()) {
            if (!auth()->user()->can('viewAny', Blog::class)) {
                if (request()->expectsJson()) {
                    return response()->json(['error' => 'You do not have permission to sort pages'], 403);
                } else {
                    return redirect('/')->with('error', 'You do not have access to view Blogs');
                }
            }
        }

        if (request()->expectsJson()) {
            $tags = collect();
            $tag_ids = collect(Arr::get(requestInput(), 'tags'))->pluck('id')->toArray();
            $tags = Tag::whereIn('id', $tag_ids)->get();

            $blogs = Blog::getBlogs($tags);

            return Paginate::create($blogs);
        } else {
            return view('blogs.index');
        }
    }
}
