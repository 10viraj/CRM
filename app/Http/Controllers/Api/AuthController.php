<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\ActivityLog;

class AuthController extends Controller
{
    /**
     * Handle API login and return token.
     */
    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required',
            'password' => 'required',
        ]);

        $loginType = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'id';

        if (!Auth::attempt([$loginType => $request->login, 'password' => $request->password])) {
            ActivityLog::create([
                'user_id' => null,
                'action' => 'Failed API Login',
                'module' => 'Auth',
                'description' => "Failed API login attempt for identifier '{$request->login}'.",
                'ip_address' => $request->ip() ?? '127.0.0.1',
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Invalid login credentials',
            ], 401);
        }

        $user = User::where($loginType, $request->login)->firstOrFail();
        $token = $user->createToken('auth_token')->plainTextToken;

        $roleLabel = ($user->id === 1 || str_contains(strtolower($user->name), 'admin') || str_contains(strtolower($user->email), 'admin')) ? 'Admin' : 'User';
        ActivityLog::create([
            'user_id' => $user->id,
            'action' => "{$roleLabel} API Login",
            'module' => 'Auth',
            'description' => "{$roleLabel} {$user->name} ({$user->email}) logged in via API / Portal.",
            'ip_address' => $request->ip() ?? '127.0.0.1',
        ]);

        return response()->json([
            'success' => true,
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ]);
    }

    /**
     * Handle API registration and return token.
     */
    public function register(Request $request)
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

        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'User Registration (API)',
            'module' => 'Auth',
            'description' => "New user {$user->name} ({$user->email}) registered via API / Portal.",
            'ip_address' => $request->ip() ?? '127.0.0.1',
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ], 201);
    }
}

