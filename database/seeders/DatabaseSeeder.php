<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Report;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        try {
            // 1. First create roles and permissions
            $this->call(RolePermissionSeeder::class);
            
            // 2. Then create departments
            $this->call(DepartmentSeeder::class);
            
            // 3. Then create users with roles
            $this->call(UserSeeder::class);
            
            // 4. Create sample projects
            $this->call(ProjectSeeder::class);
            
            // 5. Create sample reports
            $this->createReports();
            
        } catch (\Exception $e) {
            // Log any errors during seeding
            \Log::error('Error during database seeding: ' . $e->getMessage());
            echo 'Error during database seeding: ' . $e->getMessage() . "\n";
        }
    }
    
    /**
     * Create sample reports
     */
    private function createReports(): void
    {
        // Get users for reports
        $employees = User::role('Employee')->get();
        $adminDivisis = User::role('Admin Divisi')->get();
        $hrUsers = User::role('Human Resource')->get();
        
        if ($employees->isEmpty() || $adminDivisis->isEmpty() || $hrUsers->isEmpty()) {
            echo "Warning: Could not find required users for report seeding.\n";
            return;
        }

        // Get project codes
        $projectCodes = \App\Models\Project::pluck('code')->toArray();
        
        // Locations
        $locations = ['Jakarta', 'Surabaya', 'Bandung', 'Semarang', 'Makassar', 'Medan'];
        
        // Report templates to duplicate for each user
        $reportTemplates = [
            [
                'project_code_index' => 0,
                'location_index' => 0,
                'start_time' => '08:00:00',
                'end_time' => '17:00:00',
                'is_overnight' => 0,
                'is_overtime' => 0,
                'is_shift' => 0,
                'work_day_type' => 'Hari Kerja',
                'details' => [
                    [
                        'description' => 'Implementasi sistem network',
                        'status' => 'Selesai'
                    ],
                    [
                        'description' => 'Konfigurasi firewall',
                        'status' => 'Selesai'
                    ]
                ]
            ],
            [
                'project_code_index' => 1,
                'location_index' => 1,
                'start_time' => '17:00:00',
                'end_time' => '23:00:00',
                'is_overnight' => 0,
                'is_overtime' => 1,
                'is_shift' => 0,
                'work_day_type' => 'Hari Kerja',
                'details' => [
                    [
                        'description' => 'Setup dan konfigurasi server',
                        'status' => 'Selesai'
                    ]
                ]
            ],
            [
                'project_code_index' => 2,
                'location_index' => 2,
                'start_time' => '20:00:00',
                'end_time' => '05:00:00',
                'is_overnight' => 1,
                'is_overtime' => 1,
                'is_shift' => 0,
                'work_day_type' => 'Hari Kerja',
                'details' => [
                    [
                        'description' => 'Instalasi perangkat network',
                        'status' => 'Selesai'
                    ],
                    [
                        'description' => 'Pengujian koneksi',
                        'status' => 'Selesai'
                    ]
                ]
            ],
            [
                'project_code_index' => 0,
                'location_index' => 3,
                'start_time' => '09:00:00',
                'end_time' => '15:00:00',
                'is_overnight' => 0,
                'is_overtime' => 0,
                'is_shift' => 0,
                'work_day_type' => 'Hari Libur',
                'details' => [
                    [
                        'description' => 'Meeting dengan client',
                        'status' => 'Selesai'
                    ]
                ]
            ]
        ];

        // Create reports for all users
        $allUsers = $employees->merge($adminDivisis);
        $now = now();
        $days = range(1, 28);
        $hrUser = $hrUsers->first();
        
        foreach ($allUsers as $user) {
            // Get the verifikator and VP for the user's department
            $verifikator = User::role('Verifikator')->where('department_id', $user->department_id)->first();
            $vicePresident = User::role('Vice President')->where('department_id', $user->department_id)->first();
            
            if (!$verifikator || !$vicePresident) {
                echo "Warning: Could not find Verifikator or VP for user {$user->name}, department ID: {$user->department_id}\n";
                continue;
            }
            
            // Create reports for the current month (for rekap view testing)
            $monthDays = array_slice($days, 0, mt_rand(15, 25)); // 15-25 days of work per month
            
            foreach ($monthDays as $day) {
                // Pick a random report template
                $template = $reportTemplates[array_rand($reportTemplates)];
                
                // Decide on a random status for the report
                $reportStatuses = [
                    // 15% reports are still in draft
                    0 => Report::STATUS_DRAFT, 
                    
                    // 10% are non-overtime reports (only for non-overtime reports)
                    1 => Report::STATUS_NON_OVERTIME,
                    
                    // 10% waiting for verification
                    2 => Report::STATUS_PENDING_VERIFICATION,
                    
                    // 5% rejected by verifier
                    3 => Report::STATUS_REJECTED_BY_VERIFIER,
                    
                    // 10% waiting for VP approval
                    4 => Report::STATUS_PENDING_APPROVAL,
                    
                    // 5% rejected by VP
                    5 => Report::STATUS_REJECTED_BY_VP,
                    
                    // 15% waiting for HR review
                    6 => Report::STATUS_PENDING_HR,
                    
                    // 10% rejected by HR
                    7 => Report::STATUS_REJECTED_BY_HR,
                    
                    // 20% completed
                    8 => Report::STATUS_COMPLETED
                ];
                
                $randomStatusIndex = mt_rand(0, 100);
                $status = null;
                
                // If report is not overtime, force it to be NON_OVERTIME status
                if (!$template['is_overtime']) {
                    $status = Report::STATUS_NON_OVERTIME;
                } else {
                    // For overtime reports, use the normal distribution
                    if ($randomStatusIndex < 15) {
                        $status = $reportStatuses[0]; // DRAFT
                    } elseif ($randomStatusIndex < 25) {
                        $status = $reportStatuses[2]; // PENDING_VERIFICATION
                    } elseif ($randomStatusIndex < 30) {
                        $status = $reportStatuses[3]; // REJECTED_BY_VERIFIER
                    } elseif ($randomStatusIndex < 40) {
                        $status = $reportStatuses[4]; // PENDING_VP
                    } elseif ($randomStatusIndex < 45) {
                        $status = $reportStatuses[5]; // REJECTED_BY_VP
                    } elseif ($randomStatusIndex < 60) {
                        $status = $reportStatuses[6]; // PENDING_HR
                    } elseif ($randomStatusIndex < 70) {
                        $status = $reportStatuses[7]; // REJECTED_BY_HR
                    } else {
                        $status = $reportStatuses[8]; // COMPLETED
                    }
                }
                
                // Create the report with base data
                $reportData = [
                    'user_id' => $user->id,
                    'report_date' => $now->copy()->setDay($day)->format('Y-m-d'),
                    'project_code' => $projectCodes[$template['project_code_index'] % count($projectCodes)],
                    'location' => $locations[$template['location_index'] % count($locations)],
                    'start_time' => $template['start_time'],
                    'end_time' => $template['end_time'],
                    'is_overnight' => $template['is_overnight'],
                    'is_overtime' => $template['is_overtime'],
                    'is_shift' => $template['is_shift'],
                    'work_day_type' => $template['work_day_type'],
                    'verifikator_id' => $verifikator->id,
                    'vp_id' => $vicePresident->id,
                    'status' => $status,
                    'updated_by' => null,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
                
                // Add timestamps based on status
                $createTime = now()->subDays(mt_rand(1, 30));  // Random date in the past
                
                if ($status !== Report::STATUS_DRAFT && $status !== Report::STATUS_NON_OVERTIME) {
                    $reportData['submitted_at'] = $createTime;
                }
                
                if (in_array($status, [
                    Report::STATUS_PENDING_APPROVAL,
                    Report::STATUS_REJECTED_BY_VP,
                    Report::STATUS_PENDING_HR,
                    Report::STATUS_REJECTED_BY_HR,
                    Report::STATUS_COMPLETED
                ])) {
                    $reportData['verified_at'] = $createTime->copy()->addHours(mt_rand(1, 24));
                    $reportData['updated_by'] = $verifikator->id;
                }
                
                if (in_array($status, [
                    Report::STATUS_PENDING_HR, 
                    Report::STATUS_REJECTED_BY_HR, 
                    Report::STATUS_COMPLETED
                ])) {
                    $reportData['approved_at'] = $createTime->copy()->addHours(mt_rand(24, 72));
                    $reportData['updated_by'] = $vicePresident->id;
                }
                
                if (in_array($status, [
                    Report::STATUS_REJECTED_BY_HR, 
                    Report::STATUS_COMPLETED
                ])) {
                    $reportData['reviewed_at'] = $createTime->copy()->addHours(mt_rand(72, 120));
                    $reportData['reviewed_by'] = $hrUser->id;
                    $reportData['updated_by'] = $hrUser->id;
                }
                
                if ($status === Report::STATUS_COMPLETED) {
                    $reportData['completed_at'] = $createTime->copy()->addHours(mt_rand(120, 168));
                }
                
                // Add rejection notes for rejected reports
                if (in_array($status, [
                    Report::STATUS_REJECTED_BY_VERIFIER, 
                    Report::STATUS_REJECTED_BY_VP, 
                    Report::STATUS_REJECTED_BY_HR
                ])) {
                    $rejectionReasons = [
                        'Informasi tidak lengkap, mohon dilengkapi',
                        'Format laporan tidak sesuai standar',
                        'Data tidak akurat, mohon direvisi',
                        'Perlu klarifikasi untuk beberapa item pekerjaan',
                        'Laporan terlalu singkat, mohon tambahkan detail'
                    ];
                    
                    $reportData['rejection_notes'] = $rejectionReasons[array_rand($rejectionReasons)];
                    $reportData['can_revise'] = true;
                }
                
                $report = Report::create($reportData);
                
                // Create report details
                foreach ($template['details'] as $detail) {
                    $report->details()->create([
                        'description' => $detail['description'],
                        'status' => $detail['status'],
                        'created_at' => $createTime,
                        'updated_at' => $createTime
                    ]);
                }
            }
            
            // Create some reports for previous month (for history) - mostly completed
            $prevMonth = $now->copy()->subMonth();
            $prevMonthDays = array_slice($days, 0, mt_rand(10, 20)); // 10-20 days of work in prev month
            
            foreach ($prevMonthDays as $day) {
                // Pick a random report template
                $template = $reportTemplates[array_rand($reportTemplates)];
                
                // 80% completed reports, 20% rejected by HR for previous month
                $status = mt_rand(1, 10) <= 8 
                    ? Report::STATUS_COMPLETED 
                    : Report::STATUS_REJECTED_BY_HR;
                
                $createTime = $prevMonth->copy()->setDay($day)->subDays(mt_rand(1, 5));
                
                // Create the report
                $reportData = [
                    'user_id' => $user->id,
                    'report_date' => $prevMonth->copy()->setDay($day)->format('Y-m-d'),
                    'project_code' => $projectCodes[$template['project_code_index'] % count($projectCodes)],
                    'location' => $locations[$template['location_index'] % count($locations)],
                    'start_time' => $template['start_time'],
                    'end_time' => $template['end_time'],
                    'is_overnight' => $template['is_overnight'],
                    'is_overtime' => $template['is_overtime'],
                    'is_shift' => $template['is_shift'],
                    'work_day_type' => $template['work_day_type'],
                    'verifikator_id' => $verifikator->id,
                    'vp_id' => $vicePresident->id,
                    'status' => $status,
                    'submitted_at' => $createTime,
                    'verified_at' => $createTime->copy()->addHours(mt_rand(1, 24)),
                    'approved_at' => $createTime->copy()->addHours(mt_rand(24, 72)),
                    'reviewed_at' => $createTime->copy()->addHours(mt_rand(72, 120)),
                    'reviewed_by' => $hrUser->id,
                    'created_at' => $createTime,
                    'updated_at' => $createTime
                ];
                
                if ($status === Report::STATUS_COMPLETED) {
                    $reportData['completed_at'] = $createTime->copy()->addHours(mt_rand(120, 168));
                } else {
                    $rejectionReasons = [
                        'Informasi tidak lengkap, mohon dilengkapi',
                        'Format laporan tidak sesuai standar',
                        'Data tidak akurat, mohon direvisi',
                        'Perlu klarifikasi untuk beberapa item pekerjaan',
                        'Laporan terlalu singkat, mohon tambahkan detail'
                    ];
                    
                    $reportData['rejection_notes'] = $rejectionReasons[array_rand($rejectionReasons)];
                    $reportData['can_revise'] = true;
                }
                
                $report = Report::create($reportData);
                
                // Create report details
                foreach ($template['details'] as $detail) {
                    $report->details()->create([
                        'description' => $detail['description'],
                        'status' => $detail['status'],
                        'created_at' => $createTime,
                        'updated_at' => $createTime
                    ]);
                }
            }
        }
    }
}