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

        // Filter by department for Admin Divisi
        if (auth()->user()->hasRole('Admin Divisi')) {
            $adminDepartmentId = auth()->user()->department_id;
            $query->where('department_id', $adminDepartmentId);
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

        $users = $query->paginate(10)->withQueryString();
        $roles = Role::all();
        $departments = Department::all();

        return view('admin.users.index', compact('users', 'roles', 'departments'));
    }

    public function updateRole(Request $request, User $user)
    {
        // Only Super Admin can update roles
        if (auth()->user()->hasRole('Admin Divisi')) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Only Super Admin can update user roles.');
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
            
            // Admin Divisi can only create users in their department
            $departmentId = $request->department_id;
            if (auth()->user()->hasRole('Admin Divisi')) {
                $departmentId = auth()->user()->department_id;
                
                // Admin Divisi cannot create Super Admin or Admin Divisi users
                if ($request->role === 'Super Admin' || $request->role === 'Admin Divisi') {
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
        // Check if Admin Divisi can delete this user
        if (auth()->user()->hasRole('Admin Divisi')) {
            if ($user->hasRole('Super Admin')) {
                return redirect()->route('admin.users.index')
                    ->with('error', 'You cannot delete Super Admin users.');
            }
            if ($user->department_id !== auth()->user()->department_id) {
                return redirect()->route('admin.users.index')
                    ->with('error', 'You can only delete users from your department.');
            }
        }

        // Prevent deleting self
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'You cannot remove your own account.');
        }

        $user->delete();
        return redirect()->route('admin.users.index')
            ->with('success', 'User has been removed successfully.');
    }

    public function resetPassword(User $user)
    {
        // Check if Admin Divisi can reset password of this user
        if (auth()->user()->hasRole('Admin Divisi')) {
            if ($user->hasRole('Super Admin')) {
                return redirect()->route('admin.users.index')
                    ->with('error', 'You cannot reset password for Super Admin users.');
            }
            if ($user->department_id !== auth()->user()->department_id) {
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
        // Check if Admin Divisi can edit this user
        if (auth()->user()->hasRole('Admin Divisi')) {
            if ($user->hasRole('Super Admin')) {
                return redirect()->route('admin.users.index')
                    ->with('error', 'You cannot edit Super Admin users.');
            }
            if ($user->department_id !== auth()->user()->department_id) {
                return redirect()->route('admin.users.index')
                    ->with('error', 'You can only edit users from your department.');
            }
        }

        $departments = Department::all();
        return view('profile.edit', [
            'user' => $user,
            'canUpdateRole' => true,
            'departments' => $departments
        ]);
    }

    public function update(Request $request, User $user)
    {
        // Check if Admin Divisi can update this user
        if (auth()->user()->hasRole('Admin Divisi')) {
            if ($user->hasRole('Super Admin')) {
                return redirect()->route('admin.users.index')
                    ->with('error', 'You cannot update Super Admin users.');
            }
            if ($user->department_id !== auth()->user()->department_id) {
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

        // Admin dapat mengubah position dan department
        if (auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('Admin Divisi')) {
            $rules['position'] = ['required', 'string', 'max:255'];
            $rules['department_id'] = ['required', 'exists:departments,id'];
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

        $data = [
            'name' => $request->name,
            'homebase' => $request->homebase,
        ];

        // Admin dapat mengubah position dan department
        if (auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('Admin Divisi')) {
            $data['position'] = $request->position;
            $data['department_id'] = $request->department_id;
            
            // Admin Divisi must keep user in the same department
            if (auth()->user()->hasRole('Admin Divisi')) {
                $data['department_id'] = auth()->user()->department_id;
            }
        }

        $user->update($data);

        return redirect()->route('admin.users.index')
            ->with('success', 'User berhasil diupdate');
    }

    public function toggleActive(User $user)
    {
        // Check if Admin Divisi can toggle active status of this user
        if (auth()->user()->hasRole('Admin Divisi')) {
            if ($user->hasRole('Super Admin')) {
                return redirect()->route('admin.users.index')
                    ->with('error', 'You cannot deactivate Super Admin users.');
            }
            if ($user->department_id !== auth()->user()->department_id) {
                return redirect()->route('admin.users.index')
                    ->with('error', 'You can only deactivate users from your department.');
            }
        }

        // Prevent self-deactivation
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'You cannot deactivate your own account.');
        }

        $user->is_active = !$user->is_active;
        $user->inactive_reason = $user->is_active ? null : request('reason');
        $user->save();

        $status = $user->is_active ? 'activated' : 'deactivated';
        return redirect()->back()->with('success', "User has been {$status} successfully.");
    }
} 