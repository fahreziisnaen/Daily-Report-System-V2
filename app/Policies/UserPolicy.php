<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function view(User $user, User $targetUser): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        if ($user->hasRole('Vice President') || $user->hasRole('Admin Divisi') || $user->hasRole('Human Resource')) {
            return $targetUser->department_id === $user->department_id;
        }

        return $user->id === $targetUser->id;
    }

    public function update(User $user, User $targetUser): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        if ($user->hasRole('Vice President') || $user->hasRole('Admin Divisi')) {
            // Vice President and Admin Divisi cannot update Super Admin users
            if ($targetUser->hasRole('Super Admin')) {
                return false;
            }
            
            // Vice President cannot update other Vice Presidents
            if ($user->hasRole('Admin Divisi') && $targetUser->hasRole('Vice President')) {
                return false;
            }
            
            return $targetUser->department_id === $user->department_id;
        }

        return $user->id === $targetUser->id;
    }

    public function delete(User $user, User $targetUser): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        if ($user->hasRole('Vice President') || $user->hasRole('Admin Divisi')) {
            // Vice President and Admin Divisi cannot delete Super Admin users
            if ($targetUser->hasRole('Super Admin')) {
                return false;
            }
            
            // Admin Divisi cannot delete Vice President users
            if ($user->hasRole('Admin Divisi') && $targetUser->hasRole('Vice President')) {
                return false;
            }
            
            return $targetUser->department_id === $user->department_id;
        }

        return false;
    }

    public function toggleActive(User $user, User $targetUser): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        if ($user->hasRole('Vice President') || $user->hasRole('Admin Divisi')) {
            // Vice President and Admin Divisi cannot disable Super Admin users
            if ($targetUser->hasRole('Super Admin')) {
                return false;
            }
            
            // Admin Divisi cannot deactivate Vice President users
            if ($user->hasRole('Admin Divisi') && $targetUser->hasRole('Vice President')) {
                return false;
            }
            
            return $targetUser->department_id === $user->department_id;
        }

        return false;
    }

    public function updateRole(User $user, User $targetUser): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }
        
        if ($user->hasRole('Vice President')) {
            // Vice President can update roles for users in their department
            // but cannot assign Super Admin or Vice President roles
            return $targetUser->department_id === $user->department_id && 
                  !$targetUser->hasRole(['Super Admin', 'Vice President']);
        }
        
        return false;
    }

    public function before($user, $ability)
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }
    }
} 