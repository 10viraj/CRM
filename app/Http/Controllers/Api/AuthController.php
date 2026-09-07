<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Handle API login and return token.
     */
    public function login(Request $request): JsonResponse
    {
        $loginInput = $request->input('login') ?: $request->input('email');
        $request->merge(['login' => $loginInput]);

        $request->validate([
            'login' => 'required',
            'password' => 'required',
        ]);

        $loginType = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'id';

        if (!Auth::attempt([$loginType => $loginInput, 'password' => $request->password])) {
            try {
                ActivityLog::create([
                    'user_id' => null,
                    'action' => 'Failed API Login',
                    'module' => 'Auth',
                    'description' => "Failed API login attempt for identifier '{$loginInput}'.",
                    'ip_address' => $request->ip() ?? '127.0.0.1',
                ]);
            } catch (\Throwable $e) {}

            return response()->json([
                'success' => false,
                'message' => 'Invalid login credentials',
            ], 401);
        }

        $user = User::where($loginType, $loginInput)->firstOrFail();
        $token = $user->createToken('auth_token')->plainTextToken;

        try {
            $roleLabel = ($user->id === 1 || str_contains(strtolower($user->name), 'admin') || str_contains(strtolower($user->email), 'admin')) ? 'Admin' : 'User';
            ActivityLog::create([
                'user_id' => $user->id,
                'action' => "{$roleLabel} API Login",
                'module' => 'Auth',
                'description' => "{$roleLabel} {$user->name} ({$user->email}) logged in via API / Portal.",
                'ip_address' => $request->ip() ?? '127.0.0.1',
            ]);
        } catch (\Throwable $e) {}

        return response()->json([
            'success' => true,
            'token' => $token,
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => new UserResource($user),
        ]);
    }

    /**
     * Handle API registration and return token.
     */
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        try {
            ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'User Registration (API)',
                'module' => 'Auth',
                'description' => "New user {$user->name} ({$user->email}) registered via API / Portal.",
                'ip_address' => $request->ip() ?? '127.0.0.1',
            ]);
        } catch (\Throwable $e) {}

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'token' => $token,
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => new UserResource($user),
        ], 201);
    }

    /**
     * Logout current user (revoke current token).
     */
    public function logout(Request $request): JsonResponse
    {
        if ($request->user() && $request->user()->currentAccessToken()) {
            $request->user()->currentAccessToken()->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }
}
