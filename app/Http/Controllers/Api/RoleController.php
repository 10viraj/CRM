<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleController extends Controller
{
    /**
     * Display a listing of roles with their assigned permissions.
     */
    public function index(): JsonResponse
    {
        $roles = Role::with('permissions')->get()->map(function ($r) {
            return [
                'id' => $r->id,
                'name' => $r->name,
                'users_count' => $r->users()->count(),
                'permissions' => $r->permissions->pluck('name'),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $roles,
        ]);
    }

    /**
     * Store a newly created role.
     */
    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();
        if ($user && !$user->hasPermissionTo('manage-roles') && !$user->hasRole('Admin')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $role = Role::create(['name' => $validated['name'], 'guard_name' => 'web']);

        if (!empty($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Role created successfully',
            'data' => [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->permissions->pluck('name'),
            ],
        ], 201);
    }

    /**
     * Update the specified role and its permissions.
     */
    public function update(Request $request, Role $role): JsonResponse
    {
        $user = Auth::user();
        if ($user && !$user->hasPermissionTo('manage-roles') && !$user->hasRole('Admin')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Prevent renaming core Admin role
        if ($role->name === 'Admin' && $validated['name'] !== 'Admin') {
            return response()->json(['success' => false, 'message' => 'Cannot rename core Admin role.'], 422);
        }

        $role->update(['name' => $validated['name']]);

        if (isset($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Role updated successfully',
            'data' => [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->permissions->pluck('name'),
            ],
        ]);
    }

    /**
     * Remove the specified role.
     */
    public function destroy(Role $role): JsonResponse
    {
        $user = Auth::user();
        if ($user && !$user->hasPermissionTo('manage-roles') && !$user->hasRole('Admin')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }

        if (in_array($role->name, ['Admin', 'Manager', 'Sales Representative', 'Viewer'])) {
            return response()->json(['success' => false, 'message' => 'Cannot delete system default role.'], 422);
        }

        if ($role->users()->count() > 0) {
            return response()->json(['success' => false, 'message' => 'Cannot delete role with assigned users.'], 422);
        }

        $role->delete();

        return response()->json([
            'success' => true,
            'message' => 'Role deleted successfully',
        ]);
    }

    /**
     * Get all available permissions grouped by module.
     */
    public function permissions(): JsonResponse
    {
        $permissions = Permission::all()->map(function ($p) {
            $parts = explode('-', $p->name);
            $module = count($parts) > 1 ? ucfirst($parts[1]) : 'General';
            return [
                'id' => $p->id,
                'name' => $p->name,
                'label' => ucwords(str_replace('-', ' ', $p->name)),
                'module' => $module,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $permissions,
            'grouped' => $permissions->groupBy('module'),
        ]);
    }
}
