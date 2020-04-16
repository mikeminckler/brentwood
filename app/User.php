<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\ModelNotFoundException;      

use App\User;
use App\Role;
use App\FileUpload;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Laravel\Socialite\Two\User as SocialiteUser;
use Google_Client;
use Google_Service_Directory;

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

    public static function createOrUpdateFromGoogle(SocialiteUser $data) 
    {
        $validator = Validator::make([
            'id' => $data->getId(),
            'name' => $data->getName(),
            'email' => $data->getEmail(),
            'avatar' => $data->getAvatar(),
        ], [
            'id' => 'required',
            'email' => 'required|email',
            'name' => 'required:max:255',
        ])->validate();

        $user = User::where('email', $data->getEmail())->first();

        if (!$user instanceof User) {
            $user = new User;
            $user->password = Str::random(40);
        }

        $user->oauth_id = $data->getId();
        $user->email = $data->getEmail();
        $user->name = $data->getName();
        $user->avatar = $data->getAvatar();
        $user->save();

        return $user;

    }

    public function setGroupsFromGoogle() 
    {
        
        $client = new Google_Client();
        $client->setScopes(Google_Service_Directory::ADMIN_DIRECTORY_GROUP_READONLY);
        $client->setAuthConfig(base_path('service.json'));
        $client->setSubject('brent.lee@brentwood.ca');

        $service = new Google_Service_Directory($client);
        $groups = collect($service->groups->listGroups(['domain' => 'brentwood.ca', 'userKey' => $this->email])->groups)->pluck('name', 'id');

        foreach ($groups as $id => $name) {
            $role = Role::where('name', $name)->first();

            if (!$role instanceof Role) {
                $role = new Role;
                $role->name = $name;
                $role->oauth_id = $id;
                $role->save();
            }
            $this->addRole($role);
        }

        return $this;
    }
}
