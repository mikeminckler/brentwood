<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

use App\Models\Photo;

class PhotoValidation extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if ($this->route('id')) {
            $photo = Photo::findorFail($this->route('id'));
            return $this->user()->can('update', $photo);
        }
        return $this->user()->can('create', Photo::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'file_upload_id' => 'required|exists:file_uploads,id',
            'name' => 'max:255',
            'alt' => 'max:255',
            'sort_order' => 'required|integer',
            'span' => 'required|integer',
            'offsetX' => 'required|integer|min:0|max:100',
            'offsetY' => 'required|integer|min:0|max:100',
        ];
    }
}
