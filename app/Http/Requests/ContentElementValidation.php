<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Support\Str;

use App\ContentElement;
use App\Page;

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

        $page = Page::find(request('pivot.page_id') );

        return auth()->user()->can('update', $page);
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
            'pivot.page_id' => 'required|exists:pages,id',
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
