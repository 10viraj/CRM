@extends('layouts.app')

@section('title', 'Search Results for "' . $query . '" - SmartCRM')

@section('content')
<div class="mb-8">
    <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Search Results</h1>
    <p class="text-slate-500 mt-1 text-sm font-medium">Showing results for "<span class="text-indigo-600">{{ $query }}</span>"</p>
</div>

<div class="space-y-8">
    <!-- Leads Results -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
            <h2 class="text-lg font-bold text-slate-800">Leads ({{ $leads->count() }})</h2>
        </div>
        @if($leads->count() > 0)
            <div class="divide-y divide-slate-100">
                @foreach($leads as $lead)
                    <a href="{{ route('leads.show', $lead) }}" class="block px-6 py-4 hover:bg-slate-50 transition flex justify-between items-center group">
                        <div>
                            <p class="font-bold text-indigo-600 group-hover:text-indigo-800">{{ $lead->first_name }} {{ $lead->last_name }}</p>
                            <p class="text-sm text-slate-500">{{ $lead->email }} | {{ $lead->company ?? 'No Company' }}</p>
                        </div>
                        <svg class="w-5 h-5 text-slate-300 group-hover:text-indigo-500 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                @endforeach
            </div>
        @else
            <div class="p-8 text-center text-slate-500">
                <p>No leads found matching your query.</p>
            </div>
        @endif
    </div>

    <!-- Companies Results -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
            <h2 class="text-lg font-bold text-slate-800">Companies ({{ $companies->count() }})</h2>
        </div>
        @if($companies->count() > 0)
            <div class="divide-y divide-slate-100">
                @foreach($companies as $company)
                    <a href="{{ route('companies.show', $company) }}" class="block px-6 py-4 hover:bg-slate-50 transition flex justify-between items-center group">
                        <div>
                            <p class="font-bold text-indigo-600 group-hover:text-indigo-800">{{ $company->name }}</p>
                            <p class="text-sm text-slate-500">{{ $company->email }} | {{ $company->industry ?? 'No Industry' }}</p>
                        </div>
                        <svg class="w-5 h-5 text-slate-300 group-hover:text-indigo-500 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                @endforeach
            </div>
        @else
            <div class="p-8 text-center text-slate-500">
                <p>No companies found matching your query.</p>
            </div>
        @endif
    </div>

    <!-- Deals Results -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
            <h2 class="text-lg font-bold text-slate-800">Deals ({{ $deals->count() }})</h2>
        </div>
        @if($deals->count() > 0)
            <div class="divide-y divide-slate-100">
                @foreach($deals as $deal)
                    <a href="{{ route('deals.show', $deal) }}" class="block px-6 py-4 hover:bg-slate-50 transition flex justify-between items-center group">
                        <div>
                            <p class="font-bold text-indigo-600 group-hover:text-indigo-800">{{ $deal->name }}</p>
                            <p class="text-sm text-slate-500">${{ number_format($deal->value) }} | {{ $deal->stage->name ?? 'No Stage' }}</p>
                        </div>
                        <svg class="w-5 h-5 text-slate-300 group-hover:text-indigo-500 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                @endforeach
            </div>
        @else
            <div class="p-8 text-center text-slate-500">
                <p>No deals found matching your query.</p>
            </div>
        @endif
    </div>
</div>
@endsection
