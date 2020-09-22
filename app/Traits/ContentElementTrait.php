<?php

namespace App\Traits;

trait ContentElementTrait
{
    abstract public function saveContent();

    public function contentElement()
    {
        return $this->morphOne(ContentElement::class, 'content');
    }
}
