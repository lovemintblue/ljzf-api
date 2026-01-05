<?php

namespace App\Policies;

use App\Models\AdminUser;
use App\Models\ShareCover;
use Illuminate\Auth\Access\HandlesAuthorization;

class ShareCoverPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(AdminUser $user): bool
    {
        return $user->can('view_any_share::cover');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(AdminUser $user, ShareCover $shareCover): bool
    {
        return $user->can('view_share::cover');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(AdminUser $user): bool
    {
        return $user->can('create_share::cover');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(AdminUser $user, ShareCover $shareCover): bool
    {
        return $user->can('update_share::cover');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(AdminUser $user, ShareCover $shareCover): bool
    {
        return $user->can('delete_share::cover');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(AdminUser $user): bool
    {
        return $user->can('delete_any_share::cover');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(AdminUser $user, ShareCover $shareCover): bool
    {
        return $user->can('force_delete_share::cover');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(AdminUser $user): bool
    {
        return $user->can('force_delete_any_share::cover');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(AdminUser $user, ShareCover $shareCover): bool
    {
        return $user->can('restore_share::cover');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(AdminUser $user): bool
    {
        return $user->can('restore_any_share::cover');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(AdminUser $user, ShareCover $shareCover): bool
    {
        return $user->can('replicate_share::cover');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(AdminUser $user): bool
    {
        return $user->can('reorder_share::cover');
    }
}

