@extends('layouts.app')

@section('title', 'Deal Details - SmartCRM')

@section('content')
<div class="mb-6 flex justify-between items-start">
    <div>
        <a href="{{ route('deals.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800 flex items-center mb-3">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Pipeline
        </a>
        <h1 class="text-3xl font-bold text-slate-900 tracking-tight">{{ $deal->name }}</h1>
        <p class="text-slate-500 font-medium mt-1">Associated with <a href="{{ route('leads.show', $deal->lead) }}" class="text-indigo-600 hover:underline">{{ $deal->lead->company ?? ($deal->lead->first_name . ' ' . $deal->lead->last_name) }}</a></p>
    </div>
    <div class="flex space-x-3 mt-8">
        <a href="#" class="bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 px-4 py-2 rounded-lg font-medium shadow-sm transition">
            Edit Deal
        </a>
        <button class="bg-red-50 text-red-600 border border-red-200 hover:bg-red-100 px-4 py-2 rounded-lg font-medium shadow-sm transition">
            Delete
        </button>
    </div>
</div>

<!-- Visual Sales Progress -->
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-8 mb-8">
    <h2 class="text-sm font-semibold text-slate-900 uppercase tracking-wider mb-6">Pipeline Stage</h2>
    
    <div class="relative">
        <div class="overflow-hidden h-2 mb-4 text-xs flex rounded-full bg-slate-100">
            @php
                $totalStages = $stages->count();
                $currentOrder = $deal->stage->order_index;
                $percentage = ($currentOrder / $totalStages) * 100;
            @endphp
            <div style="width: {{ $percentage }}%" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-{{ $deal->stage->color }}-500 transition-all duration-500"></div>
        </div>
        
        <div class="flex justify-between w-full text-xs font-medium relative top-1">
            @foreach($stages as $stage)
                @php
                    $isPast = $stage->order_index < $currentOrder;
                    $isCurrent = $stage->order_index == $currentOrder;
                    $textColor = $isCurrent ? 'text-'.$stage->color.'-700 font-bold' : ($isPast ? 'text-slate-600' : 'text-slate-400');
                    $dotColor = $isCurrent ? 'bg-'.$stage->color.'-500 border-4 border-'.$stage->color.'-100' : ($isPast ? 'bg-slate-400' : 'bg-slate-200');
                @endphp
                <div class="flex flex-col items-center relative -top-7 w-1/6">
                    <div class="w-4 h-4 rounded-full {{ $dotColor }} mb-3 relative z-10 transition-all duration-300"></div>
                    <span class="{{ $textColor }}">{{ $stage->name }}</span>
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <!-- Key Information -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
        <h2 class="text-sm font-semibold text-slate-900 uppercase tracking-wider mb-4 border-b border-slate-100 pb-2">Deal Details</h2>
        <div class="space-y-5">
            <div class="flex items-center justify-between">
                <span class="text-sm text-slate-500 font-medium">Deal Value</span>
                <span class="text-xl font-bold text-slate-900">${{ number_format($deal->value, 2) }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-sm text-slate-500 font-medium">Expected Close Date</span>
                <span class="text-sm font-semibold text-slate-800">{{ $deal->close_date ? $deal->close_date->format('F j, Y') : 'Not set' }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-sm text-slate-500 font-medium">Created Date</span>
                <span class="text-sm text-slate-800">{{ $deal->created_at->format('M d, Y') }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-sm text-slate-500 font-medium">Owner</span>
                <div class="flex items-center">
                    <div class="w-6 h-6 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-xs font-bold mr-2">
                        {{ substr($deal->owner->name ?? 'U', 0, 1) }}
                    </div>
                    <span class="text-sm font-medium text-slate-800">{{ $deal->owner->name ?? 'Unassigned' }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
