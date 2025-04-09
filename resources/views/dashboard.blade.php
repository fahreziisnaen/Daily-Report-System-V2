<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-white leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Greeting Section -->
                    <div class="mb-8 text-center"
                        x-data="{ 
                            greeting: '',
                            initGreeting() {
                                const hour = new Date().getHours();
                                if (hour >= 5 && hour < 12) {
                                    this.greeting = 'Selamat Pagi';
                                } else if (hour >= 12 && hour < 15) {
                                    this.greeting = 'Selamat Siang';
                                } else if (hour >= 15 && hour < 18) {
                                    this.greeting = 'Selamat Sore';
                                } else {
                                    this.greeting = 'Selamat Malam';
                                }
                            }
                        }"
                        x-init="initGreeting()"
                    >
                        <h2 class="text-3xl font-bold text-gray-800 dark:text-gray-200">
                            <span x-text="greeting"></span>, {{ auth()->user()->name }}! 👋
                        </h2>
                        <p class="mt-2 text-gray-600 dark:text-gray-400">
                            @if(auth()->user()->isAdmin())
                                Semoga harimu produktif dalam mengawasi dan mengelola laporan tim.
                            @else
                                Semoga harimu produktif dan penuh semangat dalam bekerja.
                            @endif
                        </p>
                    </div>

                    <!-- Setelah Greeting Section dan sebelum Summary Cards -->
                    @if(auth()->user()->isAdmin())
                        @php
                            $hasReportToday = auth()->user()->reports()
                                ->whereDate('report_date', today())
                                ->exists();
                        @endphp
                        
                        <div class="bg-gradient-to-r from-blue-500 to-indigo-600 rounded-lg shadow-lg p-6 mb-8">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <h3 class="text-xl font-semibold text-white mb-2">
                                        @if($hasReportToday)
                                            Laporan Anda Hari Ini Sudah Dibuat! ✨
                                        @else
                                            Jangan Lupa Buat Laporan Anda Hari Ini! 📝
                                        @endif
                                    </h3>
                                    <p class="text-blue-100">
                                        @if($hasReportToday)
                                            Anda sudah memberikan contoh yang baik untuk tim. Tetap semangat!
                                        @else
                                            Sebagai admin, mari berikan contoh yang baik dengan membuat laporan tepat waktu.
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

                        <!-- Divider untuk memisahkan reminder admin dengan reminder pekerja -->
                        <div class="mb-8"></div>
                    @endunless

                    <!-- Report Reminder Card -->
                    @unless(auth()->user()->isAdmin())
                        @php
                            $hasReportToday = auth()->user()->reports()
                                ->whereDate('report_date', today())
                                ->exists();
                        @endphp
                        
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
                    @endunless

                    <!-- Summary Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                        @foreach($summaries as $summary)
                            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                                <div class="p-6">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ $summary->user_name }}</h3>
                                    <div class="grid grid-cols-7 gap-1">
                                        @php
                                            $startDate = now()->startOfMonth();
                                            $endDate = now()->endOfMonth();
                                            $reportDates = collect($summary->reports)->pluck('report_date')->map(function($date) {
                                                return $date->format('Y-m-d');
                                            })->toArray();
                                        @endphp

                                        <!-- Calendar Header -->
                                        @foreach(['M', 'S', 'S', 'R', 'K', 'J', 'S'] as $day)
                                            <div class="text-center text-xs font-medium text-gray-500">
                                                {{ $day }}
                                            </div>
                                        @endforeach

                                        <!-- Empty days before start of month -->
                                        @for($i = 1; $i < $startDate->dayOfWeek; $i++)
                                            <div></div>
                                        @endfor

                                        <!-- Days of month -->
                                        @foreach(range(1, $endDate->day) as $day)
                                            @php
                                                $currentDate = $startDate->copy()->addDays($day - 1);
                                                $dateString = $currentDate->format('Y-m-d');
                                                $hasReport = in_array($dateString, $reportDates);
                                            @endphp
                                            <div class="aspect-square flex items-center justify-center text-sm">
                                                @if($hasReport)
                                                    <div class="w-7 h-7 flex items-center justify-center bg-green-100 text-green-800 rounded-full">
                                                        {{ $day }}
                                                    </div>
                                                @else
                                                    <div class="w-7 h-7 flex items-center justify-center {{ $currentDate->isWeekend() ? 'text-gray-400' : 'text-gray-700' }}">
                                                        {{ $day }}
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>

                                    <!-- Legend -->
                                    <div class="mt-4 flex items-center justify-end text-sm">
                                        <div class="flex items-center">
                                            <div class="w-4 h-4 bg-green-100 rounded-full mr-2"></div>
                                            <span class="text-gray-600">Laporan dibuat</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Pekerja Yang Belum Laporan Section -->
                    @if(auth()->user()->isAdmin() && count($usersWithoutReport) > 0)
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                            <div class="p-6">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-lg font-semibold text-gray-900">Belum Membuat Laporan Hari Ini</h3>
                                    <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm">
                                        {{ count($usersWithoutReport) }} Orang
                                    </span>
                                </div>
                                
                                <div class="space-y-3">
                                    @foreach($usersWithoutReport as $user)
                                        <div class="flex items-center space-x-3 bg-white p-3 rounded-lg shadow-sm">
                                            <img src="{{ $user['avatar_url'] }}" alt="Avatar" class="h-10 w-10 rounded-full">
                                            <div>
                                                <div class="font-medium text-gray-900">{{ $user['name'] }}</div>
                                                <div class="text-sm text-gray-500">{{ $user['email'] }}</div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Recent Reports -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Laporan Terbaru</h3>
                            
                            <!-- Desktop View (Hidden on Mobile) -->
                            <div class="hidden md:block overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Project</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lokasi</th>
                                            @if(auth()->user()->isAdmin())
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pekerja</th>
                                            @endif
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Detail</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($recentReports as $report)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900">{{ $report->report_date->format('d/m/Y') }}</div>
                                                    <div class="text-xs text-gray-500">
                                                        @if($report->created_at == $report->updated_at)
                                                            {{ $report->created_at->diffForHumans() }}
                                                        @else
                                                            Diedit {{ $report->updated_at->diffForHumans() }}
                                                        @endif
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">{{ $report->project_code }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap">{{ $report->location }}</td>
                                                @if(auth()->user()->isAdmin())
                                                    <td class="px-6 py-4 whitespace-nowrap">{{ $report->user->name }}</td>
                                                @endif
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <a href="{{ route('reports.show', $report) }}" 
                                                        class="text-indigo-600 hover:text-indigo-900">Lihat Detail</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Mobile View (Hidden on Desktop) -->
                            <div class="md:hidden space-y-4">
                                @foreach($recentReports as $report)
                                    <div class="bg-gray-50 rounded-lg p-4 space-y-3">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">{{ $report->report_date->format('d/m/Y') }}</div>
                                                <div class="text-xs text-gray-500">
                                                    @if($report->created_at == $report->updated_at)
                                                        {{ $report->created_at->diffForHumans() }}
                                                    @else
                                                        Diedit {{ $report->updated_at->diffForHumans() }}
                                                    @endif
                                                </div>
                                            </div>
                                            @if(auth()->user()->isAdmin())
                                                <div class="text-sm text-gray-600">{{ $report->user->name }}</div>
                                            @endif
                                        </div>
                                        
                                        <div class="grid grid-cols-2 gap-2 text-sm">
                                            <div>
                                                <div class="text-gray-500">Project</div>
                                                <div class="font-medium">{{ $report->project_code }}</div>
                                            </div>
                                            <div>
                                                <div class="text-gray-500">Lokasi</div>
                                                <div class="font-medium">{{ $report->location }}</div>
                                            </div>
                                        </div>

                                        <div class="flex justify-end">
                                            <a href="{{ route('reports.show', $report) }}" 
                                                class="inline-flex items-center px-3 py-1.5 bg-indigo-50 text-indigo-600 rounded-md text-sm font-medium hover:bg-indigo-100">
                                                <span>Lihat Detail</span>
                                                <svg class="ml-1.5 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                </svg>
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
