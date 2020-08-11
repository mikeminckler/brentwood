<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

use App\Page;

class Version extends Model
{
    public function saveVersion($id = null, $input) 
    {
        if ($id) {
            $version = Version::findOrFail($id);
        } else {
            $version = new Version;
        }

        $version->name = Arr::get($input, 'name');
        $version->page_id = Page::findOrFail(Arr::get($input, 'page_id'))->id;
        $version->save();

        cache()->tags([cache_name($version)])->flush();
        return $version;    

    }

    public function page() 
    {
        return $this->belongsTo(Page::class);   
    }

    public function publish() 
    {
        $this->published_at = now();
        $this->save();
        return $this;
    }
}
