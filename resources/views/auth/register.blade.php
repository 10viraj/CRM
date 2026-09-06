@extends('layouts.app')

@section('title', 'Register - SmartCRM')

@section('content')
<div class="w-full max-w-md bg-white rounded-2xl shadow-xl border border-slate-100 p-8">
    <div class="text-center mb-8">
        <h1 class="text-3xl font-extrabold text-indigo-600 mb-2">SmartCRM</h1>
        <p class="text-slate-500">Create a new account</p>
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

    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf
        <div>
            <label for="name" class="block text-sm font-medium text-slate-700 mb-1">Full Name</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus
                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition shadow-sm">
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-slate-700 mb-1">Email Address</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required
                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition shadow-sm">
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-slate-700 mb-1">Password</label>
            <input type="password" id="password" name="password" required
                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition shadow-sm">
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-slate-700 mb-1">Confirm Password</label>
            <input type="password" id="password_confirmation" name="password_confirmation" required
                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition shadow-sm">
        </div>

        <button type="submit"
            class="w-full bg-indigo-600 text-white font-semibold py-2.5 rounded-lg hover:bg-indigo-700 focus:ring-4 focus:ring-indigo-200 transition-all shadow-md mt-4">
            Create Account
        </button>
    </form>

    <div class="mt-8 text-center text-sm text-slate-500">
        Already have an account? 
        <a href="{{ route('login') }}" class="text-indigo-600 font-medium hover:underline">Sign in</a>
    </div>
</div>
@endsection
