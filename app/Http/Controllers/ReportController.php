<?php

namespace App\Http\Controllers;

use App\Events\ReportCreated;
use App\Events\ReportUpdated;
use App\Models\Project;
use App\Models\Report;
use App\Models\ReportDetail;
use App\Models\User;
use App\Services\ReportService;
use App\Http\Requests\StoreReportRequest;
use App\Http\Requests\UpdateReportRequest;
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
    
    protected $reportService;
    
    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function index(Request $request)
    {
        $authUser = auth()->user();
        
        // Use service for filtering reports
        $reports = $this->reportService->getFilteredReports($authUser, $request);
        
        // Get filter options from service
        $filterOptions = $this->reportService->getFilterOptions($authUser);
        
        return view('reports.index', [
            'reports' => $reports,
            'employees' => $filterOptions['employees'],
            'locations' => $filterOptions['locations'],
            'projectCodes' => $filterOptions['projectCodes']
        ]);
    }

    public function create()
    {
        // Block Vice Presidents from creating reports
        if (auth()->user()->isVicePresident()) {
            return redirect()->route('reports.index')
                ->with('error', 'Vice President tidak diizinkan membuat laporan.');
        }
        
        // Get verifikators from service
        $verifikators = $this->reportService->getVerifikators();
        
        return view('reports.create', compact('verifikators'));
    }

    public function store(StoreReportRequest $request)
    {
        // Block Vice Presidents from creating reports
        if (auth()->user()->isVicePresident()) {
            return redirect()->route('reports.index')
                ->with('error', 'Vice President tidak diizinkan membuat laporan.');
        }
        
        // Check for existing report
        if ($this->reportService->reportExistsForDate(auth()->id(), $request->report_date)) {
            return back()
                ->withInput()
                ->withErrors(['report_date' => 'Laporan pada tanggal tersebut sudah ada.']);
        }

        // Create report using service, teruskan user ID secara eksplisit
        $report = $this->reportService->createReport($request->validated(), auth()->id());
        
        // Check if submitting user is a verifikator who selected themselves
        $user = auth()->user();
        $isOvertimeReport = $report->is_overtime;
        
        if ($isOvertimeReport && $user->hasRole('Verifikator') && $report->verifikator_id == $user->id) {
            // Auto-verify the report and send directly to VP
            $autoVerified = $this->reportService->submitVerifikatorOwnReport($report, $user->id);
            if ($autoVerified) {
                return redirect()->route('reports.show', $report)
                    ->with('success', 'Laporan berhasil dibuat dan otomatis diverifikasi. Laporan dikirim ke Vice President.');
            }
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
        // Block Vice Presidents from editing reports
        if (auth()->user()->isVicePresident()) {
            return redirect()->route('reports.index')
                ->with('error', 'Vice President tidak diizinkan mengedit laporan.');
        }
        
        $this->authorize('update', $report);
        
        // Get verifikators from service
        $verifikators = $this->reportService->getVerifikators();
        
        // Get vice presidents from service if verifikator is assigned
        $vps = collect();
        if ($report->verifikator) {
            $vps = $this->reportService->getVicePresidents($report->verifikator->department_id);
        }
        
        return view('reports.edit', compact('report', 'verifikators', 'vps'));
    }

    public function update(UpdateReportRequest $request, Report $report)
    {
        // Update report using service, teruskan user ID secara eksplisit
        $report = $this->reportService->updateReport($report, $request->validated(), auth()->id());

        return redirect()->route('reports.show', $report)
            ->with('success', 'Laporan berhasil diperbarui.');
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

    public function getVicePresidents(Request $request)
    {
        $request->validate([
            'verifikator_id' => 'required|exists:users,id'
        ]);
        
        $verifikator = User::findOrFail($request->verifikator_id);
        $vps = User::role('Vice President')
            ->where('department_id', $verifikator->department_id)
            ->where('is_active', true)
            ->get();
            
        return response()->json($vps);
    }

    public function submit(Report $report)
    {
        // Validasi apakah ini laporan user yang sedang login
        if ($report->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }
        
        // Use service to handle report submission
        if (!$report->is_overtime) {
            // Handle non-overtime report, teruskan user ID secara eksplisit
            $updated = $this->reportService->handleNonOvertimeReport($report, auth()->id());
            if ($updated) {
                return redirect()->route('reports.show', $report)
                    ->with('info', 'Laporan telah diubah menjadi "Laporan tanpa Lembur" dan tidak perlu dikirim ke Verifikator.');
            }
        } else {
            // Cek apakah user adalah verifikator dan memilih dirinya sendiri sebagai verifikator
            $user = auth()->user();
            if ($user->hasRole('Verifikator') && $report->verifikator_id == $user->id) {
                // Auto-verifikasi laporan dan kirim langsung ke VP
                $autoVerified = $this->reportService->submitVerifikatorOwnReport($report, auth()->id());
                if ($autoVerified) {
                    return redirect()->route('reports.show', $report)
                        ->with('success', 'Laporan berhasil diverifikasi otomatis dan dikirim ke Vice President.');
                }
            } else {
                // Handle regular report submission
                $submitted = $this->reportService->submitReport($report);
                if ($submitted) {
                    return redirect()->route('reports.show', $report)
                        ->with('success', 'Laporan berhasil dikirim ke Verifikator.');
                } else {
                    return redirect()->route('reports.show', $report)
                        ->with('info', 'Laporan tidak dapat disubmit. Pastikan status masih Draft.');
                }
            }
        }
        
        // Default error response
        return redirect()->route('reports.show', $report)
            ->with('error', 'Terjadi kesalahan saat memproses laporan.');
    }

    /**
     * Resubmit a rejected report back to verification
     */
    public function resubmit(Report $report)
    {
        // Validate user has permission to update this report
        $this->authorize('update', $report);
        
        // Use service to handle resubmission with explicit user ID
        $resubmitted = $this->reportService->resubmitReport($report, 'verifier', auth()->id());
        
        if ($resubmitted) {
            // Trigger event
            event(new ReportUpdated($report));
            
            return redirect()->route('reports.show', $report)
                ->with('success', 'Laporan berhasil dikirim ulang ke Verifikator.');
        } else {
            return redirect()->route('reports.show', $report)
                ->with('error', 'Laporan tidak dapat dikirim ulang. Pastikan status laporan ditolak dan diizinkan untuk direvisi.');
        }
    }

    /**
     * Resubmit a rejected report back to verification from VP
     */
    public function resubmitVp(Report $report)
    {
        // Validate user has permission to update this report
        $this->authorize('update', $report);
        
        // Use service to handle resubmission with explicit user ID
        $resubmitted = $this->reportService->resubmitReport($report, 'vp', auth()->id());
        
        if ($resubmitted) {
            // Trigger event
            event(new ReportUpdated($report));
            
            return redirect()->route('reports.show', $report)
                ->with('success', 'Laporan berhasil dikirim ulang ke Verifikator.');
        } else {
            return redirect()->route('reports.show', $report)
                ->with('error', 'Laporan tidak dapat dikirim ulang. Pastikan status laporan ditolak oleh VP dan diizinkan untuk direvisi.');
        }
    }
} 