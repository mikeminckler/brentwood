<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\ContentElementValidation;

use App\ContentElement;
use Illuminate\Support\Str;
use App\Page;

class ContentElementsController extends Controller
{
    protected function getModel()
    {
        return new ContentElement;
    }

    public function store(ContentElementValidation $request, $id = null) 
    {
        if ($id) {
            /**
             * here we check the policy file, App\Policies\ContentElementPolicy
             * https://laravel.com/docs/master/authorization#creating-policies
             */
            if (!auth()->user()->can('update', ContentElement::findOrFail($id))) {
                if (request()->expectsJson()) {
                    return response()->json(['error', 'You do not have permission to update that content'], 403);
                }
                return redirect('/')->with(['error' => 'You do not have permission to update that content']);
            }
        }

        $content_element = (new ContentElement)->saveContentElement($id, requestInput());

        $page = Page::findOrFail(requestInput('pivot.page_id'));
        $content_element = $page->contentElements()->where('content_element_id', $content_element->id)->first();

        return response()->json([
            'success' => Str::title(str_replace('-', ' ', $content_element->type)).' Saved',
            'content_element' => $content_element,
        ]);
    }

    public function remove($id) 
    {
        $content_element = ContentElement::findOrFail($id);

        if (!auth()->check()) {
            return abort(401);
        }

        if (!auth()->user()->can('delete', $content_element)) {
            if (request()->expectsJson()) {
                return response()->json(['error' => 'You do not have permission to remove that item'], 403);
            }
            return redirect('/')->with(['error' => 'You do not have permission to remove that item']);
        }

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
