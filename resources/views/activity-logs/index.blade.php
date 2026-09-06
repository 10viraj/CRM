@extends('layouts.app')

@section('title', 'Activity Logs - SmartCRM')

@section('content')
<div class="mb-8">
    <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Activity Logs</h1>
    <p class="text-slate-500 mt-1 text-sm font-medium">Global audit trail of system events.</p>
</div>

<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-white border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                    <th class="p-4">Date / Time</th>
                    <th class="p-4">User</th>
                    <th class="p-4">Action</th>
                    <th class="p-4">Description</th>
                    <th class="p-4 text-right">IP Address</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm">
                @forelse($logs as $log)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="p-4">
                            <div class="font-medium text-slate-900">{{ $log->created_at->format('M d, Y') }}</div>
                            <div class="text-xs text-slate-500">{{ $log->created_at->format('h:i A') }}</div>
                        </td>
                        <td class="p-4 font-medium text-slate-900">
                            {{ $log->user->name ?? 'System' }}
                        </td>
                        <td class="p-4">
                            @php
                                $actionColors = [
                                    'created' => 'bg-green-100 text-green-700',
                                    'updated' => 'bg-blue-100 text-blue-700',
                                    'deleted' => 'bg-red-100 text-red-700',
                                    'viewed' => 'bg-slate-100 text-slate-600',
                                    'completed' => 'bg-indigo-100 text-indigo-700',
                                ];
                                $badgeClass = $actionColors[$log->action] ?? 'bg-gray-100 text-gray-700';
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium {{ $badgeClass }} capitalize">
                                {{ $log->action }}
                            </span>
                            <span class="text-xs text-slate-400 ml-1 uppercase">{{ $log->module }}</span>
                        </td>
                        <td class="p-4 text-slate-600">
                            {{ $log->description }}
                        </td>
                        <td class="p-4 text-slate-500 text-right font-mono text-xs">
                            {{ $log->ip_address }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="p-12 text-center text-slate-500">
                            <p class="text-base font-medium text-slate-900">No activity logs found</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($logs->hasPages())
        <div class="p-4 border-t border-slate-200 bg-slate-50">
            {{ $logs->links() }}
        </div>
    @endif
</div>
@endsection
