<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $user = auth()->user();
        
        // Get recent reports based on role
        $recentReportsQuery = Report::with(['user', 'details'])
            ->latest('report_date');

        if ($user->hasRole('Super Admin')) {
            // Super Admin sees all reports
            $recentReports = $recentReportsQuery->take(5)->get();
        } elseif ($user->hasRole('Admin Divisi')) {
            // Admin Divisi sees reports from their department
            $recentReports = $recentReportsQuery
                ->whereHas('user', function($query) use ($user) {
                    $query->where('department_id', $user->department_id);
                })
                ->take(5)
                ->get();
        } else {
            // Employee sees only their own reports
            $recentReports = $recentReportsQuery
                ->where('user_id', $user->id)
                ->take(5)
                ->get();
        }

        // Get summaries for calendar
        $summaries = collect();
        
        if ($user->hasRole('Super Admin')) {
            // Super Admin sees all employees
            $summaries = User::role('Employee')
                ->with(['reports' => function ($query) {
                    $query->whereMonth('report_date', now()->month)
                        ->whereYear('report_date', now()->year);
                }])
                ->get()
                ->map(function ($user) {
                    return (object)[
                        'user_name' => $user->name,
                        'reports' => $user->reports
                    ];
                });
        } elseif ($user->hasRole('Admin Divisi')) {
            // Admin Divisi sees employees from their department
            $summaries = User::role('Employee')
                ->where('department_id', $user->department_id)
                ->with(['reports' => function ($query) {
                    $query->whereMonth('report_date', now()->month)
                        ->whereYear('report_date', now()->year);
                }])
                ->get()
                ->map(function ($user) {
                    return (object)[
                        'user_name' => $user->name,
                        'reports' => $user->reports
                    ];
                });
        } else {
            // Employee sees only their own data
            $summaries->push((object)[
                'user_name' => $user->name,
                'reports' => $user->reports()
                    ->whereMonth('report_date', now()->month)
                    ->whereYear('report_date', now()->year)
                    ->get()
            ]);
        }

        // Get users without report today
        $usersWithoutReport = collect();
        if ($user->hasRole('Super Admin')) {
            // Super Admin sees all users without reports
            $usersWithoutReport = User::role('Employee')
                ->whereDoesntHave('reports', function ($query) use ($today) {
                    $query->whereDate('report_date', $today);
                })
                ->get();
        } elseif ($user->hasRole('Admin Divisi')) {
            // Admin Divisi sees department users without reports
            $usersWithoutReport = User::role('Employee')
                ->where('department_id', $user->department_id)
                ->whereDoesntHave('reports', function ($query) use ($today) {
                    $query->whereDate('report_date', $today);
                })
                ->get();
        }

        $usersWithoutReport = $usersWithoutReport->map(function($user) {
            return [
                'name' => $user->name,
                'email' => $user->email,
                'avatar_url' => $user->avatar_url
            ];
        });

        // Calculate statistics based on role
        $reportsQuery = Report::query();
        
        if ($user->hasRole('Super Admin')) {
            // No additional filters for Super Admin
        } elseif ($user->hasRole('Admin Divisi')) {
            // Filter by department
            $reportsQuery->whereHas('user', function($query) use ($user) {
                $query->where('department_id', $user->department_id);
            });
        } else {
            // Filter by user
            $reportsQuery->where('user_id', $user->id);
        }

        $totalReports = (clone $reportsQuery)->count();
        $monthlyReports = (clone $reportsQuery)
            ->whereMonth('report_date', now()->month)
            ->count();
        $dailyReports = (clone $reportsQuery)
            ->whereDate('report_date', $today)
            ->count();

        return view('dashboard', compact(
            'recentReports',
            'summaries',
            'usersWithoutReport',
            'totalReports',
            'monthlyReports',
            'dailyReports'
        ));
    }
} 