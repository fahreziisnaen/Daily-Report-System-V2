<?php

namespace App\Policies;

use App\Models\Report;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReportPolicy
{
    use HandlesAuthorization;

    public function verify(User $user, Report $report)
    {
        return $user->canVerifyReport($report);
    }

    public function approve(User $user, Report $report)
    {
        return $user->canApproveReport($report);
    }

    public function review(User $user, Report $report)
    {
        return $user->canReviewReports();
    }

    public function view(User $user, Report $report)
    {
        // Super Admin can view all reports
        if ($user->isFullAdmin()) {
            return true;
        }

        // HR can view all reports
        if ($user->isHumanResource()) {
            return true;
        }

        // VP, Admin Divisi, and Verifikator can view reports from their department
        if ($user->isDepartmentAdmin() || $user->isVerifikator()) {
            return $user->department_id === $report->user->department_id;
        }

        // Regular users can only view their own reports
        return $user->id === $report->user_id;
    }

    /**
     * Determine if the user can create a report
     */
    public function create(User $user): bool
    {
        // Vice President cannot create reports
        if ($user->isVicePresident()) {
            return false;
        }
        
        // All other users can create reports
        return true;
    }

    public function update(User $user, Report $report)
    {
        // Vice President cannot update reports
        if ($user->isVicePresident()) {
            return false;
        }
        
        return $user->canModifyReport($report);
    }

    public function delete(User $user, Report $report)
    {
        // Vice President cannot delete reports
        if ($user->isVicePresident()) {
            return false;
        }
        
        // Only allow deletion if the report is in draft status
        if ($report->status !== Report::STATUS_DRAFT) {
            return false;
        }

        // Super Admin can delete any report
        if ($user->isFullAdmin()) {
            return true;
        }

        // Regular users can only delete their own reports
        return $user->id === $report->user_id;
    }

    public function export(User $user, Report $report): bool
    {
        if ($user->isFullAdmin()) {
            return true;
        }

        if ($user->isDepartmentAdmin() && !$user->isFullAdmin()) {
            // Department admins can export reports from users in their department
            return $report->user->department_id === $user->department_id;
        }

        if ($user->isVerifikator() || $user->isHumanResource()) {
            // Verifikator and Human Resource cannot export reports
            return false;
        }

        // Regular users can export their own reports
        return $report->user_id === $user->id;
    }

    public function before($user, $ability)
    {
        if ($user->isFullAdmin()) {
            return true;
        }
    }
} 