<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Detail Laporan') }}
            </h2>
            <a href="{{ route('hr-review.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 dark:bg-gray-600 dark:hover:bg-gray-500 dark:text-white font-bold py-2 px-4 rounded">
                Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <!-- Basic Info Section -->
                    <div class="mb-6">
                        <h3 class="text-lg font-medium border-b border-gray-200 dark:border-gray-700 pb-2 mb-4">Informasi Dasar</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Hari, Tanggal</p>
                                <p class="font-medium">{{ $report->report_date->translatedFormat('l, d/m/Y') }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Pekerja</p>
                                <p class="font-medium">{{ $report->user->name }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Project</p>
                                <p class="font-medium">{{ $report->project_code }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Lokasi</p>
                                <p class="font-medium">{{ $report->location }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Divisi</p>
                                <p class="font-medium">{{ $report->user->department->name ?? 'Tidak Ada' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Status</p>
                                <p class="font-medium">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                        {{ $report->status }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Time Info Section -->
                    <div class="mb-6">
                        <h3 class="text-lg font-medium border-b border-gray-200 dark:border-gray-700 pb-2 mb-4">Informasi Waktu</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Waktu Mulai</p>
                                <p class="font-medium">{{ $report->start_time->format('H:i') }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Waktu Selesai</p>
                                <p class="font-medium">{{ $report->end_time->format('H:i') }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Total Jam</p>
                                <p class="font-medium">{{ $report->total_hours }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Lembur</p>
                                <p class="font-medium">{{ $report->is_overtime ? 'Ya' : 'Tidak' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Menginap</p>
                                <p class="font-medium">{{ $report->is_overnight ? 'Ya' : 'Tidak' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Ganti Shift</p>
                                <p class="font-medium">{{ $report->is_shift_change ? 'Ya' : 'Tidak' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Work Details Section -->
                    <div class="mb-6">
                        <h3 class="text-lg font-medium border-b border-gray-200 dark:border-gray-700 pb-2 mb-4">Detail Pekerjaan</h3>
                        <div class="mb-4">
                            <p class="text-sm text-gray-600 dark:text-gray-400">Deskripsi Pekerjaan</p>
                            <div class="mt-2 p-4 bg-gray-50 dark:bg-gray-900 rounded">
                                <p>{{ $report->work_description }}</p>
                            </div>
                        </div>
                        @if($report->attachment)
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Lampiran</p>
                            <div class="mt-2">
                                <a href="{{ Storage::url($report->attachment) }}" target="_blank" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                    Unduh Lampiran
                                </a>
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Verification History Section -->
                    <div class="mb-6">
                        <h3 class="text-lg font-medium border-b border-gray-200 dark:border-gray-700 pb-2 mb-4">Riwayat Verifikasi</h3>
                        <div class="space-y-4">
                            @if($report->verified_at)
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <span class="h-8 w-8 rounded-full bg-green-100 flex items-center justify-center">
                                        <svg class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </span>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium">Diverifikasi oleh {{ $report->verified_by_user->name ?? 'Unknown' }}</p>
                                    <p class="text-sm text-gray-500">{{ $report->verified_at->format('d/m/Y H:i') }}</p>
                                </div>
                            </div>
                            @else
                            <p>Belum diverifikasi</p>
                            @endif
                        </div>
                    </div>

                    <!-- Approval History Section -->
                    <div class="mb-6">
                        <h3 class="text-lg font-medium border-b border-gray-200 dark:border-gray-700 pb-2 mb-4">Riwayat Persetujuan</h3>
                        <div class="space-y-4">
                            @if($report->approved_at)
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <span class="h-8 w-8 rounded-full bg-green-100 flex items-center justify-center">
                                        <svg class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </span>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium">Disetujui oleh {{ $report->approved_by_user->name ?? 'Unknown' }}</p>
                                    <p class="text-sm text-gray-500">{{ $report->approved_at->format('d/m/Y H:i') }}</p>
                                </div>
                            </div>
                            @else
                            <p>Belum disetujui</p>
                            @endif
                        </div>
                    </div>

                    <!-- Actions Section -->
                    <div class="flex justify-end space-x-4 mt-8">
                        <form action="{{ route('hr-review.approve', $report) }}" method="POST">
                            @csrf
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                Setujui
                            </button>
                        </form>
                        <button type="button" onclick="showRejectModal()" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                            Tolak
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tolak -->
    <div id="rejectModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="mt-3">
                <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100">Tolak Laporan</h3>
                <div class="mt-2 px-7 py-3">
                    <form id="rejectForm" action="{{ route('hr-review.reject', $report) }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label for="rejection_notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Alasan Penolakan</label>
                            <textarea id="rejection_notes" name="rejection_notes" rows="3" 
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                      required></textarea>
                        </div>
                        <div class="flex justify-end space-x-3">
                            <button type="button" onclick="hideRejectModal()"
                                    class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 dark:bg-gray-600 dark:text-gray-300 dark:hover:bg-gray-500">
                                Batal
                            </button>
                            <button type="submit"
                                    class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 dark:bg-red-700 dark:hover:bg-red-800">
                                Tolak
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function showRejectModal() {
            const modal = document.getElementById('rejectModal');
            modal.classList.remove('hidden');
        }

        function hideRejectModal() {
            const modal = document.getElementById('rejectModal');
            modal.classList.add('hidden');
        }
    </script>
    @endpush
</x-app-layout> 