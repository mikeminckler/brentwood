<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use App\FeaturedPhoto;
use Illuminate\Support\Arr;
use App\PhotosTrait;
use App\ContentElementTrait;
use App\PageLink;

class FeaturedPhoto extends Model
{
    use PhotosTrait;

    use ContentElementTrait;

    protected $with = ['photos'];

    public function saveContent($id = null, $input) 
    {
        if ($id >= 1) {
            $featured_photo = FeaturedPhoto::findOrFail($id);
        } else {
            $featured_photo = new FeaturedPhoto;
        }

        $featured_photo->body = Arr::get($input, 'body');
        $featured_photo->header = Arr::get($input, 'header');

        $featured_photo->save();

        $featured_photo->saveSinglePhoto($input);

        cache()->tags([cache_name($featured_photo)])->flush();
        return $featured_photo;
    }

    public function getBodyAttribute($value) 
    {
        return PageLink::convertLinkText($value);
    }
}
