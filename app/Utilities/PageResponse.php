<?php

namespace App\Utilities;

class PageResponse
{
    public function view($page, $view, array $attributes = null)
    {
        if (!$page) {
            return abort(404);
        }

        // if the page hasn't been published and we are not logged in dont show
        if (!$page->published_at && !auth()->check()) {
            return abort(404);
        }

        if (session()->has('editing') && request('preview')) {
            $content_elements = $page->content_elements;
        } else {
            $content_elements = $page->published_content_elements;
        }

        $page = $this->loadPageAttributes($page);

        $page_vars = [
            'page' => $page,
            'content_elements' => $content_elements,
        ];

        if ($attributes) {
            $page_vars = array_merge($page_vars, $attributes);
        }

        if (request()->wantsJson()) {
            if (request('render')) {
                return response()->json(['html' => view('content', $page_vars)->render() ]);
            }

            return response()->json($page_vars);
        }

        if ($page->editable && !request('preview')) {
            return view('pages.edit', $page_vars);
        } else {
            return view($view, $page_vars);
        }
    }

    public function loadPageAttributes($page)
    {
        if (session()->has('editing')) {
            $page->refresh();
            $page->load('versions');
        }

        $page->appendAttributes();

        return $page;
    }
}
