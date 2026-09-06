<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $query = User::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $totalUsers = User::count();
        $users = $query->latest()->paginate(15);

        return view('users.index', compact('users', 'totalUsers', 'search'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', Password::defaults()],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'Created User',
            'module' => 'Users',
            'description' => "Admin created new user {$user->name} ({$user->email}).",
            'ip_address' => $request->ip() ?? '127.0.0.1',
        ]);

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', Password::defaults()],
        ]);

        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
        ];

        if (!empty($validated['password'])) {
            $userData['password'] = Hash::make($validated['password']);
        }

        $user->update($userData);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'Updated User',
            'module' => 'Users',
            'description' => "Admin updated user {$user->name} ({$user->email}).",
            'ip_address' => $request->ip() ?? '127.0.0.1',
        ]);

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')->with('error', 'You cannot delete your own account.');
        }

        $userName = $user->name;
        $userEmail = $user->email;
        $user->delete();

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'Deleted User',
            'module' => 'Users',
            'description' => "Admin deleted user {$userName} ({$userEmail}).",
            'ip_address' => $request->ip() ?? '127.0.0.1',
        ]);

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }
}
