<!-- Greeting Section -->
<x-dashboard.greeting-section />

<!-- Report Reminder Card -->
<div class="bg-gradient-to-r from-blue-500 to-indigo-600 rounded-lg shadow-lg p-6 mb-8">
    <div class="flex items-center justify-between">
        <div class="flex-1">
            <h3 class="text-xl font-semibold text-white mb-2">
                @if($hasReportToday)
                    Laporan Hari Ini Sudah Dibuat! ✨
                @else
                    @if(auth()->user()->isVicePresident())
                        Anda Sebagai Vice President Tidak Perlu Membuat Laporan 📊
                    @else
                        Jangan Lupa Buat Laporan Anda Hari Ini! 📝
                    @endif
                @endif
            </h3>
            <p class="text-blue-100">
                @if($hasReportToday)
                    Terima kasih atas kontribusimu hari ini. Tetap semangat!
                @else
                    @if(auth()->user()->isVicePresident())
                        Anda dapat melihat dan menyetujui laporan dari tim Anda.
                    @else
                        Yuk, buat laporan kegiatan kerja kamu hari ini sebelum lupa!
                    @endif
                @endif
            </p>
        </div>
        @unless($hasReportToday || auth()->user()->isVicePresident())
            <div class="ml-4">
                <a href="{{ route('reports.create') }}" 
                    class="inline-flex items-center px-4 py-2 bg-white text-blue-600 rounded-md font-semibold text-sm hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Buat Laporan
                    <svg class="ml-2 -mr-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        @endunless
    </div>
</div>

