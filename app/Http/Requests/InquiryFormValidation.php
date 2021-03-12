<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InquiryFormValidation extends FormRequest
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
            'header' => 'max:255',
            'show_student_info' => 'required|boolean',
            'show_interests' => 'required|boolean',
            'show_livestreams' => 'required|boolean',
            'show_livestreams_first' => 'required|boolean',
            'create_password' => 'required|boolean',
        ];
    }
}
