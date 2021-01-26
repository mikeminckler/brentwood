<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class Permission extends Model
{
    public function accessable()
    {
        return $this->morphTo();
    }

    public function objectable()
    {
        return $this->morphTo();
    }

    public function savePermission($objectable, $accessable)
    {
        // the user/role is the accessable
        // the page/blog/content_element/livestream is the objectable
        
        $permission = new Permission;
        $permission->objectable_id = $objectable->id;
        $permission->objectable_type = get_class($objectable);

        if (!$accessable->permissions()->where('objectable_type', get_class($objectable))->get()->contains('objectable_id', $objectable->id)) {
            $accessable->permissions()->save($permission);
        }

        cache()->tags([cache_name($objectable), cache_name($accessable)])->flush();

        return $permission;
    }

    public function removePageAccess($objectable, $accessable)
    {
        $accessable->objectables()->where('objectable_id', $objectable->id)
                               ->where('objectable_type', get_class($objectable))
                               ->delete();

        cache()->tags([cache_name($objectable), cache_name($accessable)])->flush();

        return $accessable;
    }

    public static function findObjectable($input)
    {
        if (Str::contains(Arr::get($input, 'objectable_type'), 'App\\Models\\')) {
            $class_name = Arr::get($input, 'objectable_type');
        } else {
            $class_name = 'App\\Models\\'.Str::studly(Arr::get($input, 'objectable_type'));
        }

        Validator::make($input, [
            'objectable_id' => ['required', function ($attribute, $value, $fail) use ($input, $class_name) {
                $id_check = resolve($class_name)->find($value);
                if (!$id_check) {
                    $fail('No related object found when saving the content element');
                }
            }],
            'objectable_type' => ['required', function ($attribute, $value, $fail) use ($input, $class_name) {
                $class = resolve($class_name);
                if (!$class) {
                    $fail('No related class found when saving the content element');
                }
            }],
        ])->validate();

        return (new $class_name)->findOrFail(Arr::get($input, 'objectable_id'));
    }
}
