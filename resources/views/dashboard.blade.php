@extends('layouts.app')

@section('title', 'Admin Dashboard - SmartCRM')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <h1 class="text-xl font-bold text-slate-100 tracking-tight">Dashboard</h1>
</div>

<div class="flex flex-col lg:flex-row gap-6">
    <!-- Left Column (Main Stats & Charts) -->
    <div class="flex-1 space-y-6">
        <!-- Stats Grid -->
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
            <!-- Total Users -->
            <div class="bg-slate-900 p-4 rounded-xl border border-slate-800 shadow-sm flex flex-col justify-between h-28 relative overflow-hidden group">
                <p class="text-xs font-medium text-slate-400">Total Users</p>
                <div class="flex justify-between items-end mt-2">
                    <p class="text-3xl font-bold text-slate-100">{{ $totalUsers }}</p>
                    <span class="text-xs text-emerald-500 font-medium">↑ 8.1%</span>
                </div>
            </div>
            <!-- Active Users -->
            <div class="bg-slate-900 p-4 rounded-xl border border-slate-800 shadow-sm flex flex-col justify-between h-28 relative overflow-hidden group">
                <p class="text-xs font-medium text-slate-400">Active Users</p>
                <div class="flex justify-between items-end mt-2">
                    <p class="text-3xl font-bold text-slate-100">{{ $activeUsers }}</p>
                    <span class="text-xs text-emerald-500 font-medium">↑ 5.4%</span>
                </div>
            </div>
            <!-- Total Leads -->
            <div class="bg-slate-900 p-4 rounded-xl border border-slate-800 shadow-sm flex flex-col justify-between h-28 relative overflow-hidden group">
                <p class="text-xs font-medium text-slate-400">Total Leads</p>
                <div class="flex justify-between items-end mt-2">
                    <p class="text-3xl font-bold text-slate-100">{{ $totalLeads }}</p>
                    <span class="text-xs text-emerald-500 font-medium">↑ 12.5%</span>
                </div>
            </div>
            <!-- Total Deals -->
            <div class="bg-slate-900 p-4 rounded-xl border border-slate-800 shadow-sm flex flex-col justify-between h-28 relative overflow-hidden group">
                <p class="text-xs font-medium text-slate-400">Total Deals</p>
                <div class="flex justify-between items-end mt-2">
                    <p class="text-3xl font-bold text-slate-100">{{ $totalDeals }}</p>
                    <span class="text-xs text-emerald-500 font-medium">↑ 10.2%</span>
                </div>
            </div>
            <!-- System Uptime -->
            <div class="bg-slate-900 p-4 rounded-xl border border-slate-800 shadow-sm flex flex-col justify-between h-28 relative overflow-hidden group">
                <p class="text-xs font-medium text-slate-400">System Uptime</p>
                <div class="flex justify-between items-end mt-2">
                    <p class="text-3xl font-bold text-slate-100">99.9%</p>
                    <span class="text-xs text-emerald-500 font-medium">Excellent</span>
                </div>
            </div>
        </div>

        <!-- Charts and Info Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <!-- Leads Growth Chart -->
            <div class="bg-slate-900 p-5 rounded-xl border border-slate-800 shadow-sm lg:col-span-1">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-sm font-semibold text-slate-300">Leads Growth</h3>
                    <select class="bg-slate-800 border border-slate-700 text-slate-300 text-xs rounded px-2 py-1 outline-none"><option>This Month</option></select>
                </div>
                <div class="h-48 relative w-full">
                    <canvas id="leadsGrowthChart"></canvas>
                </div>
            </div>
            
            <!-- Deals by Stage Donut -->
            <div class="bg-slate-900 p-5 rounded-xl border border-slate-800 shadow-sm lg:col-span-1">
                <h3 class="text-sm font-semibold text-slate-300 mb-4">Deals by Stage (Count)</h3>
                <div class="flex items-center h-48">
                    <div class="w-1/2 relative h-full">
                        <canvas id="dealsStageChart"></canvas>
                    </div>
                    <div class="w-1/2 pl-4">
                        <ul class="space-y-2 text-xs">
                            @foreach($dealsByStage as $name => $count)
                            <li class="flex justify-between">
                                <span class="text-slate-400 flex items-center"><span class="w-2 h-2 rounded-full bg-blue-500 mr-2 inline-block"></span> {{ $name }}</span>
                                <span class="text-slate-200">{{ $count }}</span>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>

            <!-- System Information -->
            <div class="bg-slate-900 p-5 rounded-xl border border-slate-800 shadow-sm lg:col-span-1">
                <h3 class="text-sm font-semibold text-slate-300 mb-4">System Information</h3>
                <div class="space-y-3 text-xs">
                    <div class="flex justify-between"><span class="text-slate-500">Server Time</span><span class="text-slate-300">{{ now()->format('M d, Y h:i A') }}</span></div>
                    <div class="flex justify-between"><span class="text-slate-500">Environment</span><span class="text-slate-300 capitalize">{{ app()->environment() }}</span></div>
                    <div class="flex justify-between"><span class="text-slate-500">PHP Version</span><span class="text-slate-300">{{ PHP_VERSION }}</span></div>
                    <div class="flex justify-between"><span class="text-slate-500">DB Connection</span><span class="text-slate-300 uppercase">{{ config('database.default') }}</span></div>
                    <div class="flex justify-between"><span class="text-slate-500">Framework</span><span class="text-slate-300">Laravel {{ app()->version() }}</span></div>
                    <div class="flex justify-between"><span class="text-slate-500">Total Users</span><span class="text-slate-300">{{ $totalUsers }} Registered</span></div>
                </div>
            </div>
        </div>

        <!-- Recent System Logs -->
        <div class="bg-slate-900 rounded-xl border border-slate-800 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-slate-800 flex justify-between items-center">
                <h3 class="text-sm font-semibold text-slate-300">Recent System Logs</h3>
                <a href="{{ route('activity-logs.index') }}" class="text-xs text-blue-400 hover:text-blue-300 transition">View All Logs &rarr;</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-slate-900/50 text-slate-500 border-b border-slate-800">
                            <th class="py-3 px-4 font-medium">ID</th>
                            <th class="py-3 px-4 font-medium">User</th>
                            <th class="py-3 px-4 font-medium">Action</th>
                            <th class="py-3 px-4 font-medium">Module</th>
                            <th class="py-3 px-4 font-medium">IP Address</th>
                            <th class="py-3 px-4 font-medium">Date & Time</th>
                            <th class="py-3 px-4 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/50">
                        @forelse($systemLogs as $log)
                        <tr class="hover:bg-slate-800/50 transition text-slate-300">
                            <td class="py-3 px-4 font-mono">{{ $log->id }}</td>
                            <td class="py-3 px-4 font-medium">{{ $log->user }}</td>
                            <td class="py-3 px-4">{{ $log->action }}</td>
                            <td class="py-3 px-4"><span class="px-1.5 py-0.5 rounded bg-slate-800 text-slate-400 text-[11px]">{{ $log->module }}</span></td>
                            <td class="py-3 px-4 font-mono text-slate-400">{{ $log->ip }}</td>
                            <td class="py-3 px-4">{{ $log->date }}</td>
                            <td class="py-3 px-4">
                                @if($log->status === 'Success')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Success</span>
                                @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-red-500/10 text-red-400 border border-red-500/20">{{ $log->status }}</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-500">
                                No recent system logs recorded yet.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Right Sidebar (API Endpoints) -->
    <div class="w-full lg:w-72 flex-shrink-0">
        <div class="bg-slate-900 p-4 rounded-xl border border-slate-800 shadow-sm h-full flex flex-col">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-sm font-semibold text-slate-300">API Endpoints</h3>
                <button class="bg-blue-600 hover:bg-blue-700 text-white text-[10px] px-2 py-1 rounded transition">+ New Endpoint</button>
            </div>
            <div class="flex-1 overflow-y-auto pr-2 space-y-2 text-xs">
                @foreach($apiEndpoints as $endpoint)
                <div class="flex items-center justify-between p-2 rounded bg-slate-950 border border-slate-800 hover:border-slate-700 cursor-pointer transition">
                    <div class="flex items-center space-x-2">
                        @if($endpoint->method === 'GET')
                            <span class="px-1.5 py-0.5 rounded bg-blue-500/10 text-blue-400 font-semibold text-[10px]">GET</span>
                        @elseif($endpoint->method === 'POST')
                            <span class="px-1.5 py-0.5 rounded bg-emerald-500/10 text-emerald-400 font-semibold text-[10px]">POST</span>
                        @elseif($endpoint->method === 'PUT')
                            <span class="px-1.5 py-0.5 rounded bg-amber-500/10 text-amber-400 font-semibold text-[10px]">PUT</span>
                        @elseif($endpoint->method === 'DELETE')
                            <span class="px-1.5 py-0.5 rounded bg-red-500/10 text-red-400 font-semibold text-[10px]">DELETE</span>
                        @endif
                        <span class="text-slate-400 font-mono">{{ $endpoint->path }}</span>
                    </div>
                    <span class="text-slate-600">›</span>
                </div>
                @endforeach
            </div>
            <div class="mt-4 text-center">
                <a href="#" class="text-xs text-slate-500 hover:text-slate-300 transition">View All Endpoints</a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Chart.defaults.color = '#94a3b8';
        Chart.defaults.font.family = "'Inter', sans-serif";

        // Leads Growth Line Chart
        const leadsCtx = document.getElementById('leadsGrowthChart').getContext('2d');
        new Chart(leadsCtx, {
            type: 'line',
            data: {
                labels: ['May 12', 'May 19', 'May 26', 'Jun 02', 'Jun 09'],
                datasets: [{
                    label: 'Leads',
                    data: [120, 100, 130, 90, 140],
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 2,
                    pointBackgroundColor: '#3b82f6',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false } },
                    y: { grid: { color: '#1e293b' }, beginAtZero: true }
                }
            }
        });

        // Deals by Stage Donut Chart
        const stageCtx = document.getElementById('dealsStageChart').getContext('2d');
        const stagesData = @json($dealsByStage);
        const labels = Object.keys(stagesData);
        const data = Object.values(stagesData);
        
        const colors = ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ef4444', '#64748b'];

        new Chart(stageCtx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: colors,
                    borderWidth: 0,
                    cutout: '70%'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } }
            }
        });
        
        // Update legend colors manually since we hid the chart.js legend
        const legendItems = document.querySelectorAll('.rounded-full.bg-blue-500');
        legendItems.forEach((item, i) => {
            if (colors[i]) item.style.backgroundColor = colors[i];
        });
    });
</script>
@endsection


