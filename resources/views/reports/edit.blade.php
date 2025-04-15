<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Laporan') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-4 pb-24 md:pb-4">
                    <form method="POST" action="{{ route('reports.update', $report) }}" class="space-y-6">
                        @csrf
                        @method('PUT')
                        
                        <!-- Basic Information Section -->
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Pekerjaan</h3>
                            
                            <!-- Tanggal -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label>
                                <div class="relative">
                                    <input type="date" 
                                        name="report_date" 
                                        id="report_date"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-base" 
                                        value="{{ old('report_date', $report->report_date->format('Y-m-d')) }}"
                                        required
                                        onchange="this.setAttribute('data-date', this.value);"
                                        style="position: relative; height: 38px;"
                                        data-date="{{ old('report_date', $report->report_date->format('Y-m-d')) }}">
                                    <style>
                                        input[type="date"]::-webkit-calendar-picker-indicator {
                                            background: transparent;
                                            bottom: 0;
                                            color: transparent;
                                            cursor: pointer;
                                            height: 100%;
                                            left: 0;
                                            position: absolute;
                                            right: 0;
                                            top: 0;
                                            width: auto;
                                        }
                                        input[type="date"]::before {
                                            content: attr(data-date);
                                            color: #000000;
                                            padding: 0.5rem 0.75rem;
                                            position: absolute;
                                            left: 0;
                                            top: 50%;
                                            transform: translateY(-50%);
                                        }
                                        input[type="date"] {
                                            color: transparent;
                                            padding: 0.5rem 0.75rem;
                                            height: 38px;
                                            display: flex;
                                            align-items: center;
                                        }
                                    </style>
                                    @error('report_date')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Kode Project -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Kode Project</label>
                                <select name="project_code" 
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-base"
                                    required>
                                    <option value="">Pilih Project</option>
                                </select>
                            </div>

                            <!-- Verifikator -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Verifikator</label>
                                <select name="verifikator_id" id="verifikator_id" 
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-base"
                                    required onchange="loadVicePresidents()">
                                    <option value="">Pilih Verifikator</option>
                                    @foreach($verifikators as $verifikator)
                                        <option value="{{ $verifikator->id }}" {{ $report->verifikator_id == $verifikator->id ? 'selected' : '' }}>{{ $verifikator->name }}</option>
                                    @endforeach
                                </select>
                                @error('verifikator_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <!-- Vice President (Disabled) -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Vice President</label>
                                <select name="vp_id" id="vp_id" 
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-base bg-gray-100"
                                    required readonly>
                                    <option value="{{ $report->vp_id }}">{{ $report->vp ? $report->vp->name : 'Vice President akan dipilih otomatis' }}</option>
                                </select>
                                <!-- Hidden input to ensure vp_id is submitted even if the select is disabled -->
                                <input type="hidden" name="vp_id" value="{{ $report->vp_id }}">
                                @error('vp_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Lokasi -->
                            <div class="mb-4" x-data="{ 
                                locationType: '{{ $report->location === auth()->user()->homebase ? 'homebase' : 'dinas' }}'
                            }">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Lokasi</label>
                                <div class="grid grid-cols-2 gap-4 mb-3">
                                    <label class="flex items-center space-x-2 p-2 bg-white rounded-md border border-gray-200">
                                        <input type="radio" 
                                            name="location_type" 
                                            value="homebase" 
                                            x-model="locationType"
                                            class="w-5 h-5 text-indigo-600">
                                        <span class="text-sm">Homebase</span>
                                    </label>
                                    <label class="flex items-center space-x-2 p-2 bg-white rounded-md border border-gray-200">
                                        <input type="radio" 
                                            name="location_type" 
                                            value="dinas" 
                                            x-model="locationType"
                                            class="w-5 h-5 text-indigo-600">
                                        <span class="text-sm">Lokasi Dinas</span>
                                    </label>
                                </div>
                                
                                <!-- Hidden input untuk homebase -->
                                <input type="hidden" 
                                    name="location" 
                                    x-bind:value="locationType === 'homebase' ? '{{ auth()->user()->homebase }}' : ''">
                                
                                <!-- Input lokasi dinas yang muncul saat pilih dinas -->
                                <div x-show="locationType === 'dinas'" 
                                    x-transition:enter="transition ease-out duration-300"
                                    x-transition:enter-start="opacity-0 transform scale-95"
                                    x-transition:enter-end="opacity-100 transform scale-100">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Detail Lokasi Dinas</label>
                                    <input type="text" 
                                        x-bind:name="locationType === 'dinas' ? 'location' : ''"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-base"
                                        placeholder="Masukkan lokasi dinas"
                                        value="{{ $report->location !== auth()->user()->homebase ? $report->location : '' }}"
                                        x-bind:required="locationType === 'dinas'">
                                </div>
                            </div>

                            <!-- Waktu -->
                            <div class="mb-4">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Jam Mulai</label>
                                        <input type="time" name="start_time" 
                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-base"
                                            value="{{ old('start_time', Carbon\Carbon::parse($report->start_time)->format('H:i')) }}"
                                            required>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Jam Selesai</label>
                                        <input type="time" name="end_time" 
                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-base"
                                            value="{{ old('end_time', Carbon\Carbon::parse($report->end_time)->format('H:i')) }}"
                                            required>
                                    </div>
                                </div>
                            </div>

                            <!-- Kerja Saat -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Kerja Saat</label>
                                <div class="grid grid-cols-2 gap-4">
                                    <label class="flex items-center space-x-2 p-2 bg-white rounded-md border border-gray-200">
                                        <input type="radio" 
                                            name="work_day_type" 
                                            value="Hari Kerja" 
                                            id="work_day_type_kerja"
                                            class="w-5 h-5 text-indigo-600"
                                            {{ old('work_day_type', $report->work_day_type) === 'Hari Kerja' ? 'checked' : '' }}>
                                        <span class="text-sm">Hari Kerja</span>
                                    </label>
                                    <label class="flex items-center space-x-2 p-2 bg-white rounded-md border border-gray-200">
                                        <input type="radio" 
                                            name="work_day_type" 
                                            value="Hari Libur" 
                                            id="work_day_type_libur"
                                            class="w-5 h-5 text-indigo-600"
                                            {{ old('work_day_type', $report->work_day_type) === 'Hari Libur' ? 'checked' : '' }}>
                                        <span class="text-sm">Hari Libur</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Overnight dan Shift -->
                            <div class="flex flex-wrap gap-4 mt-4">
                                <label class="inline-flex items-center">
                                    <input type="checkbox" 
                                        name="is_overnight" 
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" 
                                        {{ $report->is_overnight ? 'checked' : '' }}>
                                    <span class="ml-2 text-sm text-gray-600">Overnight (Lanjut hari berikutnya)</span>
                                </label>

                                <!-- Checkbox Shift -->
                                <label class="inline-flex items-center">
                                    <input type="checkbox" 
                                        name="is_shift" 
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" 
                                        {{ $report->is_shift ? 'checked' : '' }}>
                                    <span class="ml-2 text-sm text-gray-600">Pergantian Shift</span>
                                </label>
                            </div>
                        </div>

                        <!-- Work Details Section -->
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-medium text-gray-900">Detail Pekerjaan</h3>
                                <button type="button" onclick="addWorkDetail()"
                                    class="inline-flex items-center px-3 py-2 bg-indigo-600 text-white rounded-md text-sm">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Tambah
                                </button>
                            </div>
                            <div id="work-details" class="space-y-4">
                                @foreach($report->details as $index => $detail)
                                    <div class="p-4 bg-white rounded-lg border border-gray-200 relative">
                                        <button type="button" onclick="this.parentElement.remove()" 
                                            class="absolute right-2 top-2 text-gray-400 hover:text-red-500 p-2">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                        <div class="space-y-4 pr-8">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Uraian Pekerjaan</label>
                                                <textarea name="work_details[{{ $index }}][description]" rows="2"
                                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-base"
                                                    required>{{ $detail->description }}</textarea>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                                <select name="work_details[{{ $index }}][status]"
                                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-base"
                                                    required>
                                                    <option value="Selesai" {{ $detail->status === 'Selesai' ? 'selected' : '' }}>Selesai</option>
                                                    <option value="Dalam Proses" {{ $detail->status === 'Dalam Proses' ? 'selected' : '' }}>Dalam Proses</option>
                                                    <option value="Tertunda" {{ $detail->status === 'Tertunda' ? 'selected' : '' }}>Tertunda</option>
                                                    <option value="Bermasalah" {{ $detail->status === 'Bermasalah' ? 'selected' : '' }}>Bermasalah</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="bg-white px-4 py-3 text-right sm:px-6 flex justify-end gap-3">
                            <x-secondary-button :href="route('reports.show', $report)">
                                {{ __('Batal') }}
                            </x-secondary-button>
                            
                            <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                {{ __('Simpan') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        let detailCount = {{ count($report->details) }};

        document.addEventListener('DOMContentLoaded', function() {
            const dateInput = document.getElementById('report_date');
            const workDayKerja = document.getElementById('work_day_type_kerja');
            const workDayLibur = document.getElementById('work_day_type_libur');
            
            // Inisialisasi lokasi
            const isHomebase = '{{ $report->location }}' === '{{ auth()->user()->homebase }}';
            const locationType = document.querySelector('input[name="location_type"][value="' + (isHomebase ? 'homebase' : 'dinas') + '"]');
            if (locationType) {
                locationType.checked = true;
            }
            
            function checkIfSunday(date) {
                const day = new Date(date).getDay();
                if (day === 0) { // 0 adalah hari Minggu
                    workDayLibur.checked = true;
                } else {
                    workDayKerja.checked = true;
                }
            }

            function formatDateForDisplay(dateString) {
                if (!dateString) return '';
                const [year, month, day] = dateString.split('-');
                return `${day}/${month}/${year}`;
            }

            // Format tanggal saat halaman dimuat
            dateInput.setAttribute('data-date', formatDateForDisplay(dateInput.value));

            // Update format dan cek hari Minggu saat tanggal berubah
            dateInput.addEventListener('change', function() {
                this.setAttribute('data-date', formatDateForDisplay(this.value));
                checkIfSunday(this.value);
            });

            // Cek hari Minggu saat halaman dimuat
            checkIfSunday(dateInput.value);

            // Fetch active projects
            fetch('/api/active-projects')
                .then(response => response.json())
                .then(projects => {
                    const projectSelect = document.querySelector('[name="project_code"]');
                    const currentProject = '{{ $report->project_code }}';
                    
                    // Hapus option default jika ada
                    projectSelect.innerHTML = '<option value="">Pilih Project</option>';
                    
                    projects.forEach(project => {
                        const option = document.createElement('option');
                        option.value = project.code;
                        option.textContent = project.code;
                        if (project.code === currentProject) {
                            option.selected = true;
                        }
                        projectSelect.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Error loading projects:', error);
                });

            // Load VP based on selected verifikator
            loadVicePresidents();
        });

        function addWorkDetail() {
            const container = document.getElementById('work-details');
            const detail = document.createElement('div');
            detail.className = 'p-4 bg-white rounded-lg border border-gray-200 relative';
            detail.innerHTML = `
                <button type="button" onclick="this.parentElement.remove()" 
                    class="absolute right-2 top-2 text-gray-400 hover:text-red-500 p-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
                <div class="space-y-4 pr-8">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Uraian Pekerjaan</label>
                        <textarea name="work_details[${detailCount}][description]" rows="2"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-base"
                            required></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="work_details[${detailCount}][status]"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-base"
                            required>
                            <option value="Selesai">Selesai</option>
                            <option value="Dalam Proses">Dalam Proses</option>
                            <option value="Tertunda">Tertunda</option>
                            <option value="Bermasalah">Bermasalah</option>
                        </select>
                    </div>
                </div>
            `;
            container.appendChild(detail);
            detailCount++;
        }

        function loadVicePresidents() {
            const verifikatorId = document.getElementById('verifikator_id').value;
            const vpSelect = document.getElementById('vp_id');
            const vpHiddenInput = document.querySelector('input[type="hidden"][name="vp_id"]');
            const currentVpId = '{{ $report->vp_id }}';
            const currentVpName = '{{ $report->vp ? $report->vp->name : "Vice President akan dipilih otomatis" }}';
            
            if (!verifikatorId) {
                // Jika tidak ada verifikator yang dipilih, tetap gunakan VP saat ini jika ada
                if (currentVpId) {
                    vpSelect.innerHTML = `<option value="${currentVpId}">${currentVpName}</option>`;
                    vpHiddenInput.value = currentVpId;
                } else {
                    vpSelect.innerHTML = '<option value="">Vice President akan dipilih otomatis</option>';
                    vpHiddenInput.value = '';
                }
                return;
            }
            
            // Fetch Vice Presidents based on selected Verifikator's department
            fetch(`/vice-presidents?verifikator_id=${verifikatorId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.length > 0) {
                        vpSelect.innerHTML = '';
                        
                        // Cek apakah VP saat ini masih dalam list
                        let vpFound = false;
                        
                        data.forEach(vp => {
                            const option = document.createElement('option');
                            option.value = vp.id;
                            option.textContent = vp.name;
                            
                            // Pilih VP yang saat ini sudah disimpan jika ada dalam list
                            if (vp.id == currentVpId) {
                                option.selected = true;
                                vpFound = true;
                            }
                            
                            vpSelect.appendChild(option);
                        });
                        
                        // Jika VP saat ini tidak ditemukan, pilih yang pertama
                        if (!vpFound && data.length > 0) {
                            vpSelect.value = data[0].id;
                        }
                        
                        // Update hidden input
                        vpHiddenInput.value = vpSelect.value;
                    } else {
                        vpSelect.innerHTML = '<option value="">Tidak ada VP tersedia</option>';
                        vpHiddenInput.value = '';
                    }
                })
                .catch(error => {
                    console.error('Error fetching Vice Presidents:', error);
                    vpSelect.innerHTML = '<option value="">Error loading data</option>';
                    vpHiddenInput.value = '';
                });
        }
    </script>
    @endpush
</x-app-layout> 