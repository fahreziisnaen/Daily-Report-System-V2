@php
    $user = auth()->user();
    $name = $user->name;
    
    $hour = now()->hour;
    
    if ($hour >= 5 && $hour < 12) {
        $greeting = 'Selamat Pagi';
    } elseif ($hour >= 12 && $hour < 15) {
        $greeting = 'Selamat Siang';
    } elseif ($hour >= 15 && $hour < 18) {
        $greeting = 'Selamat Sore';
    } else {
        $greeting = 'Selamat Malam';
    }
    
    $days = [
        'Sunday' => 'Minggu',
        'Monday' => 'Senin',
        'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday' => 'Kamis',
        'Friday' => 'Jumat',
        'Saturday' => 'Sabtu'
    ];
    
    $months = [
        'January' => 'Januari',
        'February' => 'Februari',
        'March' => 'Maret',
        'April' => 'April',
        'May' => 'Mei',
        'June' => 'Juni',
        'July' => 'Juli',
        'August' => 'Agustus',
        'September' => 'September',
        'October' => 'Oktober',
        'November' => 'November',
        'December' => 'Desember'
    ];
    
    $day = $days[now()->format('l')];
    $date = now()->format('d');
    $month = $months[now()->format('F')];
    $year = now()->format('Y');
    $today = "$day, $date $month $year";
@endphp

<div class="mb-6">
    <div class="flex flex-col items-center justify-center text-center">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $greeting }}, {{ $name }}! 👋</h1>
        <p class="text-gray-600 dark:text-gray-300 mt-1">{{ $today }}</p>
        <div class="text-sm text-gray-600 dark:text-gray-300 mt-1" 
             x-data="clock()" 
             x-init="startClock()"
             x-text="time">
        </div>
    </div>
</div>

<script>
function clock() {
    return {
        time: new Date().toLocaleTimeString('id-ID').replace(/\./g, ':'),
        startClock() {
            setInterval(() => {
                this.time = new Date().toLocaleTimeString('id-ID').replace(/\./g, ':');
            }, 1000);
        }
    }
}
</script> 