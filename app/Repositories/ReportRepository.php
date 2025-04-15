<?php

namespace App\Repositories;

use App\Models\Report;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ReportRepository
{
    /**
     * Check if a report exists for a user on a specific date
     *
     * @param int $userId
     * @param string $reportDate
     * @return bool
     */
    public function existsForUserOnDate(int $userId, string $reportDate): bool
    {
        return Report::where('user_id', $userId)
            ->where('report_date', $reportDate)
            ->exists();
    }

    /**
     * Create a new report with details
     *
     * @param array $data
     * @return Report
     */
    public function create(array $data): Report
    {
        $report = Report::create($data);
        
        if (isset($data['work_details']) && is_array($data['work_details'])) {
            foreach ($data['work_details'] as $detail) {
                $report->details()->create([
                    'description' => $detail['description'],
                    'status' => $detail['status'],
                ]);
            }
        }
        
        return $report;
    }

    /**
     * Update an existing report with details
     *
     * @param Report $report
     * @param array $data
     * @return Report
     */
    public function update(Report $report, array $data): Report
    {
        $report->update($data);
        
        // Delete and recreate details
        $report->details()->delete();
        if (isset($data['work_details']) && is_array($data['work_details'])) {
            foreach ($data['work_details'] as $detail) {
                $report->details()->create([
                    'description' => $detail['description'],
                    'status' => $detail['status'],
                ]);
            }
        }
        
        return $report;
    }

    /**
     * Get reports for a specific user
     *
     * @param int $userId
     * @param int $limit
     * @return Collection
     */
    public function getByUser(int $userId, int $limit = 10): Collection
    {
        return Report::with('details')
            ->where('user_id', $userId)
            ->orderBy('report_date', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get reports pending verification
     *
     * @param int|null $departmentId
     * @return Collection
     */
    public function getPendingVerification(?int $departmentId = null): Collection
    {
        $query = Report::with(['user', 'details'])
            ->where('status', Report::STATUS_PENDING_VERIFICATION);
            
        if ($departmentId) {
            $query->whereHas('user', function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }
            
        return $query->orderBy('submitted_at', 'desc')->get();
    }

    /**
     * Get reports pending VP approval
     *
     * @param int|null $departmentId
     * @return Collection
     */
    public function getPendingApproval(?int $departmentId = null): Collection
    {
        $query = Report::with(['user', 'details'])
            ->where('status', Report::STATUS_PENDING_APPROVAL);
            
        if ($departmentId) {
            $query->whereHas('user', function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }
            
        return $query->orderBy('verified_at', 'desc')->get();
    }

    /**
     * Get reports pending HR review
     *
     * @return Collection
     */
    public function getPendingHrReview(): Collection
    {
        return Report::with(['user', 'details'])
            ->where('status', Report::STATUS_PENDING_HR)
            ->orderBy('approved_at', 'desc')
            ->get();
    }
    
    /**
     * Get paginated reports pending HR review with filters
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginatedPendingHrReview(int $perPage = 10): LengthAwarePaginator
    {
        return Report::with(['user', 'details', 'verifikator', 'vp'])
            ->where('status', Report::STATUS_PENDING_HR)
            ->where('is_overtime', true)
            ->whereNotNull('approved_at')
            ->orderBy('approved_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Update report status
     *
     * @param Report $report
     * @param string $status
     * @param array $additionalData
     * @return Report
     */
    public function updateStatus(Report $report, string $status, array $additionalData = []): Report
    {
        $data = array_merge(['status' => $status], $additionalData);
        $report->update($data);
        return $report;
    }
} 