<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\Department;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index(Request $request)
    {
        $authUser = auth()->user();
        $users = $this->userService->getFilteredUsers($request, $authUser);
        $roles = Role::all();
        $departments = Department::all();

        return view('admin.users.index', compact('users', 'roles', 'departments'));
    }

    public function updateRole(Request $request, User $user)
    {
        $authUser = auth()->user();
        
        $request->validate([
            'role' => 'required|exists:roles,name'
        ]);

        // Check if user has permission to modify role
        if (!$this->userService->canModifyRole($authUser, $user, $request->role)) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You do not have permission to assign this role.');
        }

        $user->syncRoles([$request->role]);

        return redirect()->back()->with('success', 'User role updated successfully');
    }

    public function store(StoreUserRequest $request)
    {
        try {
            $authUser = auth()->user();
            
            // Admin Divisi and Vice President can only create users in their department
            if ($authUser->hasRole(['Admin Divisi', 'Vice President'])) {
                // Admin Divisi cannot create Super Admin, Vice President, or Admin Divisi users
                if ($authUser->hasRole('Admin Divisi') && 
                    in_array($request->role, ['Super Admin', 'Vice President', 'Admin Divisi'])) {
                    return back()
                        ->withErrors(['role' => 'You cannot create users with this role.'])
                        ->withInput()
                        ->with('show-add-user-modal', true);
                }
                
                // Vice President cannot create Super Admin or Vice President users
                if ($authUser->hasRole('Vice President') && 
                    in_array($request->role, ['Super Admin', 'Vice President'])) {
                    return back()
                        ->withErrors(['role' => 'You cannot create users with this role.'])
                        ->withInput()
                        ->with('show-add-user-modal', true);
                }
            }

            $this->userService->createUser($request->validated(), $authUser);

            return redirect()->route('admin.users.index')
                ->with('success', 'User berhasil ditambahkan');
            
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput()
                ->with('show-add-user-modal', true);
        }
    }

    public function destroy(User $user)
    {
        $authUser = auth()->user();
        
        // Check if admin can delete this user using policy
        if (!$authUser->can('delete', $user)) {
            $message = 'You do not have permission to delete this user.';
            
            if ($user->hasRole('Super Admin')) {
                $message = 'You cannot delete Super Admin users.';
            } else if ($user->hasRole('Vice President') && $authUser->hasRole(['Admin Divisi'])) {
                $message = 'You cannot delete Vice President users.';
            } else if ($user->department_id !== $authUser->department_id) {
                $message = 'You can only delete users from your department.';
            }
            
            return redirect()->route('admin.users.index')->with('error', $message);
        }

        // Delete avatar and signature files if they exist
        if ($user->avatar_path) {
            \Storage::disk('public')->delete($user->avatar_path);
        }
        
        if ($user->signature_path) {
            \Storage::disk('public')->delete($user->signature_path);
        }

        $user->delete();
        
        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully');
    }

    public function resetPassword(User $user)
    {
        $this->authorize('update', $user);
        
        $password = $this->userService->resetPassword($user);
        
        return redirect()->back()
            ->with('success', "Password has been reset to: $password");
    }

    public function edit(User $user)
    {
        $this->authorize('update', $user);
        
        $departments = Department::all();
        return view('admin.users.edit', compact('user', 'departments'));
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $this->authorize('update', $user);
        
        $authUser = auth()->user();
        $this->userService->updateUser($user, $request->validated(), $authUser);

        return redirect()->route('admin.users.index')
            ->with('success', 'User profile updated successfully');
    }

    public function toggleActive(Request $request, User $user)
    {
        $this->authorize('toggleActive', $user);
        
        $reason = null;
        if (!$user->is_active) {
            $reason = null; // User is being activated
        } else {
            $request->validate(['inactive_reason' => 'required|string|max:255']);
            $reason = $request->inactive_reason;
        }
        
        $this->userService->toggleActiveStatus($user, $reason);
        
        $status = $user->is_active ? 'activated' : 'deactivated';
        return redirect()->route('admin.users.index')
            ->with('success', "User {$user->name} has been {$status}.");
    }
} 