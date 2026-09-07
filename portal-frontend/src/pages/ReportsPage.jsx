import React, { useState, useEffect, useCallback } from 'react';
import { api } from '../services/api';
import { Line, Bar, Doughnut } from 'react-chartjs-2';
import {
  Chart as ChartJS,
  ArcElement,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  Title,
  Tooltip,
  Legend,
  Filler,
} from 'chart.js';

ChartJS.register(
  ArcElement,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  Title,
  Tooltip,
  Legend,
  Filler
);

export default function ReportsPage() {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [activeReportTab, setActiveReportTab] = useState('Overview'); // Overview, Leads, Deals, Revenue, Tasks, Sales Performance, Lead Sources, Pipeline
  
  // Filters
  const [filters, setFilters] = useState({
    range: 'all',
    user_id: '',
    role_id: '',
    source_id: '',
    stage_id: '',
    status: 'All',
  });

  const [options, setOptions] = useState({
    users: [],
    roles: [],
    sources: [],
    stages: [],
    statuses: [],
  });

  const fetchReports = useCallback(async () => {
    try {
      setLoading(true);
      const params = {
        range: filters.range !== 'all' ? filters.range : undefined,
        user_id: filters.user_id || undefined,
        role_id: filters.role_id || undefined,
        source_id: filters.source_id || undefined,
        stage_id: filters.stage_id || undefined,
        status: filters.status !== 'All' ? filters.status : undefined,
      };

      const res = await api.reports.get(params);
      if (res.data?.data) {
        setData(res.data.data);
        if (res.data.data.options) {
          setOptions(res.data.data.options);
        }
      }
    } catch (err) {
      console.error('Failed to load reports data:', err);
    } finally {
      setLoading(false);
    }
  }, [filters]);

  useEffect(() => {
    fetchReports();
  }, [fetchReports]);

  const handleFilterChange = (name, value) => {
    setFilters((prev) => ({ ...prev, [name]: value }));
  };

  const handleResetFilters = () => {
    setFilters({
      range: 'all',
      user_id: '',
      role_id: '',
      source_id: '',
      stage_id: '',
      status: 'All',
    });
  };

  // 1. Deals by Stage Chart
  const stageColors = ['#3b82f6', '#06b6d4', '#10b981', '#f59e0b', '#8b5cf6', '#10b981', '#ef4444'];
  const dealsStageChartData = {
    labels: data?.deals?.by_stage?.map((s) => s.name) || [],
    datasets: [
      {
        label: 'Deals Count',
        data: data?.deals?.by_stage?.map((s) => s.count) || [],
        backgroundColor: data?.deals?.by_stage?.map((s, i) => s.color || stageColors[i % stageColors.length]) || stageColors,
        borderRadius: 8,
      },
    ],
  };

  // 2. Revenue Trend Chart
  const revenueTrendChartData = {
    labels: data?.trends?.labels || [],
    datasets: [
      {
        label: 'Won Revenue ($)',
        data: data?.trends?.revenue || [],
        borderColor: '#10b981',
        backgroundColor: 'rgba(16, 185, 129, 0.12)',
        fill: true,
        tension: 0.35,
        pointBackgroundColor: '#10b981',
        pointRadius: 4,
      },
      {
        label: 'Deals Created',
        data: data?.trends?.deals_count || [],
        borderColor: '#3b82f6',
        backgroundColor: 'rgba(59, 130, 246, 0.08)',
        fill: false,
        tension: 0.35,
        pointBackgroundColor: '#3b82f6',
        yAxisID: 'y1',
        pointRadius: 4,
      },
    ],
  };

  // 3. Lead Sources Doughnut Chart
  const sourcePalette = ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#06b6d4', '#64748b'];
  const leadSourcesChartData = {
    labels: data?.leads?.by_source?.map((s) => s.name) || [],
    datasets: [
      {
        data: data?.leads?.by_source?.map((s) => s.count) || [],
        backgroundColor: sourcePalette,
        borderWidth: 2,
        borderColor: '#ffffff',
      },
    ],
  };

  // 4. Tasks by Priority Bar Chart
  const tasksPriorityChartData = {
    labels: data?.tasks?.by_priority?.map((p) => p.priority) || [],
    datasets: [
      {
        label: 'Tasks Count',
        data: data?.tasks?.by_priority?.map((p) => p.count) || [],
        backgroundColor: data?.tasks?.by_priority?.map((p) => p.color) || ['#ef4444', '#f59e0b', '#3b82f6', '#64748b'],
        borderRadius: 6,
      },
    ],
  };

  const reportTabs = [
    { key: 'Overview', label: '📊 All Metrics' },
    { key: 'Leads', label: '👥 Leads Analysis' },
    { key: 'Deals', label: '💼 Deals & Pipeline' },
    { key: 'Revenue', label: '💵 Revenue Forecast' },
    { key: 'Tasks', label: '✓ Tasks Velocity' },
    { key: 'Sales Performance', label: '🏆 Rep Leaderboard' },
    { key: 'Lead Sources', label: '🌐 Channels & Sources' },
  ];

  return (
    <div className="p-6 md:p-8 space-y-6">
      {/* Header */}
      <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <div className="flex items-center space-x-3">
            <h1 className="text-2xl font-black text-slate-900 tracking-tight flex items-center">
              <span className="mr-2.5 text-2xl">📈</span> Advanced CRM Analytics & Reports
            </h1>
            <span className="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
              Database Sync
            </span>
          </div>
          <p className="text-slate-500 text-xs mt-1">
            Real-time multi-dimensional reports across lead conversion, pipeline stages, closed revenue, and sales productivity.
          </p>
        </div>

        <div className="flex items-center space-x-2">
          <button
            onClick={() => window.print()}
            className="px-3.5 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 rounded-xl text-xs font-bold transition shadow-sm flex items-center space-x-1.5"
          >
            <span>🖨️ Export / Print</span>
          </button>
          <button
            onClick={fetchReports}
            className="px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition shadow-md shadow-blue-500/20 flex items-center space-x-1.5"
          >
            <span>🔄 Refresh</span>
          </button>
        </div>
      </div>

      {/* Multi-Dimensional Filter Bar */}
      <div className="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm space-y-3">
        <div className="flex items-center justify-between">
          <p className="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center space-x-1.5">
            <span>⚙️</span>
            <span>Report Filters & Dimension Controls</span>
          </p>
          {(filters.range !== 'all' ||
            filters.user_id ||
            filters.role_id ||
            filters.source_id ||
            filters.stage_id ||
            filters.status !== 'All') && (
            <button
              onClick={handleResetFilters}
              className="text-xs font-bold text-red-600 hover:underline flex items-center space-x-1"
            >
              <span>✕ Reset Filters</span>
            </button>
          )}
        </div>

        <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 pt-1">
          {/* 1. Date Range */}
          <div>
            <label className="block text-[11px] font-bold text-slate-600 mb-1">Date Interval</label>
            <select
              value={filters.range}
              onChange={(e) => handleFilterChange('range', e.target.value)}
              className="w-full px-2.5 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="all">All Time</option>
              <option value="today">Today</option>
              <option value="this_week">This Week</option>
              <option value="this_month">This Month</option>
              <option value="last_30_days">Last 30 Days</option>
              <option value="this_quarter">This Quarter</option>
              <option value="this_year">This Year</option>
            </select>
          </div>

          {/* 2. User / Sales Rep */}
          <div>
            <label className="block text-[11px] font-bold text-slate-600 mb-1">Owner / Rep</label>
            <select
              value={filters.user_id}
              onChange={(e) => handleFilterChange('user_id', e.target.value)}
              className="w-full px-2.5 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="">All Team Members</option>
              {options.users?.map((u) => (
                <option key={u.id} value={u.id}>
                  {u.name}
                </option>
              ))}
            </select>
          </div>

          {/* 3. Team / Role */}
          <div>
            <label className="block text-[11px] font-bold text-slate-600 mb-1">Team Role</label>
            <select
              value={filters.role_id}
              onChange={(e) => handleFilterChange('role_id', e.target.value)}
              className="w-full px-2.5 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="">All Teams / Roles</option>
              {options.roles?.map((r) => (
                <option key={r.id} value={r.id}>
                  {r.name}
                </option>
              ))}
            </select>
          </div>

          {/* 4. Lead Source */}
          <div>
            <label className="block text-[11px] font-bold text-slate-600 mb-1">Lead Source</label>
            <select
              value={filters.source_id}
              onChange={(e) => handleFilterChange('source_id', e.target.value)}
              className="w-full px-2.5 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="">All Sources</option>
              {options.sources?.map((s) => (
                <option key={s.id} value={s.id}>
                  {s.name}
                </option>
              ))}
            </select>
          </div>

          {/* 5. Deal Stage */}
          <div>
            <label className="block text-[11px] font-bold text-slate-600 mb-1">Deal Stage</label>
            <select
              value={filters.stage_id}
              onChange={(e) => handleFilterChange('stage_id', e.target.value)}
              className="w-full px-2.5 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="">All 7 Stages</option>
              {options.stages?.map((st) => (
                <option key={st.id} value={st.id}>
                  {st.name}
                </option>
              ))}
            </select>
          </div>

          {/* 6. Status */}
          <div>
            <label className="block text-[11px] font-bold text-slate-600 mb-1">Lifecycle Status</label>
            <select
              value={filters.status}
              onChange={(e) => handleFilterChange('status', e.target.value)}
              className="w-full px-2.5 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="All">All Statuses</option>
              <option value="Open">Open</option>
              <option value="Won">Won</option>
              <option value="Lost">Lost</option>
              <option value="Pending">Pending</option>
              <option value="In Progress">In Progress</option>
              <option value="Completed">Completed</option>
            </select>
          </div>
        </div>
      </div>

      {/* Category Tabs */}
      <div className="flex items-center space-x-1.5 overflow-x-auto pb-1">
        {reportTabs.map((tab) => (
          <button
            key={tab.key}
            onClick={() => setActiveReportTab(tab.key)}
            className={`px-4 py-2 rounded-xl text-xs font-bold transition flex-shrink-0 ${
              activeReportTab === tab.key
                ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20'
                : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-100'
            }`}
          >
            {tab.label}
          </button>
        ))}
      </div>

      {loading && !data ? (
        <div className="py-24 text-center">
          <div className="inline-block w-9 h-9 border-4 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
          <p className="mt-3 text-xs font-bold text-slate-500">Querying database reports and aggregate dimensions...</p>
        </div>
      ) : (
        <div className="space-y-6">
          {/* KPI Snapshot Row */}
          <div className="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
            <div className="p-4 bg-white rounded-2xl border border-slate-100 shadow-sm">
              <p className="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Leads</p>
              <p className="text-2xl font-black text-slate-900 mt-1">{data?.leads?.total_leads || 0}</p>
              <p className="text-[11px] text-emerald-600 font-semibold mt-0.5">{data?.leads?.conversion_rate || 0}% Converted</p>
            </div>

            <div className="p-4 bg-white rounded-2xl border border-slate-100 shadow-sm">
              <p className="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Won Revenue</p>
              <p className="text-2xl font-black text-emerald-600 mt-1">${(data?.deals?.won_revenue || 0).toLocaleString()}</p>
              <p className="text-[11px] text-slate-400 font-medium mt-0.5">{data?.deals?.won_deals || 0} Deals closed</p>
            </div>

            <div className="p-4 bg-white rounded-2xl border border-slate-100 shadow-sm">
              <p className="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Pipeline Value</p>
              <p className="text-2xl font-black text-blue-600 mt-1">${(data?.deals?.pipeline_value || 0).toLocaleString()}</p>
              <p className="text-[11px] text-slate-400 font-medium mt-0.5">{data?.deals?.open_deals || 0} Open deals</p>
            </div>

            <div className="p-4 bg-white rounded-2xl border border-slate-100 shadow-sm">
              <p className="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Win Rate</p>
              <p className="text-2xl font-black text-amber-600 mt-1">{data?.deals?.win_rate || 0}%</p>
              <p className="text-[11px] text-slate-400 font-medium mt-0.5">Won vs Lost</p>
            </div>

            <div className="p-4 bg-white rounded-2xl border border-slate-100 shadow-sm">
              <p className="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Avg Deal Size</p>
              <p className="text-2xl font-black text-purple-600 mt-1">${(data?.deals?.avg_deal_size || 0).toLocaleString()}</p>
              <p className="text-[11px] text-slate-400 font-medium mt-0.5">Across opportunities</p>
            </div>

            <div className="p-4 bg-white rounded-2xl border border-slate-100 shadow-sm">
              <p className="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Task Completion</p>
              <p className="text-2xl font-black text-slate-900 mt-1">{data?.tasks?.completion_rate || 0}%</p>
              <p className="text-[11px] text-slate-400 font-medium mt-0.5">{data?.tasks?.completed_tasks || 0} / {data?.tasks?.total_tasks || 0} done</p>
            </div>
          </div>

          {/* SECTION 1: DEALS & REVENUE CHARTS */}
          {(activeReportTab === 'Overview' || activeReportTab === 'Deals' || activeReportTab === 'Revenue' || activeReportTab === 'Pipeline') && (
            <div className="grid grid-cols-1 lg:grid-cols-12 gap-6">
              {/* Monthly Revenue Trend Line Chart */}
              <div className="lg:col-span-7 bg-white p-6 rounded-2xl border border-slate-100 shadow-sm space-y-4">
                <div className="flex justify-between items-center">
                  <div>
                    <h3 className="text-sm font-black text-slate-900 tracking-tight">Revenue Progression & Deal Creation Trend</h3>
                    <p className="text-xs text-slate-400">Monthly realized revenue vs total new deal count</p>
                  </div>
                </div>
                <div className="h-64">
                  <Line
                    data={revenueTrendChartData}
                    options={{
                      maintainAspectRatio: false,
                      plugins: {
                        legend: { position: 'top', labels: { boxWidth: 12, usePointStyle: true } },
                      },
                      scales: {
                        y: {
                          beginAtZero: true,
                          grid: { color: '#f1f5f9' },
                          ticks: { callback: (v) => `$${v >= 1000 ? v / 1000 + 'k' : v}` },
                        },
                        y1: {
                          position: 'right',
                          beginAtZero: true,
                          grid: { display: false },
                        },
                        x: { grid: { display: false } },
                      },
                    }}
                  />
                </div>
              </div>

              {/* Deals by Stage Distribution */}
              <div className="lg:col-span-5 bg-white p-6 rounded-2xl border border-slate-100 shadow-sm space-y-4">
                <div className="flex justify-between items-center">
                  <div>
                    <h3 className="text-sm font-black text-slate-900 tracking-tight">Pipeline Breakdown by Stage</h3>
                    <p className="text-xs text-slate-400">Volume and value distribution across 7 stages</p>
                  </div>
                </div>
                <div className="h-44">
                  <Bar
                    data={dealsStageChartData}
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

                <div className="space-y-2 pt-2 border-t border-slate-100 max-h-36 overflow-y-auto">
                  {data?.deals?.by_stage?.map((stg) => (
                    <div key={stg.id} className="flex items-center justify-between text-xs">
                      <div className="flex items-center space-x-2">
                        <span className="w-2.5 h-2.5 rounded-full" style={{ backgroundColor: stg.color || '#3b82f6' }}></span>
                        <span className="font-semibold text-slate-700">{stg.name}</span>
                      </div>
                      <div className="flex items-center space-x-3">
                        <span className="font-semibold text-slate-500">${stg.value.toLocaleString()}</span>
                        <span className="font-bold text-slate-900">{stg.count} deals ({stg.percentage}%)</span>
                      </div>
                    </div>
                  ))}
                </div>
              </div>
            </div>
          )}

          {/* SECTION 2: LEADS & SOURCES REPORT */}
          {(activeReportTab === 'Overview' || activeReportTab === 'Leads' || activeReportTab === 'Lead Sources') && (
            <div className="grid grid-cols-1 lg:grid-cols-12 gap-6">
              {/* Lead Sources Breakdown */}
              <div className="lg:col-span-6 bg-white p-6 rounded-2xl border border-slate-100 shadow-sm space-y-4">
                <div className="flex justify-between items-center">
                  <div>
                    <h3 className="text-sm font-black text-slate-900 tracking-tight">Lead Acquisition Channels & Source ROI</h3>
                    <p className="text-xs text-slate-400">Volume and realized revenue generated per lead channel</p>
                  </div>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-12 gap-4 items-center">
                  <div className="md:col-span-5 h-48 flex items-center justify-center">
                    <Doughnut data={leadSourcesChartData} options={{ maintainAspectRatio: false, plugins: { legend: { display: false } } }} />
                  </div>
                  <div className="md:col-span-7 space-y-2 max-h-48 overflow-y-auto">
                    {data?.leads?.by_source?.map((src, i) => (
                      <div key={src.id} className="flex items-center justify-between text-xs p-2 rounded-lg bg-slate-50 border border-slate-100">
                        <div className="flex items-center space-x-2">
                          <span className="w-2.5 h-2.5 rounded-full" style={{ backgroundColor: sourcePalette[i % sourcePalette.length] }}></span>
                          <span className="font-bold text-slate-800">{src.name}</span>
                        </div>
                        <div className="text-right">
                          <p className="font-bold text-slate-900">{src.count} leads ({src.percentage}%)</p>
                          <p className="text-[10px] text-emerald-600 font-semibold">${src.revenue.toLocaleString()} rev</p>
                        </div>
                      </div>
                    ))}
                  </div>
                </div>
              </div>

              {/* Leads by Status */}
              <div className="lg:col-span-6 bg-white p-6 rounded-2xl border border-slate-100 shadow-sm space-y-4">
                <div className="flex justify-between items-center">
                  <div>
                    <h3 className="text-sm font-black text-slate-900 tracking-tight">Lead Lifecycle Statuses</h3>
                    <p className="text-xs text-slate-400">Progression from discovery to qualification</p>
                  </div>
                </div>

                <div className="space-y-3">
                  {data?.leads?.by_status?.map((st) => (
                    <div key={st.id} className="space-y-1">
                      <div className="flex justify-between text-xs font-semibold">
                        <span className="text-slate-700 flex items-center space-x-1.5">
                          <span className="w-2 h-2 rounded-full" style={{ backgroundColor: st.color || '#3b82f6' }}></span>
                          <span>{st.name}</span>
                        </span>
                        <span className="text-slate-900">{st.count} leads ({st.percentage}%)</span>
                      </div>
                      <div className="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                        <div
                          className="h-full rounded-full transition-all duration-500"
                          style={{
                            width: `${st.percentage}%`,
                            backgroundColor: st.color || '#3b82f6',
                          }}
                        ></div>
                      </div>
                    </div>
                  ))}
                </div>
              </div>
            </div>
          )}

          {/* SECTION 3: SALES PERFORMANCE & TASK PRODUCTIVITY */}
          {(activeReportTab === 'Overview' || activeReportTab === 'Sales Performance' || activeReportTab === 'Tasks') && (
            <div className="grid grid-cols-1 lg:grid-cols-12 gap-6">
              {/* Sales Team Performance Leaderboard */}
              <div className="lg:col-span-8 bg-white p-6 rounded-2xl border border-slate-100 shadow-sm space-y-4">
                <div className="flex justify-between items-center">
                  <div>
                    <h3 className="text-sm font-black text-slate-900 tracking-tight">Sales Team Performance Leaderboard</h3>
                    <p className="text-xs text-slate-400">Deals volume, closed revenue, and win rates by account executive</p>
                  </div>
                </div>

                <div className="overflow-x-auto">
                  <table className="w-full text-left text-xs">
                    <thead className="bg-slate-50 border-b border-slate-100 text-slate-500 font-bold uppercase tracking-wider">
                      <tr>
                        <th className="px-4 py-3">Team Member</th>
                        <th className="px-4 py-3">Deals Managed</th>
                        <th className="px-4 py-3">Won Deals</th>
                        <th className="px-4 py-3">Closed Revenue</th>
                        <th className="px-4 py-3">Win Rate</th>
                        <th className="px-4 py-3 text-right">Tasks Done</th>
                      </tr>
                    </thead>
                    <tbody className="divide-y divide-slate-100">
                      {data?.sales_performance?.map((rep, idx) => (
                        <tr key={rep.id} className="hover:bg-slate-50/80 transition">
                          <td className="px-4 py-3.5 flex items-center space-x-3">
                            <div className="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-xs flex-shrink-0">
                              {rep.name.charAt(0)}
                            </div>
                            <div>
                              <p className="font-bold text-slate-900">{rep.name}</p>
                              <p className="text-[11px] text-slate-400">{rep.email}</p>
                            </div>
                          </td>
                          <td className="px-4 py-3.5 font-semibold text-slate-700">{rep.total_deals} Deals</td>
                          <td className="px-4 py-3.5 font-bold text-slate-900">{rep.won_deals} Won</td>
                          <td className="px-4 py-3.5 font-black text-emerald-600">${rep.won_revenue.toLocaleString()}</td>
                          <td className="px-4 py-3.5">
                            <span className="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                              {rep.win_rate}%
                            </span>
                          </td>
                          <td className="px-4 py-3.5 text-right font-medium text-slate-700">{rep.completed_tasks} Tasks</td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              </div>

              {/* Tasks Productivity Breakdown */}
              <div className="lg:col-span-4 bg-white p-6 rounded-2xl border border-slate-100 shadow-sm space-y-4">
                <div className="flex justify-between items-center">
                  <div>
                    <h3 className="text-sm font-black text-slate-900 tracking-tight">Tasks by Priority</h3>
                    <p className="text-xs text-slate-400">Action items workload breakdown</p>
                  </div>
                </div>

                <div className="h-44">
                  <Bar
                    data={tasksPriorityChartData}
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

                <div className="grid grid-cols-2 gap-2 pt-2 border-t border-slate-100 text-xs">
                  <div className="p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                    <p className="text-[10px] font-bold text-slate-400 uppercase">Pending</p>
                    <p className="text-lg font-black text-slate-800">{data?.tasks?.pending_tasks || 0}</p>
                  </div>
                  <div className="p-2.5 rounded-xl bg-purple-50 border border-purple-100">
                    <p className="text-[10px] font-bold text-purple-700 uppercase">In Progress</p>
                    <p className="text-lg font-black text-purple-900">{data?.tasks?.in_progress_tasks || 0}</p>
                  </div>
                  <div className="p-2.5 rounded-xl bg-emerald-50 border border-emerald-100">
                    <p className="text-[10px] font-bold text-emerald-700 uppercase">Completed</p>
                    <p className="text-lg font-black text-emerald-900">{data?.tasks?.completed_tasks || 0}</p>
                  </div>
                  <div className="p-2.5 rounded-xl bg-red-50 border border-red-100">
                    <p className="text-[10px] font-bold text-red-700 uppercase">Overdue</p>
                    <p className="text-lg font-black text-red-900">{data?.tasks?.overdue_tasks || 0}</p>
                  </div>
                </div>
              </div>
            </div>
          )}
        </div>
      )}
    </div>
  );
}
