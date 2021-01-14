<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Arr;

use App\Models\PhotoBlock;
use App\Models\Photo;

use App\Traits\ContentElementTrait;
use App\Traits\PhotosTrait;

use App\Utilities\PageLink;

class PhotoBlock extends Model
{
    use HasFactory;
    use ContentElementTrait;
    use PhotosTrait;

    protected $with = ['photos'];

    public function saveContent(array $input, $id = null, $duplicate = null)
    {
        if ($id >= 1) {
            $photo_block = PhotoBlock::findOrFail($id);
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
        $photo_block->text_style = Arr::get($input, 'text_style');

        $photo_block->save();

        $photo_block->savePhotos($input, $duplicate);

        cache()->tags([cache_name($photo_block)])->flush();
        return $photo_block;
    }

    public function getBodyAttribute($value)
    {
        return PageLink::convertLinkText($value);
    }
}
