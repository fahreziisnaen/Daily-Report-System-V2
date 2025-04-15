<?php

namespace App\Services;

use App\Models\Report;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DashboardService
{
    /**
     * Get recent reports based on user role
     *
     * @param User $user
     * @param int $limit
     * @param string|null $viewMode Mode tampilan ('personal', 'department', dll)
     * @return Collection
     */
    public function getRecentReports(User $user, int $limit = 5, ?string $viewMode = null): Collection
    {
        // Jika viewMode tidak diberikan, coba ambil dari request
        if ($viewMode === null) {
            $viewMode = request()->get('view', 'personal');
        }

        // Gunakan eager loading yang lebih efisien (hanya ambil kolom yang diperlukan)
        $query = Report::with([
                'user:id,name,department_id', 
                'details:id,report_id,description,status'
            ])
            ->latest('report_date');

        // Selalu kembalikan laporan personal saat dalam personal view
        if ($viewMode === 'personal') {
            return $query
                ->where('user_id', $user->id)
                ->take($limit)
                ->get();
        }
        
        // Jika tidak, filter berdasarkan peran
        if ($user->hasRole('Super Admin')) {
            // Super Admin melihat semua laporan
            return $query->take($limit)->get();
        } 
        elseif ($user->hasRole(['Vice President', 'Admin Divisi', 'Verifikator', 'Human Resource'])) {
            // Untuk tampilan departemen, tampilkan laporan dari departemen mereka
            return $query
                ->whereHas('user', function($q) use ($user) {
                    $q->where('department_id', $user->department_id);
                })
                ->take($limit)
                ->get();
        } 
        else {
            // Karyawan hanya melihat laporan mereka sendiri
            return $query
                ->where('user_id', $user->id)
                ->take($limit)
                ->get();
        }
    }

    /**
     * Get summaries for calendar view
     *
     * @param User $user
     * @param int|null $month
     * @param int|null $year
     * @return Collection
     */
    public function getCalendarSummaries(User $user, ?int $month = null, ?int $year = null): Collection
    {
        $summaries = collect();
        $currentMonth = $month ?: now()->month;
        $currentYear = $year ?: now()->year;
        
        if ($user->hasRole('Super Admin')) {
            // Super Admin sees all employees
            return User::role('Employee')
                ->with(['reports' => function ($query) use ($currentMonth, $currentYear) {
                    $query->whereMonth('report_date', $currentMonth)
                        ->whereYear('report_date', $currentYear);
                }])
                ->get()
                ->map(function ($user) {
                    return (object)[
                        'user_name' => $user->name,
                        'user_id' => $user->id,
                        'reports' => $user->reports
                    ];
                });
        } 
        elseif ($user->hasRole(['Vice President', 'Admin Divisi', 'Verifikator', 'Human Resource'])) {
            // Management roles see employees from their department except VP
            return User::where('department_id', $user->department_id)
                ->whereDoesntHave('roles', function($query) {
                    $query->where('name', 'Vice President');
                })
                ->with(['reports' => function ($query) use ($currentMonth, $currentYear) {
                    $query->whereMonth('report_date', $currentMonth)
                        ->whereYear('report_date', $currentYear);
                }])
                ->orderBy('name')
                ->get()
                ->map(function ($user) {
                    return (object)[
                        'user_name' => $user->name,
                        'user_id' => $user->id,
                        'reports' => $user->reports
                    ];
                });
        } 
        
            // Employee sees only their own data
            $summaries->push((object)[
                'user_name' => $user->name,
            'user_id' => $user->id,
                'reports' => $user->reports()
                    ->whereMonth('report_date', $currentMonth)
                    ->whereYear('report_date', $currentYear)
                    ->get()
            ]);
            
            return $summaries;
    }

    /**
     * Get users without reports for today
     *
     * @param User $user
     * @return Collection
     */
    public function getUsersWithoutReportToday(User $user): Collection
    {
        // Get all users in the same department
        $departmentUsers = User::where('department_id', $user->department_id)
            ->where('id', '!=', $user->id) // Exclude the current user
            ->whereDoesntHave('roles', function($query) {
                $query->where('name', 'Vice President'); // Exclude VP role
                })
                ->get();

        // Get users who have submitted reports today
        $usersWithReport = Report::whereDate('report_date', now())
            ->whereIn('user_id', $departmentUsers->pluck('id'))
            ->pluck('user_id');

        // Filter out users who have submitted reports
        return $departmentUsers->whereNotIn('id', $usersWithReport);
    }

    /**
     * Get dashboard statistics
     *
     * @param User $user
     * @return array
     */
    public function getDashboardStatistics(User $user): array
    {
        $today = Carbon::today();
        $currentMonth = now()->month;
        $currentYear = now()->year;
        
        $reportsQuery = $this->createBaseReportQuery($user);

        // Hitung semua laporan
        $totalReports = $reportsQuery->count();
        $monthlyReports = $this->countReportsForPeriod(clone $reportsQuery, $currentMonth, $currentYear);
        $dailyReports = $this->countReportsForDate(clone $reportsQuery, $today);
        
        // Untuk dashboard admin, hitung status laporan berdasarkan departemen dan periode saat ini
        $pendingVerificationCount = $this->countReportsByStatus(
            clone $reportsQuery, 
            Report::STATUS_PENDING_VERIFICATION, 
            $currentMonth, 
            $currentYear
        );
            
        $pendingApprovalCount = $this->countReportsByStatus(
            clone $reportsQuery, 
            Report::STATUS_PENDING_APPROVAL, 
            $currentMonth, 
            $currentYear
        );
            
        $pendingHrCount = $this->countReportsByStatus(
            clone $reportsQuery, 
            Report::STATUS_PENDING_HR, 
            $currentMonth, 
            $currentYear
        );
            
        $completedCount = $this->countReportsByStatus(
            clone $reportsQuery, 
            Report::STATUS_COMPLETED, 
            $currentMonth, 
            $currentYear
        );
        
        // Hitung laporan yang ditolak (dari semua jenis penolakan)
        $rejectedCount = $this->countRejectedReports(
            clone $reportsQuery, 
            $currentMonth, 
            $currentYear
        );
            
        $nonOvertimeCount = $this->countNonOvertimeReports(
            clone $reportsQuery, 
            $currentMonth, 
            $currentYear
        );

        return [
            'totalReports' => $totalReports,
            'monthlyReports' => $monthlyReports,
            'dailyReports' => $dailyReports,
            'pendingVerificationCount' => $pendingVerificationCount,
            'pendingApprovalCount' => $pendingApprovalCount,
            'pendingHrCount' => $pendingHrCount,
            'completedCount' => $completedCount,
            'rejectedCount' => $rejectedCount,
            'nonOvertimeCount' => $nonOvertimeCount
        ];
    }
    
    /**
     * Create base report query filtered by user role
     *
     * @param User $user
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function createBaseReportQuery(User $user): \Illuminate\Database\Eloquent\Builder
    {
        $query = Report::query();
        
        if ($user->hasRole('Super Admin')) {
            // No additional filters for Super Admin
            return $query;
        } 
        
        if ($user->hasRole(['Vice President', 'Admin Divisi', 'Verifikator', 'Human Resource'])) {
            // Filter by department
            return $query->whereHas('user', function($q) use ($user) {
                $q->where('department_id', $user->department_id);
            });
        } 
        
        // Filter by user as default
        return $query->where('user_id', $user->id);
    }
    
    /**
     * Count reports for specific month and year
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $month
     * @param int $year
     * @return int
     */
    private function countReportsForPeriod(
        \Illuminate\Database\Eloquent\Builder $query, 
        int $month, 
        int $year
    ): int {
        return $query->whereMonth('report_date', $month)
                    ->whereYear('report_date', $year)
                    ->count();
    }
    
    /**
     * Count reports for specific date
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Carbon\Carbon $date
     * @return int
     */
    private function countReportsForDate(
        \Illuminate\Database\Eloquent\Builder $query, 
        Carbon $date
    ): int {
        return $query->whereDate('report_date', $date)->count();
    }
    
    /**
     * Count reports by specific status for period
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $status
     * @param int $month
     * @param int $year
     * @return int
     */
    private function countReportsByStatus(
        \Illuminate\Database\Eloquent\Builder $query,
        string $status,
        int $month,
        int $year
    ): int {
        return $query->where('status', $status)
                    ->whereMonth('report_date', $month)
                    ->whereYear('report_date', $year)
                    ->count();
    }
    
    /**
     * Count rejected reports (all rejection types) for period
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $month
     * @param int $year
     * @return int
     */
    private function countRejectedReports(
        \Illuminate\Database\Eloquent\Builder $query,
        int $month,
        int $year
    ): int {
        return $query->whereIn('status', [
                        Report::STATUS_REJECTED,
                        Report::STATUS_REJECTED_BY_HR,
                        Report::STATUS_REJECTED_BY_VERIFIER,
                        Report::STATUS_REJECTED_BY_VP
                    ])
                    ->whereMonth('report_date', $month)
                    ->whereYear('report_date', $year)
            ->count();
    }
    
    /**
     * Count non-overtime reports for period
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $month
     * @param int $year
     * @return int
     */
    private function countNonOvertimeReports(
        \Illuminate\Database\Eloquent\Builder $query,
        int $month,
        int $year
    ): int {
        return $query->where('is_overtime', false)
                    ->whereMonth('report_date', $month)
                    ->whereYear('report_date', $year)
            ->count();
    }

    /**
     * Get personal dashboard statistics (only for the user's own reports)
     *
     * @param User $user
     * @return array
     */
    public function getPersonalDashboardStatistics(User $user): array
    {
        $today = now()->toDateString();
        $currentMonth = now()->month;
        $currentYear = now()->year;
        
        // Buat query base khusus untuk user ini
        $query = Report::where('user_id', $user->id);
        
        // Gunakan helper methods yang sama dengan getDashboardStatistics
        $totalReports = $query->count();
        $monthlyReports = $this->countReportsForPeriod(clone $query, $currentMonth, $currentYear);
        $dailyReports = $this->countReportsForDate(clone $query, Carbon::today());
        
        $pendingVerificationCount = $this->countReportsByStatus(
            clone $query, 
            Report::STATUS_PENDING_VERIFICATION, 
            $currentMonth, 
            $currentYear
        );
            
        $pendingApprovalCount = $this->countReportsByStatus(
            clone $query, 
            Report::STATUS_PENDING_APPROVAL, 
            $currentMonth, 
            $currentYear
        );
            
        $pendingHrCount = $this->countReportsByStatus(
            clone $query, 
            Report::STATUS_PENDING_HR, 
            $currentMonth, 
            $currentYear
        );
            
        $completedCount = $this->countReportsByStatus(
            clone $query, 
            Report::STATUS_COMPLETED, 
            $currentMonth, 
            $currentYear
        );
        
        $rejectedCount = $this->countRejectedReports(
            clone $query, 
            $currentMonth, 
            $currentYear
        );
        
        $draftCount = $this->countReportsByStatus(
            clone $query, 
            Report::STATUS_DRAFT, 
            $currentMonth, 
            $currentYear
        );
            
        $nonOvertimeCount = $this->countNonOvertimeReports(
            clone $query, 
            $currentMonth, 
            $currentYear
        );
        
        // Cek apakah user punya laporan hari ini
        $hasReportToday = $this->countReportsForDate(clone $query, Carbon::today()) > 0;

        return [
            'hasReportToday' => $hasReportToday,
            'totalReports' => $totalReports,
            'monthlyReports' => $monthlyReports,
            'dailyReports' => $dailyReports,
            'pendingVerificationCount' => $pendingVerificationCount,
            'pendingApprovalCount' => $pendingApprovalCount,
            'pendingHrCount' => $pendingHrCount,
            'completedCount' => $completedCount,
            'rejectedCount' => $rejectedCount,
            'draftCount' => $draftCount,
            'nonOvertimeCount' => $nonOvertimeCount,
        ];
    }

    /**
     * Check if user has submitted a report today
     * 
     * @param User $user
     * @return bool
     */
    public function hasReportToday(User $user): bool
    {
        return $user->reports()
            ->whereDate('report_date', today())
            ->exists();
    }

    /**
     * Get pending verification reports for Verifikator
     * 
     * @param User $user
     * @param int $limit
     * @return Collection
     */
    public function getPendingVerificationReports(User $user, int $limit = 5): Collection
    {
        if (!$user->hasRole('Verifikator')) {
            return collect();
        }

        return Report::with(['user', 'details'])
            ->whereHas('user', function($query) use ($user) {
                $query->where('department_id', $user->department_id);
            })
            ->where('status', Report::STATUS_PENDING_VERIFICATION)
            // Hanya tampilkan laporan yang bukan milik verifikator yang sedang login
            ->where('user_id', '!=', $user->id)
            ->where('is_overtime', true) // Hanya laporan lembur yang perlu verifikasi
            ->orderBy('submitted_at', 'desc')
            ->take($limit)
            ->get();
    }

    /**
     * Get reports that have been responded to by the Verifikator
     * 
     * @param User $user
     * @param int $limit
     * @return Collection
     */
    public function getRespondedVerificationReports(User $user, int $limit = 5): Collection
    {
        if (!$user->hasRole('Verifikator')) {
            return collect();
        }

        return Report::with(['user', 'details'])
            ->whereHas('user', function($query) use ($user) {
                $query->where('department_id', $user->department_id);
            })
            ->where('user_id', '!=', $user->id) // Hanya laporan yang bukan milik verifikator
            ->where(function($query) {
                $query->where('status', Report::STATUS_PENDING_APPROVAL)
                      ->orWhere('status', Report::STATUS_REJECTED_BY_VERIFIER);
            })
            ->where('is_overtime', true) // Hanya laporan lembur yang sudah diverifikasi
            ->whereNotNull('verified_at')
            ->orderBy('verified_at', 'desc')
            ->take($limit)
            ->get();
    }

    /**
     * Get pending approval reports for VP
     * 
     * @param User $user
     * @param int $limit
     * @return Collection
     */
    public function getPendingApprovalReports(User $user, int $limit = 5): Collection
    {
        if (!$user->hasRole('Vice President')) {
            return collect();
        }

        return Report::with(['user', 'verifikator', 'details'])
            ->whereHas('user', function($query) use ($user) {
                $query->where('department_id', $user->department_id);
            })
            ->where('user_id', '!=', $user->id) // Hanya laporan yang bukan milik VP
            ->where('status', Report::STATUS_PENDING_APPROVAL)
            ->where('is_overtime', true) // Hanya tampilkan laporan lembur
            ->whereNotNull('verified_at')
            ->orderBy('verified_at', 'desc')
            ->take($limit)
            ->get();
    }

    /**
     * Get reports that have been responded to by the VP
     * 
     * @param User $user
     * @param int $limit
     * @return Collection
     */
    public function getRespondedApprovalReports(User $user, int $limit = 5): Collection
    {
        if (!$user->hasRole('Vice President')) {
            return collect();
        }

        return Report::with(['user', 'verifikator', 'details'])
            ->whereHas('user', function($query) use ($user) {
                $query->where('department_id', $user->department_id);
            })
            ->where('user_id', '!=', $user->id) // Hanya laporan yang bukan milik VP
            ->where(function($query) {
                $query->where('status', Report::STATUS_PENDING_HR)
                      ->orWhere('status', Report::STATUS_REJECTED_BY_VP);
            })
            ->where('is_overtime', true) // Hanya tampilkan laporan lembur
            ->whereNotNull('approved_at')
            ->orderBy('approved_at', 'desc')
            ->take($limit)
            ->get();
    }

    /**
     * Get pending HR review reports
     * 
     * @param User $user
     * @param int $limit
     * @return Collection
     */
    public function getPendingHrReviewReports(User $user, int $limit = 5): Collection
    {
        if (!$user->hasRole('Human Resource')) {
            return collect();
        }

        $query = Report::with(['user', 'verifikator', 'vp', 'details'])
            ->where('status', Report::STATUS_PENDING_HR)
            ->where('is_overtime', true)
            ->whereNotNull('approved_at');
        
        // HR dapat melihat laporan dari semua departemen
        // Filter departemen bisa ditambahkan di sini jika kebijakan berubah
            
        return $query->orderBy('approved_at', 'desc')
            ->take($limit)
            ->get();
    }

    /**
     * Get recently reviewed reports by HR
     * 
     * @param User $user
     * @param int $limit
     * @return Collection
     */
    public function getRecentReviewedByHrReports(User $user, int $limit = 5): Collection
    {
        if (!$user->hasRole('Human Resource')) {
            return collect();
        }

        $query = Report::with(['user', 'verifikator', 'vp', 'details'])
            ->where(function($query) {
                $query->where('status', Report::STATUS_COMPLETED)
                      ->orWhere('status', Report::STATUS_REJECTED_BY_HR);
            })
            ->where('is_overtime', true)
            ->whereNotNull('reviewed_at');
            
        // HR dapat melihat laporan dari semua departemen
        // Filter departemen bisa ditambahkan di sini jika kebijakan berubah
            
        return $query->orderBy('reviewed_at', 'desc')
            ->take($limit)
            ->get();
    }

    /**
     * Get count of reports approved by HR
     * 
     * @param User $user
     * @return int
     */
    public function getApprovedByHrCount(User $user): int
    {
        if (!$user->hasRole('Human Resource')) {
            return 0;
        }

        return Report::where('status', Report::STATUS_COMPLETED)
            ->where('is_overtime', true)
            ->whereNotNull('reviewed_at')
            ->whereMonth('report_date', now()->month)
            ->whereYear('report_date', now()->year)
            ->count();
    }

    /**
     * Get count of reports rejected by HR
     * 
     * @param User $user
     * @return int
     */
    public function getRejectedByHrCount(User $user): int
    {
        if (!$user->hasRole('Human Resource')) {
            return 0;
        }

        return Report::where('status', Report::STATUS_REJECTED_BY_HR)
            ->where('is_overtime', true)
            ->whereNotNull('reviewed_at')
            ->whereMonth('report_date', now()->month)
            ->whereYear('report_date', now()->year)
            ->count();
    }

    /**
     * Get non-overtime reports count
     * 
     * @param User $user
     * @return int
     */
    public function getNonOvertimeCount(User $user): int
    {
        return Report::where('user_id', $user->id)
            ->where('is_overtime', false)
            ->whereMonth('report_date', now()->month)
            ->whereYear('report_date', now()->year)
            ->count();
    }

    /**
     * Get reports approved by VP count
     * 
     * @param User $user
     * @return int
     */
    public function getApprovedByVpCount(User $user): int
    {
        if (!$user->hasRole('Vice President')) {
            return 0;
        }

        return Report::whereHas('user', function($query) use ($user) {
                $query->where('department_id', $user->department_id);
            })
            ->where('user_id', '!=', $user->id) // Hanya laporan yang bukan milik VP
            ->where('status', Report::STATUS_PENDING_HR)
            ->where('is_overtime', true) // Hanya hitung laporan lembur
            ->whereNotNull('approved_at')
            ->whereMonth('report_date', now()->month)
            ->whereYear('report_date', now()->year)
            ->count();
    }

    /**
     * Get reports rejected by VP count
     * 
     * @param User $user
     * @return int
     */
    public function getRejectedByVpCount(User $user): int
    {
        if (!$user->hasRole('Vice President')) {
            return 0;
        }

        return Report::whereHas('user', function($query) use ($user) {
                $query->where('department_id', $user->department_id);
            })
            ->where('user_id', '!=', $user->id) // Hanya laporan yang bukan milik VP
            ->where('status', Report::STATUS_REJECTED_BY_VP)
            ->where('is_overtime', true) // Hanya laporan lembur 
            ->whereNotNull('approved_at')
            ->whereMonth('report_date', now()->month)
            ->whereYear('report_date', now()->year)
            ->count();
    }

    /**
     * Get today's report for the user
     *
     * @param User $user
     * @return \App\Models\Report|null
     */
    public function getTodayReport(User $user): ?\App\Models\Report
    {
        return Report::where('user_id', $user->id)
            ->whereDate('report_date', today())
            ->latest()
            ->first();
    }

    /**
     * Get count of reports that are currently in process
     * 
     * @param User $user
     * @return int
     */
    public function getPendingProcessCount(User $user): int
    {
        return Report::where('user_id', $user->id)
            ->whereIn('status', [
                Report::STATUS_PENDING_APPROVAL,
                Report::STATUS_PENDING_HR
            ])
            ->whereMonth('report_date', now()->month)
            ->whereYear('report_date', now()->year)
            ->count();
    }

    /**
     * Get verifikator users for report assignment
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getVerifikators(): \Illuminate\Database\Eloquent\Collection
    {
        return User::role('Verifikator')->where('is_active', true)->get();
    }

    /**
     * Get vice presidents for a specific department
     * 
     * @param int|null $departmentId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getVicePresidents(?int $departmentId = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = User::role('Vice President')->where('is_active', true);
        
        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }
        
        return $query->get();
    }

    /**
     * Get reports approved by verifikator count
     * 
     * @param User $user
     * @return int
     */
    public function getApprovedByVerifikatorCount(User $user): int
    {
        if (!$user->hasRole('Verifikator')) {
            return 0;
        }

        return Report::whereHas('user', function($query) use ($user) {
                $query->where('department_id', $user->department_id);
            })
            ->where('user_id', '!=', $user->id) // Hanya laporan yang bukan milik verifikator
            ->where('status', Report::STATUS_PENDING_APPROVAL)
            ->where('is_overtime', true) // Hanya laporan lembur
            ->whereNotNull('verified_at')
            ->whereMonth('report_date', now()->month)
            ->whereYear('report_date', now()->year)
            ->count();
    }

    /**
     * Get reports rejected by verifikator count
     * 
     * @param User $user
     * @return int
     */
    public function getRejectedByVerifikatorCount(User $user): int
    {
        if (!$user->hasRole('Verifikator')) {
            return 0;
        }

        return Report::whereHas('user', function($query) use ($user) {
                $query->where('department_id', $user->department_id);
            })
            ->where('user_id', '!=', $user->id) // Hanya laporan yang bukan milik verifikator
            ->where('status', Report::STATUS_REJECTED_BY_VERIFIER)
            ->where('is_overtime', true) // Hanya laporan lembur
            ->whereNotNull('verified_at')
            ->whereMonth('report_date', now()->month)
            ->whereYear('report_date', now()->year)
            ->count();
    }
} 