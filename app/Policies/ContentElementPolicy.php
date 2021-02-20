<?php

namespace App\Policies;

use App\Models\ContentElement;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ContentElementPolicy
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
     * Determine whether the user can view any content elements.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the content element.
     *
     * @param  \App\User  $user
     * @param  \App\ContentElement  $contentElement
     * @return mixed
     */
    public function view(User $user, ContentElement $contentElement)
    {
        //
    }

    /**
     * Determine whether the user can create content elements.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
    }

    /**
     * Determine whether the user can update the content element.
     *
     * @param  \App\User  $user
     * @param  \App\ContentElement  $contentElement
     * @return mixed
     */
    public function update(User $user, ContentElement $contentElement)
    {
    }

    /**
     * Determine whether the user can delete the content element.
     *
     * @param  \App\User  $user
     * @param  \App\ContentElement  $contentElement
     * @return mixed
     */
    public function delete(User $user, ContentElement $contentElement)
    {
        //
    }

    /**
     * Determine whether the user can restore the content element.
     *
     * @param  \App\User  $user
     * @param  \App\ContentElement  $contentElement
     * @return mixed
     */
    public function restore(User $user, ContentElement $contentElement)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the content element.
     *
     * @param  \App\User  $user
     * @param  \App\ContentElement  $contentElement
     * @return mixed
     */
    public function forceDelete(User $user, ContentElement $contentElement)
    {
        //
    }
}
