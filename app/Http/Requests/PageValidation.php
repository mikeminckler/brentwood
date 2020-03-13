<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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

        if (auth()->user()->hasRole('editor')) {
            return true;
        }
        return false;
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
            'order' => 'required|integer',
        ];
    }
}
