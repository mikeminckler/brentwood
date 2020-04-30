<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Page;
use App\Http\Requests\PageValidation;
use App\Http\Controllers\SoftDeletesControllerTrait;
use Illuminate\Support\Facades\Validator;

class PagesController extends Controller
{
    use SoftDeletesControllerTrait;

    protected function getModel()
    {
        return new Page;
    }

    /**
     * Find the page associated with the requested URL path
     * This is the main function to render all content pages
     */
    public function load() 
    {
        $page = Page::findByFullSlug(request()->path());

        if (!$page) {
            return abort(404);
        }

        // if the page hasn't been published and we are not logged in dont show
        if (!$page->published_at && !auth()->check()) {
            return abort(404);
        }

        if (session()->has('editing') && request('preview')) {
            $content_elements = $page->content_elements;
        } else {
            $content_elements = $page->published_content_elements;
        }

        if (session()->has('editing')) {
            $page->appendAttributes();
        }

        return view('page', compact('page', 'content_elements'));
    }

    /**
     * This returns a page tree for the editing side bar
     */
    public function index() 
    {
        $page = Page::findOrFail(1);
        $page->appendRecursive(['full_slug', 'editable', 'can_be_published']);

        return response()->json([
            'home_page' => $page,
        ]);
    }

    /**
     * Save a page and all its stuff
     * Look in App\Page for the actual save function
     */
    public function store(PageValidation $request, $id = null) 
    {
        if ($id) {
            /**
             * here we check the policy file, App\Policies\PagePolicy
             * https://laravel.com/docs/master/authorization#creating-policies
             */
            if (!auth()->user()->can('update', Page::findOrFail($id))) {
                if (request()->expectsJson()) {
                    return response()->json(['error' => 'You do not have permission to update that page'], 403);
                }
                return redirect('/')->with(['error' => 'You do not have permission to update that page']);
            }
        }

        $page = (new Page)->savePage($id, requestInput());
        $page->appendAttributes();
        return response()->json([
            'success' => 'Page Saved',
            'page' => $page,
        ]);
    }

    public function publish($id) 
    {

        if (!auth()->check()) {
            if (request()->expectsJson()) {
                return response()->json(['error' => 'You do not have permission to publish that page'], 403);
            }
            return redirect('/')->with(['error' => 'You do not have permission to publish that page']);
        }

        $page = Page::findOrFail($id);

        if (!auth()->user()->can('publish', $page)) {
            if (request()->expectsJson()) {
                return response()->json(['error' => 'You do not have permission to publish that page'], 403);
            }
            return redirect('/')->with(['error' => 'You do not have permission to publish that page']);
        }

        $page->publish();
        return response()->json(['success' => 'Page Published']);
    }

    public function remove($id) 
    {
        $page = Page::findOrFail($id);

        if (!auth()->check()) {
            return abort(401);
        }

        if ($page->slug === '/') {
            if (request()->expectsJson()) {
                return response()->json(['error' => 'The home page cannot be deleted'], 403);
            }
            return redirect('/')->with(['error' => 'The home page cannot be deleted']);
        }

        if (!auth()->user()->can('delete', $page)) {
            if (request()->expectsJson()) {
                return response()->json(['error' => 'You do not have permission to remove that page'], 403);
            }
            return redirect('/')->with(['error' => 'You do not have permission to remove that page']);
        }

        $page->delete();

        return response()->json(['success' => 'Page Removed']);
        
    }

    public function sortPage($id)
    {

        if (!auth()->check()) {
            abort(401);
        }

        $page = Page::findOrFail($id);

        if (!auth()->user()->can('update', $page)) {
            return response()->json(['error' => 'You do not have permission to sort pages'], 403);
        }

        Validator::make(request()->all(), [
            'parent_page_id' => 'required|integer|min:1|exists:pages,id',
            'sort_order' => 'required|numeric',
        ])->validate();

        (new Page)->sortPages($page, requestInput());

        return response()->json([
            'success' => 'Page Saved',
        ]);

    }
}
