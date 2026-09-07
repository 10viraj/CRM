@extends('layouts.app')

@section('title', 'Companies Directory - SmartCRM')

@section('content')
<div class="space-y-6">
    <!-- Header Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-3">
                <h1 class="text-xl font-bold text-white tracking-tight">Companies</h1>
                <span class="px-2 py-0.5 text-xs font-semibold rounded-md bg-dark-950 text-slate-400 border border-slate-800">
                    {{ $companies->total() }} Accounts
                </span>
            </div>
            <p class="text-xs text-slate-400 mt-1">
                Manage B2B commercial accounts, associated key contacts, and pipeline opportunities.
            </p>
        </div>

        <div>
            <a href="{{ route('companies.create') }}" class="inline-flex items-center space-x-1.5 px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold shadow-sm transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>Add Company</span>
            </a>
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

    <!-- Filter & Search Controls -->
    <div class="bg-dark-900 p-3 rounded-xl border border-slate-800 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <form method="GET" action="{{ route('companies.index') }}" class="flex-1 flex flex-wrap items-center gap-2.5">
            <div class="relative flex-1 min-w-[200px]">
                <svg class="w-4 h-4 absolute left-3 top-1/2 transform -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search company, domain, location..." class="w-full bg-dark-950 border border-slate-800 rounded-lg pl-9 pr-3 py-1.5 text-xs text-slate-200 placeholder-slate-400 focus:border-indigo-500 outline-none transition">
            </div>

            @if(isset($industries) && $industries->count() > 0)
            <select name="industry" class="bg-dark-950 border border-slate-800 rounded-lg px-2.5 py-1.5 text-xs text-slate-300 focus:border-indigo-500 outline-none">
                <option value="">All Industries</option>
                @foreach($industries as $ind)
                <option value="{{ $ind }}" {{ request('industry') == $ind ? 'selected' : '' }}>{{ $ind }}</option>
                @endforeach
            </select>
            @endif

            <button type="submit" class="px-3 py-1.5 bg-dark-950 hover:bg-slate-800 text-slate-300 border border-slate-800 rounded-lg text-xs font-semibold transition">
                Filter
            </button>
            @if(request('q') || request('industry'))
            <a href="{{ route('companies.index') }}" class="text-xs text-slate-400 hover:text-slate-200 transition">Clear</a>
            @endif
        </form>
    </div>

    <!-- Companies Data Table -->
    <div class="bg-dark-900 rounded-xl border border-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-dark-950/60 text-slate-400 border-b border-slate-800">
                        <th class="py-3 px-4 font-semibold">Company</th>
                        <th class="py-3 px-4 font-semibold">Industry</th>
                        <th class="py-3 px-4 font-semibold">Contact Info</th>
                        <th class="py-3 px-4 font-semibold">Location</th>
                        <th class="py-3 px-4 font-semibold text-center">Deals</th>
                        <th class="py-3 px-4 font-semibold text-center">Contacts</th>
                        <th class="py-3 px-4 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($companies as $company)
                    <tr class="hover:bg-slate-800/30 transition">
                        <!-- Name with Avatar -->
                        <td class="py-3 px-4 font-medium text-slate-200">
                            <div class="flex items-center space-x-2.5">
                                <div class="w-7 h-7 rounded-lg bg-indigo-600/10 text-indigo-400 font-bold flex items-center justify-center text-xs flex-shrink-0 border border-indigo-500/20">
                                    {{ strtoupper(substr($company->name, 0, 1)) }}
                                </div>
                                <div>
                                    <a href="{{ route('companies.show', $company) }}" class="text-xs font-semibold text-slate-200 hover:text-indigo-400 transition">
                                        {{ $company->name }}
                                    </a>
                                    @if($company->website)
                                    <p class="text-[10px] text-slate-400 truncate max-w-[160px]">{{ $company->website }}</p>
                                    @endif
                                </div>
                            </div>
                        </td>

                        <!-- Industry -->
                        <td class="py-3 px-4">
                            @if($company->industry)
                            <span class="badge-tag">
                                {{ $company->industry }}
                            </span>
                            @else
                            <span class="text-slate-500">—</span>
                            @endif
                        </td>

                        <!-- Contact Info -->
                        <td class="py-3 px-4 text-slate-300">
                            <p class="truncate max-w-[180px]">{{ $company->email ?: '—' }}</p>
                            <p class="text-[10px] text-slate-400">{{ $company->phone ?: '' }}</p>
                        </td>

                        <!-- Location -->
                        <td class="py-3 px-4 text-slate-400">
                            {{ collect([$company->city, $company->country])->filter()->implode(', ') ?: '—' }}
                        </td>

                        <!-- Deals Count -->
                        <td class="py-3 px-4 text-center">
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                {{ $company->deals_count ?? 0 }}
                            </span>
                        </td>

                        <!-- Contacts Count -->
                        <td class="py-3 px-4 text-center">
                            <span class="badge-count">
                                {{ $company->contacts_count ?? 0 }}
                            </span>
                        </td>

                        <!-- Actions -->
                        <td class="py-3 px-4 text-right">
                            <div class="inline-flex items-center space-x-1.5">
                                <a href="{{ route('companies.show', $company) }}" class="px-2.5 py-1 rounded-lg bg-dark-950 hover:bg-slate-800 text-slate-300 hover:text-white border border-slate-800 text-[11px] font-semibold transition">
                                    View
                                </a>
                                <a href="{{ route('companies.edit', $company) }}" class="p-1 text-slate-400 hover:text-white rounded-md hover:bg-slate-800 transition" title="Edit">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-10 text-center text-slate-400">
                            No companies found matching your query.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($companies->hasPages())
        <div class="p-3 border-t border-slate-800 bg-dark-950">
            {{ $companies->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
