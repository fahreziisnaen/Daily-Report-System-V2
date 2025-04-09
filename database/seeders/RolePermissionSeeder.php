<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // User Management
            'view-all-users',
            'manage-users',
            'view-department-users',
            
            // Department Management
            'view-all-departments',
            'manage-departments',
            'view-own-department',
            
            // Report Management
            'view-all-reports',
            'manage-all-reports',
            'view-department-reports',
            'manage-department-reports',
            'view-own-reports',
            'manage-own-reports',
            
            // Dashboard Access
            'view-dashboard',
            'view-department-dashboard',
            'view-own-dashboard',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create Super Admin role
        $superAdminRole = Role::create(['name' => 'Super Admin']);
        $superAdminRole->givePermissionTo([
            'view-all-users',
            'manage-users',
            'view-all-departments',
            'manage-departments',
            'view-all-reports',
            'manage-all-reports',
            'view-dashboard'
        ]);

        // Create Admin Divisi role
        $adminDivisiRole = Role::create(['name' => 'Admin Divisi']);
        $adminDivisiRole->givePermissionTo([
            'view-department-users',
            'view-own-department',
            'view-department-reports',
            'manage-department-reports',
            'view-department-dashboard'
        ]);

        // Create Employee role
        $employeeRole = Role::create(['name' => 'Employee']);
        $employeeRole->givePermissionTo([
            'view-own-reports',
            'manage-own-reports',
            'view-own-dashboard'
        ]);
    }
} 