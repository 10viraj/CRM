@extends('layouts.app')

@section('title', 'Reports & Analytics - SmartCRM')

@section('content')
<div class="mb-8 flex justify-between items-center">
    <div>
        <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Reports & Analytics</h1>
        <p class="text-slate-500 mt-1 text-sm font-medium">Business insights and performance metrics.</p>
    </div>
    <div>
        <button class="bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 px-4 py-2 rounded-lg font-medium shadow-sm transition flex items-center text-sm">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            Export PDF
        </button>
    </div>
</div>

<!-- Key Metrics Row -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm flex items-center">
        <div class="w-12 h-12 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center mr-4">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
        </div>
        <div>
            <p class="text-sm font-medium text-slate-500 uppercase tracking-wider">Total Sales</p>
            <h3 class="text-2xl font-black text-slate-900">{{ $salesData['total_sales'] }}</h3>
        </div>
    </div>
    
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm flex items-center">
        <div class="w-12 h-12 rounded-full bg-green-50 text-green-600 flex items-center justify-center mr-4">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
        <div>
            <p class="text-sm font-medium text-slate-500 uppercase tracking-wider">Won Deals</p>
            <h3 class="text-2xl font-black text-slate-900">{{ $salesData['won_deals'] }}</h3>
        </div>
    </div>
    
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm flex items-center">
        <div class="w-12 h-12 rounded-full bg-orange-50 text-orange-600 flex items-center justify-center mr-4">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
        </div>
        <div>
            <p class="text-sm font-medium text-slate-500 uppercase tracking-wider">Total Leads</p>
            <h3 class="text-2xl font-black text-slate-900">{{ $leadData['total_leads'] }}</h3>
        </div>
    </div>
    
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm flex items-center">
        <div class="w-12 h-12 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center mr-4">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
        <div>
            <p class="text-sm font-medium text-slate-500 uppercase tracking-wider">Revenue (Won)</p>
            <h3 class="text-2xl font-black text-indigo-700">${{ number_format($salesData['revenue']) }}</h3>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
    <!-- Revenue Chart -->
    <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-bold text-slate-900">Revenue Growth (Paid Invoices)</h3>
            <select class="text-sm border-slate-200 rounded-lg text-slate-600 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 py-1">
                <option>Last 6 Months</option>
                <option>This Year</option>
            </select>
        </div>
        <div class="relative h-72 w-full">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>
    
    <!-- Lead Breakdown -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
        <h3 class="text-lg font-bold text-slate-900 mb-6">Lead Breakdown</h3>
        <div class="relative h-60 w-full flex justify-center">
            <canvas id="leadChart"></canvas>
        </div>
        <div class="mt-4 flex justify-between items-center text-sm">
            <span class="text-slate-500">Conversion Rate</span>
            <span class="font-bold text-slate-900">{{ $leadData['conversion_rate'] }}%</span>
        </div>
    </div>
</div>

