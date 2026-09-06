@extends('layouts.app')

@section('title', $title . ' - SmartCRM')

@section('content')
<div class="mb-8 flex justify-between items-center">
    <div>
        <h1 class="text-3xl font-bold text-slate-900 tracking-tight">{{ $title }}</h1>
        <p class="text-slate-500 mt-1 text-sm font-medium">This module is currently under construction.</p>
    </div>
    <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-medium shadow-sm transition">
        + Add {{ $title }}
    </button>
</div>

<div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-12 text-center h-96 flex flex-col items-center justify-center">
    <svg class="w-16 h-16 text-slate-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
    <h2 class="text-xl font-semibold text-slate-700">Coming Soon</h2>
    <p class="text-slate-500 mt-2 max-w-md mx-auto">The {{ $title }} feature is part of the upcoming phases in the development plan. Stay tuned!</p>
    <a href="{{ route('dashboard') }}" class="mt-6 inline-flex items-center text-indigo-600 hover:text-indigo-800 font-medium">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        Back to Dashboard
    </a>
</div>
@endsection
