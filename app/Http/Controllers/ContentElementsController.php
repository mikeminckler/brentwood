<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\ContentElementValidation;

use App\ContentElement;
use Illuminate\Support\Str;

class ContentElementsController extends Controller
{
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
        return response()->json([
            'success' => Str::title(str_replace('-', ' ', $content_element->type)).' Saved',
            'content_element' => $content_element,
            'html' => view('content-elements.'.$content_element->type, ['content' => $content_element->content])->render(),
        ]);
    }
}
