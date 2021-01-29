<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Inquiry;
use App\Models\Page;
use App\Models\Livestream;
use App\Utilities\Paginate;
use App\Utilities\PageResponse;

use App\Http\Requests\InquiryValidation;

use App\Traits\PagesControllerTrait;

class InquiriesController extends Controller
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

    public function create()
    {
        $page = $this->findPage(request()->path());
        return (new PageResponse)->view($page, 'inquiries.create');
    }

    public function index()
    {
        if (auth()->user()) {
            if (!auth()->user()->can('viewAny', Inquiry::class)) {
                if (request()->expectsJson()) {
                    return response()->json(['error' => 'You do not have permission load inquiries'], 403);
                } else {
                    return redirect('/')->with('error', 'You do not have access to view Inquiries');
                }
            }
        }

        if (request()->expectsJson()) {
            return Paginate::create(Inquiry::all()->sortByDesc('updated_at'));
        } else {
            return view('inquiries.index');
        }
    }

    public function store(InquiryValidation $request, $id = null)
    {
        $inquiry = (new Inquiry)->saveInquiry(requestInput(), $id);

        return response()->json([
            'success' => 'Inquiry Saved',
            'inquiry' => $inquiry,
        ]);
    }

    public function view($id)
    {
        if (! request()->hasValidSignature()) {
            abort(401);
        }

        $inquiry = Inquiry::findOrFail($id);

        $inquiry->load('livestreams');

        $page = Inquiry::findPage();

        $content_elements = $page->published_content_elements
                                 ->filter(function ($content_element) use ($inquiry) {
                                     if (!$content_element->tags->count()) {
                                         return true;
                                     }
                                     return $inquiry->tags->intersect($content_element->tags)->count();
                                 });

        return view('inquiries.view', compact('page', 'content_elements', 'inquiry'));
    }

    public function tags()
    {
        return response()->json([
            'tags' => Inquiry::getTags(),
        ]);
    }

    public function livestreams()
    {
        return response()->json([
            'livestreams' => Inquiry::getLivestreams(),
        ]);
    }
}
