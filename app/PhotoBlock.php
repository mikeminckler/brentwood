<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use App\PhotoBlock;
use App\Photo;
use Illuminate\Support\Arr;

class PhotoBlock extends Model
{
    public function saveContent($input) 
    {
        $update = false;
        if (Arr::get($input, 'id') >= 1) {
            $photo_block = PhotoBlock::findOrFail(Arr::get($input, 'id'));
            $update = true;
        } else {
            $photo_block = new PhotoBlock;
        }

        $photo_block->columns = Arr::get($input, 'columns');
        $photo_block->height = Arr::get($input, 'height');
        $photo_block->padding = Arr::get($input, 'padding');
        $photo_block->show_text = Arr::get($input, 'show_text');
        $photo_block->header = Arr::get($input, 'header');
        $photo_block->body = Arr::get($input, 'body');
        $photo_block->text_order = Arr::get($input, 'text_order');
        $photo_block->text_span = Arr::get($input, 'text_span');

        $photo_block->save();

        $photo_block->savePhotos($input);

        cache()->tags([cache_name($photo_block)])->flush();
        return $photo_block;
    }

    public function photos() 
    {
        return $this->hasMany(Photo::class);   
    }

    public function savePhotos($input) 
    {
        if (Arr::get($input, 'photos')) {
            foreach (Arr::get($input, 'photos') as $photo_data) {
                $photo_data['photo_block_id'] = $this->id;
                $photo = (new Photo)->savePhoto(Arr::get($photo_data, 'id'), $photo_data);
            }
        }
    }
}
