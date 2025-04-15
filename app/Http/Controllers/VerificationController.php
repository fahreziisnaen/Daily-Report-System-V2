<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class VerificationController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        // Check if the user is a Verifikator
        if (!Auth::user()->hasRole('Verifikator')) {
            abort(403, 'Unauthorized action.');
        }

        $reports = Report::with(['user', 'details'])
            ->where('status', Report::STATUS_PENDING_VERIFICATION)
            ->whereHas('user', function ($query) {
                $query->where('department_id', Auth::user()->department_id);
            })
            ->orderBy('report_date', 'desc')
            ->paginate(10);

        return view('verification.index', compact('reports'));
    }

    public function show(Report $report)
    {
        $this->authorize('verify', $report);
        return view('verification.show', compact('report'));
    }

    public function approve(Report $report)
    {
        $this->authorize('verify', $report);
        
        $report->update([
            'status' => Report::STATUS_PENDING_APPROVAL,
            'verified_at' => now(),
            'verified_by' => Auth::id()
        ]);

        return redirect()->route('verification.index')
            ->with('success', 'Laporan berhasil diverifikasi dan dikirim ke Vice President.');
    }

    public function reject(Request $request, Report $report)
    {
        $this->authorize('verify', $report);
        
        $request->validate([
            'rejection_notes' => 'required|string|max:500',
            'can_revise' => 'required|boolean'
        ]);

        $report->update([
            'status' => Report::STATUS_REJECTED_BY_VERIFIER,
            'rejection_notes' => $request->rejection_notes,
            'can_revise' => $request->boolean('can_revise'),
            'verified_at' => now(),
            'verified_by' => Auth::id()
        ]);

        $canReviseText = $request->boolean('can_revise') ? 'dapat direvisi' : 'tidak dapat direvisi';
        
        return redirect()->route('verification.index')
            ->with('success', "Laporan telah ditolak dan dikembalikan ke pembuat laporan. Laporan $canReviseText.");
    }
} 