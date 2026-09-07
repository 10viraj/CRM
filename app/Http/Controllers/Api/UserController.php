<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of users with search, filtering, sorting, and pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::with(['roles', 'permissions']);

        // Search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filtering by role
        if ($role = $request->input('role')) {
            $query->role($role);
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDirection = strtolower($request->input('sort_direction', 'desc')) === 'asc' ? 'asc' : 'desc';
        $allowedSorts = ['id', 'name', 'email', 'created_at'];

        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->latest();
        }

        if ($request->boolean('all')) {
            $users = $query->get();
            return response()->json([
                'success' => true,
                'data' => UserResource::collection($users),
            ]);
        }

        $perPage = (int) $request->input('per_page', 15);
        $perPage = min(max($perPage, 1), 100);
        $users = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => UserResource::collection($users),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ],
        ]);
    }

    /**
     * Store a newly created user.
     */
    public function store(UserRequest $request): JsonResponse
    {
        $data = $request->validated();
        $roles = $data['roles'] ?? ($request->has('role') ? [$request->input('role')] : []);
        unset($data['roles'], $data['role']);

        $data['password'] = Hash::make($data['password']);
        $user = User::create($data);

        if (!empty($roles)) {
            $user->syncRoles($roles);
        }

        $user->load(['roles', 'permissions']);

        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'data' => new UserResource($user),
        ], 201);
    }

    /**
     * Display the specified user.
     */
    public function show(User $user): JsonResponse
    {
        $user->load(['roles', 'permissions']);

        return response()->json([
            'success' => true,
            'data' => new UserResource($user),
        ]);
    }

    /**
     * Update the specified user.
     */
    public function update(UserRequest $request, User $user): JsonResponse
    {
        $data = $request->validated();
        $roles = $data['roles'] ?? ($request->has('role') ? [$request->input('role')] : null);
        unset($data['roles'], $data['role']);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        if ($roles !== null) {
            $user->syncRoles($roles);
        }

        $user->load(['roles', 'permissions']);

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'data' => new UserResource($user),
        ]);
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user): JsonResponse
    {
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully',
        ]);
    }

    /**
     * Get available roles list.
     */
    public function roles(): JsonResponse
    {
        $roles = Role::with('permissions')->get()->map(function ($r) {
            return [
                'id' => $r->id,
                'name' => $r->name,
                'permissions' => $r->permissions->pluck('name'),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $roles,
        ]);
    }
}
