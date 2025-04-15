<!-- HR Dashboard -->
<div class="bg-gradient-to-r from-purple-400 to-pink-500 rounded-lg shadow-lg p-6 mb-8">
    <div class="flex items-center justify-between">
        <div class="flex-1">
            <h3 class="text-xl font-semibold text-white mb-2">
                @if($pendingHrReviewCount > 0)
                    {{ $pendingHrReviewCount }} Laporan Menunggu Review! 🔍
                @else
                    Tidak Ada Laporan yang Perlu Direview 🎉
                @endif
            </h3>
            <p class="text-purple-100">
                @if($pendingHrReviewCount > 0)
                    Anda memiliki laporan yang perlu direview setelah disetujui oleh VP.
                @else
                    Semua laporan sudah ditinjau. Terima kasih atas kerjasama Anda!
                @endif
            </p>
        </div>
        @if($pendingHrReviewCount > 0)
            <div class="ml-4">
                <a href="{{ route('hr-review.index') }}" 
                    class="inline-flex items-center px-4 py-2 bg-white text-purple-600 rounded-md font-semibold text-sm hover:bg-purple-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                    Review Sekarang
                    <svg class="ml-2 -mr-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        @endif
    </div>
</div>

<!-- HR Statistics -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <x-dashboard.stats-card 
        title="Menunggu Review ({{ now()->format('F Y') }})"
        :count="$pendingHrReviewCount"
        icon="bell"
        iconColor="purple"
        linkText="Lihat semua"
        linkUrl="{{ route('hr-review.index') }}"
    />
    
    <x-dashboard.stats-card 
        title="Disetujui HR ({{ now()->format('F Y') }})"
        :count="$approvedHrCount"
        icon="check"
        iconColor="green"
        linkText="Lihat semua"
        linkUrl="{{ route('reports.index', ['status' => \App\Models\Report::STATUS_COMPLETED]) }}"
    />
    
    <x-dashboard.stats-card 
        title="Ditolak HR ({{ now()->format('F Y') }})"
        :count="$rejectedHrCount"
        icon="x"
        iconColor="red"
        linkText="Lihat semua"
        linkUrl="{{ route('reports.index', ['status' => \App\Models\Report::STATUS_REJECTED_BY_HR]) }}"
    />
</div>

<!-- Recent Reports Section -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
        <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Laporan Menunggu Review</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">Laporan yang memerlukan review dari HR</p>
        </div>
        <div class="p-6">
            <div class="space-y-4">
                @forelse($pendingReviews as $report)
                    <div class="flex items-start p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <div class="flex-shrink-0">
                            <span class="bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200 inline-flex items-center justify-center w-10 h-10 rounded-full">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                            </span>
                        </div>
                        <div class="ml-4 flex-1">
                            <div class="flex items-center justify-between mb-1">
                                <h4 class="text-md font-medium text-gray-900 dark:text-gray-100">{{ $report->user->name }}</h4>
                                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">{{ $report->approved_at ? $report->approved_at->diffForHumans() : 'N/A' }}</span>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-300">{{ $report->project_code }} - {{ $report->location }}</p>
                            <div class="mt-2">
                                <a href="{{ route('hr-review.show', $report) }}" class="text-sm font-medium text-purple-600 hover:text-purple-800 dark:text-purple-400 dark:hover:text-purple-300">
                                    Lihat Detail →
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="bg-gray-50 rounded-lg p-4 space-y-3">
                        <div class="text-center text-sm text-gray-500">
                            Tidak ada laporan yang memerlukan review
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
    
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
        <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Laporan Terbaru Selesai</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">Laporan yang telah direview oleh HR</p>
        </div>
        <div class="p-6">
            <div class="space-y-4">
                @forelse($recentReviewed as $report)
                    <div class="flex items-start p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <div class="flex-shrink-0">
                            <span class="@if($report->status === \App\Models\Report::STATUS_COMPLETED) bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 @else bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 @endif inline-flex items-center justify-center w-10 h-10 rounded-full">
                                @if($report->status === \App\Models\Report::STATUS_COMPLETED)
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                @else
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                @endif
                            </span>
                        </div>
                        <div class="ml-4 flex-1">
                            <div class="flex items-center justify-between mb-1">
                                <h4 class="text-md font-medium text-gray-900 dark:text-gray-100">{{ $report->user->name }}</h4>
                                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">{{ $report->reviewed_at ? $report->reviewed_at->diffForHumans() : 'N/A' }}</span>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-300">{{ $report->project_code }} - {{ $report->location }}</p>
                            <div class="mt-2">
                                <a href="{{ route('reports.show', $report) }}" class="text-sm font-medium text-purple-600 hover:text-purple-800 dark:text-purple-400 dark:hover:text-purple-300">
                                    Lihat Detail →
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="bg-gray-50 rounded-lg p-4 space-y-3">
                        <div class="text-center text-sm text-gray-500">
                            Belum ada laporan yang diselesaikan
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- HR Report Summary Section -->
<div class="bg-white dark:bg-gray-800 rounded-lg shadow mb-8">
    <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Rekap Laporan Bulanan</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Ringkasan laporan per departemen bulan ini</p>
            </div>
            @php
                $currentMonth = now()->format('F Y');
            @endphp
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800 dark:bg-purple-800 dark:text-purple-100">
                {{ $currentMonth }}
            </span>
        </div>
    </div>
    <div class="p-6">
        @php
            $departments = \App\Models\Department::all();
            $reportsByDepartment = [];
            
            foreach ($departments as $department) {
                $completed = \App\Models\Report::whereHas('user', function($query) use ($department) {
                        $query->where('department_id', $department->id);
                    })
                    ->whereMonth('report_date', now()->month)
                    ->whereYear('report_date', now()->year)
                    ->where('status', \App\Models\Report::STATUS_COMPLETED)
                    ->count();
                    
                $pending = \App\Models\Report::whereHas('user', function($query) use ($department) {
                        $query->where('department_id', $department->id);
                    })
                    ->whereMonth('report_date', now()->month)
                    ->whereYear('report_date', now()->year)
                    ->where('status', \App\Models\Report::STATUS_PENDING_HR)
                    ->count();
                    
                $rejected = \App\Models\Report::whereHas('user', function($query) use ($department) {
                        $query->where('department_id', $department->id);
                    })
                    ->whereMonth('report_date', now()->month)
                    ->whereYear('report_date', now()->year)
                    ->where('status', \App\Models\Report::STATUS_REJECTED_BY_HR)
                    ->count();
                    
                $total = $completed + $pending + $rejected;
                
                $reportsByDepartment[] = [
                    'name' => $department->name,
                    'completed' => $completed,
                    'pending' => $pending,
                    'rejected' => $rejected,
                    'total' => $total
                ];
            }
        @endphp

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Departemen</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Menunggu Review</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Disetujui</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Ditolak</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Total</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($reportsByDepartment as $dept)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                {{ $dept['name'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                    {{ $dept['pending'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                    {{ $dept['completed'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                    {{ $dept['rejected'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                                    {{ $dept['total'] }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div class="mt-4 flex justify-end">
            <a href="{{ route('reports.index') }}" class="text-sm font-medium text-purple-600 hover:text-purple-800 dark:text-purple-400 dark:hover:text-purple-300">
                Lihat semua laporan →
            </a>
        </div>
    </div>
</div> 