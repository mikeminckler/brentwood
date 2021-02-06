<?php

namespace App\Policies;

use App\Models\Livestream;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LivestreamPolicy
{
    use HandlesAuthorization;

    /**
     * If we are admin return true right away
     */
    public function before($user, $ability)
    {
        if ($user->hasRole('admin')) {
            return true;
        }
    }

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Livestream  $livestream
     * @return mixed
     */
    public function view(User $user, Livestream $livestream)
    {
        $role_check = $user->roles->intersect($livestream->roles)->count();
        $user_check = $livestream->users->firstWhere('id', auth()->user()->id);
        $inquiry_check = $livestream->inquiry_users->firstWhere('id', auth()->user()->id);
        return !$role_check && !$user_check && !$inquiry_check ? false : true;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Livestream  $livestream
     * @return mixed
     */
    public function update(User $user, Livestream $livestream)
    {
        //
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Livestream  $livestream
     * @return mixed
     */
    public function delete(User $user, Livestream $livestream)
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Livestream  $livestream
     * @return mixed
     */
    public function restore(User $user, Livestream $livestream)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Livestream  $livestream
     * @return mixed
     */
    public function forceDelete(User $user, Livestream $livestream)
    {
        //
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Livestream  $livestream
     * @return mixed
     */
    public function chat(User $user, Livestream $livestream)
    {
        return $user->can('view', $livestream);
    }

    public function sendReminderEmails(User $user, Livestream $livestream)
    {
        //
    }
}
