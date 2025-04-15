<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Detail Laporan') }}
            </h2>
            <div class="flex space-x-2">
                @if($report->status === 'Draft' && $report->is_overtime)
                    <form action="{{ route('reports.submit', $report) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" 
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                    d="M5 13l4 4L19 7"/>
                            </svg>
                            {{ __('Kirim Laporan') }}
                        </button>
                    </form>
                @endif
                @if($report->is_overtime)
                    <a href="{{ route('reports.export', $report) }}" 
                        class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        {{ __('Lembur') }}
                    </a>
                @endif
                @can('update', $report)
                    <a href="{{ route('reports.edit', $report) }}" 
                        class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        {{ __('Edit') }}
                    </a>
                @endcan
                @can('delete', $report)
                    <!-- Delete Button and Modal -->
                    <div x-data="{ showModal: false }">
                        <!-- Trigger Button -->
                        <button @click="showModal = true" 
                            class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            {{ __('Hapus') }}
                        </button>

                        <!-- Modal Backdrop -->
                        <div x-show="showModal" 
                            x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0"
                            x-transition:enter-end="opacity-100"
                            x-transition:leave="transition ease-in duration-200"
                            x-transition:leave-start="opacity-100"
                            x-transition:leave-end="opacity-0"
                            class="fixed inset-0 bg-gray-500 bg-opacity-75 z-50 flex items-center justify-center"
                            @click="showModal = false">

                            <!-- Modal Content -->
                            <div x-show="showModal" 
                                x-transition:enter="transition ease-out duration-300"
                                x-transition:enter-start="opacity-0 transform scale-90"
                                x-transition:enter-end="opacity-100 transform scale-100"
                                x-transition:leave="transition ease-in duration-200"
                                x-transition:leave-start="opacity-100 transform scale-100"
                                x-transition:leave-end="opacity-0 transform scale-90"
                                @click.stop
                                class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
                                
                                <!-- Modal Header -->
                                <div class="p-4 border-b">
                                    <h3 class="text-lg font-medium text-gray-900">Konfirmasi Penghapusan</h3>
                                </div>

                                <!-- Modal Body -->
                                <div class="p-4">
                                    <div class="flex items-center mb-4">
                                        <div class="flex-shrink-0 bg-red-100 rounded-full p-2 mr-3">
                                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                            </svg>
                                        </div>
                                        <p class="text-gray-600">Apakah Anda yakin ingin menghapus laporan ini?</p>
                                    </div>
                                    <div class="text-sm text-gray-500 bg-gray-50 rounded p-3">
                                        <p><span class="font-medium">Tanggal:</span> {{ $report->report_date->translatedFormat('l, d/m/Y') }}</p>
                                        <p><span class="font-medium">Project:</span> {{ $report->project_code }}</p>
                                    </div>
                                </div>

                                <!-- Modal Footer -->
                                <div class="p-4 border-t flex justify-end space-x-3">
                                    <button type="button" @click="showModal = false"
                                        class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md text-sm font-medium hover:bg-gray-200">
                                        Batal
                                    </button>
                                    <form action="{{ route('reports.destroy', $report) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                            class="px-4 py-2 bg-red-600 text-white rounded-md text-sm font-medium hover:bg-red-700">
                                            Hapus Laporan
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Status Banner -->
            <div class="mb-6">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                    <div class="p-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    @if($report->status === 'Selesai')
                                        <svg class="h-8 w-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    @elseif($report->status === 'Draft')
                                        <svg class="h-8 w-8 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    @elseif($report->status === 'Laporan tanpa Lembur')
                                        <svg class="h-8 w-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    @else
                                        <svg class="h-8 w-8 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    @endif
                            </div>
                            <div>
                                    <h3 class="text-lg font-medium text-gray-900">Status Laporan</h3>
                                    <p class="text-sm text-gray-500">Terakhir diupdate: {{ $report->updated_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }}</p>
                            </div>
                            </div>
                            <div>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                    {{ $report->status === 'Draft' ? 'bg-gray-100 text-gray-800' : '' }}
                                    {{ $report->status === 'Laporan tanpa Lembur' ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ $report->status === 'Menunggu Verifikasi' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                    {{ $report->status === 'Ditolak Verifikator' ? 'bg-red-100 text-red-800' : '' }}
                                    {{ $report->status === 'Menunggu Approval VP' ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ $report->status === 'Ditolak VP' ? 'bg-red-100 text-red-800' : '' }}
                                    {{ $report->status === 'Menunggu Review HR' ? 'bg-purple-100 text-purple-800' : '' }}
                                    {{ $report->status === 'Selesai' ? 'bg-green-100 text-green-800' : '' }}">
                                    {{ $report->status }}
                                </span>
                            </div>
                        </div>
                            </div>
                        </div>
                    </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column: Basic Information -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Basic Information Card -->
                    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Informasi Dasar
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-1">
                                    <div class="text-sm font-medium text-gray-500">Hari, Tanggal</div>
                                    <div class="text-base">{{ $report->report_date->translatedFormat('l, d/m/Y') }}</div>
                                </div>
                                <div class="space-y-1">
                                    <div class="text-sm font-medium text-gray-500">Pekerja</div>
                                    <div class="text-base">{{ $report->user->name }}</div>
                                </div>
                                <div class="space-y-1">
                                    <div class="text-sm font-medium text-gray-500">Project</div>
                                    <div class="text-base">{{ $report->project_code }}</div>
                                </div>
                                <div class="space-y-1">
                                    <div class="text-sm font-medium text-gray-500">Lokasi</div>
                                    <div class="text-base">{{ $report->location }}</div>
                            </div>
                                @if($report->verifikator)
                                <div class="space-y-1">
                                    <div class="text-sm font-medium text-gray-500">Verifikator</div>
                                    <div class="text-base">{{ $report->verifikator->name }}</div>
                            </div>
                                    @endif
                                @if($report->vp)
                                <div class="space-y-1">
                                    <div class="text-sm font-medium text-gray-500">Vice President</div>
                                    <div class="text-base">{{ $report->vp->name }}</div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Work Details Card -->
                    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                Detail Pekerjaan
                            </h3>
                        <div class="space-y-4">
                            @foreach($report->details as $index => $detail)
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <div class="flex items-start justify-between mb-2">
                                        <h4 class="text-sm font-medium text-gray-900">Detail #{{ $index + 1 }}</h4>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            {{ $detail->status === 'Selesai' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $detail->status === 'Dalam Proses' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                            {{ $detail->status === 'Tertunda' ? 'bg-orange-100 text-orange-800' : '' }}
                                            {{ $detail->status === 'Bermasalah' ? 'bg-red-100 text-red-800' : '' }}">
                                            {{ $detail->status }}
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-600">{{ $detail->description }}</p>
                                </div>
                            @endforeach
                            </div>
                        </div>
                        </div>
                    </div>

                <!-- Right Column: Time Information and Additional Details -->
                <div class="space-y-6">
                    <!-- Time Information Card -->
                    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Informasi Waktu
                            </h3>
                            <div class="space-y-4">
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="space-y-1">
                                        <div class="text-sm font-medium text-gray-500">Waktu Mulai</div>
                                        <div class="text-base">{{ \Carbon\Carbon::parse($report->start_time)->format('H:i') }}</div>
                                    </div>
                                    <div class="space-y-1">
                                        <div class="text-sm font-medium text-gray-500">Waktu Selesai</div>
                                        <div class="text-base">{{ \Carbon\Carbon::parse($report->end_time)->format('H:i') }}</div>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <div class="text-sm font-medium text-gray-500">Status</div>
                                    <div class="flex flex-wrap gap-2">
                                        @if($report->is_overtime)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                Overtime
                                            </span>
                                        @endif
                                        @if($report->is_overnight)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 003.354-5.646z"/>
                                                </svg>
                                                Overnight
                                            </span>
                                        @endif
                                        @if($report->is_shift)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                                Pergantian Shift
                                            </span>
                                        @endif
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $report->work_day_type === 'Hari Libur' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            {{ $report->work_day_type }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Rejection Notes Card (if applicable) -->
                    @if($report->rejection_notes && ($report->status === 'Ditolak Verifikator' || $report->status === 'Ditolak VP'))
                    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-red-700 mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                Alasan Penolakan
                            </h3>
                            <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-red-700">
                                {{ $report->rejection_notes }}
                            </div>
                            
                            @if($report->status === App\Models\Report::STATUS_REJECTED_BY_VERIFIER)
                                <div class="mt-4 {{ $report->can_revise ? 'bg-green-50 border-green-200 text-green-700' : 'bg-gray-50 border-gray-200 text-gray-700' }} border rounded-lg p-4">
                                    <div class="flex items-center mb-2">
                                        @if($report->can_revise)
                                            <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                            <span class="font-medium">Laporan ini dapat direvisi</span>
                                        @else
                                            <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                            </svg>
                                            <span class="font-medium">Laporan ini tidak dapat direvisi</span>
                                        @endif
                                    </div>
                                    <p class="text-sm">
                                        @if($report->can_revise)
                                            Anda dapat mengedit laporan ini dan mengirimkan ulang untuk diverifikasi kembali. Klik tombol "Edit" di bawah untuk melakukan revisi.
                                        @else
                                            Laporan ini tidak diperbolehkan untuk direvisi. Silakan buat laporan baru jika diperlukan.
                                        @endif
                                    </p>
                                    @if($report->can_revise)
                                        @can('update', $report)
                                        <div class="mt-3 flex space-x-3">
                                            <a href="{{ route('reports.edit', $report) }}" class="inline-flex items-center px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                                Edit Laporan
                                            </a>
                                            
                                            <form action="{{ route('reports.resubmit', $report) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                    Kirim Ulang
                                                </button>
                                            </form>
                                        </div>
                                        @endcan
                                    @endif
                                </div>
                            @endif
                            
                            @if($report->status === App\Models\Report::STATUS_REJECTED_BY_VP)
                                <div class="mt-4 {{ $report->can_revise ? 'bg-green-50 border-green-200 text-green-700' : 'bg-gray-50 border-gray-200 text-gray-700' }} border rounded-lg p-4">
                                    <div class="flex items-center mb-2">
                                        @if($report->can_revise)
                                            <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                            <span class="font-medium">Laporan ini dapat direvisi</span>
                                        @else
                                            <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                            </svg>
                                            <span class="font-medium">Laporan ini tidak dapat direvisi</span>
                                        @endif
                                    </div>
                                    <p class="text-sm">
                                        @if($report->can_revise)
                                            Anda dapat mengedit laporan ini dan mengirimkan ulang untuk diverifikasi kembali. Klik tombol "Edit" di bawah untuk melakukan revisi.
                                        @else
                                            Laporan ini tidak diperbolehkan untuk direvisi. Silakan buat laporan baru jika diperlukan.
                                        @endif
                                    </p>
                                    @if($report->can_revise)
                                        @can('update', $report)
                                        <div class="mt-3 flex space-x-3">
                                            <a href="{{ route('reports.edit', $report) }}" class="inline-flex items-center px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                                Edit Laporan
                                            </a>
                                            
                                            <form action="{{ route('reports.resubmit-vp', $report) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                    Kirim Ulang
                                                </button>
                                            </form>
                                        </div>
                                        @endcan
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Timestamps Card -->
                    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Riwayat Perubahan
                            </h3>
                            <div class="space-y-3 text-sm text-gray-500">
                                <!-- Pembuatan Laporan -->
                                <div class="flex items-start">
                                    <svg class="w-4 h-4 mr-2 mt-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <div>
                                        <p class="font-medium text-gray-900">Dibuat</p>
                                        <p>{{ $report->created_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }}</p>
                                        <p class="text-xs">oleh {{ $report->user->name }}</p>
                                    </div>
                                </div>
                                
                                <!-- Pengiriman ke Verifikator -->
                                @if($report->submitted_at)
                                <div class="flex items-start">
                                    <svg class="w-4 h-4 mr-2 mt-1 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                    <div>
                                        <p class="font-medium text-gray-900">Dikirim ke Verifikator</p>
                                        <p>{{ $report->submitted_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }}</p>
                                        <p class="text-xs">oleh {{ $report->user->name }}</p>
                                    </div>
                                </div>
                                @endif
                                
                                <!-- Verifikasi -->
                                @if($report->verified_at)
                                <div class="flex items-start">
                                    <svg class="w-4 h-4 mr-2 mt-1 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <div>
                                        <p class="font-medium text-gray-900">
                                            @if($report->status === App\Models\Report::STATUS_REJECTED_BY_VERIFIER)
                                                Ditolak oleh Verifikator
                                            @else
                                                Diverifikasi
                                            @endif
                                        </p>
                                        <p>{{ $report->verified_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }}</p>
                                        <p class="text-xs">oleh {{ $report->verifikator ? $report->verifikator->name : 'Unknown' }}</p>
                                    </div>
                                </div>
                                @endif
                                
                                <!-- Approval VP -->
                                @if($report->approved_at)
                                <div class="flex items-start">
                                    <svg class="w-4 h-4 mr-2 mt-1 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <div>
                                        <p class="font-medium text-gray-900">
                                            @if($report->status === App\Models\Report::STATUS_REJECTED_BY_VP)
                                                Ditolak oleh VP
                                            @else
                                                Diapprove oleh VP
                                            @endif
                                        </p>
                                        <p>{{ $report->approved_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }}</p>
                                        <p class="text-xs">oleh {{ $report->vp ? $report->vp->name : 'Unknown' }}</p>
                                    </div>
                                </div>
                                @endif
                                
                                <!-- Review HR / Selesai -->
                                @if($report->completed_at)
                                <div class="flex items-start">
                                    <svg class="w-4 h-4 mr-2 mt-1 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <div>
                                        <p class="font-medium text-gray-900">
                                            @if($report->status === App\Models\Report::STATUS_COMPLETED)
                                                Selesai diproses
                                            @else
                                                Ditinjau oleh HR
                                            @endif
                                        </p>
                                        <p>{{ $report->completed_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }}</p>
                                        @php
                                            $hrUser = null;
                                            if (isset($report->completed_by)) {
                                                $hrUser = App\Models\User::find($report->completed_by);
                                            }
                                        @endphp
                                        <p class="text-xs">oleh {{ $hrUser ? $hrUser->name : 'HR' }}</p>
                                    </div>
                                </div>
                                @endif
                                
                                <!-- Update Terakhir (jika berbeda dengan created_at) -->
                                @if($report->created_at != $report->updated_at)
                                    <div class="flex items-start">
                                        <svg class="w-4 h-4 mr-2 mt-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                        </svg>
                                        <div>
                                            <p class="font-medium text-gray-900">Terakhir diupdate</p>
                                            <p>{{ $report->updated_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }}</p>
                                            <p class="text-xs">oleh {{ $report->updater ? $report->updater->name : 'Unknown' }}</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 