import React, { useState, useEffect, useCallback } from 'react';
import { Link } from 'react-router-dom';
import { api } from '../services/api';
import ContactModal from '../components/ContactModal';

export default function ContactsList() {
  const [contacts, setContacts] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [stats, setStats] = useState({
    total: 0,
    primary: 0,
    with_company: 0,
    with_deals: 0,
  });

  // Filter and pagination options
  const [companies, setCompanies] = useState([]);
  const [owners, setOwners] = useState([]);
  const [leads, setLeads] = useState([]);
  const [departments, setDepartments] = useState([]);

  // Query state
  const [search, setSearch] = useState('');
  const [companyFilter, setCompanyFilter] = useState('');
  const [ownerFilter, setOwnerFilter] = useState('');
  const [departmentFilter, setDepartmentFilter] = useState('');
  const [primaryFilter, setPrimaryFilter] = useState('');
  const [sortBy, setSortBy] = useState('created_at');
  const [sortDirection, setSortDirection] = useState('desc');
  const [page, setPage] = useState(1);
  const [perPage, setPerPage] = useState(15);
  const [paginationMeta, setPaginationMeta] = useState({
    current_page: 1,
    last_page: 1,
    total: 0,
  });

  // Selection & Modal state
  const [selectedIds, setSelectedIds] = useState([]);
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [contactToEdit, setContactToEdit] = useState(null);
  const [actionLoading, setActionLoading] = useState(false);

  // Fetch metadata once
  const fetchMetadata = async () => {
    try {
      const res = await api.contacts.metadata();
      if (res.data.success) {
        setStats(res.data.stats || {});
        setCompanies(res.data.options?.companies || []);
        setOwners(res.data.options?.owners || []);
        setLeads(res.data.options?.leads || []);
        setDepartments(res.data.options?.departments || []);
      }
    } catch (err) {
      console.error('Failed to load contact metadata:', err);
    }
  };

  // Fetch contacts list with query params
  const fetchContacts = useCallback(async () => {
    setLoading(true);
    setError('');
    try {
      const params = {
        page,
        per_page: perPage,
        sort_by: sortBy,
        sort_direction: sortDirection,
      };

      if (search) params.search = search;
      if (companyFilter) params.company_id = companyFilter;
      if (ownerFilter) params.owner_id = ownerFilter;
      if (departmentFilter) params.department = departmentFilter;
      if (primaryFilter !== '') params.is_primary = primaryFilter === 'true' ? 1 : 0;

      const res = await api.contacts.list(params);
      if (res.data.success) {
        setContacts(res.data.data || []);
        if (res.data.meta) {
          setPaginationMeta(res.data.meta);
        }
      }
    } catch (err) {
      setError(err.response?.data?.message || 'Failed to load contacts from server.');
    } finally {
      setLoading(false);
    }
  }, [page, perPage, sortBy, sortDirection, search, companyFilter, ownerFilter, departmentFilter, primaryFilter]);

  useEffect(() => {
    fetchMetadata();
  }, []);

  useEffect(() => {
    fetchContacts();
  }, [fetchContacts]);

  // Handle Selection
  const handleSelectAll = (e) => {
    if (e.target.checked) {
      setSelectedIds(contacts.map((c) => c.id));
    } else {
      setSelectedIds([]);
    }
  };

  const handleSelectOne = (id) => {
    setSelectedIds((prev) =>
      prev.includes(id) ? prev.filter((item) => item !== id) : [...prev, id]
    );
  };

  // Delete Single Contact
  const handleDeleteContact = async (id, name) => {
    if (!window.confirm(`Are you sure you want to delete contact "${name}"?`)) return;
    setActionLoading(true);
    try {
      await api.contacts.delete(id);
      setSelectedIds((prev) => prev.filter((item) => item !== id));
      fetchContacts();
      fetchMetadata();
    } catch (err) {
      alert(err.response?.data?.message || 'Failed to delete contact.');
    } finally {
      setActionLoading(false);
    }
  };

  // Bulk Delete Contacts
  const handleBulkDelete = async () => {
    if (!selectedIds.length) return;
    if (!window.confirm(`Are you sure you want to delete ${selectedIds.length} selected contacts?`)) return;
    setActionLoading(true);
    try {
      await api.contacts.bulkDelete(selectedIds);
      setSelectedIds([]);
      fetchContacts();
      fetchMetadata();
    } catch (err) {
      alert(err.response?.data?.message || 'Failed to bulk delete contacts.');
    } finally {
      setActionLoading(false);
    }
  };

  // Export Contacts to CSV
  const handleExportCSV = () => {
    if (!contacts.length) {
      alert('No contact records available to export.');
      return;
    }

    const headers = ['ID', 'First Name', 'Last Name', 'Email', 'Phone', 'Job Title', 'Department', 'Company', 'Primary', 'Owner', 'Created At'];
    const rows = contacts.map((c) => [
      c.id,
      `"${c.first_name || ''}"`,
      `"${c.last_name || ''}"`,
      `"${c.email || ''}"`,
      `"${c.phone || ''}"`,
      `"${c.job_title || ''}"`,
      `"${c.department || ''}"`,
      `"${c.company?.name || ''}"`,
      c.is_primary ? 'Yes' : 'No',
      `"${c.owner?.name || ''}"`,
      `"${c.created_at || ''}"`,
    ]);

    const csvContent = 'data:text/csv;charset=utf-8,' + [headers.join(','), ...rows.map((e) => e.join(','))].join('\n');
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement('a');
    link.setAttribute('href', encodedUri);
    link.setAttribute('download', `contacts_export_${new Date().toISOString().slice(0, 10)}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  };

  const getInitials = (firstName, lastName) => {
    const f = firstName ? firstName.charAt(0).toUpperCase() : '';
    const l = lastName ? lastName.charAt(0).toUpperCase() : '';
    return f + l || 'C';
  };

  return (
    <div className="p-6 space-y-6 max-w-7xl mx-auto">
      {/* Top Banner & Header */}
      <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <div className="flex items-center space-x-2">
            <span className="text-2xl">📇</span>
            <h1 className="text-2xl font-black text-gray-900 tracking-tight">Contacts Directory</h1>
            <span className="bg-indigo-50 text-indigo-700 text-xs font-bold px-2.5 py-0.5 rounded-full border border-indigo-200">
              {paginationMeta.total || stats.total} Records
            </span>
          </div>
          <p className="text-sm text-gray-500 mt-1">
            Manage your customer contacts, decision makers, and organization stakeholders.
          </p>
        </div>

        <div className="flex items-center space-x-3">
          <button
            onClick={handleExportCSV}
            className="px-3.5 py-2 text-xs font-semibold text-gray-700 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 shadow-sm transition flex items-center"
          >
            <span className="mr-1.5">📥</span> Export CSV
          </button>
          <button
            onClick={() => {
              setContactToEdit(null);
              setIsModalOpen(true);
            }}
            className="px-4 py-2 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-lg shadow-blue-500/20 transition flex items-center"
          >
            <span className="mr-2 text-lg leading-none">+</span> Add Contact
          </button>
        </div>
      </div>

      {/* KPI Stats Grid */}
      <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div className="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm">
          <div className="flex items-center justify-between">
            <span className="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Contacts</span>
            <span className="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-sm font-bold">👥</span>
          </div>
          <p className="text-2xl font-black text-gray-900 mt-2">{stats.total || paginationMeta.total || 0}</p>
          <p className="text-xs text-blue-600 font-medium mt-1">Active customer directory</p>
        </div>

        <div className="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm">
          <div className="flex items-center justify-between">
            <span className="text-xs font-bold text-gray-400 uppercase tracking-wider">Primary Contacts</span>
            <span className="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-sm font-bold">⭐</span>
          </div>
          <p className="text-2xl font-black text-amber-600 mt-2">{stats.primary || 0}</p>
          <p className="text-xs text-amber-600 font-medium mt-1">Key account stakeholders</p>
        </div>

        <div className="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm">
          <div className="flex items-center justify-between">
            <span className="text-xs font-bold text-gray-400 uppercase tracking-wider">With Company</span>
            <span className="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm font-bold">🏢</span>
          </div>
          <p className="text-2xl font-black text-emerald-600 mt-2">{stats.with_company || 0}</p>
          <p className="text-xs text-emerald-600 font-medium mt-1">Associated organizations</p>
        </div>

        <div className="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm">
          <div className="flex items-center justify-between">
            <span className="text-xs font-bold text-gray-400 uppercase tracking-wider">Linked to Deals</span>
            <span className="w-8 h-8 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-sm font-bold">🤝</span>
          </div>
          <p className="text-2xl font-black text-purple-600 mt-2">{stats.with_deals || 0}</p>
          <p className="text-xs text-purple-600 font-medium mt-1">Active pipeline deals</p>
        </div>
      </div>

      {/* Control Bar: Search & Filters */}
      <div className="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm space-y-3">
        <div className="grid grid-cols-1 md:grid-cols-12 gap-3">
          {/* Search Box */}
          <div className="md:col-span-4 relative">
            <input
              type="text"
              value={search}
              onChange={(e) => {
                setSearch(e.target.value);
                setPage(1);
              }}
              placeholder="Search by name, email, phone, job title..."
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

          {/* Company Filter */}
          <div className="md:col-span-2">
            <select
              value={companyFilter}
              onChange={(e) => {
                setCompanyFilter(e.target.value);
                setPage(1);
              }}
              className="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
            >
              <option value="">All Companies</option>
              {companies.map((c) => (
                <option key={c.id} value={c.id}>
                  {c.name}
                </option>
              ))}
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

          {/* Primary Role Filter */}
          <div className="md:col-span-2">
            <select
              value={primaryFilter}
              onChange={(e) => {
                setPrimaryFilter(e.target.value);
                setPage(1);
              }}
              className="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
            >
              <option value="">Primary & Others</option>
              <option value="true">⭐ Primary Only</option>
              <option value="false">Secondary Contacts</option>
            </select>
          </div>

          {/* Sort By Field */}
          <div className="md:col-span-2 flex items-center space-x-1">
            <select
              value={sortBy}
              onChange={(e) => setSortBy(e.target.value)}
              className="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
            >
              <option value="created_at">Date Created</option>
              <option value="first_name">First Name</option>
              <option value="last_name">Last Name</option>
              <option value="email">Email</option>
              <option value="job_title">Job Title</option>
              <option value="updated_at">Last Updated</option>
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

        {/* Bulk Action Bar */}
        {selectedIds.length > 0 && (
          <div className="flex items-center justify-between bg-indigo-50 border border-indigo-200 p-3 rounded-xl animate-fade-in">
            <div className="flex items-center space-x-2">
              <span className="text-xs font-bold text-indigo-900">
                {selectedIds.length} contact{selectedIds.length > 1 ? 's' : ''} selected
              </span>
            </div>
            <div className="flex items-center space-x-2">
              <button
                onClick={handleBulkDelete}
                disabled={actionLoading}
                className="px-3 py-1.5 text-xs font-semibold text-rose-700 bg-rose-100 hover:bg-rose-200 rounded-lg transition"
              >
                Delete Selected
              </button>
            </div>
          </div>
        )}
      </div>

      {/* Main Table */}
      <div className="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        {error && (
          <div className="p-4 bg-rose-50 text-rose-700 text-sm border-b border-rose-200 flex justify-between items-center">
            <span>{error}</span>
            <button onClick={fetchContacts} className="underline font-semibold text-xs">
              Retry
            </button>
          </div>
        )}

        <div className="overflow-x-auto">
          <table className="w-full text-left text-sm">
            <thead className="bg-slate-50/80 text-gray-400 text-[11px] font-bold uppercase tracking-wider border-b border-gray-100">
              <tr>
                <th className="p-4 w-10 text-center">
                  <input
                    type="checkbox"
                    checked={contacts.length > 0 && selectedIds.length === contacts.length}
                    onChange={handleSelectAll}
                    className="w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500"
                  />
                </th>
                <th className="px-4 py-3">Contact Name</th>
                <th className="px-4 py-3">Job Title & Org</th>
                <th className="px-4 py-3">Contact Details</th>
                <th className="px-4 py-3">Associated Lead</th>
                <th className="px-4 py-3">Owner</th>
                <th className="px-4 py-3 text-right">Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-100">
              {loading ? (
                <tr>
                  <td colSpan="7" className="p-8 text-center text-gray-400">
                    <div className="flex items-center justify-center space-x-2">
                      <svg className="animate-spin h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24">
                        <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                        <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                      </svg>
                      <span>Loading contacts from server...</span>
                    </div>
                  </td>
                </tr>
              ) : contacts.length === 0 ? (
                <tr>
                  <td colSpan="7" className="p-12 text-center text-gray-400">
                    <span className="text-3xl block mb-2">🔍</span>
                    <p className="font-semibold text-gray-700">No contacts found</p>
                    <p className="text-xs text-gray-400 mt-1">Try changing your filters or add a new contact.</p>
                    <button
                      onClick={() => {
                        setContactToEdit(null);
                        setIsModalOpen(true);
                      }}
                      className="mt-3 px-4 py-1.5 text-xs font-semibold text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-lg transition"
                    >
                      + Add First Contact
                    </button>
                  </td>
                </tr>
              ) : (
                contacts.map((contact) => (
                  <tr key={contact.id} className="hover:bg-slate-50/70 transition group">
                    <td className="p-4 text-center">
                      <input
                        type="checkbox"
                        checked={selectedIds.includes(contact.id)}
                        onChange={() => handleSelectOne(contact.id)}
                        className="w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500"
                      />
                    </td>

                    {/* Contact Name & Avatar */}
                    <td className="px-4 py-3">
                      <div className="flex items-center space-x-3">
                        <div className="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-500 to-blue-600 text-white flex items-center justify-center font-bold text-xs shadow-sm flex-shrink-0">
                          {getInitials(contact.first_name, contact.last_name)}
                        </div>
                        <div>
                          <div className="flex items-center space-x-1.5">
                            <Link
                              to={`/contacts/${contact.id}`}
                              className="font-bold text-gray-900 hover:text-blue-600 transition"
                            >
                              {contact.name || `${contact.first_name} ${contact.last_name}`}
                            </Link>
                            {contact.is_primary && (
                              <span className="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                ⭐ Primary
                              </span>
                            )}
                          </div>
                          <p className="text-[11px] text-gray-400">
                            Added {contact.created_at ? new Date(contact.created_at).toLocaleDateString() : 'recently'}
                          </p>
                        </div>
                      </div>
                    </td>

                    {/* Job Title & Organization */}
                    <td className="px-4 py-3">
                      <p className="font-semibold text-gray-800 text-xs">{contact.job_title || 'No Title'}</p>
                      <div className="flex items-center space-x-1 text-[11px] text-gray-500 mt-0.5">
                        {contact.company ? (
                          <span className="font-medium text-indigo-600 bg-indigo-50 px-1.5 py-0.5 rounded border border-indigo-100">
                            🏢 {contact.company.name}
                          </span>
                        ) : (
                          <span className="text-gray-400 italic">No Company</span>
                        )}
                        {contact.department && <span className="text-gray-400">• {contact.department}</span>}
                      </div>
                    </td>

                    {/* Email & Phone */}
                    <td className="px-4 py-3">
                      <p className="text-xs text-gray-800 font-medium">{contact.email || '—'}</p>
                      <p className="text-[11px] text-gray-400">{contact.phone || contact.mobile || 'No phone'}</p>
                    </td>

                    {/* Associated Lead */}
                    <td className="px-4 py-3">
                      {contact.lead ? (
                        <Link
                          to={`/leads/${contact.lead.id}`}
                          className="text-xs font-semibold text-blue-600 hover:underline inline-flex items-center"
                        >
                          <span className="mr-1">👥</span> {contact.lead.name || `Lead #${contact.lead.id}`}
                        </Link>
                      ) : (
                        <span className="text-xs text-gray-400">—</span>
                      )}
                    </td>

                    {/* Owner */}
                    <td className="px-4 py-3">
                      <span className="text-xs text-gray-700 font-medium">
                        {contact.owner?.name || 'Unassigned'}
                      </span>
                    </td>

                    {/* Actions */}
                    <td className="px-4 py-3 text-right">
                      <div className="flex items-center justify-end space-x-1">
                        <Link
                          to={`/contacts/${contact.id}`}
                          className="px-2.5 py-1 text-xs font-semibold text-blue-600 hover:bg-blue-50 rounded-lg transition"
                        >
                          View
                        </Link>
                        <button
                          onClick={() => {
                            setContactToEdit(contact);
                            setIsModalOpen(true);
                          }}
                          className="px-2.5 py-1 text-xs font-semibold text-gray-600 hover:bg-gray-100 rounded-lg transition"
                        >
                          Edit
                        </button>
                        <button
                          onClick={() => handleDeleteContact(contact.id, contact.name || contact.first_name)}
                          className="px-2 py-1 text-xs text-rose-600 hover:bg-rose-50 rounded-lg transition"
                          title="Delete Contact"
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

        {/* Pagination Bar */}
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
              Showing {contacts.length} of {paginationMeta.total} records
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

      {/* Create / Edit Modal */}
      <ContactModal
        isOpen={isModalOpen}
        onClose={() => {
          setIsModalOpen(false);
          setContactToEdit(null);
        }}
        contactToEdit={contactToEdit}
        companies={companies}
        owners={owners}
        leads={leads}
        onSaved={() => {
          fetchContacts();
          fetchMetadata();
        }}
      />
    </div>
  );
}
