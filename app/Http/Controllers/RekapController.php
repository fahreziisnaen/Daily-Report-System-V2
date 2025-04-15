<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\User;
use App\Models\Department;
use Carbon\Carbon;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Auth;

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
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);
        $user = Auth::user();
        $usersQuery = User::query();

        // Filter users based on role
        if ($user->hasRole('Verifikator') || $user->hasRole('Admin Divisi')) {
            // Tampilkan laporan milik sendiri dan karyawan satu departemen
            $usersQuery->where(function($query) use ($user) {
                $query->where('id', $user->id)
                    ->orWhere('department_id', $user->department_id);
            });
        } elseif ($user->hasRole('Vice President')) {
            // Tampilkan laporan semua karyawan satu departemen kecuali VP sendiri
            $usersQuery->where('department_id', $user->department_id)
                ->where('id', '!=', $user->id);
        } elseif ($user->hasRole('Human Resource')) {
            // Tampilkan laporan milik sendiri dan semua karyawan semua departemen
            $usersQuery->where(function($query) use ($user) {
                $query->where('id', $user->id)
                    ->orWhereNotNull('department_id');
            });
        } else {
            // Regular employees only see their own reports
            $usersQuery->where('id', $user->id);
        }

        // Filters
        if ($request->has('department') && $request->department) {
            $usersQuery->where('department_id', $request->department);
        }

        // Get users and process their data
        $rawUsers = $usersQuery->get();
        $users = collect();
        
        foreach ($rawUsers as $user) {
            $userData = $this->processUserData($user, $month, $year);
            $users->push($userData);
        }

        // Get reviewed reports for the current month and year
        $reviewedReports = Report::with(['user', 'reviewer', 'user.department'])
            ->whereMonth('report_date', $month)
            ->whereYear('report_date', $year)
            ->whereIn('status', [Report::STATUS_COMPLETED, Report::STATUS_REJECTED_BY_HR])
            ->whereNotNull('reviewed_at')
            ->whereNotNull('reviewed_by')
            ->orderBy('reviewed_at', 'desc')
            ->get();

        // Get departments for filter
        $departments = Department::orderBy('name')->get();
        
        // Get months for dropdown
        $months = $this->getMonths();
        
        // Get years for dropdown (current year and previous year)
        $years = range(date('Y') - 1, date('Y') + 1);

        return view('rekap.index', compact('users', 'departments', 'months', 'years', 'month', 'year', 'reviewedReports'));
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
        $authUser = auth()->user();
        
        // Update the permissions to allow Verifikator to export for users in their department
        if ($authUser->hasRole('Verifikator')) {
            // Verifikator can only export users from their department
            if ($user->department_id !== $authUser->department_id) {
                return redirect()->route('rekap.index')
                    ->with('error', 'Anda hanya dapat mengekspor rekap untuk pengguna di departemen Anda.');
            }
        } elseif (!$authUser->hasRole(['Super Admin', 'Vice President', 'Admin Divisi'])) {
            return redirect()->route('rekap.index')
                ->with('error', 'Anda tidak memiliki izin untuk mengekspor rekap.');
        }
        
        // Vice President and Admin Divisi can only export users from their department
        if (($authUser->hasRole('Vice President') || $authUser->hasRole('Admin Divisi')) && 
            $user->department_id !== $authUser->department_id) {
            return redirect()->route('admin.rekap.index')
                ->with('error', 'Anda hanya dapat mengekspor rekap untuk pengguna di departemen Anda.');
        }

        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);

        // Get all reports for the specified month and year
        $reports = $user->reports()
            ->whereMonth('report_date', $month)
            ->whereYear('report_date', $year)
            ->orderBy('report_date')
            ->get();

        // If no reports, redirect back with a message
        if ($reports->count() === 0) {
            return redirect()->route($authUser->hasRole(['Super Admin', 'Vice President', 'Admin Divisi']) ? 'admin.rekap.index' : 'rekap.index')
                ->with('error', 'Tidak ada laporan untuk periode yang dipilih.');
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

            // Ambil semua laporan
            foreach ($reports as $report) {
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

    public function employeeRekap(Request $request)
    {
        $month = $request->get('month', date('n'));
        $year = $request->get('year', date('Y'));
        $user = auth()->user();

        // Fetch all reports from the user(s), not just HR-reviewed ones
        $allReports = Report::with(['user', 'reviewer'])
            ->orderBy('report_date', 'desc')
            ->get();
        
        // Jika user adalah Verifikator, tampilkan semua pengguna di departemen yang sama
        if ($user->hasRole('Verifikator')) {
            $usersQuery = User::where('department_id', $user->department_id);
            
            // Get users and process their data
            $rawUsers = $usersQuery->get();
            $users = collect();
            
            foreach ($rawUsers as $userItem) {
                $userData = $this->processUserData($userItem, $month, $year);
                $users->push($userData);
            }
            
            $months = $this->getMonths();
            $years = range(date('Y') - 1, date('Y') + 1);
            
            return view('rekap.index', compact('users', 'months', 'years', 'month', 'year', 'allReports'));
        }
        // Untuk user biasa, tampilkan hanya data mereka sendiri
        else {
            // Get only the current user's data
            $userData = $this->processUserData($user, $month, $year);

            $months = [];
            for($i = 1; $i <= 12; $i++) {
                $months[$i] = Carbon::create(null, $i, 1)->format('F');
            }

            $years = range(date('Y') - 1, date('Y') + 1);

            return view('rekap.employee', compact('userData', 'months', 'month', 'years', 'year', 'allReports'));
        }
    }

    public function employeeExport(Request $request)
    {
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);
        $user = auth()->user();

        // Get all reports for the specified month and year (not just HR-reviewed ones)
        $reports = $user->reports()
                ->whereMonth('report_date', $month)
                ->whereYear('report_date', $year)
                ->orderBy('report_date')
                ->get();

        // If no reports, redirect back with a message
        if ($reports->count() === 0) {
            return redirect()->route('rekap.index')
                ->with('error', 'Tidak ada laporan untuk periode yang dipilih.');
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

            // Ambil semua laporan
            foreach ($reports as $report) {
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

    private function processUserData(User $user, $month, $year)
    {
        // Get all reports for the specified month and year
        $reports = $user->reports()
            ->whereMonth('report_date', $month)
            ->whereYear('report_date', $year)
            ->get();

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
            'department_id' => $user->department_id,
            'total_work_hours' => round($totalWorkHours, 2),
            'total_overtime_hours' => round($totalOvertimeHours, 2),
            'report_count' => $reportCount
        ];
    }

    /**
     * Rekap khusus untuk role Human Resource
     * Menampilkan ringkasan laporan dari semua departemen yang telah direview oleh HR
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function hrRekap(Request $request)
    {
        if (!auth()->user()->hasRole('Human Resource')) {
            abort(403, 'Unauthorized action.');
        }

        $month = $request->get('month', date('n'));
        $year = $request->get('year', date('Y'));
        
        // Get all departments
        $departments = Department::orderBy('name')->get();
        
        // Department filter (optional for HR)
        $departmentId = $request->get('department');
        
        // Get users and process their data - only reports reviewed by HR are included
        $usersQuery = User::query()
            ->whereDoesntHave('roles', function($query) {
                $query->whereIn('name', ['Super Admin', 'Vice President']);
            });
        
        // Apply department filter if selected
        if ($departmentId) {
            $usersQuery->where('department_id', $departmentId);
        }
        
        // Fetch users - note: processUserData already filters for HR reviewed reports
        $rawUsers = $usersQuery->get();
        $users = collect();
        
        foreach ($rawUsers as $user) {
            $userData = $this->processUserData($user, $month, $year);
            $users->push($userData);
        }
        
        // Base query for report statistics
        $reportsQuery = Report::query()
            ->whereMonth('report_date', $month)
            ->whereYear('report_date', $year)
            ->whereHas('user', function($query) {
                $query->whereDoesntHave('roles', function($q) {
                    $q->whereIn('name', ['Super Admin', 'Vice President']);
                });
            });
            
        // Apply department filter if selected
        if ($departmentId) {
            $reportsQuery->whereHas('user', function($query) use ($departmentId) {
                $query->where('department_id', $departmentId);
            });
        }
        
        // Get report status statistics for HR view - focus only on HR-related statuses
        $reportStatusStats = [
            'pending_hr' => (clone $reportsQuery)->where('status', Report::STATUS_PENDING_HR)->count(),
            'completed' => (clone $reportsQuery)->where('status', Report::STATUS_COMPLETED)->count(),
            'rejected_hr' => (clone $reportsQuery)->where('status', Report::STATUS_REJECTED_BY_HR)->count(),
        ];
        
        // Get months for dropdown
        $months = $this->getMonths();
        
        // Get years for dropdown (current year and previous/next years)
        $years = range(date('Y') - 1, date('Y') + 1);
        
        return view('rekap.hr', compact(
            'users', 
            'departments', 
            'months', 
            'years', 
            'month', 
            'year', 
            'departmentId',
            'reportStatusStats'
        ));
    }
} 