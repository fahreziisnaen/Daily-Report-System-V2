<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Project;
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
            $this->createProjects();
            
            // 5. Create sample reports
            $this->createReports();
            
        } catch (\Exception $e) {
            // Log any errors during seeding
            \Log::error('Error during database seeding: ' . $e->getMessage());
            echo 'Error during database seeding: ' . $e->getMessage() . "\n";
        }
    }
    
    /**
     * Create sample projects
     */
    private function createProjects(): void
    {
        $projects = [
            [
                'code' => 'KPI-JK-001',
                'name' => 'Sistem Network RU IV Cilacap & RU VI Balongan',
                'customer' => 'PT Kilang Pertamina Internasional',
                'status' => 'Berjalan',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => 'PTM-JK-037',
                'name' => 'LAN & Data Center SCU',
                'customer' => 'Persero',
                'status' => 'Berjalan',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => 'IPI-SB-001',
                'name' => 'Internal',
                'customer' => 'IPI',
                'status' => 'Berjalan',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        foreach ($projects as $project) {
            Project::updateOrCreate(
                ['code' => $project['code']],
                $project
            );
        }
    }
    
    /**
     * Create sample reports
     */
    private function createReports(): void
    {
        // Get some users for reports
        $employee = User::role('Employee')->first();
        $adminDivisi = User::role('Admin Divisi')->first();
        
        if (!$employee || !$adminDivisi) {
            echo "Warning: Could not find required users for report seeding.\n";
            return;
        }

        // Create Reports and Report Details
        $reports = [
            [
                'user_id' => $adminDivisi->id,
                'report_date' => now()->subDays(rand(1, 7))->format('Y-m-d'),
                'project_code' => 'PTM-JK-037',
                'location' => 'Surabaya',
                'start_time' => '17:00:00',
                'end_time' => '04:00:00',
                'is_overnight' => 1,
                'is_overtime' => 1,
                'is_shift' => 0,
                'work_day_type' => 'Hari Kerja',
                'updated_by' => $adminDivisi->id,
                'details' => [
                    [
                        'description' => 'Meeting Koordinasi dengan Persero',
                        'status' => 'Selesai'
                    ]
                ]
            ],
            [
                'user_id' => $employee->id,
                'report_date' => now()->subDays(rand(8, 14))->format('Y-m-d'),
                'project_code' => 'IPI-SB-001',
                'location' => 'Surabaya',
                'start_time' => '08:00:00',
                'end_time' => '20:00:00',
                'is_overnight' => 0,
                'is_overtime' => 1,
                'is_shift' => 0,
                'work_day_type' => 'Hari Kerja',
                'updated_by' => null,
                'details' => [
                    [
                        'description' => 'Implementasi sistem monitoring',
                        'status' => 'Selesai'
                    ]
                ]
            ],
            [
                'user_id' => $employee->id,
                'report_date' => now()->subDays(rand(15, 21))->format('Y-m-d'),
                'project_code' => 'PTM-JK-037',
                'location' => 'Jakarta',
                'start_time' => '13:00:00',
                'end_time' => '20:00:00',
                'is_overnight' => 0,
                'is_overtime' => 0,
                'is_shift' => 0,
                'work_day_type' => 'Hari Kerja',
                'updated_by' => null,
                'details' => [
                    [
                        'description' => 'Setup dan konfigurasi server',
                        'status' => 'Selesai'
                    ]
                ]
            ]
        ];

        foreach ($reports as $reportData) {
            $details = $reportData['details'];
            unset($reportData['details']);
            
            $reportData['created_at'] = now();
            $reportData['updated_at'] = now();
            
            $report = Report::create($reportData);
            
            foreach ($details as $detail) {
                $detail['created_at'] = now();
                $detail['updated_at'] = now();
                $report->details()->create($detail);
            }
        }
    }
} 