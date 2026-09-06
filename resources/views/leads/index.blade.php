@extends('layouts.app')

@section('title', 'Leads - SmartCRM')

@section('content')
<div class="mb-8 flex justify-between items-center">
    <div>
        <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Lead Management</h1>
        <p class="text-slate-500 mt-1 text-sm font-medium">Manage and track your prospective customers.</p>
    </div>
    <div class="flex space-x-3">
        <button class="bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 px-4 py-2 rounded-lg font-medium shadow-sm transition flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
            Export
        </button>
        <a href="{{ route('leads.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-medium shadow-sm transition">
            + Add Lead
        </a>
    </div>
</div>

@if(session('success'))
    <div class="mb-6 bg-green-50 text-green-700 p-4 rounded-lg border border-green-200">
        {{ session('success') }}
    </div>
@endif

<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <!-- Filters -->
    <div class="p-4 border-b border-slate-200 bg-slate-50 flex flex-wrap gap-4 items-center justify-between">
        <form id="bulkDeleteForm" action="{{ route('leads.bulkDelete') }}" method="POST" class="flex items-center space-x-2 hidden">
            @csrf
            @method('DELETE')
            <button type="submit" onclick="return confirm('Are you sure you want to delete the selected leads?');" class="px-3 py-2 bg-red-600 text-white text-sm rounded-lg hover:bg-red-700 transition font-medium flex items-center shadow-sm">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                Delete Selected
            </button>
        </form>

        <form method="GET" action="{{ route('leads.index') }}" class="flex flex-wrap gap-3 items-center">
            <div class="relative">
                <svg class="w-4 h-4 absolute left-3 top-2.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search leads..." class="pl-9 pr-4 py-2 border border-slate-300 rounded-lg text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none w-64">
            </div>
            
            <select name="status_id" onchange="this.form.submit()" class="rounded-lg border-slate-300 border px-3 py-2 text-sm focus:border-indigo-500 outline-none text-slate-700">
                <option value="">All Statuses</option>
                @foreach($statuses as $status)
                    <option value="{{ $status->id }}" {{ request('status_id') == $status->id ? 'selected' : '' }}>{{ $status->name }}</option>
                @endforeach
            </select>

            <select name="source_id" onchange="this.form.submit()" class="rounded-lg border-slate-300 border px-3 py-2 text-sm focus:border-indigo-500 outline-none text-slate-700">
                <option value="">All Sources</option>
                @foreach($sources as $source)
                    <option value="{{ $source->id }}" {{ request('source_id') == $source->id ? 'selected' : '' }}>{{ $source->name }}</option>
                @endforeach
            </select>
            
            @if(request()->hasAny(['search', 'status_id', 'source_id']))
                <a href="{{ route('leads.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">Clear</a>
            @endif
        </form>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-white border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                    <th class="p-4 w-10"><input type="checkbox" id="selectAll" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"></th>
                    <th class="p-4">Lead Info</th>
                    <th class="p-4">Company & Source</th>
                    <th class="p-4">Status & Score</th>
                    <th class="p-4">Owner</th>
                    <th class="p-4">Created</th>
                    <th class="p-4 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm">
                @forelse($leads as $lead)
                    <tr class="hover:bg-slate-50 transition group">
                        <td class="p-4"><input type="checkbox" name="ids[]" value="{{ $lead->id }}" form="bulkDeleteForm" class="lead-checkbox rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"></td>
                        <td class="p-4">
                            <div class="font-semibold text-slate-900">{{ $lead->first_name }} {{ $lead->last_name }}</div>
                            <div class="text-slate-500 text-xs mt-0.5">{{ $lead->email }}</div>
                            <div class="text-slate-500 text-xs">{{ $lead->phone ?? 'No Phone' }}</div>
                        </td>
                        <td class="p-4">
                            <div class="text-slate-700 font-medium">{{ $lead->company ?? 'Unknown' }}</div>
                            <div class="text-slate-500 text-xs mt-0.5">{{ $lead->source->name ?? 'Direct' }}</div>
                        </td>
                        <td class="p-4">
                            @php
                                $color = $lead->status->color ?? 'gray';
                                $bg = "bg-{$color}-100";
                                $text = "text-{$color}-700";
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $bg }} {{ $text }}">
                                {{ $lead->status->name ?? 'New' }}
                            </span>
                            <div class="mt-2 text-xs font-semibold {{ $lead->score >= 80 ? 'text-green-600' : ($lead->score >= 50 ? 'text-orange-500' : 'text-red-500') }}">
                                Score: {{ $lead->score }}
                            </div>
                        </td>
                        <td class="p-4 text-slate-600">
                            {{ $lead->owner->name ?? 'Unassigned' }}
                        </td>
                        <td class="p-4 text-slate-500 text-xs whitespace-nowrap">
                            {{ $lead->created_at->format('M d, Y') }}
                        </td>
                        <td class="p-4 text-center space-x-2">
                            <a href="{{ route('leads.show', $lead) }}" class="text-indigo-600 hover:text-indigo-900 font-medium text-xs">View</a>
                            <a href="{{ route('leads.edit', $lead) }}" class="text-slate-600 hover:text-slate-900 font-medium text-xs">Edit</a>
                            <form action="{{ route('leads.destroy', $lead) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this lead?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900 font-medium text-xs bg-transparent border-none p-0 cursor-pointer">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="p-12 text-center text-slate-500">
                            <div class="flex flex-col items-center justify-center">
                                <svg class="w-12 h-12 text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                <p class="text-base font-medium text-slate-900">No leads found</p>
                                <p class="text-sm">Try adjusting your filters or search query.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    @if($leads->hasPages())
        <div class="p-4 border-t border-slate-200 bg-slate-50">
            {{ $leads->links() }}
        </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.lead-checkbox');
        const bulkDeleteForm = document.getElementById('bulkDeleteForm');

        function toggleBulkActions() {
            const checkedCount = document.querySelectorAll('.lead-checkbox:checked').length;
            if (checkedCount > 0) {
                bulkDeleteForm.classList.remove('hidden');
            } else {
                bulkDeleteForm.classList.add('hidden');
            }
        }

        if(selectAll) {
            selectAll.addEventListener('change', (e) => {
                checkboxes.forEach(cb => {
                    cb.checked = e.target.checked;
                });
                toggleBulkActions();
            });
        }

        checkboxes.forEach(cb => {
            cb.addEventListener('change', () => {
                if(selectAll) {
                    selectAll.checked = checkboxes.length === document.querySelectorAll('.lead-checkbox:checked').length;
                }
                toggleBulkActions();
            });
        });
    });
</script>
@endsection
