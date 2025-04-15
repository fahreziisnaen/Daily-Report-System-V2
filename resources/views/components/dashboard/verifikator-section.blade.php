@props(['pendingVerificationCount' => 0])

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