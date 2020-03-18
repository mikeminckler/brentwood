<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\ModelNotFoundException;      

use App\Role;
use App\FileUpload;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function addRole($role)
    {
        if (!$role instanceof Role) {
            $role_name = $role;
            $role = Role::where('name', $role)->first();
        }

        if (!$role instanceof Role) {
            throw new ModelNotFoundException('There is no role with the name '.$role_name);
        }

        if (!$this->roles->contains('id', $role->id)) {
            $this->roles()->attach($role);
        }

        cache()->tags([cache_name($this)])->flush();
        return $this;
    }

    public function removeRole($role)
    {
        if (!$role instanceof Role) {
            $role_name = $role;
            $role = Role::where('name', $role)->first();
        }

        if (!$role instanceof Role) {
            throw new ModelNotFoundException('There is no role with the name '.$role_name);
        }

        $this->roles()->detach($role);

        cache()->tags([cache_name($this)])->flush();
        return $this;
    }

    public function hasRole($role) 
    {
        // TODO cache this
        if (!$role instanceof Role) {
            $role_name = $role;
            $role = Role::where('name', $role)->first();
        }

        if (!$role instanceof Role) {
            throw new ModelNotFoundException('There is no role with the name '.$role_name);
        }

        if ($this->roles->contains('name', 'admin')) {
            return true;
        }

        return $this->roles->contains('id', $role->id);
    }

    public function fileUploads()
    {
        return $this->morphMany(FileUpload::class, 'fileable');
    }
}
