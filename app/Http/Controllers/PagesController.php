<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Page;
use App\Http\Requests\PageValidation;
use App\Http\Controllers\PagesControllerTrait;
use Illuminate\Support\Facades\Validator;

class PagesController extends Controller
{
    use PagesControllerTrait;

    protected function getModel()
    {
        return new Page;
    }

    protected function getValidation()
    {
        return (new PageValidation);
    }

    protected function findPage($path)
    {
        return Page::findByFullSlug($path);
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
