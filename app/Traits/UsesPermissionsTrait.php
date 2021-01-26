<?php 

namespace App\Traits;

use App\Models\Permission;

trait UsesPermissionsTrait
{

    /*
     * This trait is used by users and roles
     * They can be granted permission to pages, blogs, content elements, livestreams (objectables);
     */

    public function createPermission($objectable)
    {
        $permission = (new Permission)->savePermission($objectable, $this);
        return $this;
    }

    public function removePermission($objectable)
    {
        $permission = (new Permission)->removePermission($objectable, $this);
        return $this;
    }

    public function permissions()
    {
        return $this->morphMany(Permission::class, 'accessable');
    }

}
