<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use App\EmbedCode;
use App\ContentElementTrait;
use Illuminate\Support\Arr;

class EmbedCode extends Model
{
    use ContentElementTrait;

    public function saveContent($id = null, $input) 
    {
        if ($id >= 1) {
            $embed_code = EmbedCode::findOrFail($id);
        } else {
            $embed_code = new EmbedCode;
        }

        $embed_code->code = Arr::get($input, 'code');
        $embed_code->save();

        cache()->tags([cache_name($embed_code)])->flush();
        return $embed_code;
    }


}
