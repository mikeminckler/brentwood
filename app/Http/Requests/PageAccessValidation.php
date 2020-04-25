<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PageAccessValidation extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // TODO can probably replace this with a policy
        return auth()->user()->hasRole('admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'page_id' => 'required|exists:pages,id',
            'users' => 'required_without:roles',
            'roles' => 'required_without:users',
            'users.*.id' => 'exists:users,id',
            'roles.*.id' => 'exists:roles,id',
        ];
    }
}
