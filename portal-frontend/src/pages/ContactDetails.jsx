import React, { useState, useEffect, useCallback } from 'react';
import { useParams, Link, useNavigate } from 'react-router-dom';
import { api } from '../services/api';
import ContactModal from '../components/ContactModal';

export default function ContactDetails() {
  const { id } = useParams();
  const navigate = useNavigate();

  const [contact, setContact] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [activeTab, setActiveTab] = useState('overview');

  // Edit Modal State
  const [isEditModalOpen, setIsEditModalOpen] = useState(false);
  const [companies, setCompanies] = useState([]);
  const [owners, setOwners] = useState([]);
  const [leads, setLeads] = useState([]);

  // Activity Logger State
  const [activityType, setActivityType] = useState('note');
  const [activitySubject, setActivitySubject] = useState('');
  const [activityNotes, setActivityNotes] = useState('');
  const [activitySubmitting, setActivitySubmitting] = useState(false);

  // New Task State
  const [showTaskForm, setShowTaskForm] = useState(false);
  const [taskTitle, setTaskTitle] = useState('');
  const [taskPriority, setTaskPriority] = useState('Medium');
  const [taskDueDate, setTaskDueDate] = useState('');
  const [taskDescription, setTaskDescription] = useState('');
  const [taskSubmitting, setTaskSubmitting] = useState(false);

  // New Deal State
  const [showDealModal, setShowDealModal] = useState(false);
  const [dealName, setDealName] = useState('');
  const [dealValue, setDealValue] = useState('');
  const [dealStage, setDealStage] = useState('lead_in');
  const [dealCloseDate, setDealCloseDate] = useState('');
  const [dealSubmitting, setDealSubmitting] = useState(false);

  // Fetch Contact Details
  const fetchContactDetails = useCallback(async () => {
    setLoading(true);
    setError('');
    try {
      const res = await api.contacts.get(id);
      if (res.data.success) {
        setContact(res.data.data);
      }
    } catch (err) {
      setError(err.response?.data?.message || 'Failed to load contact profile.');
    } finally {
      setLoading(false);
    }
  }, [id]);

  // Fetch metadata for edit dropdowns
  const fetchMetadata = async () => {
    try {
      const res = await api.contacts.metadata();
      if (res.data.success) {
        setCompanies(res.data.options?.companies || []);
        setOwners(res.data.options?.owners || []);
        setLeads(res.data.options?.leads || []);
      }
    } catch (err) {
      console.error('Failed to load contact metadata:', err);
    }
  };

  useEffect(() => {
    fetchContactDetails();
    fetchMetadata();
  }, [fetchContactDetails]);

  // Delete Contact
  const handleDeleteContact = async () => {
    if (!window.confirm(`Are you sure you want to permanently delete ${contact.name}?`)) return;
    try {
      await api.contacts.delete(id);
      navigate('/contacts');
    } catch (err) {
      alert(err.response?.data?.message || 'Failed to delete contact.');
    }
  };

  // Toggle Primary Status
  const handleTogglePrimary = async () => {
    try {
      const res = await api.contacts.update(id, {
        ...contact,
        is_primary: !contact.is_primary,
      });
      if (res.data.success) {
        fetchContactDetails();
      }
    } catch (err) {
      alert(err.response?.data?.message || 'Failed to update primary status.');
    }
  };

  // Log Activity
  const handleLogActivity = async (e) => {
    e.preventDefault();
    if (!activityNotes.trim()) return;
    setActivitySubmitting(true);
    try {
      await api.activities.create({
        subject_type: 'Contact',
        subject_id: parseInt(id, 10),
        type: activityType,
        subject_title: activitySubject.trim() || `${activityType.toUpperCase()} with ${contact.name}`,
        description: activityNotes.trim(),
      });
      setActivityNotes('');
      setActivitySubject('');
      fetchContactDetails();
    } catch (err) {
      alert(err.response?.data?.message || 'Failed to log activity.');
    } finally {
      setActivitySubmitting(false);
    }
  };

  // Create Task
  const handleCreateTask = async (e) => {
    e.preventDefault();
    if (!taskTitle.trim()) return;
    setTaskSubmitting(true);
    try {
      await api.tasks.create({
        title: taskTitle.trim(),
        description: taskDescription.trim() || null,
        priority: taskPriority,
        due_date: taskDueDate || null,
        related_to_type: 'Contact',
        related_to_id: parseInt(id, 10),
        status: 'Pending',
      });
      setTaskTitle('');
      setTaskDescription('');
      setTaskDueDate('');
      setShowTaskForm(false);
      fetchContactDetails();
    } catch (err) {
      alert(err.response?.data?.message || 'Failed to create task.');
    } finally {
      setTaskSubmitting(false);
    }
  };

  // Toggle Task Status
  const handleToggleTaskStatus = async (taskId, currentStatus) => {
    const isCompleted = currentStatus && currentStatus.toLowerCase() === 'completed';
    const newStatus = isCompleted ? 'Pending' : 'Completed';
    try {
      await api.tasks.updateStatus(taskId, newStatus);
      fetchContactDetails();
    } catch (err) {
      alert(err.response?.data?.message || 'Failed to update task.');
    }
  };

  // Create Associated Deal
  const handleCreateDeal = async (e) => {
    e.preventDefault();
    if (!dealName.trim()) return;
    setDealSubmitting(true);
    try {
      await api.deals.create({
        name: dealName.trim(),
        value: parseFloat(dealValue) || 0,
        close_date: dealCloseDate || null,
        contact_id: parseInt(id, 10),
        company_id: contact.company?.id || null,
        status: 'open',
      });
      setDealName('');
      setDealValue('');
      setDealCloseDate('');
      setShowDealModal(false);
      fetchContactDetails();
    } catch (err) {
      alert(err.response?.data?.message || 'Failed to create deal.');
    } finally {
      setDealSubmitting(false);
    }
  };

  const getInitials = (name) => {
    if (!name) return 'C';
    const parts = name.split(' ').filter(Boolean);
    if (parts.length >= 2) {
      return (parts[0][0] + parts[1][0]).toUpperCase();
    }
    return name.slice(0, 2).toUpperCase();
  };

  if (loading) {
    return (
      <div className="p-12 text-center text-gray-400">
        <div className="flex items-center justify-center space-x-2">
          <svg className="animate-spin h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24">
            <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
            <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
          </svg>
          <span className="font-semibold text-gray-600">Loading contact profile...</span>
        </div>
      </div>
    );
  }

  if (error || !contact) {
    return (
      <div className="p-8 max-w-3xl mx-auto text-center space-y-4">
        <div className="bg-rose-50 text-rose-700 p-6 rounded-2xl border border-rose-200">
          <h2 className="text-lg font-bold">Contact Profile Not Available</h2>
          <p className="text-sm mt-1">{error || 'This contact record may have been deleted or moved.'}</p>
        </div>
        <Link
          to="/contacts"
          className="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-xl text-sm font-semibold hover:bg-blue-700 transition shadow-md"
        >
          ← Return to Contacts Directory
        </Link>
      </div>
    );
  }

  const totalDealsValue = (contact.deals || []).reduce((acc, d) => acc + (parseFloat(d.value) || 0), 0);

  return (
    <div className="p-6 space-y-6 max-w-7xl mx-auto animate-fade-in">
      {/* Top Breadcrumb & Actions */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div className="flex items-center space-x-2 text-xs font-semibold text-gray-400">
          <Link to="/contacts" className="hover:text-blue-600 transition">
            📇 Contacts Directory
          </Link>
          <span>/</span>
          <span className="text-gray-800 font-bold">{contact.name}</span>
        </div>

        <div className="flex items-center space-x-2">
          <button
            onClick={handleTogglePrimary}
            className={`px-3 py-1.5 text-xs font-semibold rounded-xl border transition flex items-center ${
              contact.is_primary
                ? 'bg-amber-50 text-amber-700 border-amber-200 hover:bg-amber-100'
                : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50'
            }`}
          >
            ⭐ {contact.is_primary ? 'Primary Contact' : 'Mark as Primary'}
          </button>
          <button
            onClick={() => setIsEditModalOpen(true)}
            className="px-3.5 py-1.5 text-xs font-semibold text-gray-700 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 shadow-sm transition"
          >
            ✏️ Edit Profile
          </button>
          <button
            onClick={handleDeleteContact}
            className="px-3 py-1.5 text-xs font-semibold text-rose-600 bg-rose-50 border border-rose-200 rounded-xl hover:bg-rose-100 transition"
          >
            🗑️ Delete
          </button>
        </div>
      </div>

      {/* Main Profile Header Card */}
      <div className="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 overflow-hidden">
        <div className="flex flex-col md:flex-row md:items-center justify-between gap-6 pb-6 border-b border-gray-100">
          <div className="flex items-start space-x-4">
            <div className="w-16 h-16 rounded-2xl bg-gradient-to-tr from-indigo-600 to-blue-500 text-white flex items-center justify-center font-black text-xl shadow-lg shadow-indigo-200 flex-shrink-0">
              {getInitials(contact.name)}
            </div>
            <div>
              <div className="flex flex-wrap items-center gap-2">
                <h1 className="text-2xl font-black text-gray-900 tracking-tight">{contact.name}</h1>
                {contact.is_primary && (
                  <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                    ⭐ Primary Contact
                  </span>
                )}
              </div>
              <p className="text-sm font-semibold text-gray-600 mt-1">
                {contact.job_title || 'Position Unspecified'}
                {contact.company && (
                  <span className="text-indigo-600 font-bold ml-1.5">@ {contact.company.name}</span>
                )}
                {contact.department && <span className="text-gray-400 font-normal"> ({contact.department})</span>}
              </p>
              <p className="text-xs text-gray-400 mt-1">
                Managed by <strong className="text-gray-700">{contact.owner?.name || 'Unassigned'}</strong> • Added{' '}
                {contact.created_at ? new Date(contact.created_at).toLocaleDateString() : 'N/A'}
              </p>
            </div>
          </div>

          {/* Quick Contact Action Pills */}
          <div className="flex flex-wrap items-center gap-2">
            {contact.email && (
              <a
                href={`mailto:${contact.email}`}
                className="px-3.5 py-2 text-xs font-semibold text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-xl transition flex items-center"
              >
                ✉️ {contact.email}
              </a>
            )}
            {contact.phone && (
              <a
                href={`tel:${contact.phone}`}
                className="px-3.5 py-2 text-xs font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded-xl transition flex items-center"
              >
                📞 {contact.phone}
              </a>
            )}
            {contact.mobile && (
              <a
                href={`tel:${contact.mobile}`}
                className="px-3.5 py-2 text-xs font-semibold text-purple-700 bg-purple-50 hover:bg-purple-100 rounded-xl transition flex items-center"
              >
                📱 {contact.mobile}
              </a>
            )}
            <button
              onClick={() => setShowDealModal(true)}
              className="px-4 py-2 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-md shadow-indigo-200 transition"
            >
              + Create Deal
            </button>
          </div>
        </div>

        {/* Quick Relationship Bar */}
        <div className="grid grid-cols-2 sm:grid-cols-4 gap-4 pt-6 text-xs">
          <div className="p-3 bg-slate-50 rounded-xl border border-slate-100">
            <span className="text-gray-400 block font-semibold mb-0.5">Associated Company</span>
            {contact.company ? (
              <span className="font-bold text-gray-800 text-sm">🏢 {contact.company.name}</span>
            ) : (
              <span className="text-gray-400 italic">None linked</span>
            )}
          </div>

          <div className="p-3 bg-slate-50 rounded-xl border border-slate-100">
            <span className="text-gray-400 block font-semibold mb-0.5">Associated Lead</span>
            {contact.lead ? (
              <Link to={`/leads/${contact.lead.id}`} className="font-bold text-blue-600 text-sm hover:underline block truncate">
                👥 {contact.lead.name || `Lead #${contact.lead.id}`}
              </Link>
            ) : (
              <span className="text-gray-400 italic">None linked</span>
            )}
          </div>

          <div className="p-3 bg-slate-50 rounded-xl border border-slate-100">
            <span className="text-gray-400 block font-semibold mb-0.5">Connected Deals</span>
            <span className="font-bold text-emerald-600 text-sm">
              {(contact.deals || []).length} Deals (${totalDealsValue.toLocaleString()})
            </span>
          </div>

          <div className="p-3 bg-slate-50 rounded-xl border border-slate-100">
            <span className="text-gray-400 block font-semibold mb-0.5">Location</span>
            <span className="font-bold text-gray-800 text-sm truncate block">
              {[contact.city, contact.state, contact.country].filter(Boolean).join(', ') || 'Not specified'}
            </span>
          </div>
        </div>
      </div>

      {/* Tabs Navigation */}
      <div className="flex border-b border-gray-200 space-x-6 text-sm font-bold">
        <button
          onClick={() => setActiveTab('overview')}
          className={`pb-3 border-b-2 transition flex items-center space-x-2 ${
            activeTab === 'overview'
              ? 'border-blue-600 text-blue-600'
              : 'border-transparent text-gray-400 hover:text-gray-700'
          }`}
        >
          <span>📋 Overview & Details</span>
        </button>

        <button
          onClick={() => setActiveTab('deals')}
          className={`pb-3 border-b-2 transition flex items-center space-x-2 ${
            activeTab === 'deals'
              ? 'border-blue-600 text-blue-600'
              : 'border-transparent text-gray-400 hover:text-gray-700'
          }`}
        >
          <span>🤝 Deals ({(contact.deals || []).length})</span>
        </button>

        <button
          onClick={() => setActiveTab('tasks')}
          className={`pb-3 border-b-2 transition flex items-center space-x-2 ${
            activeTab === 'tasks'
              ? 'border-blue-600 text-blue-600'
              : 'border-transparent text-gray-400 hover:text-gray-700'
          }`}
        >
          <span>✓ Tasks ({(contact.tasks || []).length})</span>
        </button>

        <button
          onClick={() => setActiveTab('activities')}
          className={`pb-3 border-b-2 transition flex items-center space-x-2 ${
            activeTab === 'activities'
              ? 'border-blue-600 text-blue-600'
              : 'border-transparent text-gray-400 hover:text-gray-700'
          }`}
        >
          <span>💬 Activity History ({(contact.activities || []).length})</span>
        </button>
      </div>

      {/* Tab Content */}
      {/* 1. OVERVIEW TAB */}
      {activeTab === 'overview' && (
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
          {/* Main Info Card */}
          <div className="md:col-span-2 space-y-6">
            <div className="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-4">
              <h3 className="text-base font-bold text-gray-900 border-b border-gray-100 pb-3">
                Contact Specifications
              </h3>
              <div className="grid grid-cols-2 gap-4 text-xs">
                <div>
                  <span className="text-gray-400 block font-medium">First Name</span>
                  <p className="font-semibold text-gray-800 text-sm mt-0.5">{contact.first_name}</p>
                </div>
                <div>
                  <span className="text-gray-400 block font-medium">Last Name</span>
                  <p className="font-semibold text-gray-800 text-sm mt-0.5">{contact.last_name}</p>
                </div>
                <div>
                  <span className="text-gray-400 block font-medium">Email Address</span>
                  <p className="font-semibold text-gray-800 text-sm mt-0.5">{contact.email || '—'}</p>
                </div>
                <div>
                  <span className="text-gray-400 block font-medium">Work Phone</span>
                  <p className="font-semibold text-gray-800 text-sm mt-0.5">{contact.phone || '—'}</p>
                </div>
                <div>
                  <span className="text-gray-400 block font-medium">Mobile Phone</span>
                  <p className="font-semibold text-gray-800 text-sm mt-0.5">{contact.mobile || '—'}</p>
                </div>
                <div>
                  <span className="text-gray-400 block font-medium">Job Title</span>
                  <p className="font-semibold text-gray-800 text-sm mt-0.5">{contact.job_title || '—'}</p>
                </div>
                <div>
                  <span className="text-gray-400 block font-medium">Department</span>
                  <p className="font-semibold text-gray-800 text-sm mt-0.5">{contact.department || '—'}</p>
                </div>
                <div>
                  <span className="text-gray-400 block font-medium">Organization Role</span>
                  <p className="font-semibold text-gray-800 text-sm mt-0.5">
                    {contact.is_primary ? 'Primary Stakeholder' : 'Secondary Contact'}
                  </p>
                </div>
              </div>
            </div>

            {/* Notes & Bio */}
            <div className="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-3">
              <h3 className="text-base font-bold text-gray-900 border-b border-gray-100 pb-3">
                Background & Profile Notes
              </h3>
              <div className="bg-slate-50 p-4 rounded-xl text-xs text-gray-700 leading-relaxed min-h-[80px]">
                {contact.notes || <span className="text-gray-400 italic">No notes recorded for this contact yet.</span>}
              </div>
            </div>
          </div>

          {/* Right Sidebar: Location & Relationships */}
          <div className="space-y-6">
            {/* Address Card */}
            <div className="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-3">
              <h3 className="text-sm font-bold text-gray-900 border-b border-gray-100 pb-2">
                Postal & Location
              </h3>
              <div className="space-y-2 text-xs">
                <div>
                  <span className="text-gray-400 block">Street Address</span>
                  <p className="font-semibold text-gray-800">{contact.address || '—'}</p>
                </div>
                <div className="grid grid-cols-2 gap-2">
                  <div>
                    <span className="text-gray-400 block">City</span>
                    <p className="font-semibold text-gray-800">{contact.city || '—'}</p>
                  </div>
                  <div>
                    <span className="text-gray-400 block">State / Prov</span>
                    <p className="font-semibold text-gray-800">{contact.state || '—'}</p>
                  </div>
                </div>
                <div className="grid grid-cols-2 gap-2">
                  <div>
                    <span className="text-gray-400 block">Zip / Postal</span>
                    <p className="font-semibold text-gray-800">{contact.zip || '—'}</p>
                  </div>
                  <div>
                    <span className="text-gray-400 block">Country</span>
                    <p className="font-semibold text-gray-800">{contact.country || '—'}</p>
                  </div>
                </div>
              </div>
            </div>

            {/* Account Owner Card */}
            <div className="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-3">
              <h3 className="text-sm font-bold text-gray-900 border-b border-gray-100 pb-2">
                Relationship Owner
              </h3>
              <div className="flex items-center space-x-3">
                <div className="w-10 h-10 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-sm">
                  {contact.owner ? contact.owner.name.charAt(0) : 'U'}
                </div>
                <div>
                  <p className="text-sm font-bold text-gray-900">{contact.owner?.name || 'Unassigned'}</p>
                  <p className="text-xs text-gray-400">{contact.owner?.email || 'No email'}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      )}

      {/* 2. DEALS TAB */}
      {activeTab === 'deals' && (
        <div className="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-4">
          <div className="flex items-center justify-between">
            <div>
              <h3 className="text-base font-bold text-gray-900">Associated Deals</h3>
              <p className="text-xs text-gray-500 mt-0.5">Commercial opportunities connected to this contact.</p>
            </div>
            <button
              onClick={() => setShowDealModal(true)}
              className="px-3.5 py-1.5 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition shadow-sm"
            >
              + Add Deal
            </button>
          </div>

          {(contact.deals || []).length === 0 ? (
            <div className="p-10 text-center text-gray-400 bg-slate-50 rounded-xl border border-dashed border-gray-200">
              <span className="text-3xl block mb-2">🤝</span>
              <p className="font-semibold text-gray-700 text-sm">No deals linked to this contact yet</p>
              <button
                onClick={() => setShowDealModal(true)}
                className="mt-3 px-3.5 py-1.5 text-xs font-semibold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition"
              >
                + Create First Deal
              </button>
            </div>
          ) : (
            <div className="overflow-x-auto">
              <table className="w-full text-left text-xs">
                <thead className="bg-slate-50 text-gray-400 font-bold uppercase tracking-wider border-b border-gray-100">
                  <tr>
                    <th className="px-4 py-2.5">Deal Name</th>
                    <th className="px-4 py-2.5">Value</th>
                    <th className="px-4 py-2.5">Pipeline Stage</th>
                    <th className="px-4 py-2.5">Status</th>
                    <th className="px-4 py-2.5">Expected Close</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-100">
                  {contact.deals.map((deal) => (
                    <tr key={deal.id} className="hover:bg-slate-50/70 transition">
                      <td className="px-4 py-3 font-bold text-gray-900">{deal.name}</td>
                      <td className="px-4 py-3 font-bold text-emerald-600">
                        ${parseFloat(deal.value || 0).toLocaleString()}
                      </td>
                      <td className="px-4 py-3">
                        <span className="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200 capitalize">
                          {deal.pipeline_stage ? deal.pipeline_stage.replace('_', ' ') : 'Open'}
                        </span>
                      </td>
                      <td className="px-4 py-3 capitalize font-semibold text-gray-700">{deal.status || 'open'}</td>
                      <td className="px-4 py-3 text-gray-500">{deal.expected_close_date || '—'}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}
        </div>
      )}

      {/* 3. TASKS TAB */}
      {activeTab === 'tasks' && (
        <div className="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-4">
          <div className="flex items-center justify-between">
            <div>
              <h3 className="text-base font-bold text-gray-900">Linked Tasks & Follow-ups</h3>
              <p className="text-xs text-gray-500 mt-0.5">Manage actionable items regarding this contact.</p>
            </div>
            <button
              onClick={() => setShowTaskForm(!showTaskForm)}
              className="px-3.5 py-1.5 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition shadow-sm"
            >
              {showTaskForm ? 'Cancel' : '+ New Task'}
            </button>
          </div>

          {/* New Task Inline Form */}
          {showTaskForm && (
            <form onSubmit={handleCreateTask} className="bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-3">
              <h4 className="text-xs font-bold text-gray-800">Add New Follow-Up Task</h4>
              <div className="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div className="md:col-span-2">
                  <input
                    type="text"
                    required
                    placeholder="Task title (e.g. Schedule quarterly contract review)..."
                    value={taskTitle}
                    onChange={(e) => setTaskTitle(e.target.value)}
                    className="w-full px-3 py-1.5 text-xs border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                </div>
                <div>
                  <input
                    type="date"
                    value={taskDueDate}
                    onChange={(e) => setTaskDueDate(e.target.value)}
                    className="w-full px-3 py-1.5 text-xs border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
                  />
                </div>
              </div>

              <div className="flex items-center justify-between">
                <div className="flex items-center space-x-2 text-xs">
                  <span className="text-gray-500 font-semibold">Priority:</span>
                  <select
                    value={taskPriority}
                    onChange={(e) => setTaskPriority(e.target.value)}
                    className="px-2 py-1 text-xs border border-gray-200 rounded-lg bg-white"
                  >
                    <option value="Low">Low</option>
                    <option value="Medium">Medium</option>
                    <option value="High">High</option>
                    <option value="Urgent">Urgent</option>
                  </select>
                </div>

                <button
                  type="submit"
                  disabled={taskSubmitting}
                  className="px-4 py-1.5 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm transition disabled:opacity-50"
                >
                  {taskSubmitting ? 'Saving...' : 'Save Task'}
                </button>
              </div>
            </form>
          )}

          {(contact.tasks || []).length === 0 ? (
            <div className="p-8 text-center text-gray-400 bg-slate-50 rounded-xl border border-dashed border-gray-200">
              <p className="font-semibold text-gray-700 text-sm">No tasks assigned to this contact</p>
            </div>
          ) : (
            <div className="space-y-2">
              {contact.tasks.map((task) => (
                <div
                  key={task.id}
                  className="flex items-center justify-between p-3.5 bg-slate-50/60 rounded-xl border border-slate-100 hover:bg-slate-100/60 transition"
                >
                  <div className="flex items-center space-x-3">
                    <input
                      type="checkbox"
                      checked={task.status === 'completed'}
                      onChange={() => handleToggleTaskStatus(task.id, task.status)}
                      className="w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500"
                    />
                    <div>
                      <p
                        className={`text-xs font-bold ${
                          task.status === 'completed' ? 'line-through text-gray-400' : 'text-gray-900'
                        }`}
                      >
                        {task.title}
                      </p>
                      <p className="text-[11px] text-gray-400">
                        Due: {task.due_date ? new Date(task.due_date).toLocaleDateString() : 'No date'}
                      </p>
                    </div>
                  </div>
                  <span
                    className={`text-[10px] font-bold px-2 py-0.5 rounded-full capitalize ${
                      task.priority === 'urgent'
                        ? 'bg-rose-50 text-rose-700 border border-rose-200'
                        : task.priority === 'high'
                        ? 'bg-amber-50 text-amber-700 border border-amber-200'
                        : 'bg-slate-100 text-gray-700'
                    }`}
                  >
                    {task.priority}
                  </span>
                </div>
              ))}
            </div>
          )}
        </div>
      )}

      {/* 4. ACTIVITIES TAB */}
      {activeTab === 'activities' && (
        <div className="space-y-6">
          {/* Activity Logger Box */}
          <div className="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <h3 className="text-base font-bold text-gray-900 mb-4">Log Contact Activity</h3>
            <form onSubmit={handleLogActivity} className="space-y-3">
              <div className="flex items-center space-x-2">
                {['note', 'call', 'meeting', 'email'].map((type) => (
                  <button
                    key={type}
                    type="button"
                    onClick={() => setActivityType(type)}
                    className={`px-3 py-1.5 rounded-xl text-xs font-bold capitalize transition ${
                      activityType === type
                        ? 'bg-indigo-600 text-white shadow-sm'
                        : 'bg-slate-100 text-gray-600 hover:bg-slate-200'
                    }`}
                  >
                    {type === 'note' && '📝 Note'}
                    {type === 'call' && '📞 Phone Call'}
                    {type === 'meeting' && '🤝 Meeting'}
                    {type === 'email' && '✉️ Email'}
                  </button>
                ))}
              </div>

              <input
                type="text"
                placeholder="Activity Subject / Summary (optional)..."
                value={activitySubject}
                onChange={(e) => setActivitySubject(e.target.value)}
                className="w-full px-3.5 py-2 text-xs border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500"
              />

              <textarea
                rows={3}
                required
                placeholder={`Enter details about the ${activityType}...`}
                value={activityNotes}
                onChange={(e) => setActivityNotes(e.target.value)}
                className="w-full px-3.5 py-2 text-xs border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500"
              />

              <div className="flex justify-end">
                <button
                  type="submit"
                  disabled={activitySubmitting}
                  className="px-4 py-2 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-md transition disabled:opacity-50"
                >
                  {activitySubmitting ? 'Logging...' : 'Log Activity'}
                </button>
              </div>
            </form>
          </div>

          {/* Activity Timeline */}
          <div className="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-4">
            <h3 className="text-base font-bold text-gray-900 border-b border-gray-100 pb-3">
              Interaction Timeline
            </h3>

            {(contact.activities || []).length === 0 ? (
              <div className="p-8 text-center text-gray-400">
                <p className="text-xs">No activity logs recorded yet.</p>
              </div>
            ) : (
              <div className="space-y-4">
                {contact.activities.map((act) => (
                  <div key={act.id} className="flex items-start space-x-3 pb-3 border-b border-gray-50 last:border-0">
                    <div className="w-8 h-8 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs font-bold flex-shrink-0">
                      {act.type === 'call' ? '📞' : act.type === 'meeting' ? '🤝' : act.type === 'email' ? '✉️' : '📝'}
                    </div>
                    <div className="flex-1 text-xs">
                      <div className="flex items-center justify-between">
                        <p className="font-bold text-gray-900">{act.subject}</p>
                        <span className="text-[10px] text-gray-400">
                          {act.created_at ? new Date(act.created_at).toLocaleString() : ''}
                        </span>
                      </div>
                      <p className="text-gray-700 mt-1">{act.description}</p>
                      <p className="text-[10px] text-gray-400 mt-1">Logged by {act.user_name || 'System'}</p>
                    </div>
                  </div>
                ))}
              </div>
            )}
          </div>
        </div>
      )}

      {/* Create Deal Modal */}
      {showDealModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4 animate-fade-in">
          <div className="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 space-y-4">
            <div className="flex items-center justify-between border-b border-gray-100 pb-3">
              <h3 className="text-lg font-bold text-gray-900">Create Opportunity / Deal</h3>
              <button onClick={() => setShowDealModal(false)} className="text-gray-400 hover:text-gray-600">
                ✕
              </button>
            </div>
            <form onSubmit={handleCreateDeal} className="space-y-3 text-xs">
              <div>
                <label className="block font-semibold text-gray-700 mb-1">Deal Title *</label>
                <input
                  type="text"
                  required
                  placeholder="e.g. Enterprise License Expansion"
                  value={dealName}
                  onChange={(e) => setDealName(e.target.value)}
                  className="w-full px-3 py-2 text-xs border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>

              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block font-semibold text-gray-700 mb-1">Deal Value ($)</label>
                  <input
                    type="number"
                    placeholder="25000"
                    value={dealValue}
                    onChange={(e) => setDealValue(e.target.value)}
                    className="w-full px-3 py-2 text-xs border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                </div>

                <div>
                  <label className="block font-semibold text-gray-700 mb-1">Pipeline Stage</label>
                  <select
                    value={dealStage}
                    onChange={(e) => setDealStage(e.target.value)}
                    className="w-full px-3 py-2 text-xs border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
                  >
                    <option value="lead_in">Lead In</option>
                    <option value="contact_made">Contact Made</option>
                    <option value="meeting_scheduled">Meeting Scheduled</option>
                    <option value="proposal_made">Proposal Made</option>
                    <option value="closed_won">Closed Won</option>
                  </select>
                </div>
              </div>

              <div>
                <label className="block font-semibold text-gray-700 mb-1">Target Close Date</label>
                <input
                  type="date"
                  value={dealCloseDate}
                  onChange={(e) => setDealCloseDate(e.target.value)}
                  className="w-full px-3 py-2 text-xs border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
                />
              </div>

              <div className="pt-3 border-t border-gray-100 flex justify-end space-x-2">
                <button
                  type="button"
                  onClick={() => setShowDealModal(false)}
                  className="px-3 py-1.5 text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  disabled={dealSubmitting}
                  className="px-4 py-1.5 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-md disabled:opacity-50"
                >
                  {dealSubmitting ? 'Creating...' : 'Create Deal'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Edit Profile Modal */}
      <ContactModal
        isOpen={isEditModalOpen}
        onClose={() => setIsEditModalOpen(false)}
        contactToEdit={contact}
        companies={companies}
        owners={owners}
        leads={leads}
        onSaved={() => {
          fetchContactDetails();
        }}
      />
    </div>
  );
}
