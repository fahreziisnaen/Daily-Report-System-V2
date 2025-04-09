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
        
        if ($employees->isEmpty() || $adminDivisis->isEmpty()) {
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
        
        foreach ($allUsers as $user) {
            // Create reports for the current month (for rekap view testing)
            $monthDays = array_slice($days, 0, mt_rand(10, 20)); // 10-20 days of work per month
            
            foreach ($monthDays as $day) {
                // Pick a random report template
                $template = $reportTemplates[array_rand($reportTemplates)];
                
                // Create the report
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
                    'updated_by' => null,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
                
                $report = Report::create($reportData);
                
                // Create report details
                foreach ($template['details'] as $detail) {
                    $report->details()->create([
                        'description' => $detail['description'],
                        'status' => $detail['status'],
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }
            
            // Create some reports for previous month (for history)
            $prevMonth = $now->copy()->subMonth();
            $prevMonthDays = array_slice($days, 0, mt_rand(5, 15)); // 5-15 days of work in prev month
            
            foreach ($prevMonthDays as $day) {
                // Pick a random report template
                $template = $reportTemplates[array_rand($reportTemplates)];
                
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
                    'updated_by' => null,
                    'created_at' => $prevMonth->copy()->setDay($day),
                    'updated_at' => $prevMonth->copy()->setDay($day)
                ];
                
                $report = Report::create($reportData);
                
                // Create report details
                foreach ($template['details'] as $detail) {
                    $report->details()->create([
                        'description' => $detail['description'],
                        'status' => $detail['status'],
                        'created_at' => $prevMonth->copy()->setDay($day),
                        'updated_at' => $prevMonth->copy()->setDay($day)
                    ]);
                }
            }
        }
    }
}