<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Arr;

class BannerPhoto extends Model
{
    use PhotosTrait;
    use ContentElementTrait;

    protected $with = ['photos'];

    public function saveContent($id = null, $input) 
    {
        if ($id >= 1) {
            $banner_photo = BannerPhoto::findOrFail($id);
        } else {
            $banner_photo = new BannerPhoto;
        }

        $banner_photo->body = Arr::get($input, 'body');
        $banner_photo->header = Arr::get($input, 'header');

        $banner_photo->save();

        $banner_photo->saveSinglePhoto($input);

        cache()->tags([cache_name($banner_photo)])->flush();
        return $banner_photo;
    }

    public function getBodyAttribute($value) 
    {
        return PageLink::convertLinkText($value);
    }
}
