<?php

namespace App\Traits;

use App\Models\Photo;
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
                $photo = (new Photo)->savePhoto($photo_data, Arr::get($photo_data, 'id'), $this);
            }
        }
    }

    public function saveSinglePhoto($input)
    {
        if (Arr::get($input, 'photos')) {
            $photo_data = collect(Arr::get($input, 'photos'))->first();

            $photo_id = null;
            if ($this->photos->count()) {
                $old_photo = $this->photos->first();
                if (Arr::get($photo_data, 'id') === $old_photo->id) {
                    $photo_id = $old_photo->id;
                } else {
                    $this->photos()->delete();
                }
            }
            return (new Photo)->savePhoto($photo_data, $photo_id, $this);
        }
    }
}
