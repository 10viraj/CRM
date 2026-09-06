@extends('layouts.app')

@section('title', 'Login - SmartCRM')

@section('content')
<div class="w-full max-w-md bg-white rounded-2xl shadow-xl border border-slate-100 p-8">
    <div class="text-center mb-8">
        <h1 class="text-3xl font-extrabold text-indigo-600 mb-2">SmartCRM</h1>
        <p class="text-slate-500">Sign in to your account</p>
    </div>

    @if ($errors->any())
        <div class="bg-red-50 text-red-600 p-4 rounded-lg mb-6 text-sm border border-red-100">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-6">
        @csrf
        <div>
            <label for="login" class="block text-sm font-medium text-slate-700 mb-1">Email Address or User ID</label>
            <input type="text" id="login" name="login" value="{{ old('login') }}" required autofocus
                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition shadow-sm">
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-slate-700 mb-1">Password</label>
            <input type="password" id="password" name="password" required
                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition shadow-sm">
        </div>

        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <input id="remember" name="remember" type="checkbox"
                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-slate-300 rounded cursor-pointer">
                <label for="remember" class="ml-2 block text-sm text-slate-600 cursor-pointer">
                    Remember me
                </label>
            </div>
        </div>

        <button type="submit"
            class="w-full bg-indigo-600 text-white font-semibold py-2.5 rounded-lg hover:bg-indigo-700 focus:ring-4 focus:ring-indigo-200 transition-all shadow-md">
            Sign In
        </button>
    </form>

    <div class="mt-8 text-center text-sm text-slate-500">
        Don't have an account? 
        <a href="{{ route('register') }}" class="text-indigo-600 font-medium hover:underline">Create one</a>
    </div>
</div>
@endsection
