<!-- Admin Dashboard -->
<div class="bg-gradient-to-r from-blue-500 to-indigo-600 rounded-lg shadow-lg p-6 mb-8">
    <div class="flex items-center justify-between">
        <div class="flex-1">
            <h3 class="text-xl font-semibold text-white mb-2">
                Selamat Datang di Dashboard Admin Divisi
            </h3>
            <p class="text-blue-100">
                Panel admin untuk mengelola laporan departemen Anda.
            </p>
        </div>
    </div>
</div>

<!-- Kalender Laporan Karyawan -->
<div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-8">
    <div class="p-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 md:mb-0">Kalender Laporan Karyawan</h3>
            
            <div class="flex flex-col md:flex-row space-y-3 md:space-y-0 md:space-x-4">
                <!-- Filter Form -->
                <form action="{{ route('dashboard') }}" method="GET" class="flex space-x-3">
                    <input type="hidden" name="view" value="admin">
                    
                    <!-- Bulan -->
                    <div class="flex items-center">
                        <label for="month" class="text-sm text-gray-500 dark:text-gray-400 mr-2">Bulan:</label>
                        <select id="month" name="month" onchange="this.form.submit()" 
                                class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm">
                            @for ($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                    {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    
                    <!-- Tahun -->
                    <div class="flex items-center">
                        <label for="year" class="text-sm text-gray-500 dark:text-gray-400 mr-2">Tahun:</label>
                        <select id="year" name="year" onchange="this.form.submit()" 
                                class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm">
                            @for ($y = date('Y') - 2; $y <= date('Y') + 1; $y++)
                                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                </form>
                
                <!-- Legenda -->
                <div class="text-sm text-gray-500 dark:text-gray-400 flex flex-wrap items-center gap-x-4 gap-y-2">
                    <span class="flex items-center">
                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-200 mr-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </span>
                        <span>Disetujui</span>
                    </span>
                    <span class="flex items-center">
                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-200 mr-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </span>
                        <span>Menunggu Verifikasi</span>
                    </span>
                    <span class="flex items-center">
                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-200 mr-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </span>
                        <span>Menunggu Approval VP</span>
                    </span>
                    <span class="flex items-center">
                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-indigo-100 text-indigo-800 dark:bg-indigo-800 dark:text-indigo-200 mr-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </span>
                        <span>Menunggu Review HR</span>
                    </span>
                    <span class="flex items-center">
                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-200 mr-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </span>
                        <span>Ditolak</span>
                    </span>
                    <span class="flex items-center">
                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200 mr-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </span>
                        <span>Tanpa Lembur</span>
                    </span>
                    <span class="flex items-center">
                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-gray-50 text-gray-400 dark:bg-gray-900 dark:text-gray-600 mr-1">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                            </svg>
                        </span>
                        <span>Belum Ada Laporan</span>
                    </span>
                </div>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase sticky left-0 bg-gray-50 dark:bg-gray-700 z-10" style="min-width: 200px;">Karyawan</th>
                        @php
                            $date = Carbon\Carbon::createFromDate($year, $month, 1);
                            $daysInMonth = $date->daysInMonth;
                        @endphp
                        
                        @for ($day = 1; $day <= $daysInMonth; $day++)
                            @php
                                $date = Carbon\Carbon::createFromDate($year, $month, $day);
                                $isWeekend = $date->isWeekend();
                            @endphp
                            <th class="px-2 py-3 text-center text-xs font-medium {{ $isWeekend ? 'text-red-500 dark:text-red-400' : 'text-gray-500 dark:text-gray-300' }}">
                                <div>{{ $day }}</div>
                                <div>{{ substr($date->locale('id')->dayName, 0, 3) }}</div>
                            </th>
                        @endfor
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($summaries as $summary)
                        <tr>
                            <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100 sticky left-0 bg-white dark:bg-gray-800">
                                {{ $summary->user_name }}
                            </td>
                            
                            @for ($day = 1; $day <= $daysInMonth; $day++)
                                @php
                                    $date = Carbon\Carbon::createFromDate($year, $month, $day);
                                    $report = null;
                                    foreach ($summary->reports as $r) {
                                        if ($r->report_date->isSameDay($date)) {
                                            $report = $r;
                                            break;
                                        }
                                    }
                                    $hasReport = $report !== null;
                                    $isToday = $date->isToday();
                                    $isPast = $date->isPast() && !$isToday;
                                    
                                    $statusColor = '';
                                    $statusIcon = '';
                                    
                                    if ($hasReport) {
                                        if ($report->status === 'Selesai') {
                                            $statusColor = 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-200';
                                            $statusIcon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>';
                                        } elseif ($report->status === 'Menunggu Verifikasi') {
                                            $statusColor = 'bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-200';
                                            $statusIcon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>';
                                        } elseif ($report->status === 'Menunggu Approval VP') {
                                            $statusColor = 'bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-200';
                                            $statusIcon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>';
                                        } elseif ($report->status === 'Menunggu Review HR') {
                                            $statusColor = 'bg-indigo-100 text-indigo-800 dark:bg-indigo-800 dark:text-indigo-200';
                                            $statusIcon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>';
                                        } elseif (in_array($report->status, ['Ditolak', 'Ditolak Verifikator', 'Ditolak VP', 'Ditolak HR'])) {
                                            $statusColor = 'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-200';
                                            $statusIcon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>';
                                        } elseif ($report->is_overtime === false) {
                                            $statusColor = 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200';
                                            $statusIcon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>';
                                        } else {
                                            $statusColor = 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-200';
                                            $statusIcon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>';
                                        }
                                    }
                                @endphp
                                
                                <td class="px-2 py-4 text-center">
                                    @if ($hasReport)
                                        <a href="{{ route('reports.show', $report) }}" class="cursor-pointer">
                                            <span data-tooltip-target="tooltip-{{ $summary->user_name }}-{{ $day }}" class="inline-flex items-center justify-center w-8 h-8 rounded-full {{ $statusColor }}">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    {!! $statusIcon !!}
                                                </svg>
                                            </span>
                                            <div id="tooltip-{{ $summary->user_name }}-{{ $day }}" role="tooltip" class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700">
                                                {{ $report->status }} {{ $report->is_overtime ? '(Lembur)' : '(Non-Lembur)' }}
                                                <div class="tooltip-arrow" data-popper-arrow></div>
                                            </div>
                                        </a>
                                    @elseif ($isPast)
                                        <span data-tooltip-target="tooltip-noReport-{{ $summary->user_name }}-{{ $day }}" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-50 text-gray-400 dark:bg-gray-900 dark:text-gray-600">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                            </svg>
                                        </span>
                                        <div id="tooltip-noReport-{{ $summary->user_name }}-{{ $day }}" role="tooltip" class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700">
                                            Belum Ada Laporan
                                            <div class="tooltip-arrow" data-popper-arrow></div>
                                        </div>
                                    @elseif ($isToday)
                                        <span data-tooltip-target="tooltip-today-{{ $summary->user_name }}-{{ $day }}" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-50 text-gray-400 dark:bg-gray-900 dark:text-gray-600">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                            </svg>
                                        </span>
                                        <div id="tooltip-today-{{ $summary->user_name }}-{{ $day }}" role="tooltip" class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700">
                                            Belum Ada Laporan Hari Ini
                                            <div class="tooltip-arrow" data-popper-arrow></div>
                                        </div>
                                    @else
                                        <span data-tooltip-target="tooltip-future-{{ $summary->user_name }}-{{ $day }}" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-50 text-gray-400 dark:bg-gray-900 dark:text-gray-600">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                            </svg>
                                        </span>
                                        <div id="tooltip-future-{{ $summary->user_name }}-{{ $day }}" role="tooltip" class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700">
                                            Belum Ada Laporan
                                            <div class="tooltip-arrow" data-popper-arrow></div>
                                        </div>
                                    @endif
                                </td>
                            @endfor
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Status Laporan Bulan Ini -->
<div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-8">
    <div class="p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Status Laporan Departemen Bulan Ini</h3>
        </div>
        
        <div class="flex flex-wrap gap-3">
            <!-- Menunggu Verifikasi -->
            <div class="bg-yellow-50 dark:bg-yellow-900 rounded-lg p-3 shadow-sm flex-1 min-w-[120px]">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 mr-2">
                            <svg class="w-6 h-6 text-yellow-500 dark:text-yellow-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-xs font-medium text-yellow-800 dark:text-yellow-200">Menunggu Verifikasi</div>
                            <div class="text-xl font-bold text-yellow-900 dark:text-yellow-100">
                                {{ $pendingVerificationCount }}
                            </div>
                        </div>
                    </div>
                    <a href="{{ route('reports.index', ['status' => 'Menunggu Verifikasi', 'month' => now()->month, 'year' => now()->year]) }}" 
                       class="text-xs text-yellow-600 hover:text-yellow-800 dark:text-yellow-400 dark:hover:text-yellow-300">
                        Lihat Semua
                    </a>
                </div>
            </div>
            
            <!-- Menunggu Approval VP -->
            <div class="bg-blue-50 dark:bg-blue-900 rounded-lg p-3 shadow-sm flex-1 min-w-[120px]">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 mr-2">
                            <svg class="w-6 h-6 text-blue-500 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-xs font-medium text-blue-800 dark:text-blue-200">Menunggu Approval VP</div>
                            <div class="text-xl font-bold text-blue-900 dark:text-blue-100">
                                {{ $pendingApprovalCount }}
                            </div>
                        </div>
                    </div>
                    <a href="{{ route('reports.index', ['status' => 'Menunggu Approval VP', 'month' => now()->month, 'year' => now()->year]) }}" 
                       class="text-xs text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                        Lihat Semua
                    </a>
                </div>
            </div>
            
            <!-- Menunggu Review HR -->
            <div class="bg-indigo-50 dark:bg-indigo-900 rounded-lg p-3 shadow-sm flex-1 min-w-[120px]">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 mr-2">
                            <svg class="w-6 h-6 text-indigo-500 dark:text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-xs font-medium text-indigo-800 dark:text-indigo-200">Menunggu Review HR</div>
                            <div class="text-xl font-bold text-indigo-900 dark:text-indigo-100">
                                {{ $pendingHrCount }}
                            </div>
                        </div>
                    </div>
                    <a href="{{ route('reports.index', ['status' => 'Menunggu Review HR', 'month' => now()->month, 'year' => now()->year]) }}" 
                       class="text-xs text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300">
                        Lihat Semua
                    </a>
                </div>
            </div>
            
            <!-- Ditolak -->
            <div class="bg-red-50 dark:bg-red-900 rounded-lg p-3 shadow-sm flex-1 min-w-[120px]">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 mr-2">
                            <svg class="w-6 h-6 text-red-500 dark:text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-xs font-medium text-red-800 dark:text-red-200">Ditolak</div>
                            <div class="text-xl font-bold text-red-900 dark:text-red-100">
                                {{ $rejectedCount }}
                            </div>
                        </div>
                    </div>
                    <a href="{{ route('reports.index', ['status' => ['Ditolak', 'Ditolak Verifikator', 'Ditolak VP', 'Ditolak HR'], 'month' => now()->month, 'year' => now()->year]) }}" 
                       class="text-xs text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                        Lihat Semua
                    </a>
                </div>
            </div>
            
            <!-- Disetujui -->
            <div class="bg-green-50 dark:bg-green-900 rounded-lg p-3 shadow-sm flex-1 min-w-[120px]">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 mr-2">
                            <svg class="w-6 h-6 text-green-500 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-xs font-medium text-green-800 dark:text-green-200">Disetujui</div>
                            <div class="text-xl font-bold text-green-900 dark:text-green-100">
                                {{ $completedCount }}
                            </div>
                        </div>
                    </div>
                    <a href="{{ route('reports.index', ['status' => 'Selesai', 'month' => now()->month, 'year' => now()->year]) }}" 
                       class="text-xs text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300">
                        Lihat Semua
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Pekerja Yang Belum Laporan Section -->
@if(isset($usersWithoutReport) && count($usersWithoutReport) > 0)
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-8">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Belum Membuat Laporan Hari Ini</h3>
                <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm">
                    {{ count($usersWithoutReport) }} Orang
                </span>
            </div>
            
            <div class="space-y-3">
                @foreach($usersWithoutReport as $user)
                    <div class="flex items-center space-x-3 bg-white dark:bg-gray-700 p-3 rounded-lg shadow-sm">
                        <img src="{{ $user['avatar_url'] }}" alt="Avatar" class="h-10 w-10 rounded-full">
                        <div>
                            <div class="font-medium text-gray-900 dark:text-white">{{ $user['name'] }}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ $user['email'] }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif

<!-- Recent Reports -->
<div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Laporan Terbaru Departemen</h3>
            <a href="{{ route('reports.index') }}" class="text-sm text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                Lihat Semua
            </a>
        </div>
        
        @if(count($recentReports) > 0)
            <!-- Desktop View -->
            <div class="hidden md:block overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Karyawan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Project</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Detail</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($recentReports as $report)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">{{ $report->report_date->translatedFormat('l, d/m/Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">{{ $report->user->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">{{ $report->project_code }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <x-report-status :status="$report->status" />
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
            
            <!-- Mobile View -->
            <div class="md:hidden space-y-4">
                @foreach($recentReports as $report)
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 space-y-3">
                        <div class="flex justify-between items-start">
                            <div>
                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $report->user->name }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $report->report_date->translatedFormat('l, d/m/Y') }}</div>
                            </div>
                            <x-report-status :status="$report->status" />
                        </div>
                        
                        <div class="grid grid-cols-2 gap-2 text-sm">
                            <div>
                                <div class="text-gray-500 dark:text-gray-400">Project</div>
                                <div class="font-medium text-gray-900 dark:text-white">{{ $report->project_code }}</div>
                            </div>
                            <div>
                                <div class="text-gray-500 dark:text-gray-400">Lokasi</div>
                                <div class="font-medium text-gray-900 dark:text-white">{{ $report->location }}</div>
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