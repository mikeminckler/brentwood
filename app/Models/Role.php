<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Arr;

use App\Traits\SearchTrait;
use App\Traits\UsesPermissionsTrait;

use App\Models\PageAccess;
use App\Models\User;

class Role extends Model
{
    use HasFactory;
    use SearchTrait;
    use UsesPermissionsTrait;

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
                        cache()->tags([cache_name($user)])->flush();
                    }
                }
            }
        }

        return $role;
    }

    public function canEditPage($objectable)
    {
        return $this->permissions()
            ->get()
            ->contains(function ($permission) use ($objectable) {
                return $permission->objectable_id === $objectable->id && $permission->objectable_type == get_class($objectable);
            });
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
