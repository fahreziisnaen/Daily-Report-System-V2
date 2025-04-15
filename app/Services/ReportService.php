<?php

namespace App\Services;

use App\Models\Report;
use App\Models\User;
use App\Models\ReportDetail;
use App\Repositories\ReportRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class ReportService
{
    protected $reportRepository;
    
    /**
     * Constructor with repository injection
     *
     * @param ReportRepository $reportRepository
     */
    public function __construct(ReportRepository $reportRepository)
    {
        $this->reportRepository = $reportRepository;
    }
    
    /**
     * Check if a report is considered overtime based on its time parameters
     *
     * @param string $start_time
     * @param string $end_time
     * @param bool $is_overnight
     * @param string $work_day_type
     * @param string|null $report_date
     * @return bool
     */
    public function isOvertime(string $start_time, string $end_time, bool $is_overnight = false, string $work_day_type = 'Hari Kerja', ?string $report_date = null): bool
    {
        $date = $report_date ? Carbon::parse($report_date) : Carbon::today();
        $dayOfWeek = $date->dayOfWeek;

        // Penanganan yang lebih baik saat report_date null
        $start = $report_date 
            ? Carbon::parse($report_date . ' ' . $start_time) 
            : Carbon::parse(Carbon::today()->format('Y-m-d') . ' ' . $start_time);
            
        $end = $report_date 
            ? Carbon::parse($report_date . ' ' . $end_time) 
            : Carbon::parse(Carbon::today()->format('Y-m-d') . ' ' . $end_time);
        
        if ($is_overnight) {
            $end->addDay();
        }

        // Calculate total hours worked
        $totalMinutes = $end->diffInMinutes($start, true);
        $totalHours = $totalMinutes / 60;

        // If Sunday (0) or holiday, automatically overtime
        if ($dayOfWeek == 0 || $work_day_type === 'Hari Libur') {
            return true;
        }

        // For weekdays (Monday-Friday)
        if ($dayOfWeek >= 1 && $dayOfWeek <= 5) {
            return $totalHours > 8.25;
        }
        // For Saturday
        else if ($dayOfWeek == 6) {
            // Regular work hours on Saturday is 8:45-13:00 (4 hours 15 minutes)
            // Only consider overtime if exceeding this duration
            return $totalHours > 4.25;
        }

        return false;
    }

    /**
     * Get filtered reports based on user role and request parameters
     *
     * @param User $user
     * @param Request $request
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getFilteredReports(User $user, Request $request)
    {
        $query = Report::with('user', 'details')->orderBy('report_date', 'desc');
        
        // Filter reports based on user role
        if ($user->isFullAdmin()) {
            // Super Admin sees all reports - no additional filtering needed
        } 
        elseif ($user->isVicePresident()) {
            // VP sees reports from their department with specific statuses
            $query->whereHas('user', function ($q) use ($user) {
                $q->where('department_id', $user->department_id);
            })
            ->where(function($q) {
                $q->where('status', Report::STATUS_PENDING_APPROVAL)
                  ->orWhere('status', Report::STATUS_PENDING_HR)
                  ->orWhere('status', Report::STATUS_COMPLETED)
                  ->orWhere('status', Report::STATUS_REJECTED_BY_VP)
                  ->orWhere('status', Report::STATUS_REJECTED);
            });
        }
        elseif ($user->isVerifikator()) {
            // Verifikator sees reports from their department
            $query->whereHas('user', function ($q) use ($user) {
                $q->where('department_id', $user->department_id);
            })
            ->where(function($q) {
                $q->where('status', Report::STATUS_PENDING_VERIFICATION)
                  ->orWhere('status', Report::STATUS_PENDING_APPROVAL)
                  ->orWhere('status', Report::STATUS_PENDING_HR)
                  ->orWhere('status', Report::STATUS_COMPLETED)
                  ->orWhere('status', Report::STATUS_REJECTED_BY_VP)
                  ->orWhere('status', Report::STATUS_REJECTED_BY_VERIFIER)
                  ->orWhere('status', Report::STATUS_REJECTED);
            });
        }
        elseif ($user->hasRole('Admin Divisi')) {
            // Admin Divisi only see reports from their department
            $query->whereHas('user', function ($q) use ($user) {
                $q->where('department_id', $user->department_id);
            });
        }
        elseif ($user->hasRole('Human Resource')) {
            // HR can see all reports with HR-related statuses (regardless of department)
            $query->where(function($q) {
                $q->where('status', Report::STATUS_PENDING_HR)
                  ->orWhere('status', Report::STATUS_COMPLETED)
                  ->orWhere('status', Report::STATUS_REJECTED_BY_HR);
            })
            ->where('is_overtime', true);
        } 
        else {
            // Regular employees only see their own reports
            $query->where('user_id', $user->id);
        }
        
        // Apply additional filters
        $this->applyRequestFilters($query, $request);
        
        // If user_id is provided in request, override all other filters
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        
        return $query->paginate(10)->withQueryString();
    }

    /**
     * Apply request filters to the reports query
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param Request $request
     * @return void
     */
    private function applyRequestFilters($query, Request $request): void
    {
        // Filter by status if provided
        if ($request->filled('status')) {
            $status = $request->status;
            if (is_array($status)) {
                $query->whereIn('status', $status);
            } else {
                $query->where('status', $status);
            }
        }
        
        // Filter by date if provided
        if ($request->filled('report_date')) {
            $query->whereDate('report_date', $request->report_date);
        }
        
        // Filter by month and year if provided
        if ($request->filled('month') && $request->filled('year')) {
            $query->whereMonth('report_date', $request->month)
                  ->whereYear('report_date', $request->year);
        }
        
        // Filter by location if provided
        if ($request->filled('location')) {
            $query->where('location', $request->location);
        }
        
        // Filter by project code if provided
        if ($request->filled('project_code')) {
            $query->where('project_code', $request->project_code);
        }
        
        // Filter by employee search if provided
        if ($request->filled('employee_search')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->employee_search . '%');
            });
        }

        // Filter by is_overtime if provided
        if ($request->filled('is_overtime')) {
            $query->where('is_overtime', $request->is_overtime);
        }
    }

    /**
     * Get filter options for the reports view
     *
     * @param User $user
     * @return array
     */
    public function getFilterOptions(User $user): array
    {
        // Get unique employee names for filter dropdown (for admins)
        $employees = '[]';
        if ($user->isAdmin() || $user->hasRole('Human Resource')) {
            // For VP, Admin Divisi, and Verifikator, only show employees from their department
            $employeesQuery = User::orderBy('name');
            
            if ($user->hasRole(['Vice President', 'Admin Divisi', 'Verifikator'])) {
                $employeesQuery->where('department_id', $user->department_id);
            }
            
            $employees = $employeesQuery->pluck('name')->toJson();
        }
        
        // Get unique locations for filter dropdown
        $locationsQuery = Report::distinct()->orderBy('location');
        
        // Restrict locations based on role
        if ($user->hasRole(['Vice President', 'Admin Divisi', 'Verifikator'])) {
            $locationsQuery->whereHas('user', function ($q) use ($user) {
                $q->where('department_id', $user->department_id);
            });
        } elseif (!$user->hasRole(['Super Admin', 'Human Resource'])) {
            $locationsQuery->where('user_id', $user->id);
        }
        
        $locations = $locationsQuery->pluck('location')->filter()->values()->toJson();
        
        // Get unique project codes for filter dropdown
        $projectCodesQuery = Report::distinct()->orderBy('project_code');
        
        // Restrict project codes based on role
        if ($user->hasRole(['Vice President', 'Admin Divisi', 'Verifikator'])) {
            $projectCodesQuery->whereHas('user', function ($q) use ($user) {
                $q->where('department_id', $user->department_id);
            });
        } elseif (!$user->hasRole(['Super Admin', 'Human Resource'])) {
            $projectCodesQuery->where('user_id', $user->id);
        }
        
        $projectCodes = $projectCodesQuery->pluck('project_code')->filter()->values()->toJson();
        
        return [
            'employees' => $employees,
            'locations' => $locations,
            'projectCodes' => $projectCodes
        ];
    }

    /**
     * Check if a report already exists for the user on a specific date
     *
     * @param int $userId
     * @param string $reportDate
     * @return bool
     */
    public function reportExistsForDate(int $userId, string $reportDate): bool
    {
        return $this->reportRepository->existsForUserOnDate($userId, $reportDate);
    }

    /**
     * Create a new report with the provided data
     *
     * @param array $data
     * @param int|null $userId User ID yang membuat report
     * @return Report
     */
    public function createReport(array $data, ?int $userId = null): Report
    {
        // Gunakan userId yang disediakan atau fallback ke auth()->id()
        $userId = $userId ?? auth()->id();
        
        // Determine if report is overtime
        $is_overtime = $this->isOvertime(
            $data['start_time'], 
            $data['end_time'],
            isset($data['is_overnight']) ? (bool)$data['is_overnight'] : false,
            $data['work_day_type'],
            $data['report_date']
        );

        // Set initial status to Draft for all reports
        $initialStatus = Report::STATUS_DRAFT;

        // Prepare data for repository
        $reportData = [
            'user_id' => $userId,
            'verifikator_id' => $data['verifikator_id'],
            'vp_id' => $data['vp_id'],
            'report_date' => $data['report_date'],
            'project_code' => $data['project_code'],
            'location' => $data['location'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'is_overnight' => isset($data['is_overnight']) ? (bool)$data['is_overnight'] : false,
            'is_shift' => isset($data['is_shift']) ? (bool)$data['is_shift'] : false,
            'is_overtime' => $is_overtime,
            'work_day_type' => $data['work_day_type'],
            'status' => $initialStatus,
            'work_details' => $data['work_details']
        ];

        // Use repository to create report
        return $this->reportRepository->create($reportData);
    }

    /**
     * Update an existing report with the provided data
     *
     * @param Report $report
     * @param array $data
     * @param int|null $userId User ID yang mengupdate report
     * @return Report
     */
    public function updateReport(Report $report, array $data, ?int $userId = null): Report
    {
        // Gunakan userId yang disediakan atau fallback ke auth()->id()
        $userId = $userId ?? auth()->id();
        
        // Determine if report is overtime
        $is_overtime = $this->isOvertime(
            $data['start_time'], 
            $data['end_time'],
            isset($data['is_overnight']) ? (bool)$data['is_overnight'] : false,
            $data['work_day_type'],
            $data['report_date']
        );

        // Set status based on conditions
        $status = $report->status;
        
        // If report was previously rejected and now being resubmitted
        if (isset($data['submit_type']) && $data['submit_type'] === 'resubmit' && $report->status === Report::STATUS_REJECTED_BY_VERIFIER) {
            $status = Report::STATUS_PENDING_VERIFICATION;
        }

        // Prepare data for repository
        $reportData = [
            'report_date' => $data['report_date'],
            'project_code' => $data['project_code'],
            'location' => $data['location'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'is_overnight' => isset($data['is_overnight']) ? (bool)$data['is_overnight'] : false,
            'is_shift' => isset($data['is_shift']) ? (bool)$data['is_shift'] : false,
            'is_overtime' => $is_overtime,
            'work_day_type' => $data['work_day_type'],
            'updated_by' => $userId,
            'verifikator_id' => $data['verifikator_id'],
            'vp_id' => $data['vp_id'],
            'status' => $status,
            'work_details' => $data['work_details']
        ];

        // Use repository to update report
        return $this->reportRepository->update($report, $reportData);
    }

    /**
     * Submit a report for verification
     *
     * @param Report $report
     * @return bool
     */
    public function submitReport(Report $report): bool
    {
        try {
            // Check if report is still in draft
            if ($report->status !== Report::STATUS_DRAFT) {
                return false;
            }
            
            DB::beginTransaction();
            
            // Update status to pending verification
            $this->reportRepository->updateStatus($report, Report::STATUS_PENDING_VERIFICATION, [
                'submitted_at' => now(),
            ]);
            
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error submitting report: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Submit a report when verifikator selects themselves
     * Auto-approves verification step and sends directly to VP
     *
     * @param Report $report
     * @param int|null $userId User ID yang menangani report
     * @return bool
     */
    public function submitVerifikatorOwnReport(Report $report, ?int $userId = null): bool
    {
        // Gunakan userId yang disediakan atau fallback ke auth()->id()
        $userId = $userId ?? auth()->id();
        
        try {
            // Check if report is still in draft
            if ($report->status !== Report::STATUS_DRAFT) {
                return false;
            }
            
            DB::beginTransaction();
            
            // Update status to pending approval (skip verification)
            $report->status = Report::STATUS_PENDING_APPROVAL;
            $report->updated_by = $userId;
            $report->submitted_at = now();
            $report->verified_at = now();
            $report->verified_by = $userId; // Verifikator verifies own report
            $report->save();
            
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error auto-verifying report: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Handle non-overtime report submission
     *
     * @param Report $report
     * @param int|null $userId User ID yang menangani report
     * @return bool
     */
    public function handleNonOvertimeReport(Report $report, ?int $userId = null): bool
    {
        // Gunakan userId yang disediakan atau fallback ke auth()->id()
        $userId = $userId ?? auth()->id();
        
        try {
            DB::beginTransaction();
            
            $report->status = Report::STATUS_NON_OVERTIME;
            $report->updated_by = $userId;
            $report->save();
            
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error handling non-overtime report: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get verifikator users for report assignment
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getVerifikators()
    {
        return User::role('Verifikator')->where('is_active', true)->get();
    }

    /**
     * Get vice presidents for a specific department
     * 
     * @param int|null $departmentId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getVicePresidents($departmentId = null)
    {
        $query = User::role('Vice President')->where('is_active', true);
        
        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }
        
        return $query->get();
    }

    /**
     * Approve a report by VP
     *
     * @param Report $report
     * @return bool
     */
    public function approveReport(Report $report): bool
    {
        try {
            // Validasi status saat ini
            if ($report->status !== Report::STATUS_PENDING_APPROVAL) {
                return false;
            }
            
            DB::beginTransaction();
            
            $report->status = Report::STATUS_PENDING_HR;
            $report->approved_at = now();
            $report->save();
            
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error approving report: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Reject a report by VP
     *
     * @param Report $report
     * @param string $rejectionNotes
     * @return bool
     */
    public function rejectReport(Report $report, string $rejectionNotes): bool
    {
        try {
            // Validasi status saat ini
            if ($report->status !== Report::STATUS_PENDING_APPROVAL) {
                return false;
            }
            
            DB::beginTransaction();
            
            $report->status = Report::STATUS_REJECTED_BY_VP;
            $report->rejection_notes = $rejectionNotes;
            $report->rejected_at = now();
            $report->save();
            
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error rejecting report: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Resubmit a rejected report
     *
     * @param Report $report
     * @param string $rejectionType The type of rejection status that's being resubmitted
     * @param int|null $userId User ID yang melakukan resubmit
     * @return bool
     */
    public function resubmitReport(Report $report, string $rejectionType, ?int $userId = null): bool
    {
        try {
            // Gunakan userId yang disediakan atau fallback ke auth()->id()
            $userId = $userId ?? auth()->id();
            
            // Check if report can be resubmitted
            if (($rejectionType === 'verifier' && $report->status !== Report::STATUS_REJECTED_BY_VERIFIER) ||
                ($rejectionType === 'vp' && $report->status !== Report::STATUS_REJECTED_BY_VP) ||
                !$report->can_revise) {
                return false;
            }
            
            DB::beginTransaction();
            
            // Update report status to pending verification
            $report->status = Report::STATUS_PENDING_VERIFICATION;
            $report->updated_by = $userId;
            $report->save();
            
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error resubmitting report: ' . $e->getMessage());
            return false;
        }
    }
} 