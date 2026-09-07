import React, { useState, useEffect, useCallback } from 'react';
import { Link } from 'react-router-dom';
import { api } from '../services/api';
import DealModal from '../components/DealModal';

export default function DealsList() {
  const [deals, setDeals] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [viewMode, setViewMode] = useState('kanban'); // 'kanban' | 'table'

  // Summary Metrics & Metadata
  const [stats, setStats] = useState({
    total_deals: 0,
    open_deals: 0,
    won_deals: 0,
    lost_deals: 0,
    total_pipeline_value: 0,
    won_revenue: 0,
    win_rate: 0,
  });

  const [stages, setStages] = useState([]);
  const [companies, setCompanies] = useState([]);
  const [contacts, setContacts] = useState([]);
  const [leads, setLeads] = useState([]);
  const [owners, setOwners] = useState([]);

  // Query & Filters
  const [search, setSearch] = useState('');
  const [stageFilter, setStageFilter] = useState('');
  const [statusFilter, setStatusFilter] = useState('');
  const [ownerFilter, setOwnerFilter] = useState('');
  const [companyFilter, setCompanyFilter] = useState('');
  const [sortBy, setSortBy] = useState('created_at');
  const [sortDirection, setSortDirection] = useState('desc');
  const [page, setPage] = useState(1);
  const [perPage, setPerPage] = useState(viewMode === 'kanban' ? 100 : 15);
  const [paginationMeta, setPaginationMeta] = useState({
    current_page: 1,
    last_page: 1,
    total: 0,
  });

  // Selection & Modal States
  const [selectedIds, setSelectedIds] = useState([]);
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [dealToEdit, setDealToEdit] = useState(null);
  const [initialStageId, setInitialStageId] = useState(null);
  const [actionLoading, setActionLoading] = useState(false);
  const [draggedDealId, setDraggedDealId] = useState(null);

  // Fetch metadata & aggregate stats
  const fetchMetadata = async () => {
    try {
      const res = await api.deals.metadata();
      if (res.data.success) {
        setStats(res.data.stats || {});
        setStages(res.data.stages || []);
        setCompanies(res.data.options?.companies || []);
        setContacts(res.data.options?.contacts || []);
        setLeads(res.data.options?.leads || []);
        setOwners(res.data.options?.owners || []);
      }
    } catch (err) {
      console.error('Failed to load deals metadata:', err);
    }
  };

  // Fetch Deals
  const fetchDeals = useCallback(async () => {
    setLoading(true);
    setError('');
    try {
      const params = {
        page,
        per_page: viewMode === 'kanban' ? 100 : perPage,
        sort_by: sortBy,
        sort_direction: sortDirection,
      };

      if (search) params.search = search;
      if (stageFilter) params.deal_stage_id = stageFilter;
      if (statusFilter) params.status = statusFilter;
      if (ownerFilter) params.owner_id = ownerFilter;
      if (companyFilter) params.company_id = companyFilter;

      const res = await api.deals.list(params);
      if (res.data.success) {
        setDeals(res.data.data || []);
        if (res.data.meta) {
          setPaginationMeta(res.data.meta);
        }
      }
    } catch (err) {
      setError(err.response?.data?.message || 'Failed to load deals from server.');
    } finally {
      setLoading(false);
    }
  }, [page, perPage, sortBy, sortDirection, search, stageFilter, statusFilter, ownerFilter, companyFilter, viewMode]);

  useEffect(() => {
    fetchMetadata();
  }, []);

  useEffect(() => {
    fetchDeals();
  }, [fetchDeals]);

  // Stage change handler (Drag-and-drop or quick click)
  const handleUpdateStage = async (dealId, targetStageId, newStatus = null) => {
    try {
      // Optimistic UI Update
      setDeals((prev) =>
        prev.map((d) => {
          if (d.id === dealId) {
            const matchedStage = stages.find((s) => s.id === targetStageId);
            return {
              ...d,
              deal_stage_id: targetStageId,
              stage: matchedStage || d.stage,
              status: newStatus || (matchedStage?.name?.toLowerCase() === 'won' ? 'won' : matchedStage?.name?.toLowerCase() === 'lost' ? 'lost' : 'open'),
            };
          }
          return d;
        })
      );

      const payload = { deal_stage_id: targetStageId };
      if (newStatus) payload.status = newStatus;

      await api.deals.updateStage(dealId, payload);
      fetchMetadata();
    } catch (err) {
      console.error('Error updating stage:', err);
      fetchDeals();
    }
  };

  // Drag and Drop Handlers
  const handleDragStart = (e, dealId) => {
    setDraggedDealId(dealId);
    e.dataTransfer.setData('text/plain', String(dealId));
  };

  const handleDragOver = (e) => {
    e.preventDefault();
  };

  const handleDrop = (e, targetStageId) => {
    e.preventDefault();
    const dealId = parseInt(e.dataTransfer.getData('text/plain') || String(draggedDealId), 10);
    if (dealId) {
      handleUpdateStage(dealId, targetStageId);
    }
    setDraggedDealId(null);
  };

  // Selection & Bulk Operations
  const handleSelectAll = (e) => {
    if (e.target.checked) {
      setSelectedIds(deals.map((d) => d.id));
    } else {
      setSelectedIds([]);
    }
  };

  const handleSelectOne = (id) => {
    setSelectedIds((prev) => (prev.includes(id) ? prev.filter((item) => item !== id) : [...prev, id]));
  };

  const handleDeleteDeal = async (id, title) => {
    if (!window.confirm(`Are you sure you want to delete deal "${title}"?`)) return;
    setActionLoading(true);
    try {
      await api.deals.delete(id);
      setSelectedIds((prev) => prev.filter((item) => item !== id));
      fetchDeals();
      fetchMetadata();
    } catch (err) {
      alert(err.response?.data?.message || 'Failed to delete deal.');
    } finally {
      setActionLoading(false);
    }
  };

  const handleBulkDelete = async () => {
    if (!selectedIds.length) return;
    if (!window.confirm(`Are you sure you want to delete ${selectedIds.length} selected deals?`)) return;
    setActionLoading(true);
    try {
      await api.deals.bulkDelete(selectedIds);
      setSelectedIds([]);
      fetchDeals();
      fetchMetadata();
    } catch (err) {
      alert(err.response?.data?.message || 'Failed to bulk delete deals.');
    } finally {
      setActionLoading(false);
    }
  };

  // Export to CSV
  const handleExportCSV = () => {
    if (!deals.length) {
      alert('No deal records available to export.');
      return;
    }

    const headers = ['ID', 'Deal Title', 'Value ($)', 'Stage', 'Status', 'Probability (%)', 'Close Date', 'Company', 'Contact', 'Owner'];
    const rows = deals.map((d) => [
      d.id,
      `"${d.name || d.title || ''}"`,
      d.value || 0,
      `"${d.stage?.name || ''}"`,
      `"${d.status || 'open'}"`,
      d.probability || 0,
      `"${d.close_date || ''}"`,
      `"${d.company?.name || ''}"`,
      `"${d.contact?.name || ''}"`,
      `"${d.owner?.name || ''}"`,
    ]);

    const csvContent = 'data:text/csv;charset=utf-8,' + [headers.join(','), ...rows.map((e) => e.join(','))].join('\n');
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement('a');
    link.setAttribute('href', encodedUri);
    link.setAttribute('download', `deals_pipeline_${new Date().toISOString().slice(0, 10)}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  };

  const getStageColor = (stageName) => {
    switch (stageName?.toLowerCase()) {
      case 'new':
        return 'border-blue-500 bg-blue-50 text-blue-700';
      case 'contacted':
        return 'border-cyan-500 bg-cyan-50 text-cyan-700';
      case 'qualified':
        return 'border-indigo-500 bg-indigo-50 text-indigo-700';
      case 'proposal':
        return 'border-purple-500 bg-purple-50 text-purple-700';
      case 'negotiation':
        return 'border-amber-500 bg-amber-50 text-amber-700';
      case 'won':
        return 'border-emerald-500 bg-emerald-50 text-emerald-700';
      case 'lost':
        return 'border-rose-500 bg-rose-50 text-rose-700';
      default:
        return 'border-gray-400 bg-gray-50 text-gray-700';
    }
  };

  return (
    <div className="p-6 space-y-6 max-w-7xl mx-auto">
      {/* Top Banner & Header */}
      <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <div className="flex items-center space-x-2">
            <span className="text-2xl">🤝</span>
            <h1 className="text-2xl font-black text-gray-900 tracking-tight">Deals Pipeline</h1>
            <span className="bg-indigo-50 text-indigo-700 text-xs font-bold px-2.5 py-0.5 rounded-full border border-indigo-200">
              {stats.total_deals || deals.length} Opportunities
            </span>
          </div>
          <p className="text-sm text-gray-500 mt-1">
            Track revenue, commercial proposals, and deal progressions through your pipeline.
          </p>
        </div>

        <div className="flex items-center space-x-3">
          {/* View Toggle */}
          <div className="bg-white border border-gray-200 p-1 rounded-xl shadow-xs flex items-center space-x-1">
            <button
              onClick={() => {
                setViewMode('kanban');
                setPerPage(100);
              }}
              className={`px-3 py-1.5 text-xs font-bold rounded-lg transition flex items-center gap-1.5 ${viewMode === 'kanban' ? 'bg-indigo-600 text-white shadow-xs' : 'text-gray-600 hover:bg-gray-100'
                }`}
            >
              <span>📊</span> Kanban Pipeline
            </button>
            <button
              onClick={() => {
                setViewMode('table');
                setPerPage(15);
              }}
              className={`px-3 py-1.5 text-xs font-bold rounded-lg transition flex items-center gap-1.5 ${viewMode === 'table' ? 'bg-indigo-600 text-white shadow-xs' : 'text-gray-600 hover:bg-gray-100'
                }`}
            >
              <span>📋</span> Table View
            </button>
          </div>

          <button
            onClick={handleExportCSV}
            className="px-3.5 py-2 text-xs font-semibold text-gray-700 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 shadow-xs transition flex items-center"
          >
            <span className="mr-1.5">📥</span> Export
          </button>

          <button
            onClick={() => {
              setDealToEdit(null);
              setInitialStageId(stages.length > 0 ? stages[0].id : null);
              setIsModalOpen(true);
            }}
            className="px-4 py-2 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-lg shadow-blue-500/20 transition flex items-center"
          >
            <span className="mr-1.5 text-lg leading-none">+</span> Add Deal
          </button>
        </div>
      </div>

      {/* KPI Stats Grid */}
      <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div className="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm">
          <div className="flex items-center justify-between">
            <span className="text-xs font-bold text-gray-400 uppercase tracking-wider">Pipeline Value</span>
            <span className="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-sm font-bold">💰</span>
          </div>
          <p className="text-2xl font-black text-gray-900 mt-2">
            ${(stats.total_pipeline_value || 0).toLocaleString()}
          </p>
          <p className="text-xs text-blue-600 font-medium mt-1">{stats.open_deals || 0} active open deals</p>
        </div>

        <div className="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm">
          <div className="flex items-center justify-between">
            <span className="text-xs font-bold text-gray-400 uppercase tracking-wider">Won Revenue</span>
            <span className="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm font-bold">🏆</span>
          </div>
          <p className="text-2xl font-black text-emerald-600 mt-2">
            ${(stats.won_revenue || 0).toLocaleString()}
          </p>
          <p className="text-xs text-emerald-600 font-medium mt-1">{stats.won_deals || 0} closed won deals</p>
        </div>

        <div className="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm">
          <div className="flex items-center justify-between">
            <span className="text-xs font-bold text-gray-400 uppercase tracking-wider">Win Rate</span>
            <span className="w-8 h-8 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-sm font-bold">📈</span>
          </div>
          <p className="text-2xl font-black text-purple-600 mt-2">{stats.win_rate || 0}%</p>
          <p className="text-xs text-purple-600 font-medium mt-1">Closed-won performance</p>
        </div>

        <div className="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm">
          <div className="flex items-center justify-between">
            <span className="text-xs font-bold text-gray-400 uppercase tracking-wider">Lost Value</span>
            <span className="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-sm font-bold">📉</span>
          </div>
          <p className="text-2xl font-black text-rose-600 mt-2">{stats.lost_deals || 0} Deals</p>
          <p className="text-xs text-rose-600 font-medium mt-1">Archived or disqualified</p>
        </div>
      </div>

      {/* Filters & Control Bar */}
      <div className="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm space-y-3">
        <div className="grid grid-cols-1 md:grid-cols-12 gap-3">
          {/* Search */}
          <div className="md:col-span-4 relative">
            <input
              type="text"
              value={search}
              onChange={(e) => {
                setSearch(e.target.value);
                setPage(1);
              }}
              placeholder="Search by deal title, company, contact, notes..."
              className="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 bg-slate-50/50"
            />
            <svg
              className="w-4 h-4 text-gray-400 absolute left-3 top-1/2 transform -translate-y-1/2"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
          </div>

          {/* Stage Filter */}
          <div className="md:col-span-2">
            <select
              value={stageFilter}
              onChange={(e) => {
                setStageFilter(e.target.value);
                setPage(1);
              }}
              className="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
            >
              <option value="">All Stages</option>
              {stages.map((s) => (
                <option key={s.id} value={s.id}>
                  {s.name}
                </option>
              ))}
            </select>
          </div>

          {/* Status Filter */}
          <div className="md:col-span-2">
            <select
              value={statusFilter}
              onChange={(e) => {
                setStatusFilter(e.target.value);
                setPage(1);
              }}
              className="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
            >
              <option value="">All Statuses</option>
              <option value="open">🟢 Open</option>
              <option value="won">🏆 Won</option>
              <option value="lost">❌ Lost</option>
            </select>
          </div>

          {/* Owner Filter */}
          <div className="md:col-span-2">
            <select
              value={ownerFilter}
              onChange={(e) => {
                setOwnerFilter(e.target.value);
                setPage(1);
              }}
              className="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
            >
              <option value="">All Owners</option>
              {owners.map((u) => (
                <option key={u.id} value={u.id}>
                  {u.name}
                </option>
              ))}
            </select>
          </div>

          {/* Sort Control */}
          <div className="md:col-span-2 flex items-center space-x-1">
            <select
              value={sortBy}
              onChange={(e) => setSortBy(e.target.value)}
              className="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
            >
              <option value="created_at">Date Created</option>
              <option value="value">Deal Value</option>
              <option value="probability">Win %</option>
              <option value="close_date">Close Date</option>
              <option value="name">Deal Title</option>
            </select>
            <button
              onClick={() => setSortDirection((prev) => (prev === 'asc' ? 'desc' : 'asc'))}
              title={`Sort ${sortDirection === 'asc' ? 'Ascending' : 'Descending'}`}
              className="p-2 border border-gray-200 rounded-xl hover:bg-gray-100 transition text-gray-600 text-sm font-bold"
            >
              {sortDirection === 'asc' ? '↑' : '↓'}
            </button>
          </div>
        </div>

        {/* Bulk Action Bar (in Table View) */}
        {viewMode === 'table' && selectedIds.length > 0 && (
          <div className="flex items-center justify-between bg-indigo-50 border border-indigo-200 p-3 rounded-xl animate-fade-in">
            <span className="text-xs font-bold text-indigo-900">
              {selectedIds.length} deal{selectedIds.length > 1 ? 's' : ''} selected
            </span>
            <button
              onClick={handleBulkDelete}
              disabled={actionLoading}
              className="px-3 py-1.5 text-xs font-semibold text-rose-700 bg-rose-100 hover:bg-rose-200 rounded-lg transition"
            >
              Delete Selected
            </button>
          </div>
        )}
      </div>

      {/* VIEW 1: PIPELINE BOARD */}
      {viewMode === 'kanban' && (
        <div className="overflow-x-auto pb-4">
          <div className="flex items-start space-x-4 min-w-[1280px]">
            {stages.map((stage) => {
              const stageDeals = deals.filter((d) => d.deal_stage_id === stage.id);
              const stageTotal = stageDeals.reduce((sum, d) => sum + (parseFloat(d.value) || 0), 0);

              return (
                <div
                  key={stage.id}
                  onDragOver={handleDragOver}
                  onDrop={(e) => handleDrop(e, stage.id)}
                  className="w-72 bg-slate-100/80 rounded-2xl p-3 flex flex-col flex-shrink-0 min-h-[550px] border border-slate-200/60 shadow-xs"
                >
                  {/* Column Header */}
                  <div className="flex items-center justify-between pb-3 mb-3 border-b border-gray-200">
                    <div className="flex items-center space-x-2">
                      <span className={`w-3 h-3 rounded-full border-2 ${getStageColor(stage.name)}`}></span>
                      <h3 className="font-bold text-gray-900 text-sm">{stage.name}</h3>
                      <span className="px-2 py-0.5 rounded-full text-[11px] font-bold bg-white text-gray-700 shadow-xs">
                        {stageDeals.length}
                      </span>
                    </div>

                    <button
                      onClick={() => {
                        setDealToEdit(null);
                        setInitialStageId(stage.id);
                        setIsModalOpen(true);
                      }}
                      title="Add deal in this stage"
                      className="w-6 h-6 rounded-lg bg-white hover:bg-blue-600 hover:text-white text-gray-500 flex items-center justify-center font-bold text-xs shadow-xs transition"
                    >
                      +
                    </button>
                  </div>

                  {/* Stage Sum Value */}
                  <div className="px-1 pb-2.5 flex items-center justify-between text-[11px] font-semibold text-gray-500">
                    <span>Stage Value:</span>
                    <span className="font-bold text-gray-900">${stageTotal.toLocaleString()}</span>
                  </div>

                  {/* Deal Cards Stream */}
                  <div className="space-y-3 flex-1 overflow-y-auto">
                    {stageDeals.length === 0 ? (
                      <div className="p-6 text-center text-gray-400 border border-dashed border-gray-300 rounded-xl text-xs">
                        Drop deals here
                      </div>
                    ) : (
                      stageDeals.map((deal) => (
                        <div
                          key={deal.id}
                          draggable
                          onDragStart={(e) => handleDragStart(e, deal.id)}
                          className="bg-white p-4 rounded-xl border border-slate-200/80 shadow-sm hover:shadow-md hover:border-blue-300 transition cursor-grab active:cursor-grabbing group"
                        >
                          {/* Title & Value */}
                          <div className="flex items-start justify-between gap-2">
                            <Link
                              to={`/deals/${deal.id}`}
                              className="font-bold text-gray-900 hover:text-blue-600 text-sm leading-snug"
                            >
                              {deal.name || deal.title}
                            </Link>
                            <span className="font-extrabold text-emerald-600 text-sm flex-shrink-0">
                              ${parseFloat(deal.value || 0).toLocaleString()}
                            </span>
                          </div>

                          {/* Company / Contact */}
                          <div className="text-xs text-gray-500 mt-2 space-y-0.5">
                            {deal.company && (
                              <p className="truncate flex items-center gap-1 font-medium text-indigo-700">
                                <span>🏢</span> {deal.company.name}
                              </p>
                            )}
                            {deal.contact && (
                              <p className="truncate flex items-center gap-1 text-gray-600">
                                <span>👤</span> {deal.contact.name}
                              </p>
                            )}
                          </div>

                          {/* Probability Bar */}
                          <div className="mt-3">
                            <div className="flex justify-between text-[10px] font-semibold text-gray-400 mb-1">
                              <span>Win Probability</span>
                              <span className="text-indigo-600 font-bold">{deal.probability || 50}%</span>
                            </div>
                            <div className="w-full h-1.5 bg-gray-100 rounded-full overflow-hidden">
                              <div
                                className="h-full bg-indigo-600 rounded-full"
                                style={{ width: `${deal.probability || 50}%` }}
                              ></div>
                            </div>
                          </div>

                          {/* Footer Info & Quick Actions */}
                          <div className="mt-3 pt-3 border-t border-gray-100 flex items-center justify-between text-[11px]">
                            <span className="text-gray-400">
                              {deal.close_date ? `📅 ${new Date(deal.close_date).toLocaleDateString()}` : 'No date'}
                            </span>

                            {/* Quick Stage Progression Buttons */}
                            <div className="flex items-center space-x-1 opacity-80 group-hover:opacity-100 transition">
                              {/* 1-Click Won / Lost */}
                              {stage.name.toLowerCase() !== 'won' && (
                                <button
                                  onClick={() => {
                                    const wonStage = stages.find((s) => s.name.toLowerCase() === 'won');
                                    if (wonStage) handleUpdateStage(deal.id, wonStage.id, 'won');
                                  }}
                                  title="Mark as Won"
                                  className="px-1.5 py-0.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded text-[10px] font-bold transition border border-emerald-200"
                                >
                                  🏆
                                </button>
                              )}
                              {stage.name.toLowerCase() !== 'lost' && (
                                <button
                                  onClick={() => {
                                    const lostStage = stages.find((s) => s.name.toLowerCase() === 'lost');
                                    if (lostStage) handleUpdateStage(deal.id, lostStage.id, 'lost');
                                  }}
                                  title="Mark as Lost"
                                  className="px-1.5 py-0.5 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded text-[10px] font-bold transition border border-rose-200"
                                >
                                  ❌
                                </button>
                              )}
                              <button
                                onClick={() => {
                                  setDealToEdit(deal);
                                  setIsModalOpen(true);
                                }}
                                title="Edit Deal"
                                className="px-1.5 py-0.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded text-[10px] font-semibold transition"
                              >
                                ✏️
                              </button>
                            </div>
                          </div>
                        </div>
                      ))
                    )}
                  </div>
                </div>
              );
            })}
          </div>
        </div>
      )}

      {/* VIEW 2: TABLE LIST VIEW */}
      {viewMode === 'table' && (
        <div className="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
          <div className="overflow-x-auto">
            <table className="w-full text-left text-sm">
              <thead className="bg-slate-50 text-gray-400 text-[11px] font-bold uppercase tracking-wider border-b border-gray-100">
                <tr>
                  <th className="p-4 w-10 text-center">
                    <input
                      type="checkbox"
                      checked={deals.length > 0 && selectedIds.length === deals.length}
                      onChange={handleSelectAll}
                      className="w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500"
                    />
                  </th>
                  <th className="px-4 py-3">Opportunity / Title</th>
                  <th className="px-4 py-3">Value ($)</th>
                  <th className="px-4 py-3">Stage</th>
                  <th className="px-4 py-3">Status</th>
                  <th className="px-4 py-3">Company & Contact</th>
                  <th className="px-4 py-3">Close Date</th>
                  <th className="px-4 py-3">Owner</th>
                  <th className="px-4 py-3 text-right">Actions</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-100">
                {loading ? (
                  <tr>
                    <td colSpan="9" className="p-8 text-center text-gray-400">
                      Loading deals pipeline from server...
                    </td>
                  </tr>
                ) : deals.length === 0 ? (
                  <tr>
                    <td colSpan="9" className="p-12 text-center text-gray-400">
                      <span className="text-3xl block mb-2">🔍</span>
                      <p className="font-semibold text-gray-700">No deals found</p>
                      <button
                        onClick={() => {
                          setDealToEdit(null);
                          setIsModalOpen(true);
                        }}
                        className="mt-3 px-4 py-1.5 text-xs font-semibold text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-lg transition"
                      >
                        + Create First Opportunity
                      </button>
                    </td>
                  </tr>
                ) : (
                  deals.map((deal) => (
                    <tr key={deal.id} className="hover:bg-slate-50/70 transition">
                      <td className="p-4 text-center">
                        <input
                          type="checkbox"
                          checked={selectedIds.includes(deal.id)}
                          onChange={() => handleSelectOne(deal.id)}
                          className="w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500"
                        />
                      </td>

                      {/* Title */}
                      <td className="px-4 py-3">
                        <Link
                          to={`/deals/${deal.id}`}
                          className="font-bold text-gray-900 hover:text-blue-600 transition block text-sm"
                        >
                          {deal.name || deal.title}
                        </Link>
                        <p className="text-[11px] text-gray-400">Win Probability: {deal.probability || 50}%</p>
                      </td>

                      {/* Value */}
                      <td className="px-4 py-3 font-extrabold text-emerald-600 text-sm">
                        ${parseFloat(deal.value || 0).toLocaleString()}
                      </td>

                      {/* Stage */}
                      <td className="px-4 py-3">
                        <span
                          className={`inline-flex px-2.5 py-0.5 rounded-full text-xs font-bold border ${getStageColor(
                            deal.stage?.name
                          )}`}
                        >
                          {deal.stage?.name || 'New'}
                        </span>
                      </td>

                      {/* Status */}
                      <td className="px-4 py-3">
                        <span
                          className={`inline-flex px-2.5 py-0.5 rounded-full text-[11px] font-bold capitalize ${deal.status === 'won'
                              ? 'bg-emerald-100 text-emerald-800'
                              : deal.status === 'lost'
                                ? 'bg-rose-100 text-rose-800'
                                : 'bg-blue-50 text-blue-700'
                            }`}
                        >
                          {deal.status || 'open'}
                        </span>
                      </td>

                      {/* Company & Contact */}
                      <td className="px-4 py-3 text-xs text-gray-600">
                        {deal.company && <p className="font-semibold text-gray-800">🏢 {deal.company.name}</p>}
                        {deal.contact && <p className="text-gray-500">👤 {deal.contact.name}</p>}
                        {!deal.company && !deal.contact && <span className="text-gray-400 italic">None linked</span>}
                      </td>

                      {/* Close Date */}
                      <td className="px-4 py-3 text-xs text-gray-600">
                        {deal.close_date ? new Date(deal.close_date).toLocaleDateString() : '—'}
                      </td>

                      {/* Owner */}
                      <td className="px-4 py-3 text-xs font-medium text-gray-700">
                        {deal.owner?.name || 'Unassigned'}
                      </td>

                      {/* Actions */}
                      <td className="px-4 py-3 text-right">
                        <div className="flex items-center justify-end space-x-1">
                          <Link
                            to={`/deals/${deal.id}`}
                            className="px-2.5 py-1 text-xs font-semibold text-blue-600 hover:bg-blue-50 rounded-lg transition"
                          >
                            View
                          </Link>
                          <button
                            onClick={() => {
                              setDealToEdit(deal);
                              setIsModalOpen(true);
                            }}
                            className="px-2.5 py-1 text-xs font-semibold text-gray-600 hover:bg-gray-100 rounded-lg transition"
                          >
                            Edit
                          </button>
                          <button
                            onClick={() => handleDeleteDeal(deal.id, deal.name || deal.title)}
                            className="px-2 py-1 text-xs text-rose-600 hover:bg-rose-50 rounded-lg transition"
                          >
                            🗑️
                          </button>
                        </div>
                      </td>
                    </tr>
                  ))
                )}
              </tbody>
            </table>
          </div>

          {/* Pagination Bar (in Table view) */}
          <div className="p-4 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-gray-500">
            <div className="flex items-center space-x-2">
              <span>Rows per page:</span>
              <select
                value={perPage}
                onChange={(e) => {
                  setPerPage(Number(e.target.value));
                  setPage(1);
                }}
                className="border border-gray-200 rounded-lg px-2 py-1 bg-white focus:outline-none focus:ring-1 focus:ring-blue-500"
              >
                <option value="10">10</option>
                <option value="15">15</option>
                <option value="25">25</option>
                <option value="50">50</option>
              </select>
              <span>
                Showing {deals.length} of {paginationMeta.total} records
              </span>
            </div>

            <div className="flex items-center space-x-2">
              <button
                onClick={() => setPage((p) => Math.max(p - 1, 1))}
                disabled={page <= 1 || loading}
                className="px-3 py-1.5 border border-gray-200 rounded-lg font-semibold hover:bg-gray-50 transition disabled:opacity-40"
              >
                Previous
              </button>
              <span className="font-semibold text-gray-700">
                Page {paginationMeta.current_page} of {paginationMeta.last_page || 1}
              </span>
              <button
                onClick={() => setPage((p) => Math.min(p + 1, paginationMeta.last_page))}
                disabled={page >= paginationMeta.last_page || loading}
                className="px-3 py-1.5 border border-gray-200 rounded-lg font-semibold hover:bg-gray-50 transition disabled:opacity-40"
              >
                Next
              </button>
            </div>
          </div>
        </div>
      )}

      {/* Deal Create / Edit Modal */}
      <DealModal
        isOpen={isModalOpen}
        onClose={() => {
          setIsModalOpen(false);
          setDealToEdit(null);
        }}
        dealToEdit={dealToEdit}
        stages={stages}
        companies={companies}
        contacts={contacts}
        leads={leads}
        owners={owners}
        initialStageId={initialStageId}
        onSaved={() => {
          fetchDeals();
          fetchMetadata();
        }}
      />
    </div>
  );
}
