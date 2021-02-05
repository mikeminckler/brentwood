<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests\LivestreamValidation;

use App\Models\Livestream;
use App\Utilities\Paginate;
use App\Utilities\PageResponse;
use App\Models\Page;
use App\Models\Inquiry;

class LivestreamsController extends Controller
{
    public function index()
    {
        if (auth()->user()) {
            if (!auth()->user()->can('viewAny', Livestream::class)) {
                if (request()->expectsJson()) {
                    return response()->json(['error' => 'You do not have permission to sort pages'], 403);
                } else {
                    return redirect('/')->with('error', 'You do not have access to view Livestreams');
                }
            }
        }

        if (request()->expectsJson()) {
            return Paginate::create(Livestream::with('inquiries')->get()->sortByDesc->id);
        } else {
            return view('livestreams.index');
        }
    }

    public function store(LivestreamValidation $request, $id = null)
    {
        if ($id) {
            $livestream = Livestream::findOrFail($id);
            if (!auth()->user()->can('update', $livestream)) {
                return response()->json(['error' => 'You do not have permission to update that Livestream'], 403);
            }
        } else {
            if (!auth()->user()->can('create', Livestream::class)) {
                return response()->json(['error' => 'You do not have permission to create Livestreams'], 403);
            }
        }

        $livestream = (new Livestream)->saveLivestream(requestInput(), $id);

        return response()->json([
            'success' => $livestream->name.' Saved',
            'livestream' => $livestream,
        ]);
    }

    public function view($id)
    {
        $livestream = Livestream::findOrFail($id);

        if ($livestream->roles->count() || $livestream->users->count()) {
            if (!auth()->check()) {
                session()->put('url.intended', url()->current());
                return redirect()->route('login');
            }

            if (!auth()->user()->can('view', $livestream)) {
                return redirect('/')->with(['error' => 'You do not have permission to view that livestream']);
            }
        }
        return view('livestreams.view', compact('livestream'));
    }

    public function inquiry($id, $inquiry_id)
    {
        if (! request()->hasValidSignature()) {
            abort(401);
        }

        $livestream = Livestream::findOrFail($id);
        $inquiry = Inquiry::findOrFail($inquiry_id);

        auth()->login($inquiry->user);

        return view('livestreams.view', compact('livestream', 'inquiry'));
    }

    public function register($id)
    {
        $livestream = Livestream::findOrFail($id);
        $page = Page::where('slug', 'livestream-register')->first();
        return (new PageResponse)->view($page, 'livestreams.register', ['livestream' => $livestream]);
    }
}
