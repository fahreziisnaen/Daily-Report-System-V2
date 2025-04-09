<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" 
        action="{{ request()->routeIs('admin.users.edit') 
            ? route('admin.users.update', $user) 
            : route('profile.update') }}" 
        class="mt-6 space-y-6" 
        enctype="multipart/form-data">
        @csrf
        @method(request()->routeIs('admin.users.edit') ? 'put' : 'patch')

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            @if(request()->routeIs('admin.users.edit') && auth()->user()->hasRole('Super Admin'))
                <!-- Admin dapat mengedit email user lain -->
                <x-text-input 
                    id="email" 
                    name="email" 
                    type="email" 
                    class="mt-1 block w-full" 
                    :value="old('email', $user->email)" 
                    required 
                    autocomplete="username" />
                <x-input-error class="mt-2" :messages="$errors->get('email')" />
            @else
                <!-- User biasa tidak dapat mengedit email -->
                <x-text-input 
                    id="email" 
                    type="email" 
                    class="mt-1 block w-full bg-gray-100" 
                    :value="$user->email" 
                    disabled 
                    readonly />
                <p class="mt-1 text-sm text-gray-500">Email can only be changed by administrator.</p>
            @endif
        </div>

        <div>
            <x-input-label for="homebase" :value="__('Homebase')" />
            <x-text-input id="homebase" name="homebase" type="text" class="mt-1 block w-full" :value="old('homebase', $user->homebase)" required />
            <x-input-error class="mt-2" :messages="$errors->get('homebase')" />
        </div>

        <div>
            <x-input-label for="position" :value="__('Position')" />
            @if(request()->routeIs('admin.users.edit') && auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('Admin Divisi'))
                <!-- Admin dapat mengedit position user -->
                <x-text-input 
                    id="position" 
                    name="position" 
                    type="text" 
                    class="mt-1 block w-full" 
                    :value="old('position', $user->position)" 
                    required />
                <x-input-error class="mt-2" :messages="$errors->get('position')" />
            @else
                <!-- User biasa tidak dapat mengedit position -->
                <x-text-input 
                    id="position" 
                    type="text" 
                    class="mt-1 block w-full bg-gray-100" 
                    :value="$user->position" 
                    disabled 
                    readonly />
                <p class="mt-1 text-sm text-gray-500">Position can only be changed by administrator.</p>
            @endif
        </div>

        <div>
            <x-input-label for="department_id" :value="__('Department')" />
            @if(request()->routeIs('admin.users.edit') && auth()->user()->hasRole('Super Admin'))
                <!-- Super Admin dapat mengedit department user -->
                <select id="department_id" name="department_id" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}" {{ (old('department_id', $user->department_id) == $department->id) ? 'selected' : '' }}>
                            {{ $department->name }}
                        </option>
                    @endforeach
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('department_id')" />
            @elseif(request()->routeIs('admin.users.edit') && auth()->user()->hasRole('Admin Divisi'))
                <!-- Admin Divisi hanya bisa melihat department (tidak bisa ganti) -->
                <input type="hidden" name="department_id" value="{{ $user->department_id }}">
                <div class="mt-1 block w-full px-3 py-2 bg-gray-100 text-gray-700 border border-gray-300 rounded">
                    {{ $user->department->name ?? 'Not assigned' }}
                </div>
                <p class="mt-1 text-sm text-gray-500">Department cannot be changed. Users must remain in their current department.</p>
            @else
                <!-- User biasa tidak dapat mengedit department -->
                <div class="mt-1 block w-full px-3 py-2 bg-gray-100 text-gray-700 border border-gray-300 rounded">
                    {{ $user->department->name ?? 'Not assigned' }}
                </div>
                <p class="mt-1 text-sm text-gray-500">Department can only be changed by administrator.</p>
            @endif
        </div>

        <!-- Profile Picture Input -->
        <div>
            <x-input-label for="avatar" :value="__('Profile Picture')" />
            <input type="file" 
                id="avatar" 
                name="avatar" 
                accept="image/*"
                class="mt-1 block w-full text-sm text-gray-500
                    file:mr-4 file:py-2 file:px-4
                    file:rounded-md file:border-0
                    file:text-sm file:font-semibold
                    file:bg-indigo-50 file:text-indigo-700
                    hover:file:bg-indigo-100" />
            <x-input-error class="mt-2" :messages="$errors->get('avatar')" />
        </div>

        <!-- Signature Input -->
        <div>
            <x-input-label for="signature" :value="__('Signature')" />
            <input type="file" 
                id="signature" 
                name="signature" 
                accept="image/*"
                class="mt-1 block w-full text-sm text-gray-500
                    file:mr-4 file:py-2 file:px-4
                    file:rounded-md file:border-0
                    file:text-sm file:font-semibold
                    file:bg-indigo-50 file:text-indigo-700
                    hover:file:bg-indigo-100" />
            <x-input-error class="mt-2" :messages="$errors->get('signature')" />
        </div>

        <div class="flex items-center gap-4">
            @if(request()->routeIs('admin.users.edit'))
                <a href="{{ route('admin.users.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:bg-gray-300 active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    {{ __('Batal') }}
                </a>
            @endif
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
