<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InquiryValidation extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if ($this->route('id')) {
            if (! request()->hasValidSignature()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|min:3',
            'email' => 'required|email',
            'phone' => 'nullable',
            'password' => 'confirmed|min:6',
            'target_grade' => 'nullable|integer|between:8,12',
            'target_year' => 'nullable|date_format:Y',
            'student_type' => 'nullable',
        ];
    }
}
