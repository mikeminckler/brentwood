<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

use App\Page;

class PageValidation extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if (!auth()->check()) {
            return false;
        }

        if ($this->route('id')) {
            $page = Page::findorFail($this->route('id'));
            return $this->user()->can('update', $page);
        }
        return $this->user()->can('create', Page::class);

    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string',
            'parent_page_id' => 'required|integer|min:1|exists:pages,id',
            'unlisted' => 'boolean',
            'sort_order' => 'required|integer',
        ];
    }
}
