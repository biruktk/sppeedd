<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create Permissions - Simplified based on sidebar sections
        $permissions = [
            'manage_dashboard',
            'manage_job_order',
            'manage_work_order',
            'manage_inventory',
            'manage_payment',
            'manage_sales',
            'manage_purchase',
            'manage_proforma',
            'manage_setting',
            'manage_check_list',
            'manage_staff_management',
            'manage_income',
            'manage_expense',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create Roles and assign created permissions

        // Super Admin - has everything
        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin']);
        $superAdminRole->givePermissionTo(Permission::all());

        // Admin - has most operational permissions
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        $adminRole->givePermissionTo([
            'manage_dashboard',
            'manage_job_order',
            'manage_work_order',
            'manage_inventory',
            'manage_payment',
            'manage_sales',
            'manage_purchase',
            'manage_proforma',
            'manage_staff_management',
            'manage_income',
            'manage_expense',
        ]);

        // Employee - limited operational access
        $employeeRole = Role::firstOrCreate(['name' => 'Employee']);
        $employeeRole->givePermissionTo([
            'manage_dashboard',
            'manage_job_order',
            'manage_work_order',
            'manage_inventory',
        ]);

        // Create a Super Admin User
        $admin = Admin::firstOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Super Admin',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
            ]
        );
        $admin->assignRole($superAdminRole);
    }
}
