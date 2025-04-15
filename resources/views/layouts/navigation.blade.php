<nav x-data="{ open: false }" class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <img src="{{ asset('images/logo.png') }}" alt="Logo" class="block h-9 w-auto">
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex items-center">
                    <div class="relative" x-data="{ open: false }">
                        @if(auth()->user()->hasRole('Vice President'))
                            <x-nav-link :href="route('dashboard', ['view' => 'vice-president'])" :active="request()->routeIs('dashboard')">
                                {{ __('Dashboard') }}
                            </x-nav-link>
                        @elseif(auth()->user()->hasRole('Super Admin') || 
                            auth()->user()->hasRole('Human Resource') || 
                            auth()->user()->hasRole('Verifikator') || 
                            auth()->user()->hasRole('Admin Divisi'))
                            <x-nav-link 
                                @click="open = !open" 
                                @click.away="open = false"
                                href="javascript:void(0)" 
                                :active="request()->routeIs('dashboard')" 
                                class="relative cursor-pointer">
                                {{ __('Dashboard') }}
                                <svg class="ml-1 -mr-0.5 h-4 w-4 inline" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                                <div 
                                    x-show="open"
                                    x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="transform opacity-0 scale-95"
                                    x-transition:enter-end="transform opacity-100 scale-100"
                                    x-transition:leave="transition ease-in duration-75"
                                    x-transition:leave-start="transform opacity-100 scale-100"
                                    x-transition:leave-end="transform opacity-0 scale-95"
                                    class="absolute left-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-md shadow-lg py-1 z-50">
                                    <a href="{{ route('dashboard', ['view' => 'personal']) }}" 
                                       class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->get('view') === 'personal' ? 'bg-gray-100 dark:bg-gray-700' : '' }}">
                                        {{ __('Personal Dashboard') }}
                                    </a>
                                    @if(auth()->user()->hasRole('Human Resource'))
                                        <a href="{{ route('dashboard', ['view' => 'hr']) }}" 
                                           class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->get('view') === 'hr' ? 'bg-gray-100 dark:bg-gray-700' : '' }}">
                                            {{ __('HR Dashboard') }}
                                        </a>
                                    @endif
                                    @if(auth()->user()->hasRole('Verifikator'))
                                        <a href="{{ route('dashboard', ['view' => 'verifikator']) }}" 
                                           class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->get('view') === 'verifikator' ? 'bg-gray-100 dark:bg-gray-700' : '' }}">
                                            {{ __('Verifikator Dashboard') }}
                                        </a>
                                    @endif
                                    @if(auth()->user()->hasRole('Admin Divisi'))
                                        <a href="{{ route('dashboard', ['view' => 'admin']) }}" 
                                           class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->get('view') === 'admin' ? 'bg-gray-100 dark:bg-gray-700' : '' }}">
                                            {{ __('Admin Dashboard') }}
                                        </a>
                                    @endif
                                </div>
                            </x-nav-link>
                        @else
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                        @endif
                    </div>

                    <x-nav-link :href="route('reports.index')" :active="request()->routeIs('reports.*')">
                        {{ __('Reports') }}
                    </x-nav-link>

                    @role('Verifikator')
                    <x-nav-link :href="route('verification.index')" :active="request()->routeIs('verification.*')">
                        {{ __('Verifikasi') }}
                    </x-nav-link>
                    @endrole

                    @role('Vice President')
                    <x-nav-link :href="route('approval.index')" :active="request()->routeIs('approval.*')">
                        {{ __('Approval') }}
                    </x-nav-link>
                    @endrole

                    @role('Human Resource')
                    <x-nav-link :href="route('hr-review.index')" :active="request()->routeIs('hr-review.*')">
                        {{ __('HR Review') }}
                    </x-nav-link>
                    @endrole

                    @role(['Super Admin', 'Admin Divisi', 'Vice President'])
                    <x-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">
                        {{ __('User Management') }}
                    </x-nav-link>
                    @endrole

                    @if(Auth::user()->hasRole(['Super Admin', 'Admin Divisi', 'Vice President']))
                    <x-nav-link :href="route('admin.rekap.index')" :active="request()->routeIs('admin.rekap.*')">
                        {{ __('Rekap') }}
                    </x-nav-link>
                    @elseif(Auth::user()->hasRole('Human Resource'))
                    <x-nav-link :href="route('hr.rekap')" :active="request()->routeIs('hr.rekap')">
                        {{ __('Rekap') }}
                    </x-nav-link>
                    @else
                    <x-nav-link :href="route('rekap.index')" :active="request()->routeIs('rekap.index')">
                        {{ __('Rekap') }}
                    </x-nav-link>
                    @endif
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ml-6">
                <!-- Dark Mode Toggle - Desktop -->
                <button @click="$dispatch('toggle-dark-mode')" 
                    class="mr-3 p-2 rounded-full bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200">
                    <!-- Sun icon -->
                    <svg x-show="$store.darkMode.on" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                            d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <!-- Moon icon -->
                    <svg x-show="!$store.darkMode.on" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                            d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                    </svg>
                </button>

                <!-- Settings Dropdown -->
                <div class="ml-3 relative">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                                <div>{{ Auth::user()->name }}</div>

                                <div class="ms-1">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">
                                {{ __('Profile') }}
                            </x-dropdown-link>

                            <!-- Authentication -->
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf

                                <x-dropdown-link :href="route('logout')"
                                        onclick="event.preventDefault();
                                                    this.closest('form').submit();">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </div>
            </div>

            <!-- Hamburger -->
            <div class="-mr-2 flex items-center sm:hidden">
                <!-- Dark Mode Toggle - Mobile -->
                <button @click="$dispatch('toggle-dark-mode')" 
                    class="mr-2 p-2 rounded-full bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200">
                    <svg x-show="$store.darkMode.on" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                            d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <svg x-show="!$store.darkMode.on" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                            d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                    </svg>
                </button>

                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            @if(auth()->user()->hasRole('Vice President'))
                <x-responsive-nav-link :href="route('dashboard', ['view' => 'vice-president'])" :active="request()->routeIs('dashboard')">
                    {{ __('Dashboard') }}
                </x-responsive-nav-link>
            @elseif(auth()->user()->hasRole('Super Admin') || 
                auth()->user()->hasRole('Human Resource') || 
                auth()->user()->hasRole('Verifikator') || 
                auth()->user()->hasRole('Admin Divisi'))
                <div x-data="{ subMenuOpen: false }">
                    <x-responsive-nav-link 
                        @click="subMenuOpen = !subMenuOpen" 
                        :active="request()->routeIs('dashboard')" 
                        class="relative flex justify-between cursor-pointer">
                        <span>{{ __('Dashboard') }}</span>
                        <svg class="ml-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </x-responsive-nav-link>

                    <div 
                        x-show="subMenuOpen" 
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="transform opacity-0 scale-95"
                        x-transition:enter-end="transform opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="transform opacity-100 scale-100"
                        x-transition:leave-end="transform opacity-0 scale-95"
                        class="pl-4">
                        <a href="{{ route('dashboard', ['view' => 'personal']) }}" 
                           class="block pl-3 pr-4 py-2 border-l-4 border-transparent text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-600 {{ request()->get('view') === 'personal' ? 'border-indigo-400 dark:border-indigo-600' : '' }}">
                            {{ __('Personal Dashboard') }}
                        </a>
                        @if(auth()->user()->hasRole('Human Resource'))
                            <a href="{{ route('dashboard', ['view' => 'hr']) }}" 
                               class="block pl-3 pr-4 py-2 border-l-4 border-transparent text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-600 {{ request()->get('view') === 'hr' ? 'border-indigo-400 dark:border-indigo-600' : '' }}">
                                {{ __('HR Dashboard') }}
                            </a>
                        @endif
                        @if(auth()->user()->hasRole('Verifikator'))
                            <a href="{{ route('dashboard', ['view' => 'verifikator']) }}" 
                               class="block pl-3 pr-4 py-2 border-l-4 border-transparent text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-600 {{ request()->get('view') === 'verifikator' ? 'border-indigo-400 dark:border-indigo-600' : '' }}">
                                {{ __('Verifikator Dashboard') }}
                            </a>
                        @endif
                        @if(auth()->user()->hasRole('Admin Divisi'))
                            <a href="{{ route('dashboard', ['view' => 'admin']) }}" 
                               class="block pl-3 pr-4 py-2 border-l-4 border-transparent text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-600 {{ request()->get('view') === 'admin' ? 'border-indigo-400 dark:border-indigo-600' : '' }}">
                                {{ __('Admin Dashboard') }}
                            </a>
                        @endif
                    </div>
                </div>
            @else
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            @endif
            
            <x-responsive-nav-link :href="route('reports.index')" :active="request()->routeIs('reports.*')">
                {{ __('Reports') }}
            </x-responsive-nav-link>

            @role('Verifikator')
            <x-responsive-nav-link :href="route('verification.index')" :active="request()->routeIs('verification.*')">
                {{ __('Verifikasi') }}
            </x-responsive-nav-link>
            @endrole

            @role('Vice President')
            <x-responsive-nav-link :href="route('approval.index')" :active="request()->routeIs('approval.*')">
                {{ __('Approval') }}
            </x-responsive-nav-link>
            @endrole

            @role('Human Resource')
            <x-responsive-nav-link :href="route('hr-review.index')" :active="request()->routeIs('hr-review.*')">
                {{ __('HR Review') }}
            </x-responsive-nav-link>
            @endrole
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800 dark:text-gray-200">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
