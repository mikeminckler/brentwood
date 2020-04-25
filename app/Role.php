<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use App\SearchTrait;
use App\PageAccess;
use App\Page;
use Illuminate\Support\Arr;

class Role extends Model
{

    use SearchTrait;

    protected $appends = ['search_label'];

    public function getSearchLabelAttribute()
    {
        return $this->name;
    }

    public function getSearchFieldsAttribute()
    {
        return [
            'name',
        ];
    }

    public function saveRole($id = null, $input)
    {
        if ($id) {
            $role = Role::findOrFail($id);
        } else {
            $role = new Role;
        }

        $role->name = Arr::get($input, 'name');
        $role->save();

        return $role;
    }

    public function pageAccesses()
    {
        return $this->morphMany(PageAccess::class, 'accessable');
    }

    public function createPageAccess($page)
    {
        $page_access = (new PageAccess)->savePageAccess($page, $this);
        return $this;
    }

    public function removePageAccess($page)
    {
        $page_access = (new PageAccess)->removePageAccess($page, $this);
        return $this;
    }

    public function canEditPage(Page $page)
    {
        return $this->pageAccesses()
            ->get()
            ->contains('page_id', $page->id);
    }
}
