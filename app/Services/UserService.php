<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

class UserService
{
    /**
     * Get a filtered list of users based on request parameters
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\User $authUser
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getFilteredUsers(Request $request, User $authUser)
    {
        $query = User::with(['roles', 'department'])->latest();

        // Filter by department for Admin Divisi and Vice President
        if ($authUser->hasRole(['Admin Divisi', 'Vice President'])) {
            $query->where('department_id', $authUser->department_id);
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('homebase', 'like', "%{$search}%");
            });
        }

        // Role filter
        if ($request->filled('role')) {
            $query->whereHas('roles', function($q) use ($request) {
                $q->where('name', $request->role);
            });
        }
        
        // Department filter (only for Super Admin, as others are already filtered by department)
        if ($request->filled('department') && $authUser->hasRole('Super Admin')) {
            $query->where('department_id', $request->department);
        }

        return $query->paginate(10)->withQueryString();
    }

    /**
     * Create a new user with the provided data
     *
     * @param array $data
     * @param User $authUser
     * @return User
     */
    public function createUser(array $data, User $authUser): User
    {
        $departmentId = $data['department_id'];
        
        // Admin Divisi and Vice President can only create users in their department
        if ($authUser->hasRole(['Admin Divisi', 'Vice President'])) {
            $departmentId = $authUser->department_id;
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'homebase' => $data['homebase'],
            'position' => $data['position'],
            'department_id' => $departmentId,
            'password' => Hash::make($data['password']),
        ]);

        $user->assignRole($data['role']);

        return $user;
    }

    /**
     * Update a user with the provided data
     *
     * @param User $user
     * @param array $data
     * @param User $authUser
     * @return User
     */
    public function updateUser(User $user, array $data, User $authUser): User
    {
        // Update basic information
        $user->name = $data['name'];
        $user->homebase = $data['homebase'];
        
        // Update email if present
        if (isset($data['email'])) {
            $user->email = $data['email'];
        }

        // Handle avatar upload
        if (isset($data['avatar'])) {
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }
            $user->avatar_path = $data['avatar']->store('avatars', 'public');
        }

        // Handle signature upload
        if (isset($data['signature'])) {
            if ($user->signature_path) {
                Storage::disk('public')->delete($user->signature_path);
            }
            $user->signature_path = $data['signature']->store('signatures', 'public');
        }

        // Update position and department if admin or VP
        if ($authUser->hasRole(['Super Admin', 'Admin Divisi', 'Vice President'])) {
            $user->position = $data['position'];
            
            // Only Super Admin can change department
            if ($authUser->hasRole('Super Admin') && isset($data['department_id'])) {
                $user->department_id = $data['department_id'];
            }
        }

        $user->save();
        return $user;
    }

    /**
     * Toggle user active status
     *
     * @param User $user
     * @param string|null $inactiveReason
     * @return User
     */
    public function toggleActiveStatus(User $user, ?string $inactiveReason = null): User
    {
        $user->is_active = !$user->is_active;
        $user->inactive_reason = $user->is_active ? null : $inactiveReason;
        $user->save();
        
        return $user;
    }

    /**
     * Reset user password to a random string
     *
     * @param User $user
     * @return string The new password
     */
    public function resetPassword(User $user): string
    {
        $password = \Str::random(10);
        $user->password = Hash::make($password);
        $user->save();
        
        return $password;
    }

    /**
     * Check if user can modify role based on their position and the role being assigned
     *
     * @param User $authUser The user attempting to modify the role
     * @param User $targetUser The user whose role is being modified
     * @param string $newRole The new role to be assigned
     * @return bool Whether the user is permitted to make this role change
     */
    public function canModifyRole(User $authUser, User $targetUser, string $newRole): bool
    {
        // Super Admin can modify any role
        if ($authUser->isFullAdmin()) {
            return true;
        }
        
        // Vice President can only update users from their department
        // and cannot assign Super Admin or Vice President roles
        if ($authUser->hasRole('Vice President')) {
            if ($targetUser->department_id !== $authUser->department_id) {
                return false;
            }
            
            if (in_array($newRole, ['Super Admin', 'Vice President'])) {
                return false;
            }
            
            return true;
        }
        
        // Admin Divisi cannot modify roles
        return false;
    }
} 