@extends('layouts.app')

@section('title', 'Users Management - SmartCRM')

@section('content')
<div x-data="{ addModalOpen: false, editModalOpen: false, editUser: {} }">
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-3">
                <h1 class="text-3xl font-bold text-slate-100 tracking-tight">Users Management</h1>
                <span class="px-3 py-1 bg-indigo-500/20 text-indigo-400 border border-indigo-500/30 rounded-full text-xs font-bold">
                    {{ $totalUsers }} Total Users
                </span>
            </div>
            <p class="text-slate-400 mt-1 text-sm font-medium">Manage registered system users, administrators, and permissions.</p>
        </div>
        <div class="flex items-center space-x-3">
            <button @click="addModalOpen = true" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-medium shadow-sm transition flex items-center gap-2 text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>Add User</span>
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 bg-emerald-500/10 text-emerald-400 p-4 rounded-xl border border-emerald-500/20 text-sm flex items-center justify-between">
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 bg-red-500/10 text-red-400 p-4 rounded-xl border border-red-500/20 text-sm flex items-center justify-between">
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 bg-red-500/10 text-red-400 p-4 rounded-xl border border-red-500/20 text-sm">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Search and Filters Bar -->
    <div class="bg-slate-900 p-4 rounded-xl border border-slate-800 mb-6 flex justify-between items-center">
        <form action="{{ route('users.index') }}" method="GET" class="flex items-center space-x-3 w-full max-w-md">
            <div class="relative w-full">
                <svg class="w-4 h-4 absolute left-3 top-1/2 transform -translate-y-1/2 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                <input type="text" name="search" value="{{ $search }}" placeholder="Search by name or email..." class="w-full bg-slate-950 border border-slate-800 rounded-lg pl-9 pr-4 py-2 text-xs focus:border-indigo-500 outline-none text-slate-200">
            </div>
            <button type="submit" class="bg-slate-800 hover:bg-slate-700 text-slate-200 px-3 py-2 rounded-lg text-xs font-medium transition">Search</button>
            @if($search)
                <a href="{{ route('users.index') }}" class="text-xs text-slate-400 hover:text-slate-200 transition">Clear</a>
            @endif
        </form>
    </div>

    <!-- Users Table -->
    <div class="bg-slate-900 rounded-xl shadow-sm border border-slate-800 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-900/50 border-b border-slate-800 text-slate-400 font-semibold uppercase tracking-wider">
                        <th class="p-4">User</th>
                        <th class="p-4">Email</th>
                        <th class="p-4">Role / Access</th>
                        <th class="p-4">Registered Date</th>
                        <th class="p-4 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/50 text-slate-300">
                    @forelse($users as $user)
                        <tr class="hover:bg-slate-800/50 transition">
                            <td class="p-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 rounded-full bg-indigo-600 text-white flex items-center justify-center font-bold text-xs">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="font-semibold text-slate-100 text-sm">{{ $user->name }}</p>
                                        <p class="text-slate-500 text-[11px]">ID: #{{ str_pad($user->id, 4, '0', STR_PAD_LEFT) }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="p-4 text-slate-300 font-medium">
                                {{ $user->email }}
                            </td>
                            <td class="p-4">
                                @php
                                    $isAdmin = $user->id === 1 || str_contains(strtolower($user->name), 'admin') || str_contains(strtolower($user->email), 'admin');
                                @endphp
                                @if($isAdmin)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-purple-500/10 text-purple-400 border border-purple-500/20">
                                        Administrator
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-500/10 text-blue-400 border border-blue-500/20">
                                        Standard User
                                    </span>
                                @endif
                            </td>
                            <td class="p-4 text-slate-400">
                                {{ $user->created_at ? $user->created_at->format('M d, Y') : 'N/A' }}
                                <div class="text-[10px] text-slate-500">{{ $user->created_at ? $user->created_at->format('h:i A') : '' }}</div>
                            </td>
                            <td class="p-4 text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    <button 
                                        @click="editUser = { id: {{ $user->id }}, name: '{{ addslashes($user->name) }}', email: '{{ addslashes($user->email) }}' }; editModalOpen = true;"
                                        class="px-2.5 py-1 bg-slate-800 hover:bg-slate-700 text-indigo-400 rounded text-xs font-medium transition"
                                    >
                                        Edit
                                    </button>
                                    @if($user->id !== auth()->id())
                                        <form method="POST" action="{{ route('users.destroy', $user->id) }}" onsubmit="return confirm('Are you sure you want to delete this user?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-2.5 py-1 bg-red-500/10 hover:bg-red-500/20 text-red-400 rounded text-xs font-medium transition">
                                                Delete
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-12 text-center text-slate-500">
                                <p class="text-sm font-medium text-slate-400">No users found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
            <div class="p-4 border-t border-slate-800 bg-slate-900/50">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    <!-- Add User Modal -->
    <div x-show="addModalOpen" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-xs" style="display: none;">
        <div @click.away="addModalOpen = false" class="bg-slate-900 rounded-2xl border border-slate-800 p-6 max-w-md w-full shadow-2xl">
            <div class="flex justify-between items-center pb-3 border-b border-slate-800 mb-4">
                <h3 class="text-base font-bold text-slate-100">+ Add New User</h3>
                <button @click="addModalOpen = false" class="text-slate-500 hover:text-slate-300 text-lg font-bold">&times;</button>
            </div>
            <form action="{{ route('users.store') }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block text-slate-400 font-semibold mb-1 uppercase tracking-wider text-[10px]">Full Name *</label>
                    <input type="text" name="name" required placeholder="e.g. John Doe" class="w-full bg-slate-950 border border-slate-800 rounded-lg p-2.5 text-slate-200 outline-none focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-slate-400 font-semibold mb-1 uppercase tracking-wider text-[10px]">Email Address *</label>
                    <input type="email" name="email" required placeholder="e.g. user@company.com" class="w-full bg-slate-950 border border-slate-800 rounded-lg p-2.5 text-slate-200 outline-none focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-slate-400 font-semibold mb-1 uppercase tracking-wider text-[10px]">Password *</label>
                    <input type="password" name="password" required placeholder="••••••••" class="w-full bg-slate-950 border border-slate-800 rounded-lg p-2.5 text-slate-200 outline-none focus:border-indigo-500">
                </div>
                <div class="flex justify-end space-x-3 pt-3">
                    <button type="button" @click="addModalOpen = false" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg transition font-medium">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition font-medium shadow-sm">Create User</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div x-show="editModalOpen" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-xs" style="display: none;">
        <div @click.away="editModalOpen = false" class="bg-slate-900 rounded-2xl border border-slate-800 p-6 max-w-md w-full shadow-2xl">
            <div class="flex justify-between items-center pb-3 border-b border-slate-800 mb-4">
                <h3 class="text-base font-bold text-slate-100">Edit User</h3>
                <button @click="editModalOpen = false" class="text-slate-500 hover:text-slate-300 text-lg font-bold">&times;</button>
            </div>
            <form :action="'/users/' + editUser.id" method="POST" class="space-y-4 text-xs">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-slate-400 font-semibold mb-1 uppercase tracking-wider text-[10px]">Full Name *</label>
                    <input type="text" name="name" x-model="editUser.name" required class="w-full bg-slate-950 border border-slate-800 rounded-lg p-2.5 text-slate-200 outline-none focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-slate-400 font-semibold mb-1 uppercase tracking-wider text-[10px]">Email Address *</label>
                    <input type="email" name="email" x-model="editUser.email" required class="w-full bg-slate-950 border border-slate-800 rounded-lg p-2.5 text-slate-200 outline-none focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-slate-400 font-semibold mb-1 uppercase tracking-wider text-[10px]">Password (Leave blank to keep current)</label>
                    <input type="password" name="password" placeholder="••••••••" class="w-full bg-slate-950 border border-slate-800 rounded-lg p-2.5 text-slate-200 outline-none focus:border-indigo-500">
                </div>
                <div class="flex justify-end space-x-3 pt-3">
                    <button type="button" @click="editModalOpen = false" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg transition font-medium">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition font-medium shadow-sm">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
