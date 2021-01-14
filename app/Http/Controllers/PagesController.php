<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Models\Page;
use App\Http\Requests\PageValidation;
use App\Traits\PagesControllerTrait;

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
        return (new Page)->findByFullSlug($path);
    }

    /**
     * This returns a page tree for the editing side bar
     */
    public function index()
    {
        $page = Page::findOrFail(1);
        $page->appendRecursive(['full_slug', 'editable', 'can_be_published']);

        if (requestInput('preview')) {
            $page->appendRecursive(['preview_content_elements']);
        }

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
}
