import React, { useState, useEffect, useCallback } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import { api } from '../services/api';
import LeadModal from '../components/LeadModal';
import LeadImportModal from '../components/LeadImportModal';

export default function LeadsList() {
  const navigate = useNavigate();

  // Data State
  const [leads, setLeads] = useState([]);
  const [metadata, setMetadata] = useState({ statuses: [], sources: [], users: [], companies: [] });
  const [loading, setLoading] = useState(true);
  const [meta, setMeta] = useState({ current_page: 1, last_page: 1, per_page: 15, total: 0 });

  // Filters & Search & Sort & Pagination State
  const [search, setSearch] = useState('');
  const [statusFilter, setStatusFilter] = useState('');
  const [sourceFilter, setSourceFilter] = useState('');
  const [ownerFilter, setOwnerFilter] = useState('');
  const [minScore, setMinScore] = useState('');
  const [sortBy, setSortBy] = useState('created_at');
  const [sortDirection, setSortDirection] = useState('desc');
  const [page, setPage] = useState(1);
  const [perPage, setPerPage] = useState(15);

  // Selection & Modals State
  const [selectedIds, setSelectedIds] = useState([]);
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [editingLead, setEditingLead] = useState(null);
  const [isImportOpen, setIsImportOpen] = useState(false);
  const [toastMessage, setToastMessage] = useState(null);

  // Fetch Metadata
  useEffect(() => {
    const loadMetadata = async () => {
      try {
        const res = await api.leads.metadata();
        if (res.data.success) {
          setMetadata({
            statuses: res.data.statuses || [],
            sources: res.data.sources || [],
            users: res.data.users || [],
            companies: res.data.companies || [],
          });
        }
      } catch (err) {
        console.error('Failed to load lead metadata', err);
      }
    };
    loadMetadata();
  }, []);

  // Fetch Leads with query params
  const fetchLeads = useCallback(async () => {
    setLoading(true);
    try {
      const params = {
        page,
        per_page: perPage,
        sort_by: sortBy,
        sort_direction: sortDirection,
      };

      if (search.trim()) params.search = search.trim();
      if (statusFilter) params.status_id = statusFilter;
      if (sourceFilter) params.source_id = sourceFilter;
      if (ownerFilter) params.owner_id = ownerFilter;
      if (minScore) params.min_score = minScore;

      const response = await api.leads.list(params);
      if (response.data.success) {
        setLeads(response.data.data);
        if (response.data.meta) {
          setMeta(response.data.meta);
        }
      }
    } catch (err) {
      console.error('Error fetching leads', err);
    } finally {
      setLoading(false);
    }
  }, [page, perPage, sortBy, sortDirection, search, statusFilter, sourceFilter, ownerFilter, minScore]);

  useEffect(() => {
    const timer = setTimeout(() => {
      fetchLeads();
    }, 200);
    return () => clearTimeout(timer);
  }, [fetchLeads]);

  // Toast notification helper
  const showToast = (message) => {
    setToastMessage(message);
    setTimeout(() => setToastMessage(null), 3500);
  };

  // Selection handlers
  const handleSelectAll = (e) => {
    if (e.target.checked) {
      setSelectedIds(leads.map(l => l.id));
    } else {
      setSelectedIds([]);
    }
  };

  const handleSelectLead = (id) => {
    if (selectedIds.includes(id)) {
      setSelectedIds(selectedIds.filter(item => item !== id));
    } else {
      setSelectedIds([...selectedIds, id]);
    }
  };

  // Delete Handlers
  const handleDelete = async (id, e) => {
    e?.stopPropagation();
    if (!window.confirm('Are you sure you want to permanently delete this lead?')) return;
    try {
      await api.leads.delete(id);
      showToast('Lead deleted successfully.');
      fetchLeads();
    } catch (err) {
      alert('Failed to delete lead.');
    }
  };

  const handleBulkDelete = async () => {
    if (!window.confirm(`Are you sure you want to delete ${selectedIds.length} selected leads?`)) return;
    try {
      await api.leads.bulkDelete(selectedIds);
      setSelectedIds([]);
      showToast(`${selectedIds.length} leads deleted successfully.`);
      fetchLeads();
    } catch (err) {
      alert('Failed to delete selected leads.');
    }
  };

  // Edit / Add Handlers
  const handleOpenAdd = () => {
    setEditingLead(null);
    setIsModalOpen(true);
  };

  const handleOpenEdit = (lead, e) => {
    e?.stopPropagation();
    setEditingLead(lead);
    setIsModalOpen(true);
  };

  const handleModalSuccess = (savedLead, mode) => {
    showToast(`Lead ${mode === 'created' ? 'created' : 'updated'} successfully.`);
    fetchLeads();
  };

  const handleImportSuccess = (count) => {
    showToast(`Successfully imported ${count} leads.`);
    fetchLeads();
  };

  // Sorting Handler
  const handleSort = (column) => {
    if (sortBy === column) {
      setSortDirection(prev => prev === 'asc' ? 'desc' : 'asc');
    } else {
      setSortBy(column);
      setSortDirection('asc');
    }
    setPage(1);
  };

  // Export CSV
  const handleExportCSV = async () => {
    try {
      // Fetch all matching records for complete export
      const params = {
        all: true,
        sort_by: sortBy,
        sort_direction: sortDirection,
      };
      if (search.trim()) params.search = search.trim();
      if (statusFilter) params.status_id = statusFilter;
      if (sourceFilter) params.source_id = sourceFilter;
      if (ownerFilter) params.owner_id = ownerFilter;

      const res = await api.leads.list(params);
      const exportList = res.data.data || leads;

      if (exportList.length === 0) {
        alert('No leads to export.');
        return;
      }

      const headers = ['ID', 'First Name', 'Last Name', 'Full Name', 'Email', 'Phone', 'Company', 'Status', 'Source', 'Score', 'Owner', 'Created At'];
      const rows = exportList.map(l => [
        l.id,
        `"${l.first_name || ''}"`,
        `"${l.last_name || ''}"`,
        `"${l.name || ''}"`,
        `"${l.email || ''}"`,
        `"${l.phone || ''}"`,
        `"${l.company || l.company_name || ''}"`,
        `"${l.status?.name || 'New'}"`,
        `"${l.source?.name || 'Website'}"`,
        l.score ?? 0,
        `"${l.owner?.name || 'Unassigned'}"`,
        `"${new Date(l.created_at).toLocaleDateString()}"`,
      ]);

      const csvContent = 'data:text/csv;charset=utf-8,' + [headers.join(','), ...rows.map(e => e.join(','))].join('\n');
      const encodedUri = encodeURI(csvContent);
      const link = document.createElement('a');
      link.setAttribute('href', encodedUri);
      link.setAttribute('download', `leads_export_${new Date().toISOString().split('T')[0]}.csv`);
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);

      showToast(`Exported ${exportList.length} leads to CSV.`);
    } catch (err) {
      alert('Failed to export leads.');
    }
  };

  // Metrics calculation
  const totalCount = meta.total || leads.length;
  const qualifiedCount = leads.filter(l => l.status?.name === 'Qualified' || l.score >= 70).length;
  const avgScore = leads.length > 0 ? Math.round(leads.reduce((acc, curr) => acc + (curr.score || 0), 0) / leads.length) : 0;

  return (
    <div className="p-6 space-y-6">
      {/* Toast Notification */}
      {toastMessage && (
        <div className="fixed bottom-6 right-6 z-50 bg-slate-900 text-white px-5 py-3 rounded-2xl shadow-xl border border-slate-800 flex items-center space-x-3 animate-fade-in">
          <span className="w-2 h-2 rounded-full bg-emerald-400"></span>
          <span className="text-sm font-medium">{toastMessage}</span>
        </div>
      )}

      {/* Header & Main Actions */}
      <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <div className="flex items-center space-x-3">
            <h1 className="text-2xl font-black tracking-tight text-slate-900">Leads Pipeline</h1>
            <span className="px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-100">
              {totalCount} Total
            </span>
          </div>
          <p className="text-xs text-slate-500 mt-0.5">
            Capture, qualify, and convert high-value prospects into active opportunities.
          </p>
        </div>

        <div className="flex flex-wrap items-center gap-2.5">
          <button
            onClick={handleExportCSV}
            className="px-3.5 py-2 text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 rounded-xl shadow-xs transition flex items-center space-x-1.5"
          >
            <svg className="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
            <span>Export</span>
          </button>

          <button
            onClick={() => setIsImportOpen(true)}
            className="px-3.5 py-2 text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 rounded-xl shadow-xs transition flex items-center space-x-1.5"
          >
            <svg className="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l4-4m0 0l4 4m-4-4v12" />
            </svg>
            <span>Import CSV</span>
          </button>

          <button
            onClick={handleOpenAdd}
            className="px-4 py-2 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-sm shadow-indigo-200 transition flex items-center space-x-1.5 cursor-pointer"
          >
            <span className="text-sm font-black">+</span>
            <span>Add Lead</span>
          </button>
        </div>
      </div>

      {/* Metric Highlights */}
      <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div className="bg-white p-4 rounded-2xl border border-slate-100 shadow-xs">
          <p className="text-xs font-semibold text-slate-500">Total Leads</p>
          <div className="flex items-baseline justify-between mt-1">
            <span className="text-2xl font-black text-slate-900">{totalCount}</span>
            <span className="text-[11px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md">Live DB</span>
          </div>
        </div>

        <div className="bg-white p-4 rounded-2xl border border-slate-100 shadow-xs">
          <p className="text-xs font-semibold text-slate-500">Qualified Prospects</p>
          <div className="flex items-baseline justify-between mt-1">
            <span className="text-2xl font-black text-indigo-600">{qualifiedCount}</span>
            <span className="text-[11px] font-bold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded-md">
              {totalCount > 0 ? `${Math.round((qualifiedCount / totalCount) * 100)}%` : '0%'}
            </span>
          </div>
        </div>

        <div className="bg-white p-4 rounded-2xl border border-slate-100 shadow-xs">
          <p className="text-xs font-semibold text-slate-500">Average Lead Score</p>
          <div className="flex items-baseline justify-between mt-1">
            <span className="text-2xl font-black text-slate-900">{avgScore} <span className="text-xs font-normal text-slate-400">/ 100</span></span>
            <div className="w-12 bg-slate-100 h-2 rounded-full overflow-hidden">
              <div className="bg-indigo-600 h-full" style={{ width: `${avgScore}%` }} />
            </div>
          </div>
        </div>

        <div className="bg-white p-4 rounded-2xl border border-slate-100 shadow-xs">
          <p className="text-xs font-semibold text-slate-500">Selected for Batch</p>
          <div className="flex items-baseline justify-between mt-1">
            <span className="text-2xl font-black text-slate-900">{selectedIds.length}</span>
            {selectedIds.length > 0 ? (
              <button
                onClick={handleBulkDelete}
                className="text-[11px] font-bold text-red-600 bg-red-50 hover:bg-red-100 px-2 py-0.5 rounded-md transition"
              >
                Delete All
              </button>
            ) : (
              <span className="text-[11px] text-slate-400">None</span>
            )}
          </div>
        </div>
      </div>

      {/* Search & Filter Toolbar */}
      <div className="bg-white p-4 rounded-2xl border border-slate-100 shadow-xs space-y-3">
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
          {/* Search Box */}
          <div className="lg:col-span-2 relative">
            <svg className="w-4 h-4 absolute left-3.5 top-1/2 transform -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <input
              type="text"
              value={search}
              onChange={(e) => { setSearch(e.target.value); setPage(1); }}
              placeholder="Search by name, email, company, phone..."
              className="w-full pl-9 pr-8 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50/50 focus:bg-white outline-none focus:ring-2 focus:ring-indigo-500 transition"
            />
            {search && (
              <button
                onClick={() => setSearch('')}
                className="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-xs font-bold"
              >
                ×
              </button>
            )}
          </div>

          {/* Status Filter */}
          <div>
            <select
              value={statusFilter}
              onChange={(e) => { setStatusFilter(e.target.value); setPage(1); }}
              className="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50/50 focus:bg-white outline-none focus:ring-2 focus:ring-indigo-500 transition text-slate-700"
            >
              <option value="">All Statuses</option>
              {metadata.statuses?.map(status => (
                <option key={status.id} value={status.id}>
                  {status.name}
                </option>
              ))}
            </select>
          </div>

          {/* Source Filter */}
          <div>
            <select
              value={sourceFilter}
              onChange={(e) => { setSourceFilter(e.target.value); setPage(1); }}
              className="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50/50 focus:bg-white outline-none focus:ring-2 focus:ring-indigo-500 transition text-slate-700"
            >
              <option value="">All Sources</option>
              {metadata.sources?.map(source => (
                <option key={source.id} value={source.id}>
                  {source.name}
                </option>
              ))}
            </select>
          </div>

          {/* Owner Filter */}
          <div>
            <select
              value={ownerFilter}
              onChange={(e) => { setOwnerFilter(e.target.value); setPage(1); }}
              className="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50/50 focus:bg-white outline-none focus:ring-2 focus:ring-indigo-500 transition text-slate-700"
            >
              <option value="">All Owners</option>
              {metadata.users?.map(user => (
                <option key={user.id} value={user.id}>
                  {user.name}
                </option>
              ))}
            </select>
          </div>
        </div>

        {/* Active Filters Pill Bar (if any) */}
        {(search || statusFilter || sourceFilter || ownerFilter || minScore) && (
          <div className="flex flex-wrap items-center gap-2 pt-2 border-t border-slate-100 text-xs text-slate-500">
            <span className="font-semibold">Active filters:</span>
            {search && (
              <span className="inline-flex items-center px-2 py-0.5 rounded-full bg-slate-100 text-slate-700">
                Search: "{search}" <button onClick={() => setSearch('')} className="ml-1 font-bold">×</button>
              </span>
            )}
            {statusFilter && (
              <span className="inline-flex items-center px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700">
                Status: {metadata.statuses.find(s => s.id == statusFilter)?.name || statusFilter}
                <button onClick={() => setStatusFilter('')} className="ml-1 font-bold">×</button>
              </span>
            )}
            {sourceFilter && (
              <span className="inline-flex items-center px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700">
                Source: {metadata.sources.find(s => s.id == sourceFilter)?.name || sourceFilter}
                <button onClick={() => setSourceFilter('')} className="ml-1 font-bold">×</button>
              </span>
            )}
            {ownerFilter && (
              <span className="inline-flex items-center px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700">
                Owner: {metadata.users.find(u => u.id == ownerFilter)?.name || ownerFilter}
                <button onClick={() => setOwnerFilter('')} className="ml-1 font-bold">×</button>
              </span>
            )}
            <button
              onClick={() => { setSearch(''); setStatusFilter(''); setSourceFilter(''); setOwnerFilter(''); setMinScore(''); setPage(1); }}
              className="text-xs font-semibold text-red-600 hover:underline ml-2"
            >
              Reset All
            </button>
          </div>
        )}
      </div>

      {/* Main Leads Table Card */}
      <div className="bg-white rounded-2xl border border-slate-100 shadow-xs overflow-hidden">
        {loading ? (
          <div className="p-20 text-center text-slate-400 flex flex-col items-center justify-center space-y-3">
            <svg className="animate-spin h-7 w-7 text-indigo-600" fill="none" viewBox="0 0 24 24">
              <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
              <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" />
            </svg>
            <p className="text-xs font-medium">Fetching real leads from database...</p>
          </div>
        ) : leads.length === 0 ? (
          <div className="p-16 text-center">
            <div className="w-14 h-14 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center mx-auto mb-3 text-2xl font-bold">
              👥
            </div>
            <h3 className="text-base font-bold text-slate-900">No Leads Found</h3>
            <p className="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
              {search || statusFilter || sourceFilter
                ? 'No leads matched your filter criteria. Try adjusting or resetting your filters.'
                : 'Your pipeline is currently empty. Add your first lead or import from a CSV file.'}
            </p>
            <div className="mt-5 flex justify-center space-x-3">
              <button
                onClick={handleOpenAdd}
                className="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-sm shadow-indigo-200 transition"
              >
                + Create New Lead
              </button>
            </div>
          </div>
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full text-left text-xs">
              <thead className="bg-slate-50/80 border-b border-slate-100 text-slate-500 font-bold tracking-wider uppercase">
                <tr>
                  <th className="px-4 py-3.5 w-10 text-center">
                    <input
                      type="checkbox"
                      checked={selectedIds.length === leads.length && leads.length > 0}
                      onChange={handleSelectAll}
                      className="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer"
                    />
                  </th>
                  <th
                    onClick={() => handleSort('first_name')}
                    className="px-4 py-3.5 cursor-pointer hover:text-slate-800 transition select-none"
                  >
                    <div className="flex items-center space-x-1">
                      <span>Prospect Name</span>
                      {sortBy === 'first_name' && (<span>{sortDirection === 'asc' ? '↑' : '↓'}</span>)}
                    </div>
                  </th>
                  <th
                    onClick={() => handleSort('company')}
                    className="px-4 py-3.5 cursor-pointer hover:text-slate-800 transition select-none"
                  >
                    <div className="flex items-center space-x-1">
                      <span>Company</span>
                      {sortBy === 'company' && (<span>{sortDirection === 'asc' ? '↑' : '↓'}</span>)}
                    </div>
                  </th>
                  <th className="px-4 py-3.5">Status</th>
                  <th
                    onClick={() => handleSort('score')}
                    className="px-4 py-3.5 cursor-pointer hover:text-slate-800 transition select-none"
                  >
                    <div className="flex items-center space-x-1">
                      <span>Score</span>
                      {sortBy === 'score' && (<span>{sortDirection === 'asc' ? '↑' : '↓'}</span>)}
                    </div>
                  </th>
                  <th className="px-4 py-3.5">Source</th>
                  <th className="px-4 py-3.5">Owner</th>
                  <th
                    onClick={() => handleSort('created_at')}
                    className="px-4 py-3.5 cursor-pointer hover:text-slate-800 transition select-none"
                  >
                    <div className="flex items-center space-x-1">
                      <span>Created</span>
                      {sortBy === 'created_at' && (<span>{sortDirection === 'asc' ? '↑' : '↓'}</span>)}
                    </div>
                  </th>
                  <th className="px-4 py-3.5 text-right">Actions</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-100">
                {leads.map(lead => {
                  const statusColor = lead.status?.color || 'blue';
                  const isSelected = selectedIds.includes(lead.id);

                  return (
                    <tr
                      key={lead.id}
                      onClick={() => navigate(`/leads/${lead.id}`)}
                      className={`hover:bg-slate-50/80 transition cursor-pointer ${
                        isSelected ? 'bg-indigo-50/30' : ''
                      }`}
                    >
                      <td className="px-4 py-3.5 text-center" onClick={(e) => e.stopPropagation()}>
                        <input
                          type="checkbox"
                          checked={isSelected}
                          onChange={() => handleSelectLead(lead.id)}
                          className="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer"
                        />
                      </td>

                      {/* Lead Name & Email */}
                      <td className="px-4 py-3.5">
                        <div className="flex items-center space-x-3">
                          <div className="w-8 h-8 rounded-full bg-gradient-to-tr from-indigo-600 to-indigo-400 text-white font-bold flex items-center justify-center text-xs shadow-xs">
                            {lead.first_name ? lead.first_name.charAt(0) : 'L'}
                          </div>
                          <div>
                            <p className="font-bold text-slate-900 hover:text-indigo-600 transition">
                              {lead.first_name} {lead.last_name}
                            </p>
                            <p className="text-[11px] text-slate-400">{lead.email}</p>
                          </div>
                        </div>
                      </td>

                      {/* Company */}
                      <td className="px-4 py-3.5">
                        <p className="font-semibold text-slate-800">
                          {lead.company || lead.company_name || 'Individual'}
                        </p>
                        {lead.phone && <p className="text-[11px] text-slate-400">{lead.phone}</p>}
                      </td>

                      {/* Status */}
                      <td className="px-4 py-3.5">
                        <span className={`inline-flex px-2.5 py-0.5 rounded-full text-[11px] font-bold ${
                          statusColor === 'green' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' :
                          statusColor === 'red' ? 'bg-rose-50 text-rose-700 border border-rose-200' :
                          statusColor === 'yellow' ? 'bg-amber-50 text-amber-700 border border-amber-200' :
                          statusColor === 'orange' ? 'bg-orange-50 text-orange-700 border border-orange-200' :
                          'bg-indigo-50 text-indigo-700 border border-indigo-200'
                        }`}>
                          {lead.status?.name || 'New'}
                        </span>
                      </td>

                      {/* Score */}
                      <td className="px-4 py-3.5">
                        <div className="flex items-center space-x-2">
                          <span className={`font-black ${
                            lead.score >= 70 ? 'text-emerald-600' :
                            lead.score >= 40 ? 'text-amber-600' : 'text-slate-600'
                          }`}>
                            {lead.score ?? 0}
                          </span>
                          <div className="w-12 bg-slate-100 h-1.5 rounded-full overflow-hidden">
                            <div
                              className={`h-full ${
                                lead.score >= 70 ? 'bg-emerald-500' :
                                lead.score >= 40 ? 'bg-amber-500' : 'bg-slate-400'
                              }`}
                              style={{ width: `${lead.score || 0}%` }}
                            />
                          </div>
                        </div>
                      </td>

                      {/* Source */}
                      <td className="px-4 py-3.5 text-slate-600 font-medium">
                        {lead.source?.name || 'Website'}
                      </td>

                      {/* Owner */}
                      <td className="px-4 py-3.5 text-slate-700">
                        {lead.owner ? (
                          <div className="flex items-center space-x-1.5">
                            <div className="w-5 h-5 rounded-full bg-slate-200 text-slate-700 text-[10px] font-bold flex items-center justify-center">
                              {lead.owner.name.charAt(0)}
                            </div>
                            <span className="font-medium text-[11px]">{lead.owner.name}</span>
                          </div>
                        ) : (
                          <span className="text-slate-400 italic text-[11px]">Unassigned</span>
                        )}
                      </td>

                      {/* Created */}
                      <td className="px-4 py-3.5 text-slate-400 font-mono text-[11px]">
                        {lead.created_at ? new Date(lead.created_at).toLocaleDateString() : '-'}
                      </td>

                      {/* Actions */}
                      <td className="px-4 py-3.5 text-right" onClick={(e) => e.stopPropagation()}>
                        <div className="flex items-center justify-end space-x-2">
                          <Link
                            to={`/leads/${lead.id}`}
                            className="p-1.5 text-slate-400 hover:text-indigo-600 rounded-lg hover:bg-slate-100 transition"
                            title="View Lead Details"
                          >
                            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                          </Link>

                          <button
                            onClick={(e) => handleOpenEdit(lead, e)}
                            className="p-1.5 text-slate-400 hover:text-indigo-600 rounded-lg hover:bg-slate-100 transition"
                            title="Edit Lead"
                          >
                            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                          </button>

                          <button
                            onClick={(e) => handleDelete(lead.id, e)}
                            className="p-1.5 text-slate-400 hover:text-red-600 rounded-lg hover:bg-slate-100 transition"
                            title="Delete Lead"
                          >
                            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                          </button>
                        </div>
                      </td>
                    </tr>
                  );
                })}
              </tbody>
            </table>
          </div>
        )}

        {/* Pagination Footer */}
        {meta.total > 0 && (
          <div className="px-6 py-4 bg-slate-50/50 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">
            <div className="flex items-center space-x-2">
              <span>Showing</span>
              <span className="font-bold text-slate-800">
                {((meta.current_page - 1) * meta.per_page) + 1}
              </span>
              <span>to</span>
              <span className="font-bold text-slate-800">
                {Math.min(meta.current_page * meta.per_page, meta.total)}
              </span>
              <span>of</span>
              <span className="font-bold text-slate-800">{meta.total}</span>
              <span>leads</span>

              <div className="ml-4 flex items-center space-x-1">
                <span>Per page:</span>
                <select
                  value={perPage}
                  onChange={(e) => { setPerPage(Number(e.target.value)); setPage(1); }}
                  className="px-2 py-1 text-xs border border-slate-200 rounded-lg bg-white outline-none"
                >
                  <option value="10">10</option>
                  <option value="15">15</option>
                  <option value="25">25</option>
                  <option value="50">50</option>
                </select>
              </div>
            </div>

            <div className="flex items-center space-x-1">
              <button
                onClick={() => setPage(prev => Math.max(prev - 1, 1))}
                disabled={meta.current_page <= 1}
                className="px-3 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 disabled:opacity-40 font-medium transition"
              >
                Previous
              </button>

              {/* Page numbers */}
              {Array.from({ length: Math.min(meta.last_page, 5) }, (_, i) => {
                let pageNum = i + 1;
                if (meta.last_page > 5 && meta.current_page > 3) {
                  pageNum = meta.current_page - 3 + i;
                  if (pageNum > meta.last_page) pageNum = meta.last_page - (4 - i);
                }
                return (
                  <button
                    key={pageNum}
                    onClick={() => setPage(pageNum)}
                    className={`w-7 h-7 rounded-lg text-xs font-bold transition ${
                      meta.current_page === pageNum
                        ? 'bg-indigo-600 text-white shadow-xs'
                        : 'border border-slate-200 bg-white hover:bg-slate-50 text-slate-700'
                    }`}
                  >
                    {pageNum}
                  </button>
                );
              })}

              <button
                onClick={() => setPage(prev => Math.min(prev + 1, meta.last_page))}
                disabled={meta.current_page >= meta.last_page}
                className="px-3 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 disabled:opacity-40 font-medium transition"
              >
                Next
              </button>
            </div>
          </div>
        )}
      </div>

      {/* Add / Edit Modal */}
      <LeadModal
        isOpen={isModalOpen}
        onClose={() => setIsModalOpen(false)}
        onSuccess={handleModalSuccess}
        lead={editingLead}
        metadata={metadata}
      />

      {/* CSV Import Modal */}
      <LeadImportModal
        isOpen={isImportOpen}
        onClose={() => setIsImportOpen(false)}
        onSuccess={handleImportSuccess}
      />
    </div>
  );
}
