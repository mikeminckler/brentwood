<?php 

namespace App\Traits;

use Illuminate\Support\Str;

trait HasFooterTrait
{

    abstract public function getFooterFgPhoto();
    abstract public function getFooterBgPhoto();
    abstract public function getFooterColorAttribute($value);

    public function getFooterTextColorAttribute() 
    {
        if (!Str::contains($this->footer_color, ',')) {
            return null;
        }

        $number_total = round(collect(explode(',', $this->footer_color))->sum() / 3);

        if ($number_total < 75) {
            return 'text-gray-200';
        }

        return null;
    }

}
