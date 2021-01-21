<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\URL;

use App\Traits\TagsTrait;

class Inquiry extends Model
{
    use HasFactory;
    use TagsTrait;

    public function saveInquiry(array $input, $id = null) 
    {
        if ($id) {
            $inquiry = Inquiry::findOrFail($id);
        } else {
            $inquiry = new Inquiry;
        }

        $inquiry->name = Arr::get($input, 'name');
        $inquiry->email = strtolower(Arr::get($input, 'email'));
        $inquiry->phone = Arr::get($input, 'phone');
        $inquiry->target_grade = Arr::get($input, 'target_grade');
        $inquiry->target_year = Arr::get($input, 'target_year');
        $inquiry->student_type = Arr::get($input, 'student_type');

        $inquiry->save();

        $inquiry->refresh();

        if (!$inquiry->url) {
            $inquiry->url = URL::signedRoute('inquiries.view', ['id' => $inquiry->id]);
            $inquiry->save();
        }

        $inquiry->saveTags($input);

        cache()->tags([cache_name($inquiry)])->flush();

        return $inquiry;
    }

    public static function findPage() 
    {
        return Page::where('slug', 'inquiry')->first();
    }

    public static function getTags() 
    {
        return self::findPage()
                    ->published_content_elements
                    ->map(function($content_element) {
                        return $content_element->tags;
                    })
                    ->flatten()
                    ->unique();
    }
}
