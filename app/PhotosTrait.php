<?php

namespace App;

use App\Photo;
use Illuminate\Support\Arr;

trait PhotosTrait
{
    public function photos() 
    {
        return $this->morphMany(Photo::class, 'content'); 
    }

    public function savePhotos($input) 
    {
        if (Arr::get($input, 'photos')) {
            foreach (Arr::get($input, 'photos') as $photo_data) {
                $photo = (new Photo)->savePhoto(Arr::get($photo_data, 'id'), $photo_data, $this);
            }
        }
    }
}
