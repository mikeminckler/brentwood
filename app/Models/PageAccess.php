<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageAccess extends Model
{
    public function accessable()
    {
        return $this->morphTo();
    }

    public function pageable()
    {
        return $this->morphTo();
    }

    public function savePageAccess($pagable, $object)
    {
        $page_access = new PageAccess;
        $page_access->pageable_id = $pagable->id;
        $page_access->pageable_type = get_class($pagable);

        if (!$object->pageAccesses()->where('pageable_type', get_class($pagable))->get()->contains('pageable_id', $pagable->id)) {
            $object->pageAccesses()->save($page_access);
        }

        cache()->tags([cache_name($pagable), cache_name($object)])->flush();
        return $page_access;
    }

    public function removePageAccess($pagable, $object)
    {
        $object->pageAccesses()->where('pageable_id', $pagable->id)
                               ->where('pageable_type', get_class($pagable))
                               ->delete();
        cache()->tags([cache_name($pagable), cache_name($object)])->flush();
        return $object;
    }
}
