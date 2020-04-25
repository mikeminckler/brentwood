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
use App\PageAccess;
use App\SearchTrait;

class User extends Authenticatable
{
    use Notifiable;
    use SearchTrait;

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

    public function saveUser($id = null, $input)
    {
        if ($id) {
            $user = User::findOrFail($id);
        } else {
            $user = new User;
        }

        $user->name = Arr::get($input, 'name');
        $user->email = strtolower(Arr::get($input, 'email'));
        $user->save();

        if (auth()->user()->hasRole('admin')) {
            $user->roles()->detach();
            if (Arr::get($input, 'roles')) {
                foreach (Arr::get($input, 'roles') as $role_data) {
                    $user->addRole( Role::findOrFail(Arr::get($role_data, 'id')));
                }
            }
        }

        cache()->tags([cache_name($user)])->flush();

        return $user;
    }


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
        $client->setSubject('sa_developer@brentwood.ca');

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
        if ($this->hasRole('admin')) {
            return true;
        }

        return cache()->tags([cache_name($this), cache_name($page)])->rememberForever(cache_name($this).'-can-access-'.$page->id, function () use ($page) {
            $page_accesses = $this->pageAccesses;

            $this->roles->each(function ($role) use ($page_accesses) {
                $role->pageAccesses()->get()->each(function ($pa) use ($page_accesses) {
                    $page_accesses->push($pa);
                });
            });

            $page_accesses = $page_accesses->unique(function ($pa) {
                return $pa->page->id;
            });

            if ($page_accesses->contains('page_id', $page->id)) {
                return true;
            }
            return false;
        });
    }
}
