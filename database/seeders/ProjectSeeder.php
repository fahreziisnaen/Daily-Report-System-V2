<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Project;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
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
            ],
            [
                'code' => 'PDN-JK-023',
                'name' => 'Data Center Implementation',
                'customer' => 'PT Perusahaan Distribusi Nasional',
                'status' => 'Berjalan',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => 'TEL-SB-050',
                'name' => 'Monitoring System Integration',
                'customer' => 'Telkomsel',
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
} 