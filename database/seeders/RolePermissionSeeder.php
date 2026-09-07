<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Permissions list
        $permissions = [
            // Leads
            'view-leads',
            'create-leads',
            'edit-leads',
            'delete-leads',
            // Deals
            'view-deals',
            'create-deals',
            'edit-deals',
            'delete-deals',
            // Companies
            'view-companies',
            'create-companies',
            'edit-companies',
            'delete-companies',
            // Contacts
            'view-contacts',
            'create-contacts',
            'edit-contacts',
            'delete-contacts',
            // Tasks & Calendar
            'manage-tasks',
            'manage-calendar',
            // Quotations & Invoices
            'manage-quotations',
            'manage-invoices',
            // Settings & Audit Logs & Custom Fields
            'manage-settings',
            'view-audit-logs',
            'manage-custom-fields',
            'manage-users',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Roles
        $adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $adminRole->syncPermissions(Permission::all());

        $salesManagerRole = Role::firstOrCreate(['name' => 'Sales Manager', 'guard_name' => 'web']);
        $salesManagerRole->syncPermissions([
            'view-leads', 'create-leads', 'edit-leads', 'delete-leads',
            'view-deals', 'create-deals', 'edit-deals', 'delete-deals',
            'view-companies', 'create-companies', 'edit-companies',
            'view-contacts', 'create-contacts', 'edit-contacts',
            'manage-tasks', 'manage-calendar',
            'manage-quotations', 'manage-invoices',
            'view-audit-logs',
        ]);

        $salesRepRole = Role::firstOrCreate(['name' => 'Sales Representative', 'guard_name' => 'web']);
        $salesRepRole->syncPermissions([
            'view-leads', 'create-leads', 'edit-leads',
            'view-deals', 'create-deals', 'edit-deals',
            'view-companies', 'create-companies', 'edit-companies',
            'view-contacts', 'create-contacts', 'edit-contacts',
            'manage-tasks', 'manage-calendar',
            'manage-quotations',
        ]);

        $supportRole = Role::firstOrCreate(['name' => 'Support Agent', 'guard_name' => 'web']);
        $supportRole->syncPermissions([
            'view-companies', 'view-contacts', 'manage-tasks', 'manage-calendar',
        ]);
    }
}
