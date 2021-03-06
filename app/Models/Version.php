<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Support\Arr;

class Version extends Model
{
    use HasFactory;

    protected $dates = ['published_at'];

    public function saveVersion(array $input, $id = null)
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
