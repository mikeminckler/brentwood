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

        if ($this->route('id') == 1) {

            return [
                'name' => 'required|string',
                'unlisted' => 'boolean',
                'sort_order' => 'required|integer',
                'parent_page_id' => [
                    'required',
                    'integer',
                    function ($attribute, $value, $fail) {
                        if ($value !== 0) {
                            $fail('The home page parent id must be zero');
                        }
                    }
                ],
            ];
        
        } else {
        
            return [
                'name' => 'required|string',
                'unlisted' => 'boolean',
                'sort_order' => 'required|integer',
                'parent_page_id' => [
                    'required',
                    'integer',
                    'exists:pages,id',
                    'min:1',
                ],
            ];
        
        }

    }
}
