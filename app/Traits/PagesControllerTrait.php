<?php

namespace App\Traits;

use App\Traits\SoftDeletesControllerTrait;
use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Str;

use App\Utilities\PageResponse;

trait PagesControllerTrait
{
    use SoftDeletesControllerTrait;

    abstract protected function getValidation();
    abstract protected function findPage($page);

    /**
     * Find the page associated with the requested URL path
     * This is the main function to render all content pages
     */
    public function load()
    {
        $page = $this->findPage(request()->path());
        return (new PageResponse)->view($page, 'pages.view');
    }

    /**
     * Save a page and all its stuff
     * Look in App\Page for the actual save function
     */
    public function store($id = null)
    {
        Validator::make(request()->all(), $this->getValidation()->rules((int) $id))->validate();

        if ($id) {
            /**
             * here we check the policy file, App\Policies\PagePolicy
             * https://laravel.com/docs/master/authorization#creating-policies
             */
            if (!auth()->user()->can('update', $this->getModel()->findOrFail($id))) {
                if (request()->expectsJson()) {
                    return response()->json(['error' => 'You do not have permission to update that page'], 403);
                }
                return redirect('/')->with(['error' => 'You do not have permission to update that page']);
            }
        }

        $page = ($this->getModel())->savePage(requestInput(), $id);

        $page = (new PageResponse)->loadPageAttributes($page);

        return response()->json([
            'success' => class_basename($page).' Saved',
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

        $page = $this->getModel()->findOrFail($id);

        if (!auth()->user()->can('publish', $page)) {
            if (request()->expectsJson()) {
                return response()->json(['error' => 'You do not have permission to publish that page'], 403);
            }
            return redirect('/')->with(['error' => 'You do not have permission to publish that page']);
        }

        $page->publish();

        $page = (new PageResponse)->loadPageAttributes($page);

        return response()->json([
            'success' => class_basename($page).' Published',
            'page' => $page,
        ]);
    }

    /*
    public function remove($id)
    {
        $page = $this->getModel()->findOrFail($id);

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
     */

    public function unlist($id)
    {
        if (!auth()->check()) {
            if (request()->expectsJson()) {
                return response()->json(['error' => 'You do not have permission to hide that page'], 403);
            }
            return redirect('/')->with(['error' => 'You do not have permission to hide that page']);
        }

        $page = $this->getModel()->findOrFail($id);

        if (!auth()->user()->can('update', $page)) {
            if (request()->expectsJson()) {
                return response()->json(['error' => 'You do not have permission to hide that page'], 403);
            }
            return redirect('/')->with(['error' => 'You do not have permission to hide that page']);
        }

        $page->unlisted = 1;
        $page->save();
       
        $page->refresh();

        $page = (new PageResponse)->loadPageAttributes($page);

        return response()->json([
            'success' => Str::title(class_basename($page)).' Hidden',
            $page->type => $page,
        ]);
    }

    public function reveal($id)
    {
        if (!auth()->check()) {
            if (request()->expectsJson()) {
                return response()->json(['error' => 'You do not have permission to unhide that page'], 403);
            }
            return redirect('/')->with(['error' => 'You do not have permission to unhide that page']);
        }

        $page = $this->getModel()->findOrFail($id);

        if (!auth()->user()->can('update', $page)) {
            if (request()->expectsJson()) {
                return response()->json(['error' => 'You do not have permission to unhide that page'], 403);
            }
            return redirect('/')->with(['error' => 'You do not have permission to unhide that page']);
        }

        $page->unlisted = 0;
        $page->save();
        
        $page->refresh();

        $page = (new PageResponse)->loadPageAttributes($page);

        return response()->json([
            'success' => Str::title(class_basename($page)).' Revealed',
            $page->type => $page,
        ]);
    }
}
