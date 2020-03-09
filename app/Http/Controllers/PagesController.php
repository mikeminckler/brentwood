<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Page;
use App\Http\Requests\PageValidation;

class PagesController extends Controller
{
    public function load() 
    {
        return view('app');
    }

    public function store(PageValidation $request, $id = null) 
    {
        if ($id) {
            if (!auth()->user()->can('update', Page::findOrFail($id))) {

                if (request()->ajax()) {
                    return response()->json(['error', 'You do not have permission to update that page'], 403);
                }
                return redirect()->route('home')->with(['error' => 'You do not have permission to update that page']);
            }
        }

        $page = (new Page)->savePage($id, requestInput());
        return response()->json([
            'success' => 'Page Saved'
        ]);
    }
}
