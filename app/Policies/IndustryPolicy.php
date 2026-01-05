<?php

namespace App\Policies;

use App\Models\AdminUser;
use App\Models\Industry;
use Illuminate\Auth\Access\HandlesAuthorization;

class IndustryPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the adminUser can view any models.
     */
    public function viewAny(AdminUser $adminUser): bool
    {
        return $adminUser->can('view_any_industry');
    }

    /**
     * Determine whether the adminUser can view the model.
     */
    public function view(AdminUser $adminUser, Industry $industry): bool
    {
        return $adminUser->can('view_industry');
    }

    /**
     * Determine whether the adminUser can create models.
     */
    public function create(AdminUser $adminUser): bool
    {
        return $adminUser->can('create_industry');
    }

    /**
     * Determine whether the adminUser can update the model.
     */
    public function update(AdminUser $adminUser, Industry $industry): bool
    {
        return $adminUser->can('update_industry');
    }

    /**
     * Determine whether the adminUser can delete the model.
     */
    public function delete(AdminUser $adminUser, Industry $industry): bool
    {
        return $adminUser->can('delete_industry');
    }

    /**
     * Determine whether the adminUser can bulk delete.
     */
    public function deleteAny(AdminUser $adminUser): bool
    {
        return $adminUser->can('delete_any_industry');
    }

    /**
     * Determine whether the adminUser can permanently delete.
     */
    public function forceDelete(AdminUser $adminUser, Industry $industry): bool
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
    public function restore(AdminUser $adminUser, Industry $industry): bool
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
    public function replicate(AdminUser $adminUser, Industry $industry): bool
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
