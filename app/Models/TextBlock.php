<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Arr;

use App\Traits\ContentElementTrait;
use App\Traits\PhotosTrait;

use App\Utilities\PageLink;

class TextBlock extends Model
{
    use HasFactory;
    use ContentElementTrait;
    use PhotosTrait;

    protected $with = ['photos'];

    public function saveContent($id = null, $input)
    {
        if ($id >= 1) {
            $text_block = TextBlock::findOrFail($id);
        } else {
            $text_block = new TextBlock;
        }

        $text_block->header = Arr::get($input, 'header');
        $text_block->body = Arr::get($input, 'body');
        $text_block->style = Arr::get($input, 'style');
        $text_block->full_width = Arr::get($input, 'full_width');
        $text_block->stat_number = Arr::get($input, 'stat_number');
        $text_block->stat_name = Arr::get($input, 'stat_name');
        $text_block->save();

        $text_block->saveSinglePhoto($input);

        cache()->tags([cache_name($text_block)])->flush();
        return $text_block;
    }

    public function getBodyAttribute($value)
    {
        return PageLink::convertLinkText($value);
    }
}
