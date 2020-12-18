<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Support\Arr;

use App\Traits\PhotosTrait;
use App\Traits\ContentElementTrait;

class Quote extends Model
{
    use HasFactory;
    use PhotosTrait;
    use ContentElementTrait;

    protected $with = ['photos'];

    public function saveContent(array $input, $id = null)
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

        $quote->saveSinglePhoto($input);

        cache()->tags([cache_name($quote)])->flush();
        return $quote;
    }
}
