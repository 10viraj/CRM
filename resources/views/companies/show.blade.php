@extends('layouts.app')

@section('title', $company->name . ' - Company Profile - SmartCRM')

@section('content')
<div class="space-y-6">
    <!-- Breadcrumb & Back Link -->
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-2 text-xs text-slate-400">
            <a href="{{ route('companies.index') }}" class="hover:text-indigo-400 transition font-medium">Companies</a>
            <span>/</span>
            <span class="text-slate-200 font-semibold">{{ $company->name }}</span>
        </div>
        
        <div class="flex items-center space-x-2.5">
            <a href="{{ route('companies.edit', $company) }}" class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-lg bg-dark-900 border border-slate-800 text-xs font-semibold text-slate-300 hover:text-white hover:border-slate-700 transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                <span>Edit Company</span>
            </a>
            <form action="{{ route('companies.destroy', $company) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this company?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-lg bg-rose-500/10 border border-rose-500/20 text-xs font-semibold text-rose-400 hover:bg-rose-500/20 transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    <span>Delete</span>
                </button>
            </form>
        </div>
    </div>

    @if(session('success'))
    <div class="p-3.5 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-medium flex items-center justify-between">
        <div class="flex items-center space-x-2">
            <span>✓</span>
            <span>{{ session('success') }}</span>
        </div>
    </div>
    @endif

    <!-- Company Header Banner -->
    <div class="bg-dark-900 p-5 rounded-xl border border-slate-800 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="flex items-center space-x-4">
            <div class="w-14 h-14 rounded-xl bg-indigo-600/10 text-indigo-400 font-bold text-xl flex items-center justify-center border border-indigo-500/20 shadow-sm">
                {{ strtoupper(substr($company->name, 0, 1)) }}
            </div>
            <div>
                <div class="flex items-center space-x-3">
                    <h1 class="text-xl font-bold text-white tracking-tight">{{ $company->name }}</h1>
                    @if($company->industry)
                    <span class="badge-tag text-xs font-semibold">
                        {{ $company->industry }}
                    </span>
                    @endif
                </div>
                <div class="flex flex-wrap items-center gap-3.5 mt-1.5 text-xs text-slate-400">
                    @if($company->city || $company->country)
                    <span class="flex items-center space-x-1">
                        <span>📍</span>
                        <span>{{ collect([$company->city, $company->state, $company->country])->filter()->implode(', ') }}</span>
                    </span>
                    @endif
                    @if($company->email)
                    <span class="flex items-center space-x-1">
                        <span>✉️</span>
                        <a href="mailto:{{ $company->email }}" class="text-indigo-400 hover:underline">{{ $company->email }}</a>
                    </span>
                    @endif
                    @if($company->phone)
                    <span class="flex items-center space-x-1">
                        <span>📞</span>
                        <span>{{ $company->phone }}</span>
                    </span>
                    @endif
                    @if($company->website)
                    <span class="flex items-center space-x-1">
                        <span>🌐</span>
                        <a href="{{ str_starts_with($company->website, 'http') ? $company->website : 'https://' . $company->website }}" target="_blank" class="text-indigo-400 hover:underline">{{ $company->website }}</a>
                    </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Quick Metrics -->
        <div class="flex items-center space-x-4 bg-dark-950 p-2.5 rounded-lg border border-slate-800">
            <div class="text-center px-3 border-r border-slate-800">
                <p class="text-[10px] uppercase font-bold text-slate-400">Deals Value</p>
                <p class="text-sm font-extrabold text-emerald-400">${{ number_format($totalPipelineValue, 0) }}</p>
            </div>
            <div class="text-center px-3 border-r border-slate-800">
                <p class="text-[10px] uppercase font-bold text-slate-400">Deals Won</p>
                <p class="text-sm font-extrabold text-slate-200">{{ $wonDealsCount }} / {{ $company->deals->count() }}</p>
            </div>
            <div class="text-center px-3">
                <p class="text-[10px] uppercase font-bold text-slate-400">Contacts</p>
                <p class="text-sm font-extrabold text-indigo-400">{{ $company->contacts->count() }}</p>
            </div>
        </div>
    </div>

    <!-- Main Grid: Left Details & Right Tables -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Company Metadata Column (4 Cols) -->
        <div class="lg:col-span-4 space-y-6">
            <div class="bg-dark-900 p-5 rounded-xl border border-slate-800 shadow-sm space-y-3.5">
                <h3 class="text-xs font-bold text-white tracking-tight border-b border-slate-800 pb-2.5 uppercase">Company Information</h3>
                
                <div class="space-y-3 text-xs">
                    <div>
                        <span class="text-slate-400 block text-[11px]">Primary Email</span>
                        <span class="text-slate-200 font-medium">{{ $company->email ?: 'Not specified' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">Phone Number</span>
                        <span class="text-slate-200 font-medium">{{ $company->phone ?: 'Not specified' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">Website</span>
                        <span class="text-slate-200 font-medium">{{ $company->website ?: 'Not specified' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">Industry Sector</span>
                        <span class="text-slate-200 font-medium">{{ $company->industry ?: 'General' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">Street Address</span>
                        <span class="text-slate-200 font-medium">{{ $company->address ?: 'Not specified' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">City, State & Postal</span>
                        <span class="text-slate-200 font-medium">{{ collect([$company->city, $company->state, $company->zip])->filter()->implode(', ') ?: 'N/A' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">Country</span>
                        <span class="text-slate-200 font-medium">{{ $company->country ?: 'N/A' }}</span>
                    </div>
                    <div class="pt-2 border-t border-slate-800 text-[11px] text-slate-500">
                        <span>Created: {{ $company->created_at ? $company->created_at->format('M d, Y') : 'N/A' }}</span>
                    </div>
                </div>
            </div>

            <!-- Key Contacts Card -->
            <div class="bg-dark-900 p-5 rounded-xl border border-slate-800 shadow-sm space-y-3.5">
                <div class="flex items-center justify-between border-b border-slate-800 pb-2.5">
                    <h3 class="text-xs font-bold text-white tracking-tight uppercase">Key Contacts ({{ $company->contacts->count() }})</h3>
                </div>
                <div class="space-y-2 overflow-y-auto max-h-56">
                    @forelse($company->contacts as $contact)
                    <div class="p-2 rounded-lg bg-dark-950 border border-slate-800 flex items-center justify-between">
                        <div class="flex items-center space-x-2.5 min-w-0">
                            <div class="w-6 h-6 rounded-md bg-slate-800 text-slate-300 font-bold flex items-center justify-center text-[10px] flex-shrink-0">
                                {{ strtoupper(substr($contact->name ?? $contact->first_name ?? 'C', 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-semibold text-slate-200 truncate">{{ $contact->name ?? ($contact->first_name . ' ' . $contact->last_name) }}</p>
                                <p class="text-[10px] text-slate-400 truncate">{{ $contact->email ?: $contact->phone }}</p>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-4 text-xs text-slate-400">
                        No direct contacts linked yet.
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Opportunities & Pipeline Table (8 Cols) -->
        <div class="lg:col-span-8 space-y-6">
            <!-- Deals & Opportunities -->
            <div class="bg-dark-900 rounded-xl border border-slate-800 shadow-sm overflow-hidden">
                <div class="p-4 border-b border-slate-800 flex justify-between items-center bg-dark-950/40">
                    <div>
                        <h3 class="text-xs font-bold text-white tracking-tight uppercase">Commercial Pipeline & Deals</h3>
                        <p class="text-[11px] text-slate-400">Active and closed opportunities with {{ $company->name }}</p>
                    </div>
                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                        {{ $company->deals->count() }} Deals
                    </span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="bg-dark-950/60 text-slate-400 border-b border-slate-800">
                                <th class="py-2.5 px-4 font-semibold">Deal Title</th>
                                <th class="py-2.5 px-4 font-semibold">Stage</th>
                                <th class="py-2.5 px-4 font-semibold text-right">Value</th>
                                <th class="py-2.5 px-4 font-semibold text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60">
                            @forelse($company->deals as $deal)
                            <tr class="hover:bg-slate-800/30 transition">
                                <td class="py-3 px-4 font-medium text-slate-200">{{ $deal->title }}</td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-medium bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                                        {{ $deal->stage ? $deal->stage->name : 'N/A' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right font-bold text-emerald-400">
                                    ${{ number_format($deal->value, 0) }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    @if($deal->status === 'won')
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Won</span>
                                    @elseif($deal->status === 'lost')
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/20">Lost</span>
                                    @else
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">Active</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="py-6 text-center text-slate-400 text-xs">
                                    No commercial deals registered for this company yet.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Associated Leads Table -->
            <div class="bg-dark-900 rounded-xl border border-slate-800 shadow-sm overflow-hidden">
                <div class="p-4 border-b border-slate-800 flex justify-between items-center bg-dark-950/40">
                    <div>
                        <h3 class="text-xs font-bold text-white tracking-tight uppercase">Associated Leads</h3>
                        <p class="text-[11px] text-slate-400">Leads originating from or linked to {{ $company->name }}</p>
                    </div>
                    <span class="badge-count px-2.5 py-0.5 font-bold">
                        {{ $company->leads->count() }} Leads
                    </span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="bg-dark-950/60 text-slate-400 border-b border-slate-800">
                                <th class="py-2.5 px-4 font-semibold">Lead Name</th>
                                <th class="py-2.5 px-4 font-semibold">Email</th>
                                <th class="py-2.5 px-4 font-semibold">Status</th>
                                <th class="py-2.5 px-4 font-semibold">Created</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60">
                            @forelse($company->leads as $lead)
                            <tr class="hover:bg-slate-800/30 transition">
                                <td class="py-3 px-4 font-medium text-slate-200">
                                    <a href="{{ route('leads.show', $lead) }}" class="text-indigo-400 hover:underline">
                                        {{ $lead->first_name }} {{ $lead->last_name }}
                                    </a>
                                </td>
                                <td class="py-3 px-4 text-slate-400">{{ $lead->email }}</td>
                                <td class="py-3 px-4">
                                    <span class="badge-tag">
                                        {{ $lead->status ? $lead->status->name : 'New' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-slate-400 font-mono text-[11px]">
                                    {{ $lead->created_at ? $lead->created_at->format('M d, Y') : 'N/A' }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="py-6 text-center text-slate-400 text-xs">
                                    No leads linked to this company yet.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
