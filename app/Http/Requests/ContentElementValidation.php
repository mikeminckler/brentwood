<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Support\Str;

use App\ContentElement;

class ContentElementValidation extends FormRequest
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
            $content_element = ContentElement::findorFail($this->route('id'));
            return $this->user()->can('update', $content_element);
        }
        return $this->user()->can('create', ContentElement::class);
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {

        if (!requestInput('type')) {
            return [
                'type' => 'required',
            ];
        }

        $content_class = '\App\\Http\\Requests\\'.Str::studly(requestInput('type')).'Validation';

        $rules = collect([
            'page_id' => 'required|exists:pages,id',
            'sort_order' => 'required|integer',
            'unlisted' => 'required|boolean',
        ]);
            
        /*
        if ($this->route('id')) {
            $rules = $rules->merge(
                collect((new $content_class)->rules())->mapWithKeys(function ($rule, $field) {
                    return ['content.'.$field => $rule];
                })
            );
        }
         */

        return $rules->all();
    }
}
