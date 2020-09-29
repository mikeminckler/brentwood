<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Tag;

class TagValidation extends FormRequest
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
            $tag = Tag::findorFail($this->route('id'));
            return $this->user()->can('update', $tag);
        }
        return $this->user()->can('create', Tag::class);
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
