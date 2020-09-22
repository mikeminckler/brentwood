<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Support\Str;

use App\Models\ContentElement;

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

        $contentable = ContentElement::findContentable(requestInput());
        return auth()->user()->can('update', $contentable);
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
            'pivot.contentable_id' => 'required',
            'pivot.contentable_type' => 'required',
            'pivot.sort_order' => 'required|integer',
            'pivot.unlisted' => 'required|boolean',
            'pivot.expandable' => 'required|boolean',
        ]);
            
        $rules = $rules->merge(
            collect((new $content_class)->rules())->mapWithKeys(function ($rule, $field) {
                return ['content.'.$field => $rule];
            })
        );

        return $rules->all();
    }
}
