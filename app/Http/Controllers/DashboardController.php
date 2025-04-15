<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Report;

class DashboardController extends Controller
{
    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Display the dashboard view
     * 
     * Shows the appropriate dashboard based on user role and view mode parameter
     * 
     * @return \Illuminate\View\View
     */
    public function index(Request $request): \Illuminate\View\View
    {
        $user = auth()->user();
        $viewMode = $request->input('view', 'department');
        
        // VP selalu diarahkan ke VP dashboard, tanpa opsi lain
        if ($user->hasRole('Vice President')) {
            // Parameter view diabaikan untuk VP, selalu gunakan 'vice-president'
            $viewMode = 'vice-president';
            
            // Tambahkan variabel yang dibutuhkan oleh view vice president
            $pendingApprovalReports = $this->dashboardService->getPendingApprovalReports($user);
            $respondedApprovalReports = $this->dashboardService->getRespondedApprovalReports($user);
            
            return view('dashboard.index', [
                'title' => 'Dashboard',
                'pendingApprovalReports' => $pendingApprovalReports,
                'respondedApprovalReports' => $respondedApprovalReports,
                'dashboardService' => $this->dashboardService,
                'pendingApprovalCount' => count($pendingApprovalReports),
                'section' => 'vice-president'
            ]);
        }
        
        // Default section to personal when viewMode is personal
        $section = $viewMode === 'personal' ? 'personal' : 'dashboard';
        
        // Get month and year from request or use current date
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);
        
        // Get common data for all roles
        $personalStats = $this->dashboardService->getPersonalDashboardStatistics($user);
        $departmentStats = $this->dashboardService->getDashboardStatistics($user);
        
        // Basic data for dashboard
        $data = [
            'title' => 'Dashboard',
            'hasReportToday' => $personalStats['hasReportToday'],
            'recentReports' => $this->dashboardService->getRecentReports($user, 5, $viewMode),
            'summaries' => $this->dashboardService->getCalendarSummaries($user, $month, $year),
            'statistics' => $departmentStats,
            'personalStatistics' => $personalStats,
            'totalReports' => $personalStats['totalReports'],
            'monthlyReports' => $personalStats['monthlyReports'],
            'dailyReports' => $personalStats['dailyReports'],
            'nonOvertimeCount' => $personalStats['nonOvertimeCount'],
            'draftCount' => $personalStats['draftCount'],
            'month' => $month,
            'year' => $year,
            'viewMode' => $viewMode,
            'section' => $section
        ];
        
        // Set statistics based on view mode
        if ($viewMode === 'personal') {
            $data['pendingVerificationCount'] = $personalStats['pendingVerificationCount'];
            $data['pendingApprovalCount'] = $personalStats['pendingApprovalCount'];
            $data['pendingHrCount'] = $personalStats['pendingHrCount'];
            $data['completedCount'] = $personalStats['completedCount'];
            $data['rejectedCount'] = $personalStats['rejectedCount'];
        } else {
            // If not personal view, use department statistics
            $data['pendingVerificationCount'] = $departmentStats['pendingVerificationCount'];
            $data['pendingApprovalCount'] = $departmentStats['pendingApprovalCount'];
            $data['pendingHrCount'] = $departmentStats['pendingHrCount'];
            $data['completedCount'] = $departmentStats['completedCount'];
            $data['rejectedCount'] = $departmentStats['rejectedCount'];
            $data['nonOvertimeCount'] = $departmentStats['nonOvertimeCount'];
        }
        
        // Get role-specific data
        if ($user->hasRole('Super Admin')) {
            $data['userCount'] = \App\Models\User::count();
            $data['section'] = $viewMode === 'personal' ? 'personal' : 'super-admin';
        } 
        elseif ($user->hasRole('Human Resource')) {
            // Tambahkan variabel yang dibutuhkan oleh view HR
            $data['pendingHrReviewCount'] = $departmentStats['pendingHrCount'];
            $data['approvedHrCount'] = $this->dashboardService->getApprovedByHrCount($user);
            $data['rejectedHrCount'] = $this->dashboardService->getRejectedByHrCount($user);
            $data['pendingReviews'] = $this->dashboardService->getPendingHrReviewReports($user);
            $data['recentReviewed'] = $this->dashboardService->getRecentReviewedByHrReports($user);
            $data['dashboardService'] = $this->dashboardService;
            
            $data['section'] = $viewMode === 'personal' ? 'personal' : 'hr';
        } 
        elseif ($user->hasRole('Verifikator')) {
            // Data personal yang diperlukan untuk view verifikator
            $data['myPendingVerificationCount'] = $personalStats['pendingVerificationCount'];
            $data['pendingProcessCount'] = $this->dashboardService->getPendingProcessCount($user);
            $data['todayReport'] = $this->dashboardService->getTodayReport($user);
            $data['pendingVerificationReports'] = $this->dashboardService->getPendingVerificationReports($user);
            $data['respondedVerificationReports'] = $this->dashboardService->getRespondedVerificationReports($user);
            $data['dashboardService'] = $this->dashboardService;
            
            $data['section'] = $viewMode === 'personal' ? 'personal' : 'verifikator';
        } 
        elseif ($user->hasRole('Admin Divisi')) {
            $data['usersWithoutReport'] = $this->dashboardService->getUsersWithoutReportToday($user);
            
            // Hanya gunakan data departemen untuk dashboard admin dalam tampilan department
            if ($viewMode !== 'personal') {
                $data['pendingVerificationCount'] = $departmentStats['pendingVerificationCount'];
                $data['pendingApprovalCount'] = $departmentStats['pendingApprovalCount'];
                $data['pendingHrCount'] = $departmentStats['pendingHrCount'];
                $data['completedCount'] = $departmentStats['completedCount'];
                $data['rejectedCount'] = $departmentStats['rejectedCount'];
                $data['nonOvertimeCount'] = $departmentStats['nonOvertimeCount'];
            }
            
            $data['section'] = $viewMode === 'personal' ? 'personal' : 'admin';
        }
        else {
            // Default to personal dashboard for all other roles
            $data['section'] = 'personal';
        }

        return view('dashboard.index', $data);
    }
} 