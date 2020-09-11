<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class Version extends Model
{
    protected $dates = ['published_at'];

    public function saveVersion($id = null, $input)
    {
        if ($id) {
            $version = Version::findOrFail($id);
        } else {
            $version = new Version;
        }

        $version->name = Arr::get($input, 'name');

        // TODO we should validate if the morph exits
        $version->versionable_type = Arr::get($input, 'versionable_type');
        $version->versionable_id = Arr::get($input, 'versionable_id');
        $version->save();

        cache()->tags([cache_name($version)])->flush();
        return $version;
    }

    public function versionable()
    {
        return $this->morphTo();
    }

    public function publish()
    {
        $this->published_at = now();
        $this->save();
        return $this;
    }
}