<!-- Detailed Metrics Row -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <!-- Follow-ups -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 bg-slate-50">
            <h3 class="text-lg font-bold text-slate-900">Follow-Up Task Performance</h3>
        </div>
        <div class="p-6">
            <div class="space-y-6">
                <div>
                    <div class="flex justify-between items-end mb-2">
                        <span class="text-sm font-medium text-slate-700">Completed</span>
                        <span class="text-lg font-bold text-green-600">{{ $followUpData['completed'] }}</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2.5">
                        <div class="bg-green-500 h-2.5 rounded-full" style="width: {{ $followUpData['completed'] > 0 ? 100 : 0 }}%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between items-end mb-2">
                        <span class="text-sm font-medium text-slate-700">Pending (Upcoming)</span>
                        <span class="text-lg font-bold text-blue-600">{{ $followUpData['pending'] - $followUpData['overdue'] }}</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2.5">
                        <div class="bg-blue-500 h-2.5 rounded-full" style="width: 45%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between items-end mb-2">
                        <span class="text-sm font-medium text-slate-700">Overdue</span>
                        <span class="text-lg font-bold text-red-600">{{ $followUpData['overdue'] }}</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2.5">
                        <div class="bg-red-500 h-2.5 rounded-full" style="width: {{ $followUpData['overdue'] > 0 ? min(($followUpData['overdue'] / max($followUpData['pending'], 1)) * 100, 100) : 0 }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Sales Funnel -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 bg-slate-50">
            <h3 class="text-lg font-bold text-slate-900">Sales Funnel</h3>
        </div>
        <div class="p-6 flex flex-col justify-center h-full pb-12">
            <div class="flex items-center justify-between py-3 border-b border-slate-100">
                <div class="flex items-center">
                    <div class="w-3 h-3 rounded-full bg-slate-300 mr-3"></div>
                    <span class="text-sm font-medium text-slate-700">Total Leads</span>
                </div>
                <span class="font-bold">{{ $leadData['total_leads'] }}</span>
            </div>
            <div class="flex items-center justify-between py-3 border-b border-slate-100 ml-4">
                <div class="flex items-center">
                    <div class="w-3 h-3 rounded-full bg-blue-400 mr-3"></div>
                    <span class="text-sm font-medium text-slate-700">Active Deals</span>
                </div>
                <span class="font-bold">{{ $salesData['total_sales'] - $salesData['won_deals'] - $salesData['lost_deals'] }}</span>
            </div>
            <div class="flex items-center justify-between py-3 border-b border-slate-100 ml-8">
                <div class="flex items-center">
                    <div class="w-3 h-3 rounded-full bg-green-500 mr-3"></div>
                    <span class="text-sm font-medium text-slate-700">Won Deals</span>
                </div>
                <span class="font-bold text-green-600">{{ $salesData['won_deals'] }}</span>
            </div>
            <div class="flex items-center justify-between py-3 ml-12">
                <div class="flex items-center">
                    <div class="w-3 h-3 rounded-full bg-red-500 mr-3"></div>
                    <span class="text-sm font-medium text-slate-700">Lost Deals</span>
                </div>
                <span class="font-bold text-red-500">{{ $salesData['lost_deals'] }}</span>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Colors
    const primary = '#4f46e5';
    const primaryLight = 'rgba(79, 70, 229, 0.1)';
    const textLight = '#94a3b8';

    // Revenue Chart
    const ctx = document.getElementById('revenueChart').getContext('2d');
    const revenueChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: {!! json_encode($chartData['labels']) !!},
            datasets: [{
                label: 'Revenue ($)',
                data: {!! json_encode($chartData['data']) !!},
                borderColor: primary,
                backgroundColor: primaryLight,
                borderWidth: 3,
                pointBackgroundColor: '#ffffff',
                pointBorderColor: primary,
                pointBorderWidth: 2,
                pointRadius: 4,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return '$' + context.raw.toLocaleString();
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: '#f1f5f9', drawBorder: false },
                    ticks: {
                        color: textLight,
                        callback: function(value) { return '$' + (value >= 1000 ? (value/1000) + 'k' : value); }
                    }
                },
                x: {
                    grid: { display: false, drawBorder: false },
                    ticks: { color: textLight }
                }
            }
        }
    });

    // Lead Breakdown Chart (Doughnut)
    const leadDataRaw = {!! json_encode($leadData['by_status']) !!};
    const leadLabels = leadDataRaw.map(d => d.status.name);
    const leadCounts = leadDataRaw.map(d => d.count);
    
    const leadCtx = document.getElementById('leadChart').getContext('2d');
    const leadChart = new Chart(leadCtx, {
        type: 'doughnut',
        data: {
            labels: leadLabels,
            datasets: [{
                data: leadCounts,
                backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#64748b'],
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '75%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { boxWidth: 10, padding: 15, color: '#475569' }
                }
            }
        }
    });
});
</script>
@endsection
