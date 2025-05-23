@props(['hasReportToday' => false])

<div class="bg-gradient-to-r from-blue-500 to-indigo-600 rounded-lg shadow-lg p-6 mb-8">
    <div class="flex items-center justify-between">
        <div class="flex-1">
            <h3 class="text-xl font-semibold text-white mb-2">
                @if($hasReportToday)
                    Laporan Hari Ini Sudah Dibuat! ✨
                @else
                    Sudah Buat Laporan Pekerjaan Hari Ini? 📝
                @endif
            </h3>
            <p class="text-blue-100">
                @if($hasReportToday)
                    Terima kasih atas kontribusimu hari ini. Tetap semangat!
                @else
                    Yuk, buat laporan kegiatan kerja kamu hari ini sebelum lupa!
                @endif
            </p>
        </div>
        @unless($hasReportToday)
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