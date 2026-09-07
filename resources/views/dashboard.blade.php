@extends('layouts.app')

@section('title', 'Executive Sales Dashboard - SmartCRM')

@section('content')
<div class="space-y-6">
    <!-- Top Executive Header & Quick Controls -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-gradient-to-r from-dark-900 via-dark-850 to-dark-900 p-6 rounded-2xl border border-slate-800 shadow-xl">
        <div>
            <div class="flex items-center space-x-3">
                <h1 class="text-2xl font-bold text-white tracking-tight">Executive Dashboard</h1>
                <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 flex items-center space-x-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                    <span>Live Database Sync</span>
                </span>
            </div>
            <p class="text-xs text-slate-400 mt-1">
                Real-time commercial performance, revenue velocity, and sales pipeline analytics.
            </p>
        </div>

        <div class="flex items-center space-x-3">
            <div class="inline-flex bg-dark-950 p-1 rounded-xl border border-slate-800 text-xs font-medium text-slate-400">
                <button class="px-3 py-1.5 rounded-lg text-slate-400 hover:text-white transition">30 Days</button>
                <button class="px-3 py-1.5 rounded-lg bg-brand-600 text-white shadow font-semibold">This Quarter</button>
                <button class="px-3 py-1.5 rounded-lg text-slate-400 hover:text-white transition">Year to Date</button>
            </div>
            
            <a href="{{ route('deals.index') }}" class="inline-flex items-center space-x-2 px-4 py-2 bg-gradient-to-r from-brand-600 to-indigo-600 hover:from-brand-500 hover:to-indigo-500 text-white rounded-xl text-xs font-semibold shadow-lg shadow-brand-600/25 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>Pipeline View</span>
            </a>
        </div>
    </div>

    <!-- 6 Executive KPI Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
        <!-- 1. Total Leads -->
        <div class="bg-dark-900/90 p-5 rounded-2xl border border-slate-800/90 hover:border-slate-700 transition duration-200 flex flex-col justify-between group">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Leads</span>
                <div class="w-8 h-8 rounded-xl bg-blue-500/10 text-blue-400 flex items-center justify-center text-sm font-bold group-hover:scale-110 transition">
                    👥
                </div>
            </div>
            <div class="mt-4">
                <div class="text-2xl font-extrabold text-white tracking-tight">{{ number_format($totalLeads) }}</div>
                <div class="flex items-center space-x-1.5 mt-1">
                    <span class="text-[11px] font-bold text-emerald-400 bg-emerald-500/10 px-1.5 py-0.5 rounded">↑ 14.8%</span>
                    <span class="text-[11px] text-slate-400">vs last month</span>
                </div>
            </div>
        </div>

        <!-- 2. Open Deals -->
        <div class="bg-dark-900/90 p-5 rounded-2xl border border-slate-800/90 hover:border-slate-700 transition duration-200 flex flex-col justify-between group">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Open Pipeline</span>
                <div class="w-8 h-8 rounded-xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center text-sm font-bold group-hover:scale-110 transition">
                    💼
                </div>
            </div>
            <div class="mt-4">
                <div class="text-2xl font-extrabold text-white tracking-tight">{{ number_format($openDeals) }}</div>
                <div class="flex items-center space-x-1.5 mt-1">
                    <span class="text-[11px] font-bold text-indigo-400 bg-indigo-500/10 px-1.5 py-0.5 rounded">Active Funnel</span>
                    <span class="text-[11px] text-slate-400">{{ $totalDeals }} total</span>
                </div>
            </div>
        </div>

        <!-- 3. Won Deals -->
        <div class="bg-dark-900/90 p-5 rounded-2xl border border-slate-800/90 hover:border-slate-700 transition duration-200 flex flex-col justify-between group">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Deals Won</span>
                <div class="w-8 h-8 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center text-sm font-bold group-hover:scale-110 transition">
                    🏆
                </div>
            </div>
            <div class="mt-4">
                <div class="text-2xl font-extrabold text-emerald-400 tracking-tight">{{ number_format($wonDeals) }}</div>
                <div class="flex items-center space-x-1.5 mt-1">
                    <span class="text-[11px] font-bold text-emerald-400 bg-emerald-500/10 px-1.5 py-0.5 rounded">{{ $winRate }}%</span>
                    <span class="text-[11px] text-slate-400">win rate</span>
                </div>
            </div>
        </div>

        <!-- 4. Total Won Revenue -->
        <div class="bg-dark-900/90 p-5 rounded-2xl border border-slate-800/90 hover:border-slate-700 transition duration-200 flex flex-col justify-between group">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Won Revenue</span>
                <div class="w-8 h-8 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center text-sm font-bold group-hover:scale-110 transition">
                    💰
                </div>
            </div>
            <div class="mt-4">
                <div class="text-2xl font-extrabold text-white tracking-tight">${{ number_format($wonRevenue, 0) }}</div>
                <div class="flex items-center space-x-1.5 mt-1">
                    <span class="text-[11px] font-bold text-emerald-400 bg-emerald-500/10 px-1.5 py-0.5 rounded">↑ 22.4%</span>
                    <span class="text-[11px] text-slate-400">revenue pace</span>
                </div>
            </div>
        </div>

        <!-- 5. Active Tasks -->
        <div class="bg-dark-900/90 p-5 rounded-2xl border border-slate-800/90 hover:border-slate-700 transition duration-200 flex flex-col justify-between group">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Pending Tasks</span>
                <div class="w-8 h-8 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center text-sm font-bold group-hover:scale-110 transition">
                    📋
                </div>
            </div>
            <div class="mt-4">
                <div class="text-2xl font-extrabold text-white tracking-tight">{{ number_format($pendingTasks) }}</div>
                <div class="flex items-center space-x-1.5 mt-1">
                    @if($overdueTasks > 0)
                    <span class="text-[11px] font-bold text-rose-400 bg-rose-500/10 px-1.5 py-0.5 rounded">{{ $overdueTasks }} Overdue</span>
                    @else
                    <span class="text-[11px] font-bold text-emerald-400 bg-emerald-500/10 px-1.5 py-0.5 rounded">On Schedule</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- 6. Team & System -->
        <div class="bg-dark-900/90 p-5 rounded-2xl border border-slate-800/90 hover:border-slate-700 transition duration-200 flex flex-col justify-between group">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Team Capacity</span>
                <div class="w-8 h-8 rounded-xl bg-purple-500/10 text-purple-400 flex items-center justify-center text-sm font-bold group-hover:scale-110 transition">
                    ⚡
                </div>
            </div>
            <div class="mt-4">
                <div class="text-2xl font-extrabold text-white tracking-tight">{{ $activeUsers }}<span class="text-sm text-slate-400 font-normal"> / {{ $totalUsers }}</span></div>
                <div class="flex items-center space-x-1.5 mt-1">
                    <span class="text-[11px] font-bold text-purple-400 bg-purple-500/10 px-1.5 py-0.5 rounded">99.9% SLA</span>
                    <span class="text-[11px] text-slate-400">uptime</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section: Area Leads & Revenue Trend + Deal Stage Donut -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Leads Growth & Revenue Trend Area Chart (7 Columns) -->
        <div class="lg:col-span-7 bg-dark-900 p-6 rounded-2xl border border-slate-800 shadow-xl flex flex-col justify-between">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4">
                <div>
                    <h3 class="text-sm font-bold text-white tracking-tight">Leads & Revenue Trajectory</h3>
                    <p class="text-xs text-slate-400">Monthly leads acquisition combined with closed revenue trend</p>
                </div>
                <div class="flex items-center space-x-4 text-xs">
                    <div class="flex items-center space-x-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-brand-500"></span>
                        <span class="text-slate-300 font-medium">Leads (Vol)</span>
                    </div>
                    <div class="flex items-center space-x-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                        <span class="text-slate-300 font-medium">Won Revenue ($)</span>
                    </div>
                </div>
            </div>
            <div class="h-64 relative w-full mt-2">
                <canvas id="leadsGrowthChart"></canvas>
            </div>
        </div>

        <!-- Deals by Stage Distribution Donut & Metrics (5 Columns) -->
        <div class="lg:col-span-5 bg-dark-900 p-6 rounded-2xl border border-slate-800 shadow-xl flex flex-col justify-between">
            <div class="flex items-center justify-between mb-2">
                <div>
                    <h3 class="text-sm font-bold text-white tracking-tight">Pipeline Stage Breakdown</h3>
                    <p class="text-xs text-slate-400">Deal volume and stage distribution across the sales funnel</p>
                </div>
                <span class="px-2 py-0.5 text-[11px] font-bold rounded bg-dark-950 text-slate-300 border border-slate-800">
                    {{ $totalDeals }} Deals
                </span>
            </div>

            <div class="flex flex-col sm:flex-row items-center gap-6 my-2">
                <!-- Donut Chart Canvas Container -->
                <div class="w-44 h-44 relative flex-shrink-0">
                    <canvas id="dealsStageChart"></canvas>
                    <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                        <span class="text-xl font-extrabold text-white">{{ $totalDeals }}</span>
                        <span class="text-[10px] text-slate-400 uppercase font-semibold">In Funnel</span>
                    </div>
                </div>

                <!-- Custom Stage Legend List -->
                <div class="flex-1 w-full space-y-2 overflow-y-auto max-h-48 pr-1">
                    @forelse($dealsByStageFormatted as $stage)
                    <div class="flex items-center justify-between p-2 rounded-xl bg-dark-950/60 border border-slate-800/80 hover:border-slate-700 transition">
                        <div class="flex items-center space-x-2.5 min-w-0">
                            <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background-color: {{ $stage->color }};"></span>
                            <span class="text-xs font-medium text-slate-300 truncate">{{ $stage->name }}</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="text-xs font-bold text-white">{{ $stage->count }}</span>
                            <span class="text-[10px] text-slate-400 font-mono">({{ $stage->percentage }}%)</span>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-6 text-slate-400 text-xs">
                        No active deal stages found in database.
                    </div>
                    @endforelse
                </div>
            </div>
            
            <div class="pt-3 border-t border-slate-800/80 flex items-center justify-between text-xs text-slate-400">
                <span>Weighted Win Probability: <strong class="text-emerald-400">{{ $winRate }}%</strong></span>
                <a href="{{ route('deals.index') }}" class="text-brand-400 hover:text-brand-300 font-semibold transition">Open Kanban &rarr;</a>
            </div>
        </div>
    </div>

    <!-- Row 2: Sales Leaderboard (Admin Only) + Recent Activities Feed + System Stats -->
    @php
        $currentUser = auth()->user();
        $isAdmin = $currentUser && $currentUser->isAdmin();
    @endphp
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        @if($isAdmin)
        <!-- Sales Performance Leaderboard (4 Columns - Only Admin) -->
        <div class="lg:col-span-4 bg-dark-900 p-6 rounded-2xl border border-slate-800 shadow-xl flex flex-col">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center space-x-2">
                    <div>
                        <div class="flex items-center space-x-2">
                            <h3 class="text-sm font-bold text-white tracking-tight">Sales Leaderboard</h3>
                            <span class="px-1.5 py-0.5 rounded bg-amber-500/10 text-amber-400 border border-amber-500/20 text-[9px] font-bold">Admin Only</span>
                        </div>
                        <p class="text-xs text-slate-400">Top closing sales representatives</p>
                    </div>
                </div>
                <span class="text-xs text-brand-400 font-semibold">Rankings</span>
            </div>

            <div class="space-y-3 flex-1 overflow-y-auto">
                @forelse($salesLeaderboard as $index => $rep)
                <div class="flex items-center justify-between p-3 rounded-xl bg-dark-950/60 border border-slate-800/80 hover:border-slate-700 transition">
                    <div class="flex items-center space-x-3 min-w-0">
                        <div class="relative">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-brand-600 to-indigo-500 text-white font-bold flex items-center justify-center text-xs shadow">
                                {{ strtoupper(substr($rep->name, 0, 1)) }}
                            </div>
                            <span class="absolute -top-1.5 -left-1.5 w-4 h-4 rounded-full bg-dark-900 border border-slate-700 text-[9px] font-bold flex items-center justify-center text-amber-400">
                                #{{ $index + 1 }}
                            </span>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-bold text-slate-200 truncate">{{ $rep->name }}</p>
                            <p class="text-[10px] text-slate-400">{{ $rep->won_deals }} deals closed</p>
                        </div>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <p class="text-xs font-bold text-emerald-400">${{ number_format($rep->won_revenue, 0) }}</p>
                        <p class="text-[10px] text-slate-400">Won Value</p>
                    </div>
                </div>
                @empty
                <div class="text-center py-8 text-slate-400 text-xs">
                    No sales data recorded yet.
                </div>
                @endforelse
            </div>
        </div>
        @endif

        <!-- Recent CRM Activity Feed (5 Columns for Admin, 8 Columns for Non-Admin) -->
        <div class="{{ $isAdmin ? 'lg:col-span-5' : 'lg:col-span-8' }} bg-dark-900 p-6 rounded-2xl border border-slate-800 shadow-xl flex flex-col">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-sm font-bold text-white tracking-tight">Recent CRM Activities</h3>
                    <p class="text-xs text-slate-400">Live operational events and team interactions</p>
                </div>
                <a href="{{ route('activity-logs.index') }}" class="text-xs text-brand-400 hover:text-brand-300 font-semibold transition">View All</a>
            </div>

            <div class="space-y-3 flex-1 overflow-y-auto max-h-80">
                @forelse($recentActivities as $act)
                <div class="flex items-start space-x-3 p-3 rounded-xl bg-dark-950/60 border border-slate-800/80">
                    <div class="w-8 h-8 rounded-lg bg-slate-800 text-slate-300 flex items-center justify-center flex-shrink-0 text-xs font-bold mt-0.5">
                        @if(strtolower($act->type) == 'call') 📞
                        @elseif(strtolower($act->type) == 'meeting') 👥
                        @elseif(strtolower($act->type) == 'email') ✉️
                        @else 📝
                        @endif
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center justify-between">
                            <p class="text-xs font-bold text-slate-200 truncate">{{ $act->subject }}</p>
                            <span class="text-[10px] text-slate-400 font-mono">{{ $act->date }}</span>
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1 line-clamp-1">{{ $act->notes }}</p>
                    </div>
                </div>
                @empty
                <div class="text-center py-8 text-slate-400 text-xs">
                    No recent activities logged in CRM.
                </div>
                @endforelse
            </div>
        </div>

        <!-- System & Developer Reference (3 Columns for Admin, 4 Columns for Non-Admin) -->
        <div class="{{ $isAdmin ? 'lg:col-span-3' : 'lg:col-span-4' }} bg-dark-900 p-6 rounded-2xl border border-slate-800 shadow-xl flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-bold text-white tracking-tight">System Status</h3>
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                </div>
                
                <div class="space-y-2.5 text-xs">
                    <div class="flex justify-between items-center py-1.5 border-b border-slate-800/80">
                        <span class="text-slate-400">Environment</span>
                        <span class="text-emerald-400 font-bold uppercase">{{ app()->environment() }}</span>
                    </div>
                    <div class="flex justify-between items-center py-1.5 border-b border-slate-800/80">
                        <span class="text-slate-400">Database</span>
                        <span class="text-slate-200 font-mono uppercase">{{ config('database.default') }} Live</span>
                    </div>
                    <div class="flex justify-between items-center py-1.5 border-b border-slate-800/80">
                        <span class="text-slate-400">Framework</span>
                        <span class="text-slate-200 font-medium">Laravel {{ app()->version() }}</span>
                    </div>
                    <div class="flex justify-between items-center py-1.5 border-b border-slate-800/80">
                        <span class="text-slate-400">PHP Runtime</span>
                        <span class="text-slate-200 font-mono">{{ PHP_VERSION }}</span>
                    </div>
                    <div class="flex justify-between items-center py-1.5">
                        <span class="text-slate-400">Server Time</span>
                        <span class="text-slate-300 font-mono text-[11px]">{{ now()->format('M d, H:i') }}</span>
                    </div>
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-slate-800">
                <a href="http://localhost:5173/dashboard" target="_blank" class="w-full py-2.5 px-3 bg-dark-950 hover:bg-slate-800 border border-slate-800 hover:border-slate-700 rounded-xl flex items-center justify-center space-x-2 text-xs font-semibold text-brand-300 hover:text-white transition">
                    <span>Switch to React SPA Portal</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </a>
            </div>
        </div>
    </div>

    <!-- Row 3: Recent Commercial Opportunities & System Audit Logs -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Recent Deals Table (6 Columns) -->
        <div class="lg:col-span-6 bg-dark-900 rounded-2xl border border-slate-800 shadow-xl overflow-hidden flex flex-col">
            <div class="p-5 border-b border-slate-800 flex justify-between items-center bg-dark-900/60">
                <div>
                    <h3 class="text-sm font-bold text-white tracking-tight">Active Opportunities</h3>
                    <p class="text-xs text-slate-400">Key commercial deals under negotiation</p>
                </div>
                <a href="{{ route('deals.index') }}" class="text-xs text-brand-400 hover:text-brand-300 font-semibold transition">View All Deals &rarr;</a>
            </div>
            <div class="overflow-x-auto flex-1">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-dark-950/80 text-slate-400 border-b border-slate-800">
                            <th class="py-3 px-4 font-semibold">Deal Title</th>
                            <th class="py-3 px-4 font-semibold">Company</th>
                            <th class="py-3 px-4 font-semibold">Stage</th>
                            <th class="py-3 px-4 font-semibold text-right">Value</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        @forelse($recentDeals as $deal)
                        <tr class="hover:bg-slate-800/40 transition">
                            <td class="py-3.5 px-4 font-medium text-slate-200">{{ $deal->title }}</td>
                            <td class="py-3.5 px-4 text-slate-400">{{ $deal->company ? $deal->company->name : 'N/A' }}</td>
                            <td class="py-3.5 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-brand-500/10 text-brand-300 border border-brand-500/20">
                                    {{ $deal->stage ? $deal->stage->name : ucfirst($deal->status) }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right font-bold text-emerald-400">
                                ${{ number_format($deal->value, 0) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="py-8 text-center text-slate-400">
                                No commercial deals recorded in database.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Audit Logs Table (6 Columns) -->
        <div class="lg:col-span-6 bg-dark-900 rounded-2xl border border-slate-800 shadow-xl overflow-hidden flex flex-col">
            <div class="p-5 border-b border-slate-800 flex justify-between items-center bg-dark-900/60">
                <div>
                    <h3 class="text-sm font-bold text-white tracking-tight">Security & Audit Logs</h3>
                    <p class="text-xs text-slate-400">Real-time system telemetry and access authentication</p>
                </div>
                <a href="{{ route('activity-logs.index') }}" class="text-xs text-brand-400 hover:text-brand-300 font-semibold transition">Full Log Stream &rarr;</a>
            </div>
            <div class="overflow-x-auto flex-1">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-dark-950/80 text-slate-400 border-b border-slate-800">
                            <th class="py-3 px-4 font-semibold">User</th>
                            <th class="py-3 px-4 font-semibold">Action</th>
                            <th class="py-3 px-4 font-semibold">Module</th>
                            <th class="py-3 px-4 font-semibold">Timestamp</th>
                            <th class="py-3 px-4 font-semibold text-right">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        @forelse($systemLogs as $log)
                        <tr class="hover:bg-slate-800/40 transition">
                            <td class="py-3.5 px-4 font-medium text-slate-300">{{ $log->user }}</td>
                            <td class="py-3.5 px-4 text-slate-300">{{ $log->action }}</td>
                            <td class="py-3.5 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-800 text-slate-300 border border-slate-700">
                                    {{ $log->module }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-slate-400 font-mono text-[11px]">{{ $log->date }}</td>
                            <td class="py-3.5 px-4 text-right">
                                @if($log->status === 'Success')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                    Success
                                </span>
                                @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/20">
                                    {{ $log->status }}
                                </span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-400">
                                No system activity logs recorded.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let leadsChart = null;
        let stageChart = null;

        function renderCharts(theme) {
            const isWhite = theme === 'white';
            const isBlack = theme === 'black';

            const textColor = isWhite ? '#475569' : (isBlack ? '#a3a3a3' : '#94a3b8');
            const gridColor = isWhite ? 'rgba(226, 232, 240, 0.8)' : (isBlack ? 'rgba(40, 40, 40, 0.8)' : 'rgba(30, 41, 59, 0.6)');
            const tooltipBg = isWhite ? '#ffffff' : (isBlack ? '#121212' : '#0f172a');
            const tooltipText = isWhite ? '#0f172a' : '#ffffff';
            const tooltipBorder = isWhite ? '#e2e8f0' : (isBlack ? '#262626' : '#334155');
            const donutBorder = isWhite ? '#ffffff' : (isBlack ? '#0a0a0a' : '#0f172a');

            Chart.defaults.color = textColor;
            Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";

            // 1. Leads Growth & Revenue Multi-Axis Chart
            const leadsCtx = document.getElementById('leadsGrowthChart');
            if (leadsCtx) {
                if (leadsChart) leadsChart.destroy();

                const labels = @json($leadsLabels);
                const leadsData = @json($leadsMonthlyData);
                const revenueData = @json($revenueMonthlyData);

                const gradientLeads = leadsCtx.getContext('2d').createLinearGradient(0, 0, 0, 260);
                gradientLeads.addColorStop(0, 'rgba(99, 102, 241, 0.35)');
                gradientLeads.addColorStop(1, 'rgba(99, 102, 241, 0.0)');

                const gradientRevenue = leadsCtx.getContext('2d').createLinearGradient(0, 0, 0, 260);
                gradientRevenue.addColorStop(0, 'rgba(16, 185, 129, 0.25)');
                gradientRevenue.addColorStop(1, 'rgba(16, 185, 129, 0.0)');

                leadsChart = new Chart(leadsCtx.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'Leads Volume',
                                data: leadsData,
                                borderColor: '#6366f1',
                                backgroundColor: gradientLeads,
                                borderWidth: 2.5,
                                pointBackgroundColor: '#6366f1',
                                pointHoverRadius: 6,
                                fill: true,
                                tension: 0.35,
                                yAxisID: 'y'
                            },
                            {
                                label: 'Revenue ($)',
                                data: revenueData,
                                borderColor: '#10b981',
                                backgroundColor: gradientRevenue,
                                borderWidth: 2.5,
                                borderDash: [4, 4],
                                pointBackgroundColor: '#10b981',
                                pointHoverRadius: 6,
                                fill: false,
                                tension: 0.35,
                                yAxisID: 'y1'
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'index',
                            intersect: false,
                        },
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: tooltipBg,
                                titleColor: tooltipText,
                                bodyColor: textColor,
                                borderColor: tooltipBorder,
                                borderWidth: 1,
                                padding: 12,
                                boxPadding: 4,
                                callbacks: {
                                    label: function(context) {
                                        let label = context.dataset.label || '';
                                        if (label) {
                                            label += ': ';
                                        }
                                        if (context.dataset.yAxisID === 'y1') {
                                            label += '$' + Number(context.parsed.y).toLocaleString();
                                        } else {
                                            label += context.parsed.y;
                                        }
                                        return label;
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { color: textColor, font: { size: 11 } }
                            },
                            y: {
                                type: 'linear',
                                display: true,
                                position: 'left',
                                grid: { color: gridColor },
                                ticks: { color: textColor, font: { size: 11 }, precision: 0 }
                            },
                            y1: {
                                type: 'linear',
                                display: true,
                                position: 'right',
                                grid: { drawOnChartArea: false },
                                ticks: {
                                    color: '#10b981',
                                    font: { size: 11 },
                                    callback: function(value) { return '$' + (value >= 1000 ? (value/1000) + 'k' : value); }
                                }
                            }
                        }
                    }
                });
            }

            // 2. Deals by Stage Donut Chart
            const stageCtx = document.getElementById('dealsStageChart');
            if (stageCtx) {
                if (stageChart) stageChart.destroy();

                const stagesFormatted = @json($dealsByStageFormatted);
                const stageLabels = stagesFormatted.map(s => s.name);
                const stageCounts = stagesFormatted.map(s => s.count);
                const stageColors = stagesFormatted.map(s => s.color);

                // Handle empty stage data gracefully
                const finalData = stageCounts.length > 0 && stageCounts.some(c => c > 0) ? stageCounts : [1];
                const finalColors = stageCounts.length > 0 && stageCounts.some(c => c > 0) ? stageColors : ['#334155'];
                const finalLabels = stageCounts.length > 0 && stageCounts.some(c => c > 0) ? stageLabels : ['No Deals'];

                stageChart = new Chart(stageCtx.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: finalLabels,
                        datasets: [{
                            data: finalData,
                            backgroundColor: finalColors,
                            borderWidth: 2,
                            borderColor: donutBorder,
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '76%',
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: tooltipBg,
                                titleColor: tooltipText,
                                bodyColor: textColor,
                                borderColor: tooltipBorder,
                                borderWidth: 1,
                                padding: 10
                            }
                        }
                    }
                });
            }
        }

        // Initialize with current theme
        const initialTheme = localStorage.getItem('crm_theme') || 'default';
        renderCharts(initialTheme);

        // Listen for theme switch events
        window.addEventListener('theme-changed', function(e) {
            renderCharts(e.detail.theme);
        });
    });
</script>
@endsection
