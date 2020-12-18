<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Arr;

use App\Traits\SearchTrait;

use App\Models\PageAccess;
use App\Models\User;

class Role extends Model
{
    use HasFactory;
    use SearchTrait;

    protected $appends = ['search_label'];

    public function saveRole(array $input, $id = null)
    {
        if ($id) {
            $role = Role::findOrFail($id);
        } else {
            $role = new Role;
        }

        $role->name = Arr::get($input, 'name');
        $role->save();

        if (auth()->user()->hasRole('admin')) {
            $role->users()->detach();
            if (Arr::get($input, 'users')) {
                foreach (Arr::get($input, 'users') as $user_data) {
                    $user = User::find(Arr::get($user_data, 'id'));
                    if ($user instanceof User) {
                        $user->addRole($role);
                    }
                }
            }
        }

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

    public function canEditPage($pageable)
    {
        return $this->pageAccesses()
            ->get()
            ->contains(function ($page_access) use ($pageable) {
                return $page_access->pageable_id === $pageable->id && $page_access->pageable_type == get_class($pageable);
            });
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
