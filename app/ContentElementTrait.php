<?php

namespace App;

trait ContentElementTrait
{

    abstract public function saveContent();

    public function contentElement() 
    {
        return $this->morphOne(ContentElement::class, 'content');
    }

}
