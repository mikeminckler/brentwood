<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Inquiry;
use App\Models\Page;

use App\Http\Requests\InquiryValidation;

use App\Traits\PagesControllerTrait;

class InquiriesController extends Controller
{
    use PagesControllerTrait;

    protected $base_view = 'inquiries.create';

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

    public function index() 
    {
        if (!auth()->user()->can('viewAny', Inquiry::class)) {
            return redirect('/')->with(['error' => 'You do not have permission to view that page']);
        }

        return view('inquiries.index');
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

        $page = Inquiry::findPage();

        $content_elements = $page->published_content_elements
                                 ->filter(function($content_element) use ($inquiry) {
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

}
