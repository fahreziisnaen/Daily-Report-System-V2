<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;

class RekapController extends Controller
{
    private function calculateHours($report) {
        // Parse tanggal dan waktu
        $baseDate = Carbon::parse($report->report_date);
        
        // Parse waktu mulai dan selesai
        $start = Carbon::parse($report->report_date)->setTimeFromTimeString($report->start_time);
        $end = Carbon::parse($report->report_date)->setTimeFromTimeString($report->end_time);
        
        // Jika overnight, tambah 1 hari ke waktu selesai
        if($report->is_overnight) {
            $end->addDay();
        }

        // Total durasi kerja dalam jam
        $totalHours = $start->diffInMinutes($end) / 60;

        // Jika hari libur atau Minggu, semua jam dihitung sebagai lembur
        if($report->work_day_type === 'Hari Libur' || $baseDate->dayOfWeek == 0) {
            return [
                'workHours' => 0,
                'overtimeHours' => $totalHours
            ];
        }

        // Untuk hari kerja (Senin-Jumat)
        if($baseDate->dayOfWeek >= 1 && $baseDate->dayOfWeek <= 5) {
            $normalHours = 8.25; // 8 jam 15 menit
        }
        // Untuk hari Sabtu
        else {
            $normalHours = 4.25; // 4 jam 15 menit
        }

        // Jika total jam kerja kurang dari jam normal
        if($totalHours <= $normalHours) {
            return [
                'workHours' => $totalHours,
                'overtimeHours' => 0
            ];
        }
        // Jika lebih dari jam normal
        else {
            return [
                'workHours' => $normalHours,
                'overtimeHours' => $totalHours - $normalHours
            ];
        }
    }

    public function index(Request $request)
    {
        $month = $request->get('month', date('n'));
        $year = $request->get('year', date('Y'));

        // Get users based on role
        $usersQuery = User::query();
        
        // Filter user by department for Admin Divisi
        if (auth()->user()->hasRole('Admin Divisi')) {
            $adminDepartmentId = auth()->user()->department_id;
            $usersQuery->where('department_id', $adminDepartmentId);
        }
        
        // Get all users
        $users = $usersQuery->get()->map(function($user) use ($month, $year) {
            \Log::info("Processing user {$user->name}", [
                'user_id' => $user->id,
                'month' => $month,
                'year' => $year
            ]);

            $reports = $user->reports()
                ->whereMonth('report_date', $month)
                ->whereYear('report_date', $year)
                ->get();

            \Log::info("Found reports for {$user->name}", [
                'report_count' => $reports->count(),
                'reports' => $reports->pluck('report_date', 'id')
            ]);

            $totalWorkHours = 0;
            $totalOvertimeHours = 0;
            $reportCount = $reports->count();

            foreach($reports as $report) {
                $hours = $this->calculateHours($report);
                $totalWorkHours += $hours['workHours'];
                $totalOvertimeHours += $hours['overtimeHours'];
            }

            return [
                'id' => $user->id,
                'name' => $user->name,
                'total_work_hours' => round($totalWorkHours, 2),
                'total_overtime_hours' => round($totalOvertimeHours, 2),
                'report_count' => $reportCount
            ];
        });

        // Filter hanya user yang memiliki laporan
        $users = $users->filter(function($user) {
            return $user['report_count'] > 0;
        })->values();

        $months = [];
        for($i = 1; $i <= 12; $i++) {
            $months[$i] = Carbon::create(null, $i, 1)->format('F');
        }

        $years = range(date('Y') - 1, date('Y') + 1);

        return view('rekap.index', compact('users', 'months', 'month', 'years', 'year'));
    }

    private function getMonths()
    {
        return [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];
    }

    public function export(Request $request, User $user)
    {
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);

        // Restrict access for Admin Divisi to only users in their department
        if (auth()->user()->hasRole('Admin Divisi') && $user->department_id !== auth()->user()->department_id) {
            return redirect()->route('admin.rekap.index')
                ->with('error', 'You can only export reports for users in your department.');
        }

        try {
            $templatePath = storage_path('app/templates/exportlaporan.xlsx');
            if (!file_exists($templatePath)) {
                throw new \Exception('Template file tidak ditemukan di: ' . $templatePath);
            }

            $spreadsheet = IOFactory::load($templatePath);
            $sheet = $spreadsheet->getActiveSheet();

            // Set informasi user dan periode
            $sheet->setCellValue('B1', $user->name);
            $sheet->setCellValue('B2', Carbon::create($year, $month)->locale('id')->isoFormat('MMMM Y'));

            $row = 5; // Mulai dari baris 5

            // Ambil semua laporan (tidak perlu filter is_overtime lagi)
            foreach ($user->reports()
                ->whereMonth('report_date', $month)
                ->whereYear('report_date', $year)
                ->orderBy('report_date')
                ->get() as $report) {

                // Isi data ke excel
                $sheet->setCellValue("A{$row}", $report->report_date->format('d-m-Y'));
                $sheet->setCellValue("B{$row}", $report->project_code);
                $sheet->setCellValue("C{$row}", Carbon::parse($report->start_time)->format('H:i'));
                $sheet->setCellValue("D{$row}", Carbon::parse($report->end_time)->format('H:i'));
                $sheet->setCellValue("E{$row}", $report->location);
                
                // Uraian pekerjaan dari detail laporan
                $details = [];
                foreach ($report->details as $detail) {
                    $details[] = $detail->description;
                }
                $sheet->setCellValue("F{$row}", implode("\n", $details));
                
                // Status pekerjaan
                $statuses = [];
                foreach ($report->details as $detail) {
                    $statuses[] = $detail->status;
                }
                $sheet->setCellValue("G{$row}", implode("\n", $statuses));

                // Set style untuk baris
                $sheet->getStyle("A{$row}:G{$row}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                        ]
                    ]
                ]);

                // Wrap text untuk kolom deskripsi dan status
                $sheet->getStyle("F{$row}:G{$row}")->getAlignment()->setWrapText(true);

                $row++;
            }

            // Auto-size columns
            foreach (range('A', 'G') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            // Format nama bulan
            $monthName = Carbon::create($year, $month)->locale('id')->isoFormat('MMMM');

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="Summary Pekerjaan ' . $user->name . ' - ' . $monthName . ' ' . $year . '.xlsx"');
            header('Cache-Control: max-age=0');

            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            ob_end_clean();
            $writer->save('php://output');
            exit;

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengexport file: ' . $e->getMessage());
        }
    }
} 