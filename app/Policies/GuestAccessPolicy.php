<?php

namespace App\Policies;

use App\Models\GuestAccess;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class GuestAccessPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Solo admin puede ver los reportes
        return $user->roles->contains('name', 'admin');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, GuestAccess $guestAccess): bool
    {
        // Solo admin puede ver los accesos individuales
        return $user->roles->contains('name', 'admin');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Admin y security pueden crear registros de acceso
        return $user->roles->contains(function ($role) {
            return in_array($role->name, ['admin', 'security']);
        });
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, GuestAccess $guestAccess): bool
    {
        // Solo admin puede actualizar registros
        return $user->roles->contains('name', 'admin');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, GuestAccess $guestAccess): bool
    {
        // Solo admin puede eliminar registros
        return $user->roles->contains('name', 'admin');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, GuestAccess $guestAccess): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, GuestAccess $guestAccess): bool
    {
        return false;
    }
}
