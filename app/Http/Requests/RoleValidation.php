<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

use App\Role;

class RoleValidation extends FormRequest
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
            $role = Role::findorFail($this->route('id'));
            return $this->user()->can('update', $role);
        }
        return $this->user()->can('create', Role::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required',
        ];
    }
}
