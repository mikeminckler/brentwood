<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

use App\Models\Permission;

class PermissionValidation extends FormRequest
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
            $permission = Permission::findorFail($this->route('id'));
            return $this->user()->can('update', $permission);
        }
        return $this->user()->can('create', Permission::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'objectable_id' => 'required',
            'objectable_type' => 'required',
            'users' => 'required_without:roles',
            'roles' => 'required_without:users',
            'users.*.id' => 'exists:users,id',
            'roles.*.id' => 'exists:roles,id',
        ];
    }
}
