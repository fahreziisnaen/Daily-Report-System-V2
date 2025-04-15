<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class HrReviewController extends Controller
{
    public function index()
    {
        // Only users with HR role should access this
        Gate::authorize('hr-review');
        
        $reports = Report::with(['user', 'details'])
            ->where('status', Report::STATUS_PENDING_HR)
            ->orderBy('report_date', 'desc')
            ->paginate(10);

        return view('hr-review.index', compact('reports'));
    }

    public function show(Report $report)
    {
        Gate::authorize('hr-review', $report);
        return view('hr-review.show', compact('report'));
    }

    public function approve(Report $report)
    {
        Gate::authorize('hr-review', $report);
        
        $report->update([
            'status' => Report::STATUS_COMPLETED,
            'reviewed_at' => now(),
            'reviewed_by' => Auth::id()
        ]);

        return redirect()->route('hr-review.index')
            ->with('success', 'Laporan telah disetujui dan selesai diproses.');
    }

    public function reject(Request $request, Report $report)
    {
        Gate::authorize('hr-review', $report);
        
        $request->validate([
            'rejection_notes' => 'required|string|max:500'
        ]);

        $report->update([
            'status' => Report::STATUS_REJECTED_BY_HR,
            'rejection_notes' => $request->rejection_notes,
            'reviewed_at' => now(),
            'reviewed_by' => Auth::id()
        ]);

        return redirect()->route('hr-review.index')
            ->with('success', 'Laporan telah ditolak.');
    }
} 