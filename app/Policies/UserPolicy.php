<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function view(User $user, User $targetUser): bool
    {
        if ($user->isFullAdmin()) {
            return true;
        }

        if ($user->isVicePresident() || $user->isDivisiAdmin() || $user->isHumanResource()) {
            return $targetUser->department_id === $user->department_id;
        }

        return $user->id === $targetUser->id;
    }

    public function update(User $user, User $targetUser): bool
    {
        if ($user->isFullAdmin()) {
            return true;
        }

        if ($user->isDepartmentAdmin() && !$user->isFullAdmin()) {
            // Pembatasan akses berdasarkan hierarki role
            if ($targetUser->isFullAdmin()) {
                return false; // Tidak ada yang bisa edit Super Admin
            }
            
            if ($user->isDivisiAdmin()) {
                // Admin Divisi tidak bisa edit VP, Verifikator, HR
                if ($targetUser->isVicePresident() || $targetUser->isVerifikator() || $targetUser->isHumanResource()) {
                    return false;
                }
            }
            
            if ($user->isVicePresident()) {
                // VP tidak bisa edit Super Admin
                if ($targetUser->isFullAdmin()) {
                    return false;
                }
            }
            
            if ($user->isHumanResource()) {
                // HR tidak bisa edit Super Admin
                if ($targetUser->isFullAdmin()) {
                    return false;
                }
            }
            
            if ($user->isVerifikator()) {
                // Verifikator tidak bisa edit VP, HR, Super Admin
                if ($targetUser->isVicePresident() || $targetUser->isHumanResource() || $targetUser->isFullAdmin()) {
                    return false;
                }
            }
            
            // Hanya boleh edit user dalam departemen yang sama
            return $targetUser->department_id === $user->department_id;
        }

        // User biasa hanya bisa edit dirinya sendiri
        return $user->id === $targetUser->id;
    }

    public function delete(User $user, User $targetUser): bool
    {
        if ($user->isFullAdmin()) {
            return true;
        }

        if ($user->isDepartmentAdmin() && !$user->isFullAdmin()) {
            // Pembatasan akses berdasarkan hierarki role
            if ($targetUser->isFullAdmin()) {
                return false; // Tidak ada yang bisa hapus Super Admin
            }
            
            if ($user->isDivisiAdmin()) {
                // Admin Divisi tidak bisa hapus VP, Verifikator, HR
                if ($targetUser->isVicePresident() || $targetUser->isVerifikator() || $targetUser->isHumanResource()) {
                    return false;
                }
            }
            
            if ($user->isVicePresident()) {
                // VP tidak bisa hapus Super Admin
                if ($targetUser->isFullAdmin()) {
                    return false;
                }
            }
            
            if ($user->isHumanResource()) {
                // HR tidak bisa hapus Super Admin
                if ($targetUser->isFullAdmin()) {
                    return false;
                }
            }
            
            if ($user->isVerifikator()) {
                // Verifikator tidak bisa hapus VP, HR, Super Admin
                if ($targetUser->isVicePresident() || $targetUser->isHumanResource() || $targetUser->isFullAdmin()) {
                    return false;
                }
            }
            
            // Hanya boleh hapus user dalam departemen yang sama
            return $targetUser->department_id === $user->department_id;
        }

        return false;
    }

    public function toggleActive(User $user, User $targetUser): bool
    {
        if ($user->isFullAdmin()) {
            return true;
        }

        if ($user->isDepartmentAdmin() && !$user->isFullAdmin()) {
            // Pembatasan akses berdasarkan hierarki role
            if ($targetUser->isFullAdmin()) {
                return false; // Tidak ada yang bisa nonaktifkan Super Admin
            }
            
            if ($user->isDivisiAdmin()) {
                // Admin Divisi tidak bisa nonaktifkan VP, Verifikator, HR
                if ($targetUser->isVicePresident() || $targetUser->isVerifikator() || $targetUser->isHumanResource()) {
                    return false;
                }
            }
            
            if ($user->isVicePresident()) {
                // VP tidak bisa nonaktifkan Super Admin
                if ($targetUser->isFullAdmin()) {
                    return false;
                }
            }
            
            if ($user->isHumanResource()) {
                // HR tidak bisa nonaktifkan Super Admin
                if ($targetUser->isFullAdmin()) {
                    return false;
                }
            }
            
            if ($user->isVerifikator()) {
                // Verifikator tidak bisa nonaktifkan VP, HR, Super Admin
                if ($targetUser->isVicePresident() || $targetUser->isHumanResource() || $targetUser->isFullAdmin()) {
                    return false;
                }
            }
            
            // Hanya boleh nonaktifkan user dalam departemen yang sama
            return $targetUser->department_id === $user->department_id;
        }

        return false;
    }

    public function updateRole(User $user, User $targetUser): bool
    {
        if ($user->isFullAdmin()) {
            return true;
        }
        
        if ($user->isVicePresident()) {
            // VP tidak bisa mengubah role Super Admin
            if ($targetUser->isFullAdmin()) {
                return false;
            }
            
            // VP hanya bisa mengubah role user dalam departemen yang sama
            // dan tidak bisa membuat VP baru atau HR baru
            return $targetUser->department_id === $user->department_id && 
                  !in_array($targetUser->roles->first()->name, ['Super Admin', 'Vice President', 'Human Resource']);
        }
        
        // Role lainnya tidak bisa mengubah role user
        return false;
    }

    public function before($user, $ability)
    {
        if ($user->isFullAdmin()) {
            return true;
        }
    }
} 