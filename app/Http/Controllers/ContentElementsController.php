<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\ContentElementValidation;

use App\ContentElement;
use Illuminate\Support\Str;
use App\Page;
use Illuminate\Support\Facades\Validator;
use App\Events\ContentElementRemoved;

class ContentElementsController extends Controller
{
    protected function getModel()
    {
        return new ContentElement;
    }

    /**
     * Find the page associated with the requested URL path
     * This is the main function to render all content pages
     */
    public function load($id) 
    {

        Validator::make(request()->all(), [
            'page_id' => 'required|exists:pages,id',
        ])->validate();

        $content_element = ContentElement::findOrFail($id);
        $page = Page::findOrFail(request('page_id'));
        $content_element = $page->contentElements()->where('content_element_id', $content_element->id)->first();

        if (!auth()->user()->can('view', $content_element)) {
            if (request()->expectsJson()) {
                return response()->json(['error' => 'You do not have permission to load that item'], 403);
            }
            return redirect('/')->with(['error' => 'You do not have permission to load that item']);
        }

        return response()->json([
            'content_element' => $content_element,
        ]);
    }

    public function store(ContentElementValidation $request, $id = null) 
    {
        $contentable = ContentElement::findContentable(requestInput());
        $content_element = (new ContentElement)->saveContentElement($id, requestInput());
        $content_element = $contentable->contentElements()->where('content_element_id', $content_element->id)->first();

        return response()->json([
            'success' => Str::title(str_replace('-', ' ', $content_element->type)).' Saved',
            'content_element' => $content_element,
        ]);
    }

    public function remove($id) 
    {

        Validator::make(request()->all(), [
            'page_id' => 'required|exists:pages,id',
        ])->validate();

        $content_element = ContentElement::findOrFail($id);
        $page = Page::findOrFail(requestInput('page_id'));

        if (!auth()->check()) {
            return abort(401);
        }

        if (!auth()->user()->can('delete', $content_element)) {
            if (request()->expectsJson()) {
                return response()->json(['error' => 'You do not have permission to remove that item'], 403);
            }
            return redirect('/')->with(['error' => 'You do not have permission to remove that item']);
        }

        broadcast( new ContentElementRemoved($content_element, $page));

        if (requestInput('remove_all')) {
            ContentElement::where('uuid', $content_element->uuid)->delete();
            return response()->json([
                'success' => Str::title(str_replace('-', ' ', $content_element->type)).' Removed',
            ]);
        } else {

            $previous_content_element = $content_element->getPreviousVersion();
            $content_element->delete();

            return response()->json([
                'success' => Str::title(str_replace('-', ' ', $content_element->type)).' Removed',
                'content_element' => $previous_content_element,
            ]);
        }
        
    }

    public function restore($id) 
    {
        $content_element = ContentElement::onlyTrashed()
            ->where('id', $id)
            ->first();

        if (!auth()->check()) {
            return abort(401);
        }

        if (!auth()->user()->can('delete', $content_element)) {
            if (request()->expectsJson()) {
                return response()->json(['error' => 'You do not have permission to restore that item'], 403);
            }
            return redirect('/')->with(['error' => 'You do not have permission to restore that item']);
        }

        $content_element->restore();

        return response()->json(['success' => Str::title(str_replace('-', ' ', $content_element->type)).' Restored']);
    }
}
