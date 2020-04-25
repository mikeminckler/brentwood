<?php

namespace App\Policies;

use App\PageAccess;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PageAccessPolicy
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
     * Determine whether the user can view any page accesses.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the page access.
     *
     * @param  \App\User  $user
     * @param  \App\PageAccess  $pageAccess
     * @return mixed
     */
    public function view(User $user, PageAccess $pageAccess)
    {
        //
    }

    /**
     * Determine whether the user can create page accesses.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Determine whether the user can update the page access.
     *
     * @param  \App\User  $user
     * @param  \App\PageAccess  $pageAccess
     * @return mixed
     */
    public function update(User $user, PageAccess $pageAccess)
    {
        //
    }

    /**
     * Determine whether the user can delete the page access.
     *
     * @param  \App\User  $user
     * @param  \App\PageAccess  $pageAccess
     * @return mixed
     */
    public function delete(User $user, PageAccess $pageAccess)
    {
        //
    }

    /**
     * Determine whether the user can restore the page access.
     *
     * @param  \App\User  $user
     * @param  \App\PageAccess  $pageAccess
     * @return mixed
     */
    public function restore(User $user, PageAccess $pageAccess)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the page access.
     *
     * @param  \App\User  $user
     * @param  \App\PageAccess  $pageAccess
     * @return mixed
     */
    public function forceDelete(User $user, PageAccess $pageAccess)
    {
        //
    }
}
