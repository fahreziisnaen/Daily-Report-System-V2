<!-- VP Dashboard -->
<div class="bg-gradient-to-r from-blue-400 to-indigo-500 rounded-lg shadow-lg p-6 mb-8">
    <div class="flex items-center justify-between">
        <div class="flex-1">
            <h3 class="text-xl font-semibold text-white mb-2">
                @if($pendingApprovalCount > 0)
                    {{ $pendingApprovalCount }} Laporan Lembur Menunggu Approval! 🔍
                @else
                    Tidak Ada Laporan Lembur yang Perlu Diapprove 🎉
                @endif
            </h3>
            <p class="text-blue-100">
                @if($pendingApprovalCount > 0)
                    Anda memiliki laporan lembur yang perlu diapprove dari tim departemen Anda.
                @else
                    Semua laporan lembur sudah diapprove. Terima kasih atas kerjasama Anda!
                @endif
            </p>
        </div>
        @if($pendingApprovalCount > 0)
            <div class="ml-4">
                <a href="{{ route('approval.index') }}" 
                    class="inline-flex items-center px-4 py-2 bg-white text-indigo-600 rounded-md font-semibold text-sm hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Approval Sekarang
                    <svg class="ml-2 -mr-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        @endif
    </div>
</div>

<!-- VP Statistics -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <x-dashboard.stats-card 
        title="Menunggu Approval ({{ now()->format('F Y') }})"
        :count="$pendingApprovalCount"
        icon="bell"
        iconColor="yellow"
        linkText="Lihat semua"
        linkUrl="{{ route('approval.index') }}"
    />
    
    <x-dashboard.stats-card 
        title="Laporan Disetujui ({{ now()->format('F Y') }})"
        :count="$dashboardService->getApprovedByVpCount(auth()->user())"
        icon="check"
        iconColor="green"
        linkText="Lihat semua"
        linkUrl="{{ route('reports.index') }}"
    />
    
    <x-dashboard.stats-card 
        title="Laporan Ditolak ({{ now()->format('F Y') }})"
        :count="$dashboardService->getRejectedByVpCount(auth()->user())"
        icon="x"
        iconColor="red"
        linkText="Lihat semua"
        linkUrl="{{ route('reports.index') }}"
    />
</div>

<!-- Laporan Menunggu Approval -->
<div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
    <div class="p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Laporan Lembur Menunggu Approval</h3>
            @if(count($pendingApprovalReports) > 0)
                <a href="{{ route('approval.index') }}" class="text-sm text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                    Lihat Semua
                </a>
            @endif
        </div>
        
        @if(count($pendingApprovalReports) > 0)
            <!-- Desktop View (Hidden on Mobile) -->
            <div class="hidden md:block overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Tanggal Laporan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Nama Karyawan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Project</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Verifikator</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Diverifikasi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($pendingApprovalReports as $report)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">{{ $report->report_date->format('d/m/Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">{{ $report->user->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">{{ $report->project_code }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">{{ $report->verifikator ? $report->verifikator->name : 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $report->verified_at ? $report->verified_at->diffForHumans() : 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <a href="{{ route('approval.show', $report) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 mr-3">
                                        Approve
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Mobile View (Hidden on Desktop) -->
            <div class="md:hidden space-y-4">
                @foreach($pendingApprovalReports as $report)
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
                                <div class="text-gray-500 dark:text-gray-400">Verifikator</div>
                                <div class="font-medium text-gray-900 dark:text-white">{{ $report->verifikator ? $report->verifikator->name : 'N/A' }}</div>
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <a href="{{ route('approval.show', $report) }}" 
                                class="inline-flex items-center px-3 py-1.5 bg-indigo-50 text-indigo-600 rounded-md text-sm font-medium hover:bg-indigo-100 dark:bg-indigo-900 dark:text-indigo-200 dark:hover:bg-indigo-800">
                                <span>Approve</span>
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
                Tidak ada laporan lembur yang menunggu approval saat ini.
            </div>
        @endif
    </div>
</div>

<!-- Laporan yang Sudah Direspon -->
<div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Riwayat Approval Lembur Terakhir</h3>
        </div>
        
        @if(count($respondedApprovalReports) > 0)
            <!-- Desktop View (Hidden on Mobile) -->
            <div class="hidden md:block overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Tanggal Laporan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Nama Karyawan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Project</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Diapprove</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Detail</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($respondedApprovalReports as $report)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">{{ $report->report_date->format('d/m/Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">{{ $report->user->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">{{ $report->project_code }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($report->status == \App\Models\Report::STATUS_PENDING_HR)
                                        <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100">
                                            Disetujui
                                        </span>
                                    @elseif($report->status == \App\Models\Report::STATUS_REJECTED_BY_VP)
                                        <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100">
                                            Ditolak
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $report->approved_at ? $report->approved_at->diffForHumans() : 'N/A' }}
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
                @foreach($respondedApprovalReports as $report)
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 space-y-3">
                        <div class="flex justify-between items-start">
                            <div>
                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $report->user->name }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $report->report_date->format('d/m/Y') }}</div>
                            </div>
                            @if($report->status == \App\Models\Report::STATUS_PENDING_HR)
                                <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100">
                                    Disetujui
                                </span>
                            @elseif($report->status == \App\Models\Report::STATUS_REJECTED_BY_VP)
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
                                <div class="text-gray-500 dark:text-gray-400">Diapprove</div>
                                <div class="font-medium text-gray-900 dark:text-white">{{ $report->approved_at ? $report->approved_at->diffForHumans() : 'N/A' }}</div>
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
                Belum ada riwayat approval laporan lembur.
            </div>
        @endif
    </div>
</div> 