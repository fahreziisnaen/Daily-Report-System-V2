<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-white leading-tight">
                {{ __('User Management') }}
            </h2>
            <div class="flex gap-2">
                @role('Super Admin')
                <a href="{{ route('admin.departments.index') }}" 
                    class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                    Manage Department
                </a>
                @endrole
            <button 
                type="button"
                x-data=""
                @click="$dispatch('open-modal', 'add-user')" 
                class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Add User
            </button>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Search/Filter Section -->
            <div class="mb-4">
                <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-col sm:flex-row gap-4">
                    <div class="flex-1">
                        <input type="text" 
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Search by name, email or position..." 
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div class="w-full sm:w-auto">
                        <select name="role" 
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            onchange="this.form.submit()">
                            <option value="">All Roles</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}" {{ request('role') == $role->name ? 'selected' : '' }}>
                                    {{ ucfirst($role->name) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="w-full sm:w-auto flex gap-2">
                        <button type="submit" 
                            class="flex-1 sm:flex-none px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                            Search
                        </button>
                        @if(request()->hasAny(['search', 'role']))
                            <a href="{{ route('admin.users.index') }}" 
                                class="flex-1 sm:flex-none px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 text-center">
                                Clear
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Users Table - Responsive -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Pekerja</th>
                            <th class="hidden md:table-cell px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Position</th>
                            <th class="hidden md:table-cell px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Department</th>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($users as $user)
                        <tr class="hover:bg-gray-50">
                            <td class="px-3 py-2">
                                <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                <div class="text-sm text-gray-500 md:hidden">{{ $user->email }}</div>
                                <div class="text-sm text-gray-500 md:hidden">{{ $user->position ?: '-' }}</div>
                                <div class="text-sm text-gray-500 md:hidden">{{ $user->department->name ?? '-' }}</div>
                            </td>
                            <td class="hidden md:table-cell px-3 py-2 text-sm">{{ $user->position ?: '-' }}</td>
                            <td class="hidden md:table-cell px-3 py-2 text-sm">{{ $user->department->name ?? '-' }}</td>
                            <td class="px-3 py-2 text-center">
                                @role('Super Admin')
                                <div x-data="{ 
                                    newRole: '{{ $user->roles->first()->name }}',
                                    oldRole: '{{ $user->roles->first()->name }}',
                                    updateRole() {
                                        if (this.newRole !== this.oldRole) {
                                            if (confirm('Are you sure you want to change the role?')) {
                                                $refs.roleForm.submit();
                                        } else {
                                                this.newRole = this.oldRole;
                                            }
                                        }
                                    }
                                }">
                                    <form x-ref="roleForm" 
                                        action="{{ route('admin.users.update-role', $user) }}" 
                                        method="POST" 
                                        class="inline">
                                        @csrf
                                        @method('PUT')
                                        <select name="role" 
                                            x-model="newRole"
                                            @change="updateRole()"
                                            class="w-full min-w-[150px] rounded-md border-gray-300 text-sm">
                                            @foreach($roles as $role)
                                                <option value="{{ $role->name }}" 
                                                    {{ $user->roles->first()->name === $role->name ? 'selected' : '' }}>
                                                    {{ ucfirst($role->name) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </form>
                                </div>
                                @else
                                <span class="px-2 py-1 text-xs rounded-full inline-block min-w-[150px] w-full text-center
                                    @if($user->roles->first()->name === 'Super Admin')
                                        bg-red-100 text-red-800
                                    @elseif($user->roles->first()->name === 'Admin Divisi')
                                        bg-blue-100 text-blue-800
                                    @else
                                        bg-green-100 text-green-800
                                    @endif">
                                    {{ ucfirst($user->roles->first()->name) }}
                                </span>
                                @endrole
                            </td>
                            <td class="px-3 py-2">
                                <span class="px-2 py-1 text-sm rounded-full {{ $user->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="px-3 py-2 text-right">
                                <div class="flex items-center justify-end gap-3">
                                    <!-- Edit User -->
                                    @if(!auth()->user()->hasRole('Admin Divisi') || !$user->hasRole('Super Admin'))
                                    <a href="{{ route('admin.users.edit', $user) }}" 
                                        class="text-blue-600 hover:text-blue-900"
                                        title="Edit User">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </a>

                                    <!-- Reset Password -->
                                    <button type="button"
                                        @click="$dispatch('open-modal', 'reset-password-{{ $user->id }}')" 
                                        class="text-yellow-600 hover:text-yellow-900"
                                        title="Reset Password">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                                        </svg>
                                    </button>

                                    <!-- Activate/Deactivate User -->
                                    @if($user->is_active)
                                        <button type="button" 
                                            @click="$dispatch('open-modal', 'deactivate-user-{{ $user->id }}')" 
                                            class="text-orange-600 hover:text-orange-900"
                                            title="Deactivate User">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                                            </svg>
                                        </button>
                                    @else
                                        <form action="{{ route('admin.users.toggle-active', $user) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" 
                                                class="text-green-600 hover:text-green-900" 
                                                title="Activate User">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                    
                                    <!-- Delete User -->
                                    <button type="button"
                                        @click="$dispatch('open-modal', 'delete-user-{{ $user->id }}')"
                                            class="text-red-600 hover:text-red-900"
                                            title="Delete User">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    @else
                                    <!-- Show disabled buttons for Super Admin users when viewed by Admin Divisi -->
                                    <span class="text-gray-400 cursor-not-allowed" title="You cannot edit Super Admin users">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </span>
                                    <span class="text-gray-400 cursor-not-allowed" title="You cannot reset password for Super Admin users">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                                        </svg>
                                    </span>
                                    <span class="text-gray-400 cursor-not-allowed" title="You cannot deactivate Super Admin users">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                                        </svg>
                                    </span>
                                    <span class="text-gray-400 cursor-not-allowed" title="You cannot delete Super Admin users">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $users->links() }}
            </div>
        </div>
    </div>

    <!-- Add User Modal -->
    <x-modal name="add-user" :show="$errors->any()">
        <form method="POST" action="{{ route('admin.users.store') }}" class="p-6">
            @csrf
            <h2 class="text-lg font-medium text-gray-900">
                {{ __('Add New User') }}
            </h2>

            <div class="mt-4">
                <x-input-label for="name" :value="__('Name')" />
                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required autofocus />
                <x-input-error class="mt-2" :messages="$errors->get('name')" />
            </div>

            <div class="mt-4">
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email')" required />
                <x-input-error class="mt-2" :messages="$errors->get('email')" />
            </div>

            <div class="mt-4">
                <x-input-label for="homebase" :value="__('Homebase')" />
                <x-text-input id="homebase" name="homebase" type="text" class="mt-1 block w-full" :value="old('homebase')" required />
                <x-input-error class="mt-2" :messages="$errors->get('homebase')" />
            </div>

            <div class="mt-4">
                <x-input-label for="position" :value="__('Position')" />
                <x-text-input id="position" name="position" type="text" class="mt-1 block w-full" :value="old('position')" required />
                <x-input-error class="mt-2" :messages="$errors->get('position')" />
            </div>

            <div class="mt-4">
                <x-input-label for="department_id" :value="__('Department')" />
                @role('Super Admin')
                <select id="department_id" name="department_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                    <option value="">Select Department</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
                            {{ $department->name }}
                        </option>
                    @endforeach
                </select>
                @else
                <input type="hidden" name="department_id" value="{{ auth()->user()->department_id }}">
                <div class="mt-1 block w-full px-3 py-2 bg-gray-100 text-gray-700 border border-gray-300 rounded">
                    {{ auth()->user()->department->name }}
                </div>
                <p class="mt-1 text-sm text-gray-500">Users will be added to your department automatically.</p>
                @endrole
                <x-input-error class="mt-2" :messages="$errors->get('department_id')" />
            </div>

            <div class="mt-4">
                <x-input-label for="role" :value="__('Role')" />
                @role('Super Admin')
                <select id="role" name="role" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                    <option value="">Select Role</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->name }}" {{ old('role') == $role->name ? 'selected' : '' }}>
                            {{ ucfirst($role->name) }}
                        </option>
                    @endforeach
                </select>
                @else
                <select id="role" name="role" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                    <option value="">Select Role</option>
                    @foreach($roles as $role)
                        @if($role->name === 'Employee')
                        <option value="{{ $role->name }}" selected>
                            {{ ucfirst($role->name) }}
                        </option>
                        @endif
                    @endforeach
                </select>
                <p class="mt-1 text-sm text-gray-500">You can only create users with Employee role.</p>
                @endrole
                <x-input-error class="mt-2" :messages="$errors->get('role')" />
            </div>

            <div class="mt-4">
                <x-input-label for="password" :value="__('Password')" />
                <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" required />
                <x-input-error class="mt-2" :messages="$errors->get('password')" />
                    </div>

            <div class="mt-4">
                <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
                <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" required />
                <x-input-error class="mt-2" :messages="$errors->get('password_confirmation')" />
            </div>

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-primary-button class="ml-3">
                    {{ __('Save') }}
                </x-primary-button>
            </div>
        </form>
    </x-modal>

    <!-- Delete User Modal -->
    @foreach($users as $user)
        @if(!auth()->user()->hasRole('Admin Divisi') || !$user->hasRole('Super Admin'))
        <x-modal name="delete-user-{{ $user->id }}" :show="false">
            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="p-6">
            @csrf
                @method('DELETE')
            <h2 class="text-lg font-medium text-gray-900">
                    {{ __('Delete User') }}
            </h2>

                <p class="mt-4 text-sm text-gray-600">
                    {{ __('Are you sure you want to delete this user? This action cannot be undone.') }}
            </p>

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                        {{ __('Cancel') }}
                </x-secondary-button>

                    <x-danger-button class="ml-3">
                        {{ __('Delete') }}
                    </x-danger-button>
            </div>
        </form>
    </x-modal>

        <!-- Reset Password Modal -->
        <x-modal name="reset-password-{{ $user->id }}" :show="false">
            <form method="POST" action="{{ route('admin.users.reset-password', $user) }}" class="p-6">
            @csrf
            <h2 class="text-lg font-medium text-gray-900">
                    {{ __('Reset Password') }}
            </h2>

                <div class="mt-4">
                    <x-input-label for="new_password" :value="__('New Password')" />
                    <x-text-input id="new_password" name="new_password" type="password" class="mt-1 block w-full" required />
                    <x-input-error class="mt-2" :messages="$errors->get('new_password')" />
            </div>

                <div class="mt-4">
                    <x-input-label for="new_password_confirmation" :value="__('Confirm New Password')" />
                    <x-text-input id="new_password_confirmation" name="new_password_confirmation" type="password" class="mt-1 block w-full" required />
                    <x-input-error class="mt-2" :messages="$errors->get('new_password_confirmation')" />
                </div>

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                        {{ __('Cancel') }}
                </x-secondary-button>

                <x-primary-button class="ml-3">
                        {{ __('Reset Password') }}
                </x-primary-button>
            </div>
        </form>
    </x-modal>

        <!-- Deactivate User Modal -->
        @if($user->is_active)
        <x-modal name="deactivate-user-{{ $user->id }}" :show="false">
            <form method="POST" action="{{ route('admin.users.toggle-active', $user) }}" class="p-6">
            @csrf
                @method('PUT')
            <h2 class="text-lg font-medium text-gray-900">
                    {{ __('Deactivate User') }}
            </h2>

                <div class="mt-4">
                    <x-input-label for="reason" :value="__('Reason for Deactivation (Optional)')" />
                    <textarea id="reason" name="reason" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" rows="3"></textarea>
            </div>

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                        {{ __('Cancel') }}
                </x-secondary-button>

                    <x-danger-button class="ml-3">
                        {{ __('Deactivate') }}
                    </x-danger-button>
            </div>
        </form>
    </x-modal>
        @endif
        @endif
    @endforeach
</x-app-layout>