<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Verifikasi Laporan') }} - {{ $report->report_date->translatedFormat('l, d/m/Y') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <!-- Basic Information -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold mb-4">Informasi Dasar</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">Hari, Tanggal</div>
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
                        </div>
                    </div>

                    <!-- Time Information -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold mb-4">Informasi Waktu</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Waktu Mulai</p>
                                <p class="font-medium">{{ $report->start_time->format('H:i') }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Waktu Selesai</p>
                                <p class="font-medium">{{ $report->end_time->format('H:i') }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Jenis Hari Kerja</p>
                                <p class="font-medium">{{ $report->work_day_type }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Lembur</p>
                                <p class="font-medium">{{ $report->is_overtime ? 'Ya' : 'Tidak' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Work Details -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold mb-4">Detail Pekerjaan</h3>
                        <div class="space-y-4">
                            @foreach($report->details as $detail)
                                <div class="border dark:border-gray-700 rounded-lg p-4">
                                    <div>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">Deskripsi</p>
                                        <p class="font-medium">{{ $detail->description }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Verification Actions -->
                    <div class="mt-8 flex justify-end space-x-4">
                        <form action="{{ route('verification.reject', $report) }}" method="POST" class="inline">
                            @csrf
                            <button type="button" 
                                    onclick="showRejectModal()"
                                    class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                                Tolak
                            </button>
                        </form>

                        <form action="{{ route('verification.approve', $report) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" 
                                    class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                Setujui
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Rejection Modal -->
    <div id="rejectModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="mt-3">
                <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white mb-4">Tolak Laporan</h3>
                <form action="{{ route('verification.reject', $report) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="rejection_notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Alasan Penolakan
                        </label>
                        <textarea name="rejection_notes" 
                                  id="rejection_notes" 
                                  rows="4" 
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                  required></textarea>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Boleh Direvisi?
                        </label>
                        <div class="flex space-x-4">
                            <label class="inline-flex items-center">
                                <input type="radio" name="can_revise" value="1" class="form-radio text-indigo-600" checked>
                                <span class="ml-2 text-gray-700 dark:text-gray-300">Ya, boleh direvisi</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="can_revise" value="0" class="form-radio text-indigo-600">
                                <span class="ml-2 text-gray-700 dark:text-gray-300">Tidak boleh direvisi</span>
                            </label>
                        </div>
                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Jika disetujui untuk revisi, karyawan dapat mengedit dan mengirim ulang laporan ini
                        </div>
                    </div>
                    
                    <div class="flex justify-end space-x-4">
                        <button type="button" 
                                onclick="hideRejectModal()"
                                class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            Batal
                        </button>
                        <button type="submit" 
                                class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                            Tolak
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function showRejectModal() {
            document.getElementById('rejectModal').classList.remove('hidden');
        }

        function hideRejectModal() {
            document.getElementById('rejectModal').classList.add('hidden');
        }
    </script>
    @endpush
</x-app-layout> 