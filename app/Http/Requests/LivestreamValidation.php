<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

use App\Models\Livestream;

class LivestreamValidation extends FormRequest
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
            $livestream = Livestream::findorFail($this->route('id'));
            return $this->user()->can('update', $livestream);
        }
        return $this->user()->can('create', Livestream::class);
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
            'video_id' => 'required',
            'start_date' => 'required|date',
        ];
    }
}
