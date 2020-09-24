<?php

namespace App\Traits;

use App\Models\ContentElement;

trait ContentElementTrait
{
    abstract public function saveContent();

    public function contentElement()
    {
        return $this->morphOne(ContentElement::class, 'content');
    }
}
