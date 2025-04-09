<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-white leading-tight">
                {{ __('Department Management') }}
            </h2>
            <button 
                type="button"
                x-data=""
                @click="$dispatch('open-modal', 'add-department')" 
                class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Add Department
            </button>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Departments Table -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                            <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($departments as $department)
                        <tr class="hover:bg-gray-50">
                            <td class="px-3 py-2 text-sm">{{ $department->name }}</td>
                            <td class="px-3 py-2 text-sm">{{ $department->code }}</td>
                            <td class="px-3 py-2 text-sm">{{ $department->description ?: '-' }}</td>
                            <td class="px-3 py-2 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button type="button"
                                        @click="$dispatch('open-modal', 'edit-department-{{ $department->id }}')"
                                        class="text-blue-600 hover:text-blue-900"
                                        title="Edit Department">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>
                                    <button type="button"
                                        @click="$dispatch('open-modal', 'delete-department-{{ $department->id }}')"
                                        class="text-red-600 hover:text-red-900"
                                        title="Delete Department">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-3 py-4 text-center text-sm text-gray-500">
                                No departments found
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add Department Modal -->
    <x-modal name="add-department" :show="$errors->any()">
        <form method="POST" action="{{ route('admin.departments.store') }}" class="p-6">
            @csrf
            <h2 class="text-lg font-medium text-gray-900">
                {{ __('Add New Department') }}
            </h2>

            <div class="mt-4">
                <x-input-label for="name" :value="__('Department Name')" />
                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required />
                <x-input-error class="mt-2" :messages="$errors->get('name')" />
            </div>

            <div class="mt-4">
                <x-input-label for="code" :value="__('Department Code')" />
                <x-text-input id="code" name="code" type="text" class="mt-1 block w-full" :value="old('code')" required />
                <x-input-error class="mt-2" :messages="$errors->get('code')" />
            </div>

            <div class="mt-4">
                <x-input-label for="description" :value="__('Description')" />
                <textarea id="description" name="description" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" rows="3">{{ old('description') }}</textarea>
                <x-input-error class="mt-2" :messages="$errors->get('description')" />
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

    <!-- Edit Department Modal -->
    @foreach($departments as $department)
        <x-modal name="edit-department-{{ $department->id }}" :show="false">
            <form method="POST" action="{{ route('admin.departments.update', $department) }}" class="p-6">
                @csrf
                @method('PUT')
                <h2 class="text-lg font-medium text-gray-900">
                    {{ __('Edit Department') }}
                </h2>

                <div class="mt-4">
                    <x-input-label for="name_{{ $department->id }}" :value="__('Department Name')" />
                    <x-text-input id="name_{{ $department->id }}" name="name" type="text" class="mt-1 block w-full" :value="$department->name" required />
                    <x-input-error class="mt-2" :messages="$errors->get('name')" />
                </div>

                <div class="mt-4">
                    <x-input-label for="code_{{ $department->id }}" :value="__('Department Code')" />
                    <x-text-input id="code_{{ $department->id }}" name="code" type="text" class="mt-1 block w-full" :value="$department->code" required />
                    <x-input-error class="mt-2" :messages="$errors->get('code')" />
                </div>

                <div class="mt-4">
                    <x-input-label for="description_{{ $department->id }}" :value="__('Description')" />
                    <textarea id="description_{{ $department->id }}" name="description" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" rows="3">{{ $department->description }}</textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('description')" />
                </div>

                <div class="mt-6 flex justify-end">
                    <x-secondary-button x-on:click="$dispatch('close')">
                        {{ __('Cancel') }}
                    </x-secondary-button>

                    <x-primary-button class="ml-3">
                        {{ __('Update') }}
                    </x-primary-button>
                </div>
            </form>
        </x-modal>

        <!-- Delete Department Modal -->
        <x-modal name="delete-department-{{ $department->id }}" :show="false">
            <form method="POST" action="{{ route('admin.departments.destroy', $department) }}" class="p-6">
                @csrf
                @method('DELETE')
                <h2 class="text-lg font-medium text-gray-900">
                    {{ __('Delete Department') }}
                </h2>

                <p class="mt-4 text-sm text-gray-600">
                    {{ __('Are you sure you want to delete this department? This action cannot be undone.') }}
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
    @endforeach
</x-app-layout> 