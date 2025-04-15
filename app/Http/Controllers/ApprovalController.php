<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApprovalController extends Controller
{
    protected $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function index()
    {
        if (!Auth::user()->hasRole('Vice President')) {
            abort(403, 'Unauthorized action.');
        }
 
        $reports = Report::with(['user', 'details'])
            ->where('status', Report::STATUS_PENDING_APPROVAL)
            ->whereHas('user', function ($query) {
                $query->where('department_id', Auth::user()->department_id);
            })
            ->orderBy('report_date', 'desc')
            ->paginate(10);
 
        return view('approval.index', compact('reports'));
    }

    public function show(Report $report)
    {
        if (!Auth::user()->hasRole('Vice President')) {
            abort(403, 'Unauthorized action.');
        }

        if ($report->status !== Report::STATUS_PENDING_APPROVAL) {
            abort(404, 'Report not found or not in pending approval status.');
        }

        return view('approval.show', compact('report'));
    }

    public function approve(Request $request, Report $report)
    {
        if (!Auth::user()->hasRole('Vice President')) {
            abort(403, 'Unauthorized action.');
        }

        if ($report->status !== Report::STATUS_PENDING_APPROVAL) {
            abort(404, 'Report not found or not in pending approval status.');
        }

        $this->reportService->approveReport($report);

        return redirect()->route('approval.index')
            ->with('success', 'Laporan berhasil diapprove.');
    }

    public function reject(Request $request, Report $report)
    {
        if (!Auth::user()->hasRole('Vice President')) {
            abort(403, 'Unauthorized action.');
        }

        if ($report->status !== Report::STATUS_PENDING_APPROVAL) {
            abort(404, 'Report not found or not in pending approval status.');
        }

        $request->validate([
            'rejection_notes' => 'required|string|max:1000'
        ]);

        $this->reportService->rejectReport($report, $request->rejection_notes);

        return redirect()->route('approval.index')
            ->with('success', 'Laporan berhasil ditolak.');
    }
} 