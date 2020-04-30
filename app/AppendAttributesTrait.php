<?php
  
namespace App;

trait AppendAttributesTrait
{
    public function appendAttributes($attributes = null)
    {

        if (!$attributes) {
            $attributes = $this->append_attributes;
        }

        if (!is_array($attributes) && $attributes) {
            $attributes = [$attributes];
        }

        if (is_array($attributes)) {
            return $this->append($attributes);
        }
        return $this;
    }

}
