<?php

namespace App\Http\Controllers;

use App\Events\ReportCreated;
use App\Events\ReportUpdated;
use App\Models\Project;
use App\Models\Report;
use App\Models\ReportDetail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ReportController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $query = Report::with('user', 'details')->orderBy('report_date', 'desc');
        
        // Filter by user if admin and employee search is provided
        if (auth()->user()->isAdmin() && $request->filled('employee_search')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', $request->employee_search);
            });
        } 
        // For non-admin users, only show their own reports
        elseif (!auth()->user()->isAdmin()) {
            $query->where('user_id', auth()->id());
        }
        
        // Filter by date if provided
        if ($request->filled('report_date')) {
            $query->whereDate('report_date', $request->report_date);
        }
        
        // Filter by location if provided
        if ($request->filled('location')) {
            $query->where('location', $request->location);
        }
        
        // Filter by project code if provided
        if ($request->filled('project_code')) {
            $query->where('project_code', $request->project_code);
        }
        
        $reports = $query->paginate(10)->withQueryString();
        
        // Get unique employee names for filter dropdown (for admins)
        $employees = '[]';
        if (auth()->user()->isAdmin()) {
            $employees = User::orderBy('name')
                ->pluck('name')
                ->toJson();
        }
        
        // Get unique locations for filter dropdown
        $locations = Report::distinct()
            ->orderBy('location')
            ->pluck('location')
            ->filter()
            ->values()
            ->toJson();
            
        // Get unique project codes for filter dropdown
        $projectCodes = Report::distinct()
            ->orderBy('project_code')
            ->pluck('project_code')
            ->filter()
            ->values()
            ->toJson();
            
        return view('reports.index', compact('reports', 'employees', 'locations', 'projectCodes'));
    }

    public function create()
    {
        return view('reports.create');
    }

    private function isOvertime($start_time, $end_time, $is_overnight = false, $work_day_type = 'Hari Kerja', $report_date = null)
    {
        // Log input parameters
        \Log::info('Overtime Calculation Input', [
            'start_time' => $start_time,
            'end_time' => $end_time,
            'is_overnight' => $is_overnight,
            'work_day_type' => $work_day_type,
            'report_date' => $report_date
        ]);

        $date = $report_date ? Carbon::parse($report_date) : Carbon::today();
        $dayOfWeek = $date->dayOfWeek;

        $start = Carbon::parse($report_date . ' ' . $start_time);
        $end = Carbon::parse($report_date . ' ' . $end_time);
        
        if ($is_overnight) {
            $end->addDay();
        }

        // Gunakan diffInMinutes(true) untuk mendapatkan nilai absolut
        $totalMinutes = $end->diffInMinutes($start, true);
        $totalHours = $totalMinutes / 60;

        // Jika hari Minggu (0) atau hari libur, otomatis overtime
        if ($dayOfWeek == 0 || $work_day_type === 'Hari Libur') {
            return true;
        }

        // Untuk hari kerja (Senin-Jumat)
        if ($dayOfWeek >= 1 && $dayOfWeek <= 5) {
            return $totalHours > 8.25; // Ubah dari >= menjadi >
        }
        // Untuk hari Sabtu
        else if ($dayOfWeek == 6) {
            return $totalHours > 4.25; // Ubah dari >= menjadi >
        }

        return false;
    }

    public function store(Request $request)
    {
        // Cek apakah sudah ada laporan di tanggal yang sama untuk user ini
        $existingReport = Report::where('user_id', auth()->id())
            ->where('report_date', $request->report_date)
            ->first();

        if ($existingReport) {
            return back()
                ->withInput()
                ->withErrors(['report_date' => 'Laporan pada tanggal tersebut sudah ada.']);
        }

        $request->validate([
            'report_date' => 'required|date',
            'project_code' => 'required|string',
            'location' => 'required|string',
            'start_time' => 'required',
            'end_time' => 'required',
            'work_day_type' => 'required|in:Hari Kerja,Hari Libur',
            'work_details' => 'required|array|min:1',
            'work_details.*.description' => 'required|string',
            'work_details.*.status' => 'required|in:Selesai,Dalam Proses,Tertunda,Bermasalah',
        ]);

        $is_overtime = $this->isOvertime(
            $request->start_time, 
            $request->end_time,
            $request->boolean('is_overnight'),
            $request->work_day_type,
            $request->report_date
        );

        $report = Report::create([
            'user_id' => auth()->id(),
            'report_date' => $request->report_date,
            'project_code' => $request->project_code,
            'location' => $request->location,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'is_overnight' => $request->boolean('is_overnight'),
            'is_shift' => $request->boolean('is_shift'),
            'is_overtime' => $is_overtime,
            'work_day_type' => $request->work_day_type,
        ]);

        foreach ($request->work_details as $detail) {
            $report->details()->create([
                'description' => $detail['description'],
                'status' => $detail['status'],
            ]);
        }

        return redirect()->route('reports.show', $report)
            ->with('success', 'Laporan berhasil dibuat.');
    }

    public function show(Report $report)
    {
        $this->authorize('view', $report);
        return view('reports.show', compact('report'));
    }

    public function edit(Report $report)
    {
        $this->authorize('update', $report);
        return view('reports.edit', compact('report'));
    }

    public function update(Request $request, Report $report)
    {
        $this->authorize('update', $report);

        $request->validate([
            'report_date' => 'required|date',
            'project_code' => 'required|string',
            'location' => 'required|string',
            'start_time' => 'required',
            'end_time' => 'required',
            'work_day_type' => 'required|in:Hari Kerja,Hari Libur',
            'work_details' => 'required|array|min:1',
            'work_details.*.description' => 'required|string',
            'work_details.*.status' => 'required|in:Selesai,Dalam Proses,Tertunda,Bermasalah',
        ]);

        // Hitung ulang overtime berdasarkan data terbaru
        $is_overtime = $this->isOvertime(
            $request->start_time,
            $request->end_time,
            $request->boolean('is_overnight'),
            $request->work_day_type,
            $request->report_date
        );

        // Update report dengan data baru termasuk is_overtime
        $report->update([
            'report_date' => $request->report_date,
            'project_code' => $request->project_code,
            'location' => $request->location,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'is_overnight' => $request->boolean('is_overnight'),
            'is_shift' => $request->boolean('is_shift'),
            'is_overtime' => $is_overtime,  // Set nilai overtime yang baru
            'work_day_type' => $request->work_day_type,
            'updated_by' => auth()->id()
        ]);

        // Update work details
        $report->details()->delete();
        foreach ($request->work_details as $detail) {
            $report->details()->create([
                'description' => $detail['description'],
                'status' => $detail['status'],
            ]);
        }

        return redirect()->route('reports.show', $report)
            ->with('success', 'Laporan berhasil diupdate.');
    }

    public function destroy(Report $report)
    {
        $this->authorize('delete', $report);
        $report->delete();

        return redirect()->route('reports.index')
            ->with('success', 'Laporan berhasil dihapus.');
    }

    public function export(Report $report)
    {
        $this->authorize('export', $report);

        // Check if user is authorized to export this report
        if (auth()->user()->hasRole('Super Admin')) {
            // Super Admin can export any report
        } elseif (auth()->user()->hasRole('Admin Divisi') || auth()->user()->hasRole('Verifikator')) {
            // Admin Divisi and Verifikator can export reports from their department
            if ($report->user->department_id != auth()->user()->department_id) {
                abort(403, 'Unauthorized action.');
            }
        } else {
            // Regular employee can only export their own reports
            if ($report->user_id != auth()->id()) {
                abort(403, 'Unauthorized action.');
            }
        }

        try {
            // Prepare the Excel file
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Format tanggal dan data umum
            Carbon::setLocale('id');
            $reportDate = Carbon::parse($report->report_date);
            $dayDate = $reportDate->isoFormat('dddd, D MMMM Y');
            $exportDate = 'Tgl. ' . $reportDate->isoFormat('D MMMM Y');

            // Waktu mulai dan selesai (langsung ambil dari database)
            $startTime = Carbon::parse($report->start_time)->format('H:i');
            $endTime = Carbon::parse($report->end_time)->format('H:i');

            // Set checkbox berdasarkan work_day_type
            if ($report->work_day_type === 'Hari Kerja') {
                $sheet->setCellValue('H7', 'Hari Kerja            ☑');
                $sheet->setCellValue('H8', 'Hari Libur            ☐');
            } else {
                $sheet->setCellValue('H7', 'Hari Kerja            ☐');
                $sheet->setCellValue('H8', 'Hari Libur            ☑');
            }

            // Fill data
            $sheet->setCellValue('C7', $report->user->name);
            $sheet->setCellValue('C8', 'Project Engineering');
            $sheet->setCellValue('C11', $dayDate);
            $sheet->setCellValue('H11', $startTime);
            $sheet->setCellValue('H12', $endTime);

            // Set checkbox dan lokasi
            if ($report->location === $report->user->homebase) {
                $sheet->setCellValue('C12', '☑');
                $sheet->setCellValue('C13', '☐');
                $sheet->setCellValue('E13', '');
            } else {
                $sheet->setCellValue('C12', '☐');
                $sheet->setCellValue('C13', '☑');
                $sheet->setCellValue('E13', $report->location);
            }

            // Fill work details (maksimal 3)
            $details = $report->details->take(3)->values();
            foreach ($details as $index => $detail) {
                $description = preg_replace('/^Task #\d+:\s*/', '', $detail->description);
                $description = preg_replace('/ - (Selesai|Dalam Proses|Tertunda|Bermasalah)$/', '', $description);
                $currentRow = 14 + $index;
                $sheet->setCellValue('C' . $currentRow, $description);
            }

            // Project ID dan tanda tangan
            $sheet->setCellValue('C17', $report->project_code);
            $sheet->setCellValue('B25', $report->user->name);
            $sheet->setCellValue('B26', $exportDate);

            // Tambahkan border bottom untuk cell tanda tangan
            $sheet->getStyle('B25')->getBorders()->getBottom()->setBorderStyle(
                \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
            );

            // Update posisi tanda tangan
            if ($report->user->signature_path && file_exists(storage_path('app/public/' . $report->user->signature_path))) {
                $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                $drawing->setName('Signature');
                $drawing->setDescription('Signature');
                $drawing->setPath(storage_path('app/public/' . $report->user->signature_path));
                $drawing->setCoordinates('B23');
                $drawing->setWidth(200);
                $drawing->setHeight(80);
                $drawing->setOffsetX(35);
                $drawing->setOffsetY(0);
                $drawing->setRotation(0);
                $drawing->setWorksheet($sheet);
            }

            // Set header untuk download
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="Lembur-' . $report->user->name . '-' . $report->report_date->format('Y-m-d') . '.xlsx"');
            header('Cache-Control: max-age=0');

            // Create Excel writer dan export
            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            ob_end_clean();
            $writer->save('php://output');
            exit;

        } catch (\Exception $e) {
            \Log::error('Export Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Gagal mengexport file: ' . $e->getMessage());
        }
    }
} 