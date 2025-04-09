<?php

namespace App\Policies;

use App\Models\Report;
use App\Models\User;

class ReportPolicy
{
    public function view(User $user, Report $report): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        if ($user->hasRole('Vice President') || $user->hasRole('Admin Divisi') || $user->hasRole('Verifikator')) {
            return $report->user->department_id === $user->department_id;
        }

        return $report->user_id === $user->id;
    }

    public function update(User $user, Report $report): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        if ($user->hasRole('Vice President')) {
            return $report->user->department_id === $user->department_id;
        }

        if ($user->hasRole('Admin Divisi') || $user->hasRole('Verifikator')) {
            return $report->user_id === $user->id;
        }

        return $report->user_id === $user->id;
    }

    public function delete(User $user, Report $report): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        if ($user->hasRole('Vice President')) {
            return $report->user->department_id === $user->department_id;
        }

        if ($user->hasRole('Admin Divisi') || $user->hasRole('Verifikator')) {
            return $report->user_id === $user->id;
        }

        return $report->user_id === $user->id;
    }

    public function export(User $user, Report $report): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        if ($user->hasRole('Vice President') || $user->hasRole('Admin Divisi')) {
            // Vice President and Admin Divisi can export reports from users in their department
            return $report->user->department_id === $user->department_id;
        }

        if ($user->hasRole('Verifikator')) {
            // Verifikator cannot export reports
            return false;
        }

        // Regular users can export their own reports
        return $report->user_id === $user->id;
    }

    public function before($user, $ability)
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }
    }
} 