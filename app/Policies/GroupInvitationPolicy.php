<?php

namespace App\Policies;

use App\Models\GroupInvitation;
use App\Models\User;

class GroupInvitationPolicy
{

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, GroupInvitation $group): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, GroupInvitation $group): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, GroupInvitation $groupInvitation): bool
    {
        return $groupInvitation->group->user_id === $user->id || $user->is_admin;
    }
}
