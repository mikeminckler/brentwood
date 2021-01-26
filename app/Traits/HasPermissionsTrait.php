<?php

namespace App\Traits;

use App\Models\Permission;

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
}
