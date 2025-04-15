<?php

namespace App\Http\ViewComposers;

use App\Services\DashboardService;
use Illuminate\View\View;

class DashboardComposer
{
    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Compose the dashboard view
     *
     * @param View $view
     * @return void
     */
    public function compose(View $view): void
    {
        $user = auth()->user();
        $viewParam = request()->get('view');
        
        // Get dashboard statistics for both personal and department
        $personalStats = $this->dashboardService->getPersonalDashboardStatistics($user);
        $departmentStats = $this->dashboardService->getDashboardStatistics($user);
        
        // Get common data for all roles
        $view->with([
            'hasReportToday' => $personalStats['hasReportToday'],
            'recentReports' => $this->dashboardService->getRecentReports($user),
            'summaries' => $this->dashboardService->getCalendarSummaries($user),
            'statistics' => $departmentStats,
            'personalStatistics' => $personalStats,
        ]);

        // Get role-specific data
        if ($user->hasRole('Super Admin')) {
            $this->composeSuperAdminDashboard($view, $viewParam);
        } elseif ($user->hasRole('Vice President')) {
            $this->composeVicePresidentDashboard($view, $viewParam, $departmentStats);
        } elseif ($user->hasRole('Admin Divisi')) {
            $this->composeAdminDivisiDashboard($view, $viewParam, $user, $departmentStats);
        } elseif ($user->hasRole('Verifikator')) {
            $this->composeVerifikatorDashboard($view, $viewParam, $user, $departmentStats, $personalStats);
        } elseif ($user->hasRole('Human Resource')) {
            $this->composeHumanResourceDashboard($view, $viewParam, $user, $departmentStats);
        } else {
            $this->composePersonalDashboard($view, $viewParam);
        }
    }

    /**
     * Compose Super Admin dashboard data
     * 
     * @param View $view
     * @param string|null $viewParam
     * @return void
     */
    private function composeSuperAdminDashboard(View $view, ?string $viewParam): void
    {
        $view->with([
            'userCount' => \App\Models\User::count(),
            'section' => $viewParam === 'personal' ? 'personal' : 'super-admin',
        ]);
    }

    /**
     * Compose Vice President dashboard data
     * 
     * @param View $view
     * @param string|null $viewParam
     * @param array $departmentStats
     * @return void
     */
    private function composeVicePresidentDashboard(View $view, ?string $viewParam, array $departmentStats): void
    {
        $user = auth()->user();
        
        $view->with([
            'pendingApprovalCount' => $departmentStats['pendingApprovalCount'],
            'pendingApprovalReports' => $this->dashboardService->getPendingApprovalReports($user),
            'respondedApprovalReports' => $this->dashboardService->getRespondedApprovalReports($user),
            'dashboardService' => $this->dashboardService,
            'section' => $viewParam === 'personal' ? 'personal' : 'vice-president',
        ]);
    }

    /**
     * Compose Admin Divisi dashboard data
     * 
     * @param View $view
     * @param string|null $viewParam
     * @param \App\Models\User $user
     * @param array $departmentStats
     * @return void
     */
    private function composeAdminDivisiDashboard(View $view, ?string $viewParam, \App\Models\User $user, array $departmentStats): void
    {
        $view->with([
            'usersWithoutReport' => $this->dashboardService->getUsersWithoutReportToday($user),
            'pendingVerificationCount' => $departmentStats['pendingVerificationCount'],
            'pendingApprovalCount' => $departmentStats['pendingApprovalCount'],
            'pendingHrCount' => $departmentStats['pendingHrCount'],
            'completedCount' => $departmentStats['completedCount'],
            'rejectedCount' => $departmentStats['rejectedCount'],
            'nonOvertimeCount' => $departmentStats['nonOvertimeCount'],
            'section' => $viewParam === 'personal' ? 'personal' : 'admin',
        ]);
    }

    /**
     * Compose Verifikator dashboard data
     * 
     * @param View $view
     * @param string|null $viewParam
     * @param \App\Models\User $user
     * @param array $departmentStats
     * @param array $personalStats
     * @return void
     */
    private function composeVerifikatorDashboard(
        View $view, 
        ?string $viewParam, 
        \App\Models\User $user, 
        array $departmentStats, 
        array $personalStats
    ): void
    {
        $view->with([
            'pendingVerificationCount' => $departmentStats['pendingVerificationCount'],
            'myPendingVerificationCount' => $personalStats['pendingVerificationCount'],
            'pendingProcessCount' => $this->dashboardService->getPendingProcessCount($user),
            'todayReport' => $this->dashboardService->getTodayReport($user),
            'pendingVerificationReports' => $this->dashboardService->getPendingVerificationReports($user),
            'respondedVerificationReports' => $this->dashboardService->getRespondedVerificationReports($user),
            'dashboardService' => $this->dashboardService,
            'section' => $viewParam === 'personal' ? 'personal' : 'verifikator',
        ]);
    }

    /**
     * Compose Human Resource dashboard data
     * 
     * @param View $view
     * @param string|null $viewParam
     * @param \App\Models\User $user
     * @param array $departmentStats
     * @return void
     */
    private function composeHumanResourceDashboard(
        View $view, 
        ?string $viewParam, 
        \App\Models\User $user, 
        array $departmentStats
    ): void
    {
        $view->with([
            'pendingHrReviewCount' => $departmentStats['pendingHrCount'],
            'approvedHrCount' => $this->dashboardService->getApprovedByHrCount($user),
            'rejectedHrCount' => $this->dashboardService->getRejectedByHrCount($user),
            'pendingReviews' => $this->dashboardService->getPendingHrReviewReports($user),
            'recentReviewed' => $this->dashboardService->getRecentReviewedByHrReports($user),
            'dashboardService' => $this->dashboardService,
            'section' => $viewParam === 'personal' ? 'personal' : 'hr',
        ]);
    }

    /**
     * Compose personal dashboard data
     * 
     * @param View $view
     * @param string|null $viewParam
     * @return void
     */
    private function composePersonalDashboard(View $view, ?string $viewParam): void
    {
        $view->with([
            'section' => 'personal',
        ]);
    }
} 