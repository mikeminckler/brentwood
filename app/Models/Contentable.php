<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\ContentElement;
use App\Models\Version;

class Contentable extends Model
{
    use HasFactory;

    public function contentElement() 
    {
        return $this->belongsTo(ContentElement::class);   
    }

    public function pageable() 
    {
        return $this->morphTo('contentable');   
    }

    public function version() 
    {
        return $this->belongsTo(Version::class);
    }

}
