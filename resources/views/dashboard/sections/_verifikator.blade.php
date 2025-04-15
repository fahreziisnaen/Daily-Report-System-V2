<!-- Verifikator Dashboard -->
<div class="bg-gradient-to-r from-yellow-400 to-orange-500 rounded-lg shadow-lg p-6 mb-8">
    <div class="flex items-center justify-between">
        <div class="flex-1">
            <h3 class="text-xl font-semibold text-white mb-2">
                @if($pendingVerificationCount > 0)
                    {{ $pendingVerificationCount }} Laporan Menunggu Verifikasi! 🔍
                @else
                    Tidak Ada Laporan yang Perlu Diverifikasi 🎉
                @endif
            </h3>
            <p class="text-yellow-100">
                @if($pendingVerificationCount > 0)
                    Anda memiliki laporan yang perlu diverifikasi dari tim departemen Anda.
                @else
                    Semua laporan sudah diverifikasi. Terima kasih atas kerjasama Anda!
                @endif
            </p>
        </div>
        @if($pendingVerificationCount > 0)
            <div class="ml-4">
                <a href="{{ route('verification.index') }}" 
                    class="inline-flex items-center px-4 py-2 bg-white text-orange-600 rounded-md font-semibold text-sm hover:bg-orange-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                    Verifikasi Sekarang
                    <svg class="ml-2 -mr-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        @endif
    </div>
</div>

<!-- Verifikator Statistics -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <x-dashboard.stats-card 
        title="Menunggu Verifikasi ({{ now()->format('F Y') }})"
        :count="$pendingVerificationCount"
        icon="bell"
        iconColor="yellow"
        linkText="Lihat semua"
        linkUrl="{{ route('verification.index') }}"
    />
    
    <x-dashboard.stats-card 
        title="Laporan Terverifikasi ({{ now()->format('F Y') }})"
        :count="$dashboardService->getApprovedByVerifikatorCount(auth()->user())"
        icon="check"
        iconColor="green"
        linkText="Lihat semua"
        linkUrl="{{ route('reports.index', ['status' => 'Menunggu Approval VP']) }}"
    />
    
    <x-dashboard.stats-card 
        title="Laporan Ditolak ({{ now()->format('F Y') }})"
        :count="$dashboardService->getRejectedByVerifikatorCount(auth()->user())"
        icon="x"
        iconColor="red"
        linkText="Lihat semua"
        linkUrl="{{ route('reports.index', ['status' => 'Ditolak Verifikator']) }}"
    />
</div>

<!-- Laporan Menunggu Verifikasi -->
<div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
    <div class="p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Laporan Menunggu Verifikasi</h3>
            @if(count($pendingVerificationReports) > 0)
                <a href="{{ route('verification.index') }}" class="text-sm text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                    Lihat Semua
                </a>
            @endif
        </div>
        
        @if(count($pendingVerificationReports) > 0)
            <!-- Desktop View (Hidden on Mobile) -->
            <div class="hidden md:block overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Tanggal Laporan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Nama Karyawan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Project</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Diajukan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($pendingVerificationReports as $report)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">{{ $report->report_date->format('d/m/Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">{{ $report->user->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">{{ $report->project_code }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $report->submitted_at ? $report->submitted_at->diffForHumans() : 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <a href="{{ route('verification.show', $report) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 mr-3">
                                        Verifikasi
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Mobile View (Hidden on Desktop) -->
            <div class="md:hidden space-y-4">
                @foreach($pendingVerificationReports as $report)
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 space-y-3">
                        <div class="flex justify-between items-start">
                            <div>
                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $report->user->name }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $report->report_date->format('d/m/Y') }}</div>
                            </div>
                            <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100">
                                Menunggu
                            </span>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-2 text-sm">
                            <div>
                                <div class="text-gray-500 dark:text-gray-400">Project</div>
                                <div class="font-medium text-gray-900 dark:text-white">{{ $report->project_code }}</div>
                            </div>
                            <div>
                                <div class="text-gray-500 dark:text-gray-400">Diajukan</div>
                                <div class="font-medium text-gray-900 dark:text-white">{{ $report->submitted_at ? $report->submitted_at->diffForHumans() : 'N/A' }}</div>
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <a href="{{ route('verification.show', $report) }}" 
                                class="inline-flex items-center px-3 py-1.5 bg-indigo-50 text-indigo-600 rounded-md text-sm font-medium hover:bg-indigo-100 dark:bg-indigo-900 dark:text-indigo-200 dark:hover:bg-indigo-800">
                                <span>Verifikasi</span>
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
                Tidak ada laporan yang menunggu verifikasi saat ini.
            </div>
        @endif
    </div>
</div>

<!-- Laporan yang Sudah Direspon -->
<div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Riwayat Verifikasi Terakhir</h3>
        </div>
        
        @if(count($respondedVerificationReports) > 0)
            <!-- Desktop View (Hidden on Mobile) -->
            <div class="hidden md:block overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Tanggal Laporan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Nama Karyawan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Project</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Diverifikasi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Detail</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($respondedVerificationReports as $report)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">{{ $report->report_date->format('d/m/Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">{{ $report->user->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">{{ $report->project_code }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($report->status == \App\Models\Report::STATUS_PENDING_APPROVAL)
                                        <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100">
                                            Disetujui
                                        </span>
                                    @elseif($report->status == \App\Models\Report::STATUS_REJECTED_BY_VERIFIER)
                                        <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100">
                                            Ditolak
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $report->verified_at ? $report->verified_at->diffForHumans() : 'N/A' }}
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
                @foreach($respondedVerificationReports as $report)
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 space-y-3">
                        <div class="flex justify-between items-start">
                            <div>
                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $report->user->name }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $report->report_date->format('d/m/Y') }}</div>
                            </div>
                            @if($report->status == \App\Models\Report::STATUS_PENDING_APPROVAL)
                                <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100">
                                    Disetujui
                                </span>
                            @elseif($report->status == \App\Models\Report::STATUS_REJECTED_BY_VERIFIER)
                                <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100">
                                    Ditolak
                                </span>
                            @endif
                        </div>
                        
                        <div class="grid grid-cols-2 gap-2 text-sm">
                            <div>
                                <div class="text-gray-500 dark:text-gray-400">Project</div>
                                <div class="font-medium text-gray-900 dark:text-white">{{ $report->project_code }}</div>
                            </div>
                            <div>
                                <div class="text-gray-500 dark:text-gray-400">Diverifikasi</div>
                                <div class="font-medium text-gray-900 dark:text-white">{{ $report->verified_at ? $report->verified_at->diffForHumans() : 'N/A' }}</div>
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
                Belum ada riwayat verifikasi.
            </div>
        @endif
    </div>
</div>