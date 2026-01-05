<?php

namespace App\Policies;

use App\Models\AdminUser;
use App\Models\HouseFollowUp;
use Illuminate\Auth\Access\HandlesAuthorization;

class HouseFollowUpPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the adminUser can view any models.
     */
    public function viewAny(AdminUser $adminUser): bool
    {
        return $adminUser->can('view_any_house::follow::up');
    }

    /**
     * Determine whether the adminUser can view the model.
     */
    public function view(AdminUser $adminUser, HouseFollowUp $houseFollowUp): bool
    {
        return $adminUser->can('view_house::follow::up');
    }

    /**
     * Determine whether the adminUser can create models.
     */
    public function create(AdminUser $adminUser): bool
    {
        return $adminUser->can('create_house::follow::up');
    }

    /**
     * Determine whether the adminUser can update the model.
     */
    public function update(AdminUser $adminUser, HouseFollowUp $houseFollowUp): bool
    {
        return $adminUser->can('update_house::follow::up');
    }

    /**
     * Determine whether the adminUser can delete the model.
     */
    public function delete(AdminUser $adminUser, HouseFollowUp $houseFollowUp): bool
    {
        return $adminUser->can('delete_house::follow::up');
    }

    /**
     * Determine whether the adminUser can bulk delete.
     */
    public function deleteAny(AdminUser $adminUser): bool
    {
        return $adminUser->can('delete_any_house::follow::up');
    }

    /**
     * Determine whether the adminUser can permanently delete.
     */
    public function forceDelete(AdminUser $adminUser, HouseFollowUp $houseFollowUp): bool
    {
        return $adminUser->can('{{ ForceDelete }}');
    }

    /**
     * Determine whether the adminUser can permanently bulk delete.
     */
    public function forceDeleteAny(AdminUser $adminUser): bool
    {
        return $adminUser->can('{{ ForceDeleteAny }}');
    }

    /**
     * Determine whether the adminUser can restore.
     */
    public function restore(AdminUser $adminUser, HouseFollowUp $houseFollowUp): bool
    {
        return $adminUser->can('{{ Restore }}');
    }

    /**
     * Determine whether the adminUser can bulk restore.
     */
    public function restoreAny(AdminUser $adminUser): bool
    {
        return $adminUser->can('{{ RestoreAny }}');
    }

    /**
     * Determine whether the adminUser can replicate.
     */
    public function replicate(AdminUser $adminUser, HouseFollowUp $houseFollowUp): bool
    {
        return $adminUser->can('{{ Replicate }}');
    }

    /**
     * Determine whether the adminUser can reorder.
     */
    public function reorder(AdminUser $adminUser): bool
    {
        return $adminUser->can('{{ Reorder }}');
    }
}
