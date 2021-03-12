<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

use App\Traits\ContentElementTrait;
use App\Traits\TagsTrait;
use App\Traits\AppendAttributesTrait;

class InquiryForm extends Model
{
    use HasFactory;
    use ContentElementTrait;
    use TagsTrait;
    use AppendAttributesTrait;

    protected $with = ['tags'];
    protected $appends = ['livestreams'];

    public function saveContent(array $input, $id = null)
    {
        if ($id >= 1) {
            $inquiry_form = InquiryForm::findOrFail($id);
        } else {
            $inquiry_form = new InquiryForm;
        }

        $inquiry_form->header = Arr::get($input, 'header');
        $inquiry_form->body = Arr::get($input, 'body');
        $inquiry_form->show_student_info = Arr::get($input, 'show_student_info') ? true : false;
        $inquiry_form->show_interests = Arr::get($input, 'show_interests') ? true : false;
        $inquiry_form->show_livestreams = Arr::get($input, 'show_livestreams') ? true : false;
        $inquiry_form->show_livestreams_first = Arr::get($input, 'show_livestreams_first') ? true : false;
        $inquiry_form->create_password = Arr::get($input, 'create_password') ? true : false;

        $inquiry_form->save();

        $inquiry_form->saveTags($input);

        cache()->tags([cache_name($inquiry_form)])->flush();
        return $inquiry_form;
    }

    public function getLivestreamsAttribute()
    {
        return Livestream::where('start_date', '>=', now())
            ->get()
            ->filter(function ($livestream) {
                if (!$this->tags->count()) {
                    return true;
                }
                return $livestream->tags->intersect($this->tags)->count();
            })
            ->sortBy(function ($livestream) {
                return $livestream->start_date;
            });
    }
}
