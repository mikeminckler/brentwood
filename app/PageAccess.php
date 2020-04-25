<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use App\Page;

class PageAccess extends Model
{
    public function accessable()
    {
        return $this->morphTo();
    }

    public function page()
    {
        return $this->belongsTo(Page::class);
    }

    public function savePageAccess(Page $page, $object)
    {
        $page_access = new PageAccess;
        $page_access->page_id = $page->id;

        if (!$object->pageAccesses()->get()->contains('page_id', $page->id)) {
            $object->pageAccesses()->save($page_access);
        }

        cache()->tags([cache_name($page), cache_name($object)])->flush();
        return $page_access;
    }

    public function removePageAccess($page, $object)
    {
        $object->pageAccesses()->where('page_id', $page->id)->delete();
        cache()->tags([cache_name($page), cache_name($object)])->flush();
        return $object;
    }
}
