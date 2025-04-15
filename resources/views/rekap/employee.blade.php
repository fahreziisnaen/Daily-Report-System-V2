<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-white leading-tight">
            {{ __('Rekap Laporan Saya') }}
        </h2>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Filter Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-4 sm:p-6">
                    <form method="GET" action="{{ route('rekap.index') }}" class="flex flex-col sm:flex-row gap-4">
                        <div class="flex-1">
                            <x-input-label for="month" :value="__('Bulan')" />
                            <select name="month" id="month" class="mt-1 block w-full rounded-md border-gray-300" onchange="this.form.submit()">
                                @foreach($months as $value => $label)
                                    <option value="{{ $value }}" {{ $value == $month ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex-1">
                            <x-input-label for="year" :value="__('Tahun')" />
                            <select name="year" id="year" class="mt-1 block w-full rounded-md border-gray-300" onchange="this.form.submit()">
                                @foreach($years as $yearOption)
                                    <option value="{{ $yearOption }}" {{ $yearOption == $year ? 'selected' : '' }}>
                                        {{ $yearOption }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Summary Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Ringkasan Laporan</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-blue-50 rounded-lg p-4">
                            <div class="text-sm font-medium text-blue-700">Total Jam Kerja</div>
                            <div class="text-2xl font-bold text-blue-800 mt-2">
                                {{ \App\Helpers\TimeHelper::formatHoursToHoursMinutes($userData['total_work_hours']) }}
                            </div>
                        </div>
                        
                        <div class="bg-yellow-50 rounded-lg p-4">
                            <div class="text-sm font-medium text-yellow-700">Total Jam Lembur</div>
                            <div class="text-2xl font-bold text-yellow-800 mt-2">
                                {{ \App\Helpers\TimeHelper::formatHoursToHoursMinutes($userData['total_overtime_hours']) }}
                            </div>
                        </div>
                        
                        <div class="bg-green-50 rounded-lg p-4">
                            <div class="text-sm font-medium text-green-700">Jumlah Laporan</div>
                            <div class="text-2xl font-bold text-green-800 mt-2">
                                {{ $userData['report_count'] }}
                            </div>
                        </div>
                    </div>
                    
                    @if($userData['report_count'] > 0)
                        <div class="mt-6 flex justify-end">
                            <a href="{{ route('rekap.employee.export', ['month' => $month, 'year' => $year]) }}" 
                                class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                Export Excel
                            </a>
                        </div>
                    @else
                        <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                            <p class="text-center text-gray-500">Tidak ada laporan untuk periode yang dipilih.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <!-- All Reports Section -->
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Daftar Laporan Saya</h3>
                    
                    <!-- Desktop view -->
                    <div class="hidden md:block">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tanggal</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Proyek</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Jam Kerja</th>
                                        @if(isset($showReviewer) && $showReviewer)
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Direview Oleh</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tanggal Review</th>
                                        @endif
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-600">
                                    @php
                                        $myReports = $allReports->where('user_id', Auth::id())
                                            ->filter(function($report) use ($month, $year) {
                                                return $report->report_date->month == $month && $report->report_date->year == $year;
                                            });
                                    @endphp
                                    @forelse($myReports as $report)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $report->report_date->format('d/m/Y') }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ $report->project_code }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                @if($report->status === 'Draft')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                                        Draft
                                                    </span>
                                                @elseif($report->status === 'Menunggu Verifikasi')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                                        Menunggu Verifikasi
                                                    </span>
                                                @elseif($report->status === 'Ditolak Verifikator')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                                        Ditolak Verifikator
                                                    </span>
                                                @elseif($report->status === 'Menunggu Approval VP')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                        Menunggu Approval VP
                                                    </span>
                                                @elseif($report->status === 'Ditolak VP')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                                        Ditolak VP
                                                    </span>
                                                @elseif($report->status === 'Menunggu Review HR')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                                                        Menunggu Review HR
                                                    </span>
                                                @elseif($report->status === 'Ditolak HR')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                                        Ditolak HR
                                                    </span>
                                                @elseif($report->status === 'Selesai')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                        Selesai
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                                {{ Carbon\Carbon::parse($report->start_time)->format('H:i') }} - {{ Carbon\Carbon::parse($report->end_time)->format('H:i') }}
                                            </td>
                                            @if(isset($showReviewer) && $showReviewer && $report->reviewer)
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ $report->reviewer->name ?? 'N/A' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ $report->reviewed_at ? $report->reviewed_at->format('d/m/Y H:i') : 'N/A' }}</td>
                                            @endif
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <a href="{{ route('reports.show', $report) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">Lihat</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300 text-center">Anda tidak memiliki laporan pada periode ini</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Mobile view -->
                    <div class="md:hidden space-y-4">
                        @php
                            $myReports = $allReports->where('user_id', Auth::id())
                                ->filter(function($report) use ($month, $year) {
                                    return $report->report_date->month == $month && $report->report_date->year == $year;
                                });
                        @endphp
                        @forelse($myReports as $report)
                            <div class="bg-gray-50 dark:bg-gray-700 rounded p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $report->report_date->format('d/m/Y') }}
                                    </div>
                                    @if($report->status === 'Draft')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                            Draft
                                        </span>
                                    @elseif($report->status === 'Menunggu Verifikasi')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                            Menunggu Verifikasi
                                        </span>
                                    @elseif($report->status === 'Ditolak Verifikator')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                            Ditolak Verifikator
                                        </span>
                                    @elseif($report->status === 'Menunggu Approval VP')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                            Menunggu Approval VP
                                        </span>
                                    @elseif($report->status === 'Ditolak VP')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                            Ditolak VP
                                        </span>
                                    @elseif($report->status === 'Menunggu Review HR')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                                            Menunggu Review HR
                                        </span>
                                    @elseif($report->status === 'Ditolak HR')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                            Ditolak HR
                                        </span>
                                    @elseif($report->status === 'Selesai')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                            Selesai
                                        </span>
                                    @endif
                                </div>
                                <div class="space-y-1 mb-3">
                                    <div class="flex justify-between">
                                        <span class="text-xs text-gray-500 dark:text-gray-400">Proyek</span>
                                        <span class="text-sm text-gray-900 dark:text-white">{{ $report->project_code }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-xs text-gray-500 dark:text-gray-400">Jam Kerja</span>
                                        <span class="text-sm text-gray-900 dark:text-white">{{ Carbon\Carbon::parse($report->start_time)->format('H:i') }} - {{ Carbon\Carbon::parse($report->end_time)->format('H:i') }}</span>
                                    </div>
                                    @if($report->reviewer)
                                    <div class="flex justify-between">
                                        <span class="text-xs text-gray-500 dark:text-gray-400">Direview oleh</span>
                                        <span class="text-sm text-gray-900 dark:text-white">{{ $report->reviewer->name ?? 'N/A' }}</span>
                                    </div>
                                    @endif
                                    @if($report->reviewed_at)
                                    <div class="flex justify-between">
                                        <span class="text-xs text-gray-500 dark:text-gray-400">Tanggal review</span>
                                        <span class="text-sm text-gray-900 dark:text-white">{{ $report->reviewed_at ? $report->reviewed_at->format('d/m/Y H:i') : 'N/A' }}</span>
                                    </div>
                                    @endif
                                </div>
                                <div class="flex justify-end">
                                    <a href="{{ route('reports.show', $report) }}" class="inline-flex items-center px-3 py-1.5 bg-indigo-50 text-indigo-600 dark:bg-indigo-900 dark:text-indigo-200 rounded-md hover:bg-indigo-100 dark:hover:bg-indigo-800">
                                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        Lihat
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="bg-gray-50 dark:bg-gray-700 rounded p-4 text-center text-gray-500 dark:text-gray-400">
                                Anda tidak memiliki laporan pada periode ini
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 