<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use App\Quote;
use Illuminate\Support\Arr;
use App\PhotosTrait;
use App\ContentElementTrait;

class Quote extends Model
{
    use PhotosTrait;
    use ContentElementTrait;

    protected $with = ['photos'];

    public function saveContent($id = null, $input) 
    {
        if ($id >= 1) {
            $quote = Quote::findOrFail($id);
        } else {
            $quote = new Quote;
        }

        $quote->body = Arr::get($input, 'body');
        $quote->author_name = Arr::get($input, 'author_name');
        $quote->author_details = Arr::get($input, 'author_details');

        $quote->save();

        $quote->savePhotos($input);

        cache()->tags([cache_name($quote)])->flush();
        return $quote;
    }

}
