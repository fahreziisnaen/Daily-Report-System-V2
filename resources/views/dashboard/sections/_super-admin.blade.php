<!-- Super Admin Dashboard -->
<div class="bg-gradient-to-r from-purple-600 to-indigo-700 rounded-lg shadow-lg p-6 mb-8">
    <div class="flex items-center justify-between">
        <div class="flex-1">
            <h3 class="text-xl font-semibold text-white mb-2">
                Selamat Datang, Super Admin!
            </h3>
            <p class="text-purple-100">
                Panel admin untuk mengelola seluruh sistem pelaporan harian.
            </p>
        </div>
    </div>
</div>

<!-- Super Admin Statistics -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <x-dashboard.stats-card 
        title="Total Laporan"
        :count="$totalReports"
        icon="document"
        iconColor="blue"
        linkText="Lihat Semua"
        linkUrl="{{ route('reports.index') }}"
    />
    
    <x-dashboard.stats-card 
        title="Laporan Bulan Ini"
        :count="$monthlyReports"
        icon="calendar"
        iconColor="purple"
        linkText="Lihat Rekap"
        linkUrl="{{ route('rekap.index') }}"
    />
    
    <x-dashboard.stats-card 
        title="Laporan Hari Ini"
        :count="$dailyReports"
        icon="clock"
        iconColor="indigo"
    />

    <x-dashboard.stats-card 
        title="Total Users"
        :count="$userCount ?? \App\Models\User::count()"
        icon="user"
        iconColor="green"
        linkText="Kelola Users"
        linkUrl="{{ route('admin.users.index') }}"
    />
</div>

