<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Wishlist;

class WishlistPolicy
{
//    /**
//     * Perform pre-authorization checks.
//     */
//    public function before(User $user, string $ability): bool|null
//    {
//        if ($user->isAnActiveAdmin()) {
//            return true;
//        }
//
//        return null;
//    }

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
    public function view(User $user, Wishlist $wishlist): bool
    {
        return $wishlist->user_id === $user->id || $wishlist->is_shared;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Wishlist $wishlist): bool
    {
        return $wishlist->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Wishlist $wishlist): bool
    {
        return $wishlist->user_id === $user->id;
    }

//    /**
//     * Determine whether the user can restore the model.
//     */
//    public function restore(User $user, Wishlist $model): bool
//    {
//        return false;
//    }

//    /**
//     * Determine whether the user can permanently delete the model.
//     */
//    public function forceDelete(User $user, Wishlist $model): bool
//    {
//        return false;
//    }

    public function duplicate(User $user, Wishlist $wishlist): bool
    {
        return $wishlist->user_id === $user->id || $wishlist->can_be_duplicated;
    }
}
