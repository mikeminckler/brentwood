<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Tag;
use Illuminate\Support\Str;

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

        return response()->json(['tags' => $tags]);
    }
}
