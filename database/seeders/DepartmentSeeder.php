<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            [
                'name' => 'Project Engineering',
                'code' => 'PE',
                'description' => 'Department yang menangani proyek-proyek engineering',
            ],
            [
                'name' => 'Production',
                'code' => 'PRD',
                'description' => 'Department yang menangani produksi',
            ],
            [
                'name' => 'Quality Control',
                'code' => 'QC',
                'description' => 'Department yang menangani kontrol kualitas',
            ],
            [
                'name' => 'Maintenance',
                'code' => 'MNT',
                'description' => 'Department yang menangani perawatan mesin dan fasilitas',
            ],
        ];

        foreach ($departments as $department) {
            Department::updateOrCreate(
                ['code' => $department['code']],
                $department
            );
        }
    }
} 