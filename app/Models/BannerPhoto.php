<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use App\Traits\PhotosTrait;
use App\Traits\ContentElementTrait;

use Illuminate\Support\Arr;
use App\Utilities\PageLink;

class BannerPhoto extends Model
{
    use HasFactory;
    use PhotosTrait;
    use ContentElementTrait;

    protected $with = ['photos'];

    public function saveContent(array $input, $id = null)
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
