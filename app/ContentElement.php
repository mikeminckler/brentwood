<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

use App\Page;

use App\TextBlock;

class ContentElement extends Model
{
    public function saveContentElement($id = null, $input, Page $page) 
    {
        $update = false;
        if ($id) {
            $content_element = ContentElement::findOrFail($id);
            $update = true;
        } else {
            $content_element = new ContentElement;
        }

        $content_element->page_id = $page->id;

        $content_class = 'App\\'.Str::studly(Arr::get($input, 'type'));

        $content = (new $content_class)->saveContent(Arr::get($input, 'content_data'));

        $content_element->content_id = $content->id;
        $content_element->content_type = get_class($content);

        // always draft?
        $content_element->version_id = $page->getDraftVersion()->id;

        $content_element->save();

        cache()->tags([cache_name($content_element)])->flush();
        return $content_element;
    }

    public function page() 
    {
        return $this->belongsTo(Page::class);   
    }

    public function content() 
    {
        return $this->morphTo();   
    }

    public function getTypeAttribute() 
    {
        return Str::kebab(class_basename($this->content));
    }
}
