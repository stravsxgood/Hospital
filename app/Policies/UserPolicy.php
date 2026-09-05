<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can view any user models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['super-admin', 'admin']) || in_array($user->role, ['admin', 'super-admin'], true);
    }

    /**
     * Determine whether the user can view the specific user model.
     */
    public function view(User $user, User $model): bool
    {
        return $user->id === $model->id
            || $user->hasRole(['super-admin', 'admin'])
            || in_array($user->role, ['admin', 'super-admin'], true);
    }

    /**
     * Determine whether the user can create user models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole(['super-admin', 'admin']) || in_array($user->role, ['admin', 'super-admin'], true);
    }

    /**
     * Determine whether the user can update the user model.
     */
    public function update(User $user, User $model): bool
    {
        return $user->id === $model->id
            || $user->hasRole(['super-admin', 'admin'])
            || in_array($user->role, ['admin', 'super-admin'], true);
    }

    /**
     * Determine whether the user can delete the target user model.
     *
     * Rules:
     * 1. Self-deletion is strictly forbidden.
     * 2. Only inactive accounts may be deleted (Strict Inactive Check).
     * 3. Current user must possess 'staff.delete' permission or have super-admin / admin privileges.
     */
    public function delete(User $user, User $model): bool
    {
        // 1. Self-deletion guard: Super Admin cannot delete their own account
        if ($user->id === $model->id) {
            return false;
        }

        // 2. Strict inactive check: Only inactive accounts may be deleted
        if ($model->is_active !== false) {
            return false;
        }

        // 3. Authorization check via Spatie permission or role
        return $user->can('staff.delete')
            || $user->hasRole(['super-admin', 'admin'])
            || in_array($user->role, ['admin', 'super-admin'], true);
    }

    /**
     * Determine whether the user can restore the user model.
     */
    public function restore(User $user, User $model): bool
    {
        return $user->hasRole(['super-admin', 'admin']) || in_array($user->role, ['admin', 'super-admin'], true);
    }

    /**
     * Determine whether the user can permanently delete the user model.
     * Only Super Admin can permanently delete patient accounts and records.
     */
    public function forceDelete(User $user, User $model): bool
    {
        // 1. Self-deletion is strictly forbidden
        if ($user->id === $model->id) {
            return false;
        }

        // 2. Only patient accounts can be permanently deleted
        if ($model->role !== 'patient' && ! $model->patient) {
            return false;
        }

        // 3. Super Admin authorization
        return $user->can('staff.delete')
            || $user->hasRole(['super-admin', 'admin'])
            || in_array($user->role, ['admin', 'super-admin'], true);
    }
}
