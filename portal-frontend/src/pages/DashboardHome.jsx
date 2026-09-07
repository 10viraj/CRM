import React, { useState, useEffect, useCallback } from 'react';
import { api } from '../services/api';
import { Line, Bar } from 'react-chartjs-2';
import { Link } from 'react-router-dom';

export default function DashboardHome() {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [dateRange, setDateRange] = useState('this_month'); // today, this_week, this_month, last_30_days, this_quarter, this_year, all
  const [refreshing, setRefreshing] = useState(false);

  const fetchDashboardData = useCallback(async () => {
    try {
      setRefreshing(true);
      const res = await api.dashboard.get({ range: dateRange });
      if (res.data?.data) {
        setData(res.data.data);
      }
    } catch (err) {
      console.error('Failed to load dynamic dashboard data:', err);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  }, [dateRange]);

  useEffect(() => {
    fetchDashboardData();
  }, [fetchDashboardData]);

  if (loading && !data) {
    return (
      <div className="flex h-96 items-center justify-center">
        <div className="text-center space-y-3">
          <div className="inline-block w-9 h-9 border-4 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
          <p className="text-xs font-bold text-slate-500">Calculating real-time CRM analytics...</p>
        </div>
      </div>
    );
  }

  // 1. Leads Overview Line Chart Data
  const leadsChartData = {
    labels: data?.leads_overview?.labels || [],
    datasets: [
      {
        label: 'New Leads',
        data: data?.leads_overview?.new_leads || [],
        borderColor: '#3b82f6',
        backgroundColor: 'rgba(59, 130, 246, 0.1)',
        fill: true,
        tension: 0.4,
        pointBackgroundColor: '#3b82f6',
        pointRadius: 4,
        pointHoverRadius: 6,
      },
      {
        label: 'Converted / Qualified',
        data: data?.leads_overview?.converted_leads || [],
        borderColor: '#10b981',
        backgroundColor: 'rgba(16, 185, 129, 0.1)',
        fill: true,
        tension: 0.4,
        pointBackgroundColor: '#10b981',
        pointRadius: 4,
        pointHoverRadius: 6,
      },
    ],
  };

  // 2. Deals by Stage Horizontal Bar Chart Data
  const stageColors = ['#3b82f6', '#06b6d4', '#10b981', '#f59e0b', '#8b5cf6', '#10b981', '#ef4444'];
  const dealsChartData = {
    labels: data?.deals_by_stage?.map((s) => s.name) || [],
    datasets: [
      {
        label: 'Deals Count',
        data: data?.deals_by_stage?.map((s) => s.count) || [],
        backgroundColor: data?.deals_by_stage?.map((s, idx) => s.color || stageColors[idx % stageColors.length]) || stageColors,
        borderRadius: 8,
        borderSkipped: false,
      },
    ],
  };

  // 3. Revenue Trend Chart Data
  const revenueChartData = {
    labels: data?.revenue_trend?.labels || [],
    datasets: [
      {
        label: 'Won Revenue ($)',
        data: data?.revenue_trend?.revenue || [],
        borderColor: '#10b981',
        backgroundColor: 'rgba(16, 185, 129, 0.15)',
        fill: true,
        tension: 0.35,
        pointBackgroundColor: '#10b981',
        pointRadius: 5,
      },
    ],
  };

  const getActivityIcon = (icon) => {
    switch (icon) {
      case 'lead':
        return '👤';
      case 'deal':
        return '🤝';
      case 'task':
        return '✓';
      case 'contact':
        return '📇';
      case 'meeting':
        return '📅';
      default:
        return '⚡';
    }
  };

  const getActivityBg = (color) => {
    switch (color) {
      case 'blue':
        return 'bg-blue-50 text-blue-600 border-blue-100';
      case 'amber':
        return 'bg-amber-50 text-amber-600 border-amber-100';
      case 'emerald':
        return 'bg-emerald-50 text-emerald-600 border-emerald-100';
      case 'purple':
        return 'bg-purple-50 text-purple-600 border-purple-100';
      case 'indigo':
        return 'bg-indigo-50 text-indigo-600 border-indigo-100';
      default:
        return 'bg-slate-50 text-slate-600 border-slate-100';
    }
  };

  return (
    <div className="p-6 md:p-8 space-y-6">
      {/* Header & Date Range Filter */}
      <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <div className="flex items-center space-x-3">
            <h1 className="text-2xl font-black text-slate-900 tracking-tight flex items-center">
              <span>Executive CRM Dashboard</span>
            </h1>
            <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
              ● Live Database
            </span>
          </div>
          <p className="text-slate-500 text-xs mt-1">
            Real-time pipeline metrics, revenue conversion, team velocity, and client activities.
          </p>
        </div>

        {/* Controls */}
        <div className="flex items-center space-x-3 flex-wrap gap-2">
          {/* Date Range Selector */}
          <div className="flex items-center bg-white border border-slate-200 rounded-xl px-3 py-1.5 shadow-sm">
            <span className="mr-2 text-xs text-slate-400">📅</span>
            <select
              value={dateRange}
              onChange={(e) => setDateRange(e.target.value)}
              className="bg-transparent text-xs font-bold text-slate-700 focus:outline-none cursor-pointer"
            >
              <option value="this_month">This Month</option>
              <option value="today">Today</option>
              <option value="this_week">This Week</option>
              <option value="last_30_days">Last 30 Days</option>
              <option value="this_quarter">This Quarter</option>
              <option value="this_year">This Year</option>
              <option value="all">All Time</option>
            </select>
          </div>

          <button
            onClick={fetchDashboardData}
            disabled={refreshing}
            className="px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition flex items-center space-x-1.5"
            title="Refresh metrics from database"
          >
            <span className={refreshing ? 'animate-spin' : ''}>🔄</span>
            <span>{refreshing ? 'Syncing...' : 'Sync'}</span>
          </button>
        </div>
      </div>

      {/* 6 Key Performance Indicators */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
        {/* 1. Total Leads */}
        <div className="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm flex flex-col justify-between relative overflow-hidden group hover:border-slate-300 transition">
          <div className="flex justify-between items-start">
            <p className="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Leads</p>
            <div className="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-sm font-bold">
              👥
            </div>
          </div>
          <div className="mt-3">
            <p className="text-2xl font-black text-slate-900">{data?.kpis?.total_leads?.value || 0}</p>
            <p className="text-[11px] font-semibold mt-1 flex items-center space-x-1">
              <span className={data?.kpis?.total_leads?.is_positive ? 'text-emerald-600' : 'text-slate-500'}>
                {data?.kpis?.total_leads?.change}
              </span>
              <span className="text-slate-400 font-normal">vs prev period</span>
            </p>
          </div>
        </div>

        {/* 2. Open Deals */}
        <div className="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm flex flex-col justify-between relative overflow-hidden group hover:border-slate-300 transition">
          <div className="flex justify-between items-start">
            <p className="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Open Deals</p>
            <div className="w-8 h-8 rounded-xl bg-cyan-50 text-cyan-600 flex items-center justify-center text-sm font-bold">
              💼
            </div>
          </div>
          <div className="mt-3">
            <p className="text-2xl font-black text-slate-900">{data?.kpis?.open_deals?.value || 0}</p>
            <p className="text-[11px] font-semibold mt-1 flex items-center space-x-1">
              <span className={data?.kpis?.open_deals?.is_positive ? 'text-emerald-600' : 'text-slate-500'}>
                {data?.kpis?.open_deals?.change}
              </span>
              <span className="text-slate-400 font-normal">active pipeline</span>
            </p>
          </div>
        </div>

        {/* 3. Won Deals */}
        <div className="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm flex flex-col justify-between relative overflow-hidden group hover:border-slate-300 transition">
          <div className="flex justify-between items-start">
            <p className="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Won Deals</p>
            <div className="w-8 h-8 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-sm font-bold">
              🏆
            </div>
          </div>
          <div className="mt-3">
            <p className="text-2xl font-black text-slate-900">{data?.kpis?.won_deals?.value || 0}</p>
            <p className="text-[11px] font-semibold mt-1 flex items-center space-x-1">
              <span className={data?.kpis?.won_deals?.is_positive ? 'text-emerald-600' : 'text-slate-500'}>
                {data?.kpis?.won_deals?.change}
              </span>
              <span className="text-slate-400 font-normal">closed won</span>
            </p>
          </div>
        </div>

        {/* 4. Revenue */}
        <div className="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm flex flex-col justify-between relative overflow-hidden group hover:border-slate-300 transition">
          <div className="flex justify-between items-start">
            <p className="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Won Revenue</p>
            <div className="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm font-bold">
              💵
            </div>
          </div>
          <div className="mt-3">
            <p className="text-2xl font-black text-emerald-600">
              ${(data?.kpis?.revenue?.value || 0).toLocaleString()}
            </p>
            <p className="text-[11px] font-semibold mt-1 flex items-center space-x-1">
              <span className={data?.kpis?.revenue?.is_positive ? 'text-emerald-600' : 'text-slate-500'}>
                {data?.kpis?.revenue?.change}
              </span>
              <span className="text-slate-400 font-normal">realized</span>
            </p>
          </div>
        </div>

        {/* 5. Conversion Rate */}
        <div className="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm flex flex-col justify-between relative overflow-hidden group hover:border-slate-300 transition">
          <div className="flex justify-between items-start">
            <p className="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Win Rate</p>
            <div className="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-sm font-bold">
              🎯
            </div>
          </div>
          <div className="mt-3">
            <p className="text-2xl font-black text-amber-600">{data?.kpis?.conversion_rate?.value || 0}%</p>
            <p className="text-[11px] text-slate-400 font-medium mt-1">
              {data?.kpis?.conversion_rate?.label || 'Pipeline efficiency'}
            </p>
          </div>
        </div>

        {/* 6. Pending Tasks */}
        <div className="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm flex flex-col justify-between relative overflow-hidden group hover:border-slate-300 transition">
          <div className="flex justify-between items-start">
            <p className="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Pending Tasks</p>
            <div className="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm font-bold">
              ✓
            </div>
          </div>
          <div className="mt-3">
            <p className="text-2xl font-black text-slate-900">{data?.kpis?.pending_tasks?.value || 0}</p>
            <p className="text-[11px] font-semibold mt-1">
              {data?.kpis?.pending_tasks?.overdue > 0 ? (
                <span className="text-red-500 font-bold">🚨 {data?.kpis?.pending_tasks?.overdue} Overdue</span>
              ) : (
                <span className="text-emerald-600">All on track</span>
              )}
            </p>
          </div>
        </div>
      </div>

      {/* Row 1 Charts: Leads Overview + Deals by Stage */}
      <div className="grid grid-cols-1 lg:grid-cols-12 gap-6">
        {/* Leads Overview Line Chart */}
        <div className="lg:col-span-7 bg-white p-6 rounded-2xl border border-slate-100 shadow-sm space-y-4">
          <div className="flex justify-between items-center">
            <div>
              <h3 className="text-sm font-black text-slate-900 tracking-tight">Leads Acquisition & Conversion</h3>
              <p className="text-xs text-slate-400">New leads incoming vs qualified conversions</p>
            </div>
            <Link to="/dashboard/leads" className="text-xs font-bold text-blue-600 hover:underline">
              View Leads →
            </Link>
          </div>
          <div className="h-64">
            <Line
              data={leadsChartData}
              options={{
                maintainAspectRatio: false,
                plugins: {
                  legend: {
                    position: 'top',
                    labels: { boxWidth: 12, usePointStyle: true, font: { weight: 'bold', size: 11 } },
                  },
                },
                scales: {
                  y: { beginAtZero: true, grid: { color: '#f1f5f9' } },
                  x: { grid: { display: false } },
                },
              }}
            />
          </div>
        </div>

        {/* Deals by Stage Chart */}
        <div className="lg:col-span-5 bg-white p-6 rounded-2xl border border-slate-100 shadow-sm space-y-4">
          <div className="flex justify-between items-center">
            <div>
              <h3 className="text-sm font-black text-slate-900 tracking-tight">Deals Pipeline by Stage</h3>
              <p className="text-xs text-slate-400">Distribution of commercial opportunities</p>
            </div>
            <Link to="/dashboard/deals" className="text-xs font-bold text-blue-600 hover:underline">
              Pipeline →
            </Link>
          </div>

          <div className="h-44">
            <Bar
              data={dealsChartData}
              options={{
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                  y: { beginAtZero: true, grid: { color: '#f1f5f9' } },
                  x: { grid: { display: false } },
                },
              }}
            />
          </div>

          {/* Stage breakdown progress bars */}
          <div className="space-y-2 pt-2 border-t border-slate-100 max-h-36 overflow-y-auto">
            {data?.deals_by_stage?.map((stg) => (
              <div key={stg.id} className="flex items-center justify-between text-xs">
                <div className="flex items-center space-x-2">
                  <span className="w-2.5 h-2.5 rounded-full" style={{ backgroundColor: stg.color || '#3b82f6' }}></span>
                  <span className="font-semibold text-slate-700">{stg.name}</span>
                </div>
                <div className="flex items-center space-x-2">
                  <span className="font-bold text-slate-900">{stg.count} deals</span>
                  <span className="text-slate-400 text-[11px]">({stg.percentage}%)</span>
                </div>
              </div>
            ))}
          </div>
        </div>
      </div>

      {/* Row 2: Revenue Trend + Sales Performance + Recent Activity */}
      <div className="grid grid-cols-1 lg:grid-cols-12 gap-6">
        {/* Revenue Trend Line Chart */}
        <div className="lg:col-span-4 bg-white p-6 rounded-2xl border border-slate-100 shadow-sm space-y-4">
          <div className="flex justify-between items-center">
            <div>
              <h3 className="text-sm font-black text-slate-900 tracking-tight">Monthly Revenue Trend</h3>
              <p className="text-xs text-slate-400">Closed-won revenue trajectory</p>
            </div>
          </div>
          <div className="h-64">
            <Line
              data={revenueChartData}
              options={{
                maintainAspectRatio: false,
                plugins: {
                  legend: { display: false },
                },
                scales: {
                  y: {
                    beginAtZero: true,
                    grid: { color: '#f1f5f9' },
                    ticks: {
                      callback: (val) => `$${val >= 1000 ? (val / 1000) + 'k' : val}`,
                    },
                  },
                  x: { grid: { display: false } },
                },
              }}
            />
          </div>
        </div>

        {/* Sales Performance Team Leaderboard (Admin Only) */}
        {data?.sales_performance && data.sales_performance.length > 0 && (
          <div className="lg:col-span-4 bg-white p-6 rounded-2xl border border-slate-100 shadow-sm space-y-4">
            <div className="flex justify-between items-center">
              <div>
                <div className="flex items-center space-x-2">
                  <h3 className="text-sm font-black text-slate-900 tracking-tight">Sales Performance</h3>
                  <span className="px-1.5 py-0.5 rounded bg-amber-50 text-amber-600 border border-amber-200 text-[9px] font-bold">Admin Only</span>
                </div>
                <p className="text-xs text-slate-400">Team member deal conversions</p>
              </div>
            </div>

            <div className="divide-y divide-slate-100">
              {data.sales_performance.map((member, idx) => (
                <div key={member.id} className="py-3 flex items-center justify-between">
                  <div className="flex items-center space-x-3">
                    <div className="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-xs">
                      {member.name.charAt(0)}
                    </div>
                    <div>
                      <p className="text-xs font-bold text-slate-900">{member.name}</p>
                      <p className="text-[11px] text-slate-400">{member.won_deals} Won Deals • {member.win_rate}% Win</p>
                    </div>
                  </div>
                  <div className="text-right">
                    <p className="text-xs font-black text-emerald-600">${member.won_revenue.toLocaleString()}</p>
                    <span className="text-[10px] font-bold text-slate-400">#{idx + 1} Rank</span>
                  </div>
                </div>
              ))}
            </div>
          </div>
        )}

        {/* Recent Activities Feed */}
        <div className={`${data?.sales_performance && data.sales_performance.length > 0 ? 'lg:col-span-4' : 'lg:col-span-8'} bg-white p-6 rounded-2xl border border-slate-100 shadow-sm space-y-4`}>
          <div className="flex justify-between items-center">
            <div>
              <h3 className="text-sm font-black text-slate-900 tracking-tight">Recent CRM Activities</h3>
              <p className="text-xs text-slate-400">Real-time audit & pipeline movements</p>
            </div>
          </div>

          <div className="space-y-3 max-h-72 overflow-y-auto pr-1">
            {(!data?.recent_activities || data.recent_activities.length === 0) ? (
              <p className="text-xs text-slate-400 py-8 text-center italic">No recent activities recorded</p>
            ) : (
              data.recent_activities.map((act) => (
                <div key={act.id} className="flex items-start space-x-3 text-xs">
                  <div className={`w-7 h-7 rounded-lg border flex items-center justify-center flex-shrink-0 font-bold ${getActivityBg(act.color)}`}>
                    {getActivityIcon(act.icon)}
                  </div>
                  <div className="flex-1 overflow-hidden">
                    <p className="text-slate-800 font-semibold line-clamp-1">{act.title}</p>
                    <p className="text-slate-500 text-[11px] line-clamp-1">{act.text}</p>
                    <p className="text-slate-400 text-[10px] mt-0.5">{act.time} by {act.user_name}</p>
                  </div>
                </div>
              ))
            )}
          </div>
        </div>
      </div>
    </div>
  );
}
