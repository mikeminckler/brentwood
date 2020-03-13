<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Page;
use App\Http\Requests\PageValidation;

class PagesController extends Controller
{
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
        return view('page', compact('page'));
    }

    /**
     * This returns a page tree for the editing side bar
     */
    public function index() 
    {
        $home_page = Page::findOrFail(1);

        return response()->json([
            'home_page' => $home_page,
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
                    return response()->json(['error', 'You do not have permission to update that page'], 403);
                }
                return redirect('/')->with(['error' => 'You do not have permission to update that page']);
            }
        }

        $page = (new Page)->savePage($id, requestInput());
        return response()->json([
            'success' => 'Page Saved',
            'page' => $page,
        ]);
    }
}
