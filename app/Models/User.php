<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Carbon\Carbon;

use App\Models\Role;
use App\Models\FileUpload;
use App\Models\Permission;

use App\Mail\EmailVerification;

use Laravel\Socialite\Two\User as SocialiteUser;
use Google_Client;
use Google_Service_Directory;

use App\Traits\SearchTrait;
use App\Traits\UsesPermissionsTrait;

class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;
    use SearchTrait;
    use UsesPermissionsTrait;

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

    public function saveUser(array $input, $id = null)
    {
        if ($id) {
            $user = User::findOrFail($id);
        } else {
            $user = new User;
        }

        $user->name = Arr::get($input, 'name');
        $user->email = strtolower(Arr::get($input, 'email'));

        if (!$id) {
            $user->password = Hash::make(Arr::get($input, 'password') ?? Str::random(40));
        } else {
            if (Arr::get($input, 'password') && auth()->check()) {
                if (auth()->user()->can('update', $user)) {
                    $user->password = Hash::make(Arr::get($input, 'password'));
                }
            }
        }

        $user->save();

        if (!$id && Arr::get($input, 'password')) {
            Mail::to($user->email)
                ->queue(new EmailVerification($user));
        }

        if (auth()->check()) {
            if (auth()->user()->hasRole('admin')) {
                $user->roles()->detach();
                if (Arr::get($input, 'roles')) {
                    foreach (Arr::get($input, 'roles') as $role_data) {
                        $user->addRole(Role::findOrFail(Arr::get($role_data, 'id')));
                    }
                }
            }
        }

        cache()->tags([cache_name($user)])->flush();

        return $user;
    }

    public static function findOrCreate($input)
    {
        $id = null;
        $user = User::where('email', Arr::get($input, 'email'))->first();

        if ($user) {
            $id = $user->id;
        }

        return (new User)->saveUser($input, $id);
    }

    public function inquiries()
    {
        return $this->hasMany(Inquiry::class);
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
        return cache()->tags([cache_name($this), cache_name($role)])->rememberForever(cache_name($this).'-has-role-'.cache_name($role), function () use ($role) {
            if (is_int($role)) {
                $role = Role::findOrFail($role);
            }

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
        });
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

    public function canEditPage($objectable)
    {
        if ($this->hasRole('admin')) {
            return true;
        }

        //return cache()->tags([cache_name($this), cache_name($objectable)])->rememberForever(cache_name($this).'-can-access-'.cache_name($objectable), function () use ($objectable) {
        $permissions = $this->permissions;

        $this->roles->each(function ($role) use ($permissions) {
            $role->permissions()->get()->each(function ($pa) use ($permissions) {
                $permissions->push($pa);
            });
        });

        $permissions = $permissions->unique(function ($pa) {
            return class_basename($pa->objectable).$pa->objectable->id;
        });

        return $permissions->contains(function ($permission) use ($objectable) {
            return $permission->objectable_id === $objectable->id && $permission->objectable_type === get_class($objectable);
        });
        //});
    }

    public function whispers()
    {
        return $this->belongsToMany(Chat::class, 'whispers');
    }

    public function getEmailVerificationUrl()
    {
        return URL::temporarySignedRoute('users.verify-email', Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)), [ 'id' => $this->id ]);
    }
}
