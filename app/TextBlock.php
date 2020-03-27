<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Arr;

use App\ContentElement;
use App\ContentElementTrait;
use App\PhotosTrait;
use App\Page;

class TextBlock extends Model
{
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
        $text_block->save();

        $text_block->savePhotos($input);

        cache()->tags([cache_name($text_block)])->flush();
        return $text_block;
    }

    public function getBodyAttribute($value) 
    {
        if (!session()->has('editing')) {

            $replace = [];

            preg_match_all('/href="(\d+)"/', $value, $match);

            $find = collect($match[0])->map(function($string) {
                return '/'.$string.'/';
            })->all();

            foreach ($match[1] as $page_id) {
                $page = Page::find($page_id);
                if ($page instanceof Page) {
                    $replace[] = 'href="/'.$page->full_slug.'"';
                }
            }

            return preg_replace($find, $replace, $value);   
        }
        return $value;
    }
}
