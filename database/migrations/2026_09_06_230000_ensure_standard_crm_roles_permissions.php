<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. All system permissions
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
            // Contacts
            'view-contacts',
            'create-contacts',
            'edit-contacts',
            'delete-contacts',
            // Companies
            'view-companies',
            'create-companies',
            'edit-companies',
            'delete-companies',
            // Tasks & Calendar
            'manage-tasks',
            'manage-calendar',
            // Quotations & Invoices
            'manage-quotations',
            'manage-invoices',
            // Administration & Settings
            'manage-settings',
            'view-audit-logs',
            'manage-custom-fields',
            'manage-users',
            'manage-roles',
            'manage-pipelines',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // 2. Standard Roles Setup
        // Admin
        $admin = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $admin->syncPermissions(Permission::all());

        // Manager
        $manager = Role::firstOrCreate(['name' => 'Manager', 'guard_name' => 'web']);
        $manager->syncPermissions([
            'view-leads', 'create-leads', 'edit-leads', 'delete-leads',
            'view-deals', 'create-deals', 'edit-deals', 'delete-deals',
            'view-contacts', 'create-contacts', 'edit-contacts', 'delete-contacts',
            'view-companies', 'create-companies', 'edit-companies', 'delete-companies',
            'manage-tasks', 'manage-calendar',
            'manage-quotations', 'manage-invoices',
            'view-audit-logs',
            'manage-custom-fields',
            'manage-pipelines',
        ]);

        // Sales Representative
        $salesRep = Role::firstOrCreate(['name' => 'Sales Representative', 'guard_name' => 'web']);
        $salesRep->syncPermissions([
            'view-leads', 'create-leads', 'edit-leads',
            'view-deals', 'create-deals', 'edit-deals',
            'view-contacts', 'create-contacts', 'edit-contacts',
            'view-companies', 'create-companies', 'edit-companies',
            'manage-tasks', 'manage-calendar',
            'manage-quotations',
        ]);

        // Viewer (Read-only)
        $viewer = Role::firstOrCreate(['name' => 'Viewer', 'guard_name' => 'web']);
        $viewer->syncPermissions([
            'view-leads',
            'view-deals',
            'view-contacts',
            'view-companies',
            'view-audit-logs',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Keep permissions intact
    }
};
