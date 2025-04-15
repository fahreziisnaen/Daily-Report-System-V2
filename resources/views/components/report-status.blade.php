@props(['status'])

@php
$statusClasses = match($status) {
    'Draft' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
    'Menunggu Verifikasi' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100',
    'Ditolak Verifikator' => 'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100',
    'Menunggu Approval VP' => 'bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100',
    'Ditolak VP' => 'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100',
    'Menunggu Review HR' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-800 dark:text-indigo-100',
    'Ditolak HR' => 'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100',
    'Selesai' => 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100',
    default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
};
@endphp

<span class="px-2 py-1 text-xs rounded-full {{ $statusClasses }}">
    {{ $status }}
</span> 