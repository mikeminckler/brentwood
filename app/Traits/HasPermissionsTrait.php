<?php

namespace App\Traits;

use Illuminate\Support\Arr;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;

trait HasPermissionsTrait
{

    /*
     * This trait is used by pages, blogs, content elements, livestreams
     * Users and Roles can be granted permission to these objects (acccessables)
     */

    public function createPermission($accessable)
    {
        $permission = (new Permission)->savePermission($this, $accessable);
        return $this;
    }

    public function removePermission($accessable)
    {
        $permission = (new Permission)->removePermission($this, $accessable);
        return $this;
    }

    public function permissions()
    {
        return $this->morphMany(Permission::class, 'objectable');
    }

    public function getRolesAttribute()
    {
        return $this->permissions()->where('accessable_type', Role::class)->get()->map->accessable;
    }

    public function getUsersAttribute()
    {
        return $this->permissions()->where('accessable_type', User::class)->get()->map->accessable;
    }

    public function saveRoles($input)
    {
        $this->permissions()->where('accessable_type', Role::class)->delete();
        
        if (Arr::get($input, 'roles')) {
            foreach (Arr::get($input, 'roles') as $role_data) {
                $this->createPermission(Role::findOrFail(Arr::get($role_data, 'id')));
            }
        }
        return $this;
    }
}
