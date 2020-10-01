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
            if (!auth()->user()) {
                $blogs = Blog::whereNotNull('published_version_id')
                    ->orderBy('id', 'desc')
                    ->get()
                    ->filter(function ($blog) {
                        return $blog->unlisted ? false : true;
                    });
            } elseif (session()->has('editing')) {
                $blogs = Blog::orderBy('id', 'desc')->with('versions')->get();
            } else {
                $blogs = Blog::whereNotNull('published_version_id')
                    ->orderBy('id', 'desc')
                    ->get()
                    ->filter(function ($blog) {
                        return $blog->unlisted ? false : true;
                    });
            }

            if (requestInput('tags')) {
                $tags = collect();
                $tag_ids = collect(Arr::get(requestInput(), 'tags'))->pluck('id')->toArray();
                $tags = Tag::whereIn('id', $tag_ids)->get();

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

            return Paginate::create($blogs);
        } else {
            return view('blogs.index');
        }
    }
}
