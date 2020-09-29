<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Tag;
use Illuminate\Support\Str;
use App\Http\Requests\TagValidation;

class TagsController extends Controller
{
    public function search()
    {
        if (!auth()->user()->can('viewAny', Tag::class)) {
            return response()->json(['error' => 'You do not have permission to view tags'], 403);
        }

        request()->validate([
            'terms' => 'required',
        ]);

        $tags = Tag::where('name', 'LIKE', '%'.Str::title(request('terms')).'%')->get();

        return response()->json(['results' => $tags]);
    }

    public function store(TagValidation $request, $id = null)
    {
        if ($id) {
            $tag = Tag::findOrFail($id);
            if (!auth()->user()->can('update', $tag)) {
                return response()->json(['error' => 'You do not have permission to update that Tag'], 403);
            }
        } else {
            if (!auth()->user()->can('create', Tag::class)) {
                return response()->json(['error' => 'You do not have permission to create Tags'], 403);
            }
        }

        $tag = (new Tag)->saveTag($id, requestInput());

        return response()->json([
            'success' => $tag->name.' Saved',
            'tag' => $tag,
        ]);
    }
}