<!-- System Status Overview -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
    <!-- Laporan Status -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Status Laporan</h3>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <!-- Laporan Draft -->
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 shadow-sm">
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0">
                            <svg class="w-8 h-8 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-700 dark:text-gray-300">Draft</div>
                            <div class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">
                                {{ App\Models\Report::where('status', 'Draft')->count() }}
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Menunggu Verifikasi -->
                <div class="bg-yellow-50 dark:bg-yellow-900 rounded-lg p-4 shadow-sm">
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0">
                            <svg class="w-8 h-8 text-yellow-500 dark:text-yellow-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Menunggu Verifikasi</div>
                            <div class="mt-1 text-2xl font-bold text-yellow-900 dark:text-yellow-100">
                                {{ App\Models\Report::where('status', 'Menunggu Verifikasi')->count() }}
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Menunggu Approval VP -->
                <div class="bg-blue-50 dark:bg-blue-900 rounded-lg p-4 shadow-sm">
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0">
                            <svg class="w-8 h-8 text-blue-500 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-blue-800 dark:text-blue-200">Menunggu Approval VP</div>
                            <div class="mt-1 text-2xl font-bold text-blue-900 dark:text-blue-100">
                                {{ App\Models\Report::where('status', 'Menunggu Approval VP')->count() }}
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Menunggu Review HR -->
                <div class="bg-indigo-50 dark:bg-indigo-900 rounded-lg p-4 shadow-sm">
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0">
                            <svg class="w-8 h-8 text-indigo-500 dark:text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-indigo-800 dark:text-indigo-200">Menunggu Review HR</div>
                            <div class="mt-1 text-2xl font-bold text-indigo-900 dark:text-indigo-100">
                                {{ App\Models\Report::where('status', 'Menunggu Review HR')->count() }}
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Ditolak -->
                <div class="bg-red-50 dark:bg-red-900 rounded-lg p-4 shadow-sm">
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0">
                            <svg class="w-8 h-8 text-red-500 dark:text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-red-800 dark:text-red-200">Ditolak</div>
                            <div class="mt-1 text-2xl font-bold text-red-900 dark:text-red-100">
                                {{ App\Models\Report::whereIn('status', ['Ditolak Verifikator', 'Ditolak VP', 'Ditolak HR'])->count() }}
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Selesai -->
                <div class="bg-green-50 dark:bg-green-900 rounded-lg p-4 shadow-sm">
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0">
                            <svg class="w-8 h-8 text-green-500 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-green-800 dark:text-green-200">Selesai</div>
                            <div class="mt-1 text-2xl font-bold text-green-900 dark:text-green-100">
                                {{ App\Models\Report::where('status', 'Selesai')->count() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- User Role Distribution -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Distribusi User</h3>
                <a href="{{ route('admin.users.index') }}" class="text-sm text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                    Kelola Users
                </a>
            </div>
            
            <div class="space-y-4">
                @php
                    $roles = ['Super Admin', 'Vice President', 'Admin Divisi', 'Verifikator', 'Human Resource', 'Employee'];
                    $colors = [
                        'Super Admin' => ['bg' => 'bg-purple-50', 'text' => 'text-purple-800', 'dark-bg' => 'dark:bg-purple-900', 'dark-text' => 'dark:text-purple-200', 'progress' => 'bg-purple-500'],
                        'Vice President' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-800', 'dark-bg' => 'dark:bg-blue-900', 'dark-text' => 'dark:text-blue-200', 'progress' => 'bg-blue-500'],
                        'Admin Divisi' => ['bg' => 'bg-indigo-50', 'text' => 'text-indigo-800', 'dark-bg' => 'dark:bg-indigo-900', 'dark-text' => 'dark:text-indigo-200', 'progress' => 'bg-indigo-500'],
                        'Verifikator' => ['bg' => 'bg-teal-50', 'text' => 'text-teal-800', 'dark-bg' => 'dark:bg-teal-900', 'dark-text' => 'dark:text-teal-200', 'progress' => 'bg-teal-500'],
                        'Human Resource' => ['bg' => 'bg-green-50', 'text' => 'text-green-800', 'dark-bg' => 'dark:bg-green-900', 'dark-text' => 'dark:text-green-200', 'progress' => 'bg-green-500'],
                        'Employee' => ['bg' => 'bg-gray-50', 'text' => 'text-gray-800', 'dark-bg' => 'dark:bg-gray-700', 'dark-text' => 'dark:text-gray-200', 'progress' => 'bg-gray-500'],
                    ];
                    $totalUsers = \App\Models\User::count();
                @endphp
                
                @foreach($roles as $roleName)
                    @php
                        $count = \Spatie\Permission\Models\Role::findByName($roleName)->users()->count();
                        $percentage = $totalUsers > 0 ? round(($count / $totalUsers) * 100) : 0;
                        $color = $colors[$roleName] ?? ['bg' => 'bg-gray-50', 'text' => 'text-gray-800', 'dark-bg' => 'dark:bg-gray-700', 'dark-text' => 'dark:text-gray-200', 'progress' => 'bg-gray-500'];
                    @endphp
                    <div class="{{ $color['bg'] }} {{ $color['dark-bg'] }} rounded-lg p-4">
                        <div class="flex justify-between mb-1">
                            <span class="text-sm font-medium {{ $color['text'] }} {{ $color['dark-text'] }}">{{ $roleName }}</span>
                            <span class="text-sm font-medium {{ $color['text'] }} {{ $color['dark-text'] }}">{{ $count }} ({{ $percentage }}%)</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-600">
                            <div class="{{ $color['progress'] }} h-2.5 rounded-full" style="width: {{ $percentage }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- Recent Reports -->
<div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Laporan Terbaru</h3>
            <a href="{{ route('reports.index') }}" class="text-sm text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                Lihat Semua
            </a>
        </div>
        
        @if(count($recentReports) > 0)
            <!-- Desktop View (Hidden on Mobile) -->
            <div class="hidden md:block overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Karyawan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Department</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Project</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Detail</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($recentReports as $report)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">{{ $report->report_date->translatedFormat('d/m/Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">{{ $report->user->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">{{ $report->user->department->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">{{ $report->project_code }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($report->status === 'Draft')
                                        <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                            Draft
                                        </span>
                                    @elseif($report->status === 'Menunggu Verifikasi')
                                        <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100">
                                            Menunggu Verifikasi
                                        </span>
                                    @elseif($report->status === 'Ditolak Verifikator')
                                        <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100">
                                            Ditolak Verifikator
                                        </span>
                                    @elseif($report->status === 'Menunggu Approval VP')
                                        <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100">
                                            Menunggu Approval VP
                                        </span>
                                    @elseif($report->status === 'Ditolak VP')
                                        <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100">
                                            Ditolak VP
                                        </span>
                                    @elseif($report->status === 'Menunggu Review HR')
                                        <span class="px-2 py-1 text-xs rounded-full bg-indigo-100 text-indigo-800 dark:bg-indigo-800 dark:text-indigo-100">
                                            Menunggu Review HR
                                        </span>
                                    @elseif($report->status === 'Ditolak HR')
                                        <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100">
                                            Ditolak HR
                                        </span>
                                    @elseif($report->status === 'Selesai')
                                        <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100">
                                            Selesai
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <a href="{{ route('reports.show', $report) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                        Lihat Detail
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Mobile View (Hidden on Desktop) -->
            <div class="md:hidden space-y-4">
                @foreach($recentReports as $report)
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 space-y-3">
                        <div class="flex justify-between items-start">
                            <div>
                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $report->user->name }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $report->report_date->translatedFormat('d/m/Y') }}</div>
                            </div>
                            @if($report->status === 'Draft')
                                <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                    Draft
                                </span>
                            @elseif($report->status === 'Menunggu Verifikasi')
                                <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100">
                                    Menunggu Verifikasi
                                </span>
                            @elseif($report->status === 'Ditolak Verifikator')
                                <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100">
                                    Ditolak Verifikator
                                </span>
                            @elseif($report->status === 'Menunggu Approval VP')
                                <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100">
                                    Menunggu Approval VP
                                </span>
                            @elseif($report->status === 'Ditolak VP')
                                <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100">
                                    Ditolak VP
                                </span>
                            @elseif($report->status === 'Menunggu Review HR')
                                <span class="px-2 py-1 text-xs rounded-full bg-indigo-100 text-indigo-800 dark:bg-indigo-800 dark:text-indigo-100">
                                    Menunggu Review HR
                                </span>
                            @elseif($report->status === 'Ditolak HR')
                                <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100">
                                    Ditolak HR
                                </span>
                            @elseif($report->status === 'Selesai')
                                <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100">
                                    Selesai
                                </span>
                            @endif
                        </div>
                        
                        <div class="grid grid-cols-2 gap-2 text-sm">
                            <div>
                                <div class="text-gray-500 dark:text-gray-400">Department</div>
                                <div class="font-medium text-gray-900 dark:text-white">{{ $report->user->department->name }}</div>
                            </div>
                            <div>
                                <div class="text-gray-500 dark:text-gray-400">Project</div>
                                <div class="font-medium text-gray-900 dark:text-white">{{ $report->project_code }}</div>
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <a href="{{ route('reports.show', $report) }}" 
                                class="inline-flex items-center px-3 py-1.5 bg-indigo-50 text-indigo-600 rounded-md text-sm font-medium hover:bg-indigo-100 dark:bg-indigo-900 dark:text-indigo-200 dark:hover:bg-indigo-800">
                                <span>Lihat Detail</span>
                                <svg class="ml-1.5 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-4 text-gray-500 dark:text-gray-400">
                Belum ada laporan yang dibuat.
            </div>
        @endif
    </div>
</div> 