<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\URL;

use App\Traits\TagsTrait;

use App\Models\Livestream;

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
        return Page::where('slug', 'inquiry-content')->first();
    }

    public static function getTags() 
    {
        
        $tags = self::findPage()
                    ->published_content_elements
                    ->map(function($content_element) {
                        return $content_element->tags;
                    })
                    ->flatten();

        $boarding_tag = Tag::where('name', 'Boarding Student')->first();
        $day_tag = Tag::where('name', 'Day Student')->first();

        $tags->push($boarding_tag);
        $tags->push($day_tag);

        $inquiry_tags = $tags->unique(function($tag) {
                    return $tag->id;
                });

        return Tag::filterWithHierarchy($inquiry_tags);
    }

    public function getFilteredTagsAttribute() 
    {
        $boarding_tag = Tag::where('name', 'Boarding Student')->first();
        $day_tag = Tag::where('name', 'Day Student')->first();

        return $this->tags->filter(function($tag) use ($boarding_tag, $day_tag) {
            return $tag->id !== $boarding_tag->id && $tag->id !== $day_tag->id;
        });
    }

    public function livestreams() 
    {
        return $this->belongsToMany(Livestream::class);   
    }
}
