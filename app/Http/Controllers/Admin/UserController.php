<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\Department;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['roles', 'department'])->latest();
        $authUser = auth()->user();

        // Filter by department for Admin Divisi and Vice President
        if ($authUser->hasRole(['Admin Divisi', 'Vice President'])) {
            $departmentId = $authUser->department_id;
            $query->where('department_id', $departmentId);
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

        $users = $query->paginate(10)->withQueryString();
        $roles = Role::all();
        $departments = Department::all();

        return view('admin.users.index', compact('users', 'roles', 'departments'));
    }

    public function updateRole(Request $request, User $user)
    {
        $authUser = auth()->user();
        
        // Only Super Admin and Vice President can update roles
        if ($authUser->hasRole('Admin Divisi')) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Only Super Admin or Vice President can update user roles.');
        }
        
        // Vice President can only update users from their department
        if ($authUser->hasRole('Vice President') && $user->department_id !== $authUser->department_id) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You can only update users from your department.');
        }
        
        // Vice President cannot assign Super Admin or Vice President roles
        if ($authUser->hasRole('Vice President') && 
            ($request->role === 'Super Admin' || $request->role === 'Vice President')) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You cannot assign Super Admin or Vice President roles.');
        }

        $validated = $request->validate([
            'role' => 'required|exists:roles,name'
        ]);

        $user->syncRoles([$validated['role']]);

        return redirect()->back()->with('success', 'User role updated successfully');
    }

    public function store(Request $request)
    {
        try {
            $rules = [
                'name' => ['required', 'string', 'max:255'],
                'email' => [
                    'required', 
                    'string', 
                    'email', 
                    'max:255',
                    'unique:users,email'
                ],
                'homebase' => ['required', 'string', 'max:255'],
                'position' => ['required', 'string', 'max:255'],
                'department_id' => ['required', 'exists:departments,id'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
                'role' => ['required', 'exists:roles,name'],
            ];
            
            // Additional validation messages
            $messages = [
                'email.unique' => 'Email sudah digunakan oleh user lain.',
                'email.required' => 'Email harus diisi.',
                'email.email' => 'Format email tidak valid.',
                'name.required' => 'Nama harus diisi.',
                'homebase.required' => 'Homebase harus diisi.',
                'position.required' => 'Jabatan harus diisi.',
                'department_id.required' => 'Department harus diisi.',
                'department_id.exists' => 'Department tidak valid.',
                'password.required' => 'Password harus diisi.',
                'password.min' => 'Password minimal 8 karakter.',
            ];

            $request->validate($rules, $messages);
            
            $authUser = auth()->user();
            $departmentId = $request->department_id;
            
            // Admin Divisi and Vice President can only create users in their department
            if ($authUser->hasRole(['Admin Divisi', 'Vice President'])) {
                $departmentId = $authUser->department_id;
                
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

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'homebase' => $request->homebase,
                'position' => $request->position,
                'department_id' => $departmentId,
                'password' => Hash::make($request->password),
            ]);

            $user->assignRole($request->role);

            return redirect()->route('admin.users.index')
                ->with('success', 'User berhasil ditambahkan');
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()
                ->withErrors($e->validator)
                ->withInput()
                ->with('show-add-user-modal', true);
        }
    }

    public function destroy(User $user)
    {
        $authUser = auth()->user();
        
        // Check permissions for Admin Divisi and Vice President
        if ($authUser->hasRole(['Admin Divisi', 'Vice President'])) {
            // Cannot delete Super Admin users
            if ($user->hasRole('Super Admin')) {
                return redirect()->route('admin.users.index')
                    ->with('error', 'You cannot delete Super Admin users.');
            }
            
            // Vice President cannot delete other Vice President users
            if ($authUser->hasRole('Vice President') && $user->hasRole('Vice President')) {
                return redirect()->route('admin.users.index')
                    ->with('error', 'You cannot delete other Vice President users.');
            }
            
            // Admin Divisi cannot delete Vice President users
            if ($authUser->hasRole('Admin Divisi') && $user->hasRole('Vice President')) {
                return redirect()->route('admin.users.index')
                    ->with('error', 'You cannot delete Vice President users.');
            }
            
            // Can only delete users from their department
            if ($user->department_id !== $authUser->department_id) {
                return redirect()->route('admin.users.index')
                    ->with('error', 'You can only delete users from your department.');
            }
        }

        // Prevent deleting self
        if ($user->id === $authUser->id) {
            return redirect()->back()->with('error', 'You cannot remove your own account.');
        }

        $user->delete();
        return redirect()->route('admin.users.index')
            ->with('success', 'User has been removed successfully.');
    }

    public function resetPassword(User $user)
    {
        $authUser = auth()->user();
        
        // Check permissions for Admin Divisi and Vice President
        if ($authUser->hasRole(['Admin Divisi', 'Vice President'])) {
            // Cannot reset password for Super Admin users
            if ($user->hasRole('Super Admin')) {
                return redirect()->route('admin.users.index')
                    ->with('error', 'You cannot reset password for Super Admin users.');
            }
            
            // Vice President cannot reset password for other Vice President users
            if ($authUser->hasRole('Vice President') && $user->hasRole('Vice President') && $user->id !== $authUser->id) {
                return redirect()->route('admin.users.index')
                    ->with('error', 'You cannot reset password for other Vice President users.');
            }
            
            // Admin Divisi cannot reset password for Vice President users
            if ($authUser->hasRole('Admin Divisi') && $user->hasRole('Vice President')) {
                return redirect()->route('admin.users.index')
                    ->with('error', 'You cannot reset password for Vice President users.');
            }
            
            // Can only reset password for users from their department
            if ($user->department_id !== $authUser->department_id) {
                return redirect()->route('admin.users.index')
                    ->with('error', 'You can only reset passwords for users from your department.');
            }
        }

        $validated = request()->validate([
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        // Reset password to new password
        $user->update([
            'password' => Hash::make($validated['new_password'])
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Password has been reset successfully');
    }

    public function edit(User $user)
    {
        $authUser = auth()->user();
        
        // Check permissions for Admin Divisi and Vice President
        if ($authUser->hasRole(['Admin Divisi', 'Vice President'])) {
            // Cannot edit Super Admin users
            if ($user->hasRole('Super Admin')) {
                return redirect()->route('admin.users.index')
                    ->with('error', 'You cannot edit Super Admin users.');
            }
            
            // Vice President cannot edit other Vice President users
            if ($authUser->hasRole('Vice President') && $user->hasRole('Vice President') && $user->id !== $authUser->id) {
                return redirect()->route('admin.users.index')
                    ->with('error', 'You cannot edit other Vice President users.');
            }
            
            // Admin Divisi cannot edit Vice President users
            if ($authUser->hasRole('Admin Divisi') && $user->hasRole('Vice President')) {
                return redirect()->route('admin.users.index')
                    ->with('error', 'You cannot edit Vice President users.');
            }
            
            // Can only edit users from their department
            if ($user->department_id !== $authUser->department_id) {
                return redirect()->route('admin.users.index')
                    ->with('error', 'You can only edit users from your department.');
            }
        }

        $departments = Department::all();
        return view('profile.edit', [
            'user' => $user,
            'canUpdateRole' => $authUser->hasRole(['Super Admin', 'Vice President']),
            'departments' => $departments
        ]);
    }

    public function update(Request $request, User $user)
    {
        $authUser = auth()->user();
        
        // Check permissions for Admin Divisi and Vice President
        if ($authUser->hasRole(['Admin Divisi', 'Vice President'])) {
            // Cannot update Super Admin users
            if ($user->hasRole('Super Admin')) {
                return redirect()->route('admin.users.index')
                    ->with('error', 'You cannot update Super Admin users.');
            }
            
            // Vice President cannot update other Vice President users
            if ($authUser->hasRole('Vice President') && $user->hasRole('Vice President') && $user->id !== $authUser->id) {
                return redirect()->route('admin.users.index')
                    ->with('error', 'You cannot update other Vice President users.');
            }
            
            // Admin Divisi cannot update Vice President users
            if ($authUser->hasRole('Admin Divisi') && $user->hasRole('Vice President')) {
                return redirect()->route('admin.users.index')
                    ->with('error', 'You cannot update Vice President users.');
            }
            
            // Can only update users from their department
            if ($user->department_id !== $authUser->department_id) {
                return redirect()->route('admin.users.index')
                    ->with('error', 'You can only update users from your department.');
            }
        }

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'homebase' => ['required', 'string', 'max:255'],
            'avatar' => ['nullable', 'image', 'max:1024'],
            'signature' => ['nullable', 'image', 'max:1024'],
        ];

        // Admin, VP dapat mengubah position dan department
        if ($authUser->hasRole(['Super Admin', 'Admin Divisi', 'Vice President'])) {
            $rules['position'] = ['required', 'string', 'max:255'];
            
            // Super Admin can change department
            if ($authUser->hasRole('Super Admin')) {
                $rules['department_id'] = ['required', 'exists:departments,id'];
            } 
            // VP and Admin Divisi can't change department (must stay in their own department)
            else {
                $request->merge(['department_id' => $authUser->department_id]);
            }
        }

        $request->validate($rules);

        if ($request->hasFile('avatar')) {
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }
            $user->avatar_path = $request->file('avatar')->store('avatars', 'public');
        }

        if ($request->hasFile('signature')) {
            if ($user->signature_path) {
                Storage::disk('public')->delete($user->signature_path);
            }
            $user->signature_path = $request->file('signature')->store('signatures', 'public');
        }

        $user->update([
            'name' => $request->name,
            'homebase' => $request->homebase,
        ]);

        // Update position and department if admin or VP
        if ($authUser->hasRole(['Super Admin', 'Admin Divisi', 'Vice President'])) {
            $user->position = $request->position;
            
            // Only Super Admin can change department
            if ($authUser->hasRole('Super Admin')) {
                $user->department_id = $request->department_id;
            }
            
            $user->save();
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'User profile updated successfully');
    }

    public function toggleActive(User $user)
    {
        $authUser = auth()->user();
        
        // Check permissions for Admin Divisi and Vice President
        if ($authUser->hasRole(['Admin Divisi', 'Vice President'])) {
            // Cannot toggle active status for Super Admin users
            if ($user->hasRole('Super Admin')) {
                return redirect()->route('admin.users.index')
                    ->with('error', 'You cannot change status for Super Admin users.');
            }
            
            // Vice President cannot toggle other Vice President users
            if ($authUser->hasRole('Vice President') && $user->hasRole('Vice President') && $user->id !== $authUser->id) {
                return redirect()->route('admin.users.index')
                    ->with('error', 'You cannot change status for other Vice President users.');
            }
            
            // Admin Divisi cannot toggle Vice President users
            if ($authUser->hasRole('Admin Divisi') && $user->hasRole('Vice President')) {
                return redirect()->route('admin.users.index')
                    ->with('error', 'You cannot change status for Vice President users.');
            }
            
            // Can only toggle users from their department
            if ($user->department_id !== $authUser->department_id) {
                return redirect()->route('admin.users.index')
                    ->with('error', 'You can only change status for users from your department.');
            }
        }

        $user->is_active = !$user->is_active;
        $user->save();
        
        $status = $user->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "User has been {$status} successfully.");
    }
} 