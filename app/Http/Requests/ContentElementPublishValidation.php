<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

use App\Models\ContentElement;

class ContentElementPublishValidation extends FormRequest
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

        $type = requestInput('pivot.contentable_type');

        if (!$type) {
            return false;
        }
        return auth()->user()->hasRole(Str::plural($type).'-publisher');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'pivot.contentable_id' => 'required|integer',
            'pivot.contentable_type' => 'required|string',
        ];
    }
}
