<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Blog;
use App\Paginate;
use App\Http\Requests\BlogValidation;
use App\Http\Controllers\PagesControllerTrait;

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
        if (!auth()->user()->can('viewAny', Blog::class)) {
            if (request()->expectsJson()) {
                return response()->json(['error' => 'You do not have permission to sort pages'], 403);
            } else {
                return redirect('/')->with('error', 'You do not have access to view Blogs');
            }
        }

        if (request()->expectsJson()) {
            $blogs = Blog::orderBy('id', 'desc')->with('versions')->get();
            return Paginate::create($blogs);
        } else {
            return view('blogs.index');
        }
    }
}