<!-- Personal Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <!-- Laporan Bulan Ini -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
        <div class="p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 dark:bg-green-900">
                    <svg class="h-8 w-8 text-green-600 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <div class="ml-5">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Laporan Bulan Ini</h3>
                    <div class="mt-2 text-3xl font-semibold text-gray-900 dark:text-gray-100">
                        {{ $monthlyReports }}
                    </div>
                    <div class="mt-2">
                        <a href="{{ route('reports.index', [
                            'month' => now()->format('m'),
                            'year' => now()->format('Y'),
                            'user_id' => auth()->id()
                        ]) }}" class="text-sm text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300">
                            Lihat Semua →
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Laporan Draft -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
        <div class="p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 dark:bg-yellow-900">
                    <svg class="h-8 w-8 text-yellow-600 dark:text-yellow-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                </div>
                <div class="ml-5">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Laporan Belum Dikirim</h3>
                    <div class="mt-2 text-3xl font-semibold text-gray-900 dark:text-gray-100">
                        {{ $draftCount }}
                    </div>
                    <div class="mt-2">
                        <a href="{{ route('reports.index', ['status' => 'Draft', 'user_id' => auth()->id()]) }}" class="text-sm text-yellow-600 hover:text-yellow-900 dark:text-yellow-400 dark:hover:text-yellow-300">
                            Lihat Semua →
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Laporan Hari Ini -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
        <div class="p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 dark:bg-purple-900">
                    <svg class="h-8 w-8 text-purple-600 dark:text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-5">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Laporan Hari Ini</h3>
                    <div class="mt-2 text-3xl font-semibold text-gray-900 dark:text-gray-100">
                        {{ $dailyReports }}
                    </div>
                    <div class="mt-2">
                        <a href="{{ route('reports.index', [
                            'report_date' => now()->format('Y-m-d'),
                            'user_id' => auth()->id()
                        ]) }}" class="text-sm text-purple-600 hover:text-purple-900 dark:text-purple-400 dark:hover:text-purple-300">
                            Lihat Semua →
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reports Tables Section -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Laporan Terbaru -->
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg">
        <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Laporan Terbaru</h3>
        </div>
        <div class="divide-y divide-gray-200 dark:divide-gray-700">
            @forelse($recentReports as $report)
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <span class="@if($report->status === \App\Models\Report::STATUS_COMPLETED) bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 @elseif($report->status === \App\Models\Report::STATUS_REJECTED) bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 @else bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200 @endif inline-flex items-center justify-center w-10 h-10 rounded-full">
                                    @if($report->status === \App\Models\Report::STATUS_COMPLETED)
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    @elseif($report->status === \App\Models\Report::STATUS_REJECTED)
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    @else
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    @endif
                                </span>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $report->project_code }}</h4>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $report->location }}</p>
                            </div>
                        </div>
                        <div class="flex items-center">
                            <span class="text-sm text-gray-500 dark:text-gray-400">{{ $report->created_at->format('d M Y') }}</span>
                            <a href="{{ route('reports.show', $report) }}" class="ml-4 text-sm font-medium text-purple-600 hover:text-purple-800 dark:text-purple-400 dark:hover:text-purple-300">
                                Lihat Detail →
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-6">
                    <p class="text-center text-sm text-gray-500 dark:text-gray-400">Belum ada laporan yang dibuat</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Status Laporan -->
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg">
        <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Status Laporan Bulan Ini</h3>
        </div>
        <div class="p-6">
            <div class="space-y-6">
                <!-- Menunggu Verifikasi -->
                <a href="{{ route('reports.index', ['status' => \App\Models\Report::STATUS_PENDING_VERIFICATION, 'user_id' => auth()->id()]) }}" 
                   class="flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700 p-2 rounded-lg transition-colors duration-150">
                    <div class="flex items-center">
                        <span class="w-3 h-3 rounded-full bg-yellow-400"></span>
                        <span class="ml-3 text-sm font-medium text-gray-900 dark:text-gray-100">Menunggu Verifikasi</span>
                    </div>
                    <span class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $pendingVerificationCount ?? 0 }}</span>
                </a>

                <!-- Menunggu Approval VP -->
                <a href="{{ route('reports.index', ['status' => \App\Models\Report::STATUS_PENDING_APPROVAL, 'user_id' => auth()->id()]) }}" 
                   class="flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700 p-2 rounded-lg transition-colors duration-150">
                    <div class="flex items-center">
                        <span class="w-3 h-3 rounded-full bg-blue-400"></span>
                        <span class="ml-3 text-sm font-medium text-gray-900 dark:text-gray-100">Menunggu Approval VP</span>
                    </div>
                    <span class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $pendingApprovalCount ?? 0 }}</span>
                </a>

                <!-- Menunggu Review HR -->
                <a href="{{ route('reports.index', ['status' => \App\Models\Report::STATUS_PENDING_HR, 'user_id' => auth()->id()]) }}" 
                   class="flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700 p-2 rounded-lg transition-colors duration-150">
                    <div class="flex items-center">
                        <span class="w-3 h-3 rounded-full bg-purple-400"></span>
                        <span class="ml-3 text-sm font-medium text-gray-900 dark:text-gray-100">Menunggu Review HR</span>
                    </div>
                    <span class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $pendingHrCount ?? 0 }}</span>
                </a>

                <!-- Disetujui -->
                <a href="{{ route('reports.index', ['status' => \App\Models\Report::STATUS_COMPLETED, 'user_id' => auth()->id()]) }}" 
                   class="flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700 p-2 rounded-lg transition-colors duration-150">
                    <div class="flex items-center">
                        <span class="w-3 h-3 rounded-full bg-green-400"></span>
                        <span class="ml-3 text-sm font-medium text-gray-900 dark:text-gray-100">Disetujui</span>
                    </div>
                    <span class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $completedCount ?? 0 }}</span>
                </a>

                <!-- Ditolak -->
                <a href="{{ route('reports.index', [
                    'status' => [
                        \App\Models\Report::STATUS_REJECTED,
                        \App\Models\Report::STATUS_REJECTED_BY_HR,
                        \App\Models\Report::STATUS_REJECTED_BY_VERIFIER,
                        \App\Models\Report::STATUS_REJECTED_BY_VP
                    ],
                    'user_id' => auth()->id()
                ]) }}" 
                   class="flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700 p-2 rounded-lg transition-colors duration-150">
                    <div class="flex items-center">
                        <span class="w-3 h-3 rounded-full bg-red-400"></span>
                        <span class="ml-3 text-sm font-medium text-gray-900 dark:text-gray-100">Ditolak</span>
                    </div>
                    <span class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $rejectedCount ?? 0 }}</span>
                </a>

                <!-- Tanpa Lembur -->
                <a href="{{ route('reports.index', [
                    'is_overtime' => false,
                    'user_id' => auth()->id()
                ]) }}" 
                   class="flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700 p-2 rounded-lg transition-colors duration-150">
                    <div class="flex items-center">
                        <span class="w-3 h-3 rounded-full bg-gray-400"></span>
                        <span class="ml-3 text-sm font-medium text-gray-900 dark:text-gray-100">Tanpa Lembur</span>
                    </div>
                    <span class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $nonOvertimeCount ?? 0 }}</span>
                </a>
            </div>
        </div>
    </div>
</div> 