@extends('layouts.app')

@section('title', 'Lead Profile - SmartCRM')

@section('content')
<div class="mb-6 flex justify-between items-start">
    <div>
        <a href="{{ route('leads.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800 flex items-center mb-3">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Leads
        </a>
        <div class="flex items-center space-x-4">
            <div class="w-16 h-16 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center text-2xl font-bold">
                {{ substr($lead->first_name, 0, 1) }}{{ substr($lead->last_name, 0, 1) }}
            </div>
            <div>
                <h1 class="text-3xl font-bold text-slate-900 tracking-tight">{{ $lead->first_name }} {{ $lead->last_name }}</h1>
                <p class="text-slate-500 font-medium mt-1">{{ $lead->company ?? 'Unknown Company' }}</p>
            </div>
        </div>
    </div>
    <div class="flex space-x-3 mt-8">
        <a href="#" class="bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 px-4 py-2 rounded-lg font-medium shadow-sm transition">
            Edit Lead
        </a>
        <button class="bg-red-50 text-red-600 border border-red-200 hover:bg-red-100 px-4 py-2 rounded-lg font-medium shadow-sm transition">
            Delete
        </button>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Left Column: Details -->
    <div class="lg:col-span-1 space-y-6">
        <!-- Key Metrics Card -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            <h2 class="text-sm font-semibold text-slate-900 uppercase tracking-wider mb-4 border-b border-slate-100 pb-2">Status & Score</h2>
            <div class="flex items-center justify-between mb-4">
                <span class="text-sm text-slate-500">Current Status</span>
                @php
                    $color = $lead->status->color ?? 'gray';
                    $bg = "bg-{$color}-100";
                    $text = "text-{$color}-700";
                @endphp
                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold {{ $bg }} {{ $text }}">
                    {{ $lead->status->name ?? 'Unknown' }}
                </span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-sm text-slate-500">Lead Score</span>
                <span class="text-lg font-bold {{ $lead->score >= 80 ? 'text-green-600' : ($lead->score >= 50 ? 'text-orange-500' : 'text-red-500') }}">
                    {{ $lead->score }}
                </span>
            </div>
        </div>

        <!-- Contact Information -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            <h2 class="text-sm font-semibold text-slate-900 uppercase tracking-wider mb-4 border-b border-slate-100 pb-2">Contact Info</h2>
            <div class="space-y-4">
                <div>
                    <span class="text-xs text-slate-400 font-medium block">Email Address</span>
                    <a href="mailto:{{ $lead->email }}" class="text-sm text-indigo-600 hover:underline font-medium">{{ $lead->email }}</a>
                </div>
                <div>
                    <span class="text-xs text-slate-400 font-medium block">Phone Number</span>
                    <a href="tel:{{ $lead->phone }}" class="text-sm text-slate-800 font-medium">{{ $lead->phone ?? 'Not provided' }}</a>
                </div>
                <div>
                    <span class="text-xs text-slate-400 font-medium block">Website</span>
                    <span class="text-sm text-slate-800 font-medium">Not provided</span>
                </div>
            </div>
        </div>

        <!-- Lead Information -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            <h2 class="text-sm font-semibold text-slate-900 uppercase tracking-wider mb-4 border-b border-slate-100 pb-2">Lead Details</h2>
            <div class="space-y-4">
                <div>
                    <span class="text-xs text-slate-400 font-medium block">Source</span>
                    <span class="text-sm text-slate-800 font-medium">{{ $lead->source->name ?? 'Direct' }}</span>
                </div>
                <div>
                    <span class="text-xs text-slate-400 font-medium block">Lead Owner</span>
                    <div class="flex items-center mt-1">
                        <div class="w-5 h-5 rounded-full bg-slate-200 text-slate-600 flex items-center justify-center text-[10px] font-bold mr-2">
                            {{ substr($lead->owner->name ?? 'U', 0, 1) }}
                        </div>
                        <span class="text-sm text-slate-800 font-medium">{{ $lead->owner->name ?? 'Unassigned' }}</span>
                    </div>
                </div>
                <div>
                    <span class="text-xs text-slate-400 font-medium block">Created Date</span>
                    <span class="text-sm text-slate-800 font-medium">{{ $lead->created_at->format('F j, Y, g:i a') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Activity Timeline -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden h-full">
            <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                <h2 class="text-lg font-semibold text-slate-900">Activity Timeline</h2>
                <button class="text-sm font-medium text-indigo-600 hover:text-indigo-800">+ Log Activity</button>
            </div>
            
            <div class="p-8">
                @if($activities->count() > 0)
                    <div class="relative border-l-2 border-slate-200 ml-4 space-y-8">
                        @foreach($activities as $activity)
                            <div class="relative pl-8">
                                <!-- Timeline Dot -->
                                @php
                                    $iconBg = 'bg-slate-100 text-slate-500';
                                    if($activity->type == 'email') $iconBg = 'bg-blue-100 text-blue-500';
                                    if($activity->type == 'call') $iconBg = 'bg-green-100 text-green-500';
                                    if($activity->type == 'status_change') $iconBg = 'bg-purple-100 text-purple-500';
                                @endphp
                                <div class="absolute -left-3.5 top-0 w-7 h-7 rounded-full {{ $iconBg }} border-4 border-white flex items-center justify-center">
                                    @if($activity->type == 'email')
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                    @elseif($activity->type == 'call')
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                    @else
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                    @endif
                                </div>
                                
                                <!-- Content -->
                                <div>
                                    <p class="text-sm text-slate-800">{{ $activity->description }}</p>
                                    <div class="mt-1 flex items-center space-x-2 text-xs text-slate-500">
                                        <span class="font-medium text-slate-700">{{ $activity->user->name ?? 'System' }}</span>
                                        <span>•</span>
                                        <span>{{ $activity->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-10">
                        <p class="text-slate-500">No activity recorded yet.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
