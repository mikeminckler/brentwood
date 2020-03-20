<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PhotoBlockValidation extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
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
            //'photos' => 'required',
            'columns' => 'required|integer',
            'height' => 'required|integer',
            'padding' => 'required|boolean',
            'show_text' => 'required|boolean',
            'header' => 'string|nullable',
            'body' => 'string|nullable',
            'text_order' => 'integer|nullable',
            'text_span' => 'integer|nullable',
            'text_style' => 'string|nullable',
        ];
    }
}
