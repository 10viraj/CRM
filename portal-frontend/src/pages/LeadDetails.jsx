import React, { useState, useEffect } from 'react';
import { useParams, useNavigate, Link } from 'react-router-dom';
import { api } from '../services/api';
import LeadModal from '../components/LeadModal';

export default function LeadDetails() {
  const { id } = useParams();
  const navigate = useNavigate();

  const [lead, setLead] = useState(null);
  const [metadata, setMetadata] = useState({ statuses: [], sources: [], users: [] });
  const [loading, setLoading] = useState(true);
  const [activeTab, setActiveTab] = useState('overview');
  const [isEditModalOpen, setIsEditModalOpen] = useState(false);
  const [toastMessage, setToastMessage] = useState(null);

  // New Activity State
  const [activityForm, setActivityForm] = useState({
    type: 'call',
    title: '',
    description: '',
    duration_minutes: 15,
  });
  const [activityLoading, setActivityLoading] = useState(false);

  // New Task State
  const [taskForm, setTaskForm] = useState({
    title: '',
    description: '',
    priority: 'Medium',
    due_date: new Date(Date.now() + 86400000).toISOString().split('T')[0],
  });
  const [taskLoading, setTaskLoading] = useState(false);
  const [isTaskModalOpen, setIsTaskModalOpen] = useState(false);

  // Convert to Deal State
  const [dealForm, setDealForm] = useState({
    name: '',
    value: 25000,
    probability: 60,
  });
  const [dealLoading, setDealLoading] = useState(false);
  const [isDealModalOpen, setIsDealModalOpen] = useState(false);

  // Toast Helper
  const showToast = (msg) => {
    setToastMessage(msg);
    setTimeout(() => setToastMessage(null), 3500);
  };

  // Fetch Lead & Metadata
  const fetchLeadDetails = async () => {
    setLoading(true);
    try {
      const [leadRes, metaRes] = await Promise.all([
        api.leads.get(id),
        api.leads.metadata(),
      ]);

      if (leadRes.data.success) {
        setLead(leadRes.data.data);
        setDealForm(prev => ({
          ...prev,
          name: `${leadRes.data.data.company || leadRes.data.data.name} Deal`,
        }));
      }
      if (metaRes.data.success) {
        setMetadata(metaRes.data);
      }
    } catch (err) {
      console.error('Error fetching lead details', err);
      if (err.response?.status === 404) {
        alert('Lead not found.');
        navigate('/leads');
      }
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchLeadDetails();
  }, [id]);

  // Status Stepper Progression Click
  const handleStatusChange = async (statusId) => {
    if (!lead) return;
    try {
      const res = await api.leads.update(lead.id, {
        first_name: lead.first_name,
        last_name: lead.last_name,
        email: lead.email,
        phone: lead.phone,
        company: lead.company,
        lead_status_id: statusId,
        score: lead.score,
      });
      if (res.data.success) {
        setLead(res.data.data);
        showToast('Lead status updated.');
      }
    } catch (err) {
      alert('Failed to update lead status.');
    }
  };

  // Delete Lead
  const handleDelete = async () => {
    if (!window.confirm('Are you sure you want to permanently delete this lead?')) return;
    try {
      await api.leads.delete(id);
      navigate('/leads');
    } catch (err) {
      alert('Failed to delete lead.');
    }
  };

  // Log Activity
  const handleLogActivity = async (e) => {
    e.preventDefault();
    if (!activityForm.title.trim()) return;

    setActivityLoading(true);
    try {
      await api.activities.create({
        type: activityForm.type,
        title: activityForm.title,
        description: activityForm.description,
        duration_minutes: Number(activityForm.duration_minutes),
        subject_type: 'Lead',
        subject_id: lead.id,
      });
      setActivityForm({ type: 'call', title: '', description: '', duration_minutes: 15 });
      showToast('Activity logged successfully.');
      fetchLeadDetails();
    } catch (err) {
      alert('Failed to log activity.');
    } finally {
      setActivityLoading(false);
    }
  };

  // Create Task for Lead
  const handleCreateTask = async (e) => {
    e.preventDefault();
    if (!taskForm.title.trim()) return;

    setTaskLoading(true);
    try {
      await api.tasks.create({
        title: taskForm.title,
        description: taskForm.description,
        priority: taskForm.priority,
        due_date: taskForm.due_date,
        related_to_type: 'Lead',
        related_to_id: lead.id,
        status: 'Pending',
      });
      setTaskForm({
        title: '',
        description: '',
        priority: 'Medium',
        due_date: new Date(Date.now() + 86400000).toISOString().split('T')[0],
      });
      setIsTaskModalOpen(false);
      showToast('Task created.');
      fetchLeadDetails();
    } catch (err) {
      alert('Failed to create task.');
    } finally {
      setTaskLoading(false);
    }
  };

  // Convert Lead to Deal
  const handleConvertToDeal = async (e) => {
    e.preventDefault();
    setDealLoading(true);
    try {
      await api.deals.create({
        name: dealForm.name,
        value: Number(dealForm.value),
        probability: Number(dealForm.probability),
        lead_id: lead.id,
        company_id: lead.company_id || null,
        status: 'open',
      });
      setIsDealModalOpen(false);
      showToast('Deal created from lead successfully.');
      fetchLeadDetails();
    } catch (err) {
      alert('Failed to convert to deal.');
    } finally {
      setDealLoading(false);
    }
  };

  if (loading || !lead) {
    return (
      <div className="p-20 text-center text-slate-400 flex flex-col items-center justify-center space-y-3">
        <svg className="animate-spin h-8 w-8 text-indigo-600" fill="none" viewBox="0 0 24 24">
          <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
          <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" />
        </svg>
        <p className="text-sm font-medium">Loading lead profile...</p>
      </div>
    );
  }

  const currentStatusId = lead.lead_status_id || lead.status?.id;

  return (
    <div className="p-6 space-y-6 max-w-7xl mx-auto">
      {/* Toast Notification */}
      {toastMessage && (
        <div className="fixed bottom-6 right-6 z-50 bg-slate-900 text-white px-5 py-3 rounded-2xl shadow-xl border border-slate-800 flex items-center space-x-3 animate-fade-in">
          <span className="w-2 h-2 rounded-full bg-emerald-400"></span>
          <span className="text-sm font-medium">{toastMessage}</span>
        </div>
      )}

      {/* Breadcrumbs */}
      <nav className="flex items-center space-x-2 text-xs font-semibold text-slate-400">
        <Link to="/dashboard" className="hover:text-slate-700 transition">Dashboard</Link>
        <span>/</span>
        <Link to="/leads" className="hover:text-slate-700 transition">Leads</Link>
        <span>/</span>
        <span className="text-slate-800 font-bold">{lead.name || `Lead #${lead.id}`}</span>
      </nav>

      {/* Top Profile Header Card */}
      <div className="bg-white rounded-2xl border border-slate-100 shadow-xs p-6">
        <div className="flex flex-col md:flex-row md:items-center justify-between gap-6">
          <div className="flex items-center space-x-4">
            <div className="w-16 h-16 rounded-2xl bg-gradient-to-tr from-indigo-600 to-indigo-400 text-white font-black text-2xl flex items-center justify-center shadow-lg shadow-indigo-100">
              {lead.first_name ? lead.first_name.charAt(0) : 'L'}
            </div>
            <div>
              <div className="flex items-center space-x-3">
                <h1 className="text-2xl font-black text-slate-900">{lead.first_name} {lead.last_name}</h1>
                <span className="px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                  {lead.status?.name || 'New'}
                </span>
                <span className={`px-2 py-0.5 rounded-full text-xs font-black ${
                  lead.score >= 70 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' :
                  lead.score >= 40 ? 'bg-amber-50 text-amber-700 border border-amber-200' :
                  'bg-slate-100 text-slate-700'
                }`}>
                  ★ {lead.score ?? 0} Score
                </span>
              </div>

              <div className="flex flex-wrap items-center gap-y-1 gap-x-4 text-xs text-slate-500 mt-1">
                {lead.company && (
                  <span className="flex items-center font-medium text-slate-700">
                    🏢 {lead.company}
                  </span>
                )}
                <span className="flex items-center">
                  ✉️ {lead.email}
                </span>
                {lead.phone && (
                  <span className="flex items-center">
                    📞 {lead.phone}
                  </span>
                )}
              </div>
            </div>
          </div>

          <div className="flex flex-wrap items-center gap-2">
            <button
              onClick={() => setIsDealModalOpen(true)}
              className="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-sm shadow-emerald-200 transition flex items-center space-x-1.5"
            >
              <span>🤝</span>
              <span>Convert to Deal</span>
            </button>

            <button
              onClick={() => setIsEditModalOpen(true)}
              className="px-3.5 py-2 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 text-xs font-semibold rounded-xl shadow-xs transition flex items-center space-x-1.5"
            >
              <span>✏️</span>
              <span>Edit Lead</span>
            </button>

            <button
              onClick={handleDelete}
              className="px-3.5 py-2 bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-semibold rounded-xl transition flex items-center space-x-1.5"
            >
              <span>🗑️</span>
              <span>Delete</span>
            </button>
          </div>
        </div>

        {/* Pipeline Stage Stepper */}
        <div className="mt-6 pt-6 border-t border-slate-100">
          <p className="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-3">
            Lead Qualification Pipeline (Click to update status)
          </p>
          <div className="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-2">
            {metadata.statuses?.map((st, idx) => {
              const isCurrent = currentStatusId === st.id;
              return (
                <button
                  key={st.id}
                  type="button"
                  onClick={() => handleStatusChange(st.id)}
                  className={`p-2 rounded-xl text-xs font-bold text-center transition border ${
                    isCurrent
                      ? 'bg-indigo-600 text-white border-indigo-600 shadow-md shadow-indigo-100'
                      : 'bg-slate-50 hover:bg-slate-100 text-slate-600 border-slate-200'
                  }`}
                >
                  <div className="text-[10px] opacity-75">Step {idx + 1}</div>
                  <div className="truncate">{st.name}</div>
                </button>
              );
            })}
          </div>
        </div>
      </div>

      {/* Tabs Navigation */}
      <div className="flex border-b border-slate-200 space-x-6 text-sm font-bold">
        <button
          onClick={() => setActiveTab('overview')}
          className={`pb-3 transition border-b-2 ${
            activeTab === 'overview'
              ? 'border-indigo-600 text-indigo-600'
              : 'border-transparent text-slate-400 hover:text-slate-700'
          }`}
        >
          📋 Overview & Info
        </button>

        <button
          onClick={() => setActiveTab('activities')}
          className={`pb-3 transition border-b-2 flex items-center space-x-1.5 ${
            activeTab === 'activities'
              ? 'border-indigo-600 text-indigo-600'
              : 'border-transparent text-slate-400 hover:text-slate-700'
          }`}
        >
          <span>💬 Activities & Notes</span>
        </button>

        <button
          onClick={() => setActiveTab('tasks')}
          className={`pb-3 transition border-b-2 flex items-center space-x-1.5 ${
            activeTab === 'tasks'
              ? 'border-indigo-600 text-indigo-600'
              : 'border-transparent text-slate-400 hover:text-slate-700'
          }`}
        >
          <span>✓ Tasks</span>
        </button>
      </div>

      {/* Tab 1: Overview */}
      {activeTab === 'overview' && (
        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
          {/* Contact Details Card */}
          <div className="bg-white p-6 rounded-2xl border border-slate-100 shadow-xs space-y-4">
            <h3 className="font-bold text-slate-900 text-sm border-b border-slate-100 pb-3">
              Prospect Details
            </h3>
            <div className="grid grid-cols-2 gap-4 text-xs">
              <div>
                <p className="text-slate-400 font-semibold">First Name</p>
                <p className="text-slate-800 font-bold mt-0.5">{lead.first_name}</p>
              </div>
              <div>
                <p className="text-slate-400 font-semibold">Last Name</p>
                <p className="text-slate-800 font-bold mt-0.5">{lead.last_name}</p>
              </div>
              <div>
                <p className="text-slate-400 font-semibold">Email Address</p>
                <p className="text-slate-800 font-bold mt-0.5">{lead.email}</p>
              </div>
              <div>
                <p className="text-slate-400 font-semibold">Phone Number</p>
                <p className="text-slate-800 font-bold mt-0.5">{lead.phone || 'N/A'}</p>
              </div>
              <div>
                <p className="text-slate-400 font-semibold">Lead Source</p>
                <p className="text-slate-800 font-bold mt-0.5">{lead.source?.name || 'Direct'}</p>
              </div>
              <div>
                <p className="text-slate-400 font-semibold">Account Owner</p>
                <p className="text-slate-800 font-bold mt-0.5">{lead.owner?.name || 'Unassigned'}</p>
              </div>
            </div>
          </div>

          {/* Company & Scoring Details Card */}
          <div className="bg-white p-6 rounded-2xl border border-slate-100 shadow-xs space-y-4">
            <h3 className="font-bold text-slate-900 text-sm border-b border-slate-100 pb-3">
              Organization & Scoring
            </h3>
            <div className="grid grid-cols-2 gap-4 text-xs">
              <div>
                <p className="text-slate-400 font-semibold">Company Name</p>
                <p className="text-slate-800 font-bold mt-0.5">{lead.company || lead.company_name || 'Individual Prospect'}</p>
              </div>
              <div>
                <p className="text-slate-400 font-semibold">Qualification Score</p>
                <p className="text-emerald-600 font-black mt-0.5">{lead.score ?? 0} / 100</p>
              </div>
              <div>
                <p className="text-slate-400 font-semibold">Created Date</p>
                <p className="text-slate-800 font-medium mt-0.5">{new Date(lead.created_at).toLocaleString()}</p>
              </div>
              <div>
                <p className="text-slate-400 font-semibold">Last Updated</p>
                <p className="text-slate-800 font-medium mt-0.5">{new Date(lead.updated_at).toLocaleString()}</p>
              </div>
            </div>

            {/* Notes */}
            <div className="pt-3 border-t border-slate-100">
              <p className="text-slate-400 font-semibold text-xs mb-1">Notes & Discovery Context</p>
              <p className="text-slate-700 text-xs bg-slate-50 p-3 rounded-xl border border-slate-100 whitespace-pre-wrap">
                {lead.notes || 'No discovery notes recorded yet.'}
              </p>
            </div>
          </div>
        </div>
      )}

      {/* Tab 2: Activities */}
      {activeTab === 'activities' && (
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          {/* Activity Form */}
          <div className="bg-white p-6 rounded-2xl border border-slate-100 shadow-xs h-fit space-y-4">
            <h3 className="font-bold text-slate-900 text-sm border-b border-slate-100 pb-3">
              Log Activity
            </h3>
            <form onSubmit={handleLogActivity} className="space-y-3 text-xs">
              <div>
                <label className="block font-semibold text-slate-700 mb-1">Activity Type</label>
                <select
                  value={activityForm.type}
                  onChange={(e) => setActivityForm({ ...activityForm, type: e.target.value })}
                  className="w-full px-3 py-2 border border-slate-200 rounded-xl bg-slate-50 outline-none focus:ring-2 focus:ring-indigo-500"
                >
                  <option value="call">📞 Phone Call</option>
                  <option value="email">✉️ Email Message</option>
                  <option value="meeting">🤝 In-person / Video Meeting</option>
                  <option value="note">📝 Internal Note</option>
                </select>
              </div>

              <div>
                <label className="block font-semibold text-slate-700 mb-1">Subject / Title *</label>
                <input
                  type="text"
                  required
                  placeholder="e.g. Discovery call regarding pricing"
                  value={activityForm.title}
                  onChange={(e) => setActivityForm({ ...activityForm, title: e.target.value })}
                  className="w-full px-3 py-2 border border-slate-200 rounded-xl bg-slate-50 outline-none focus:ring-2 focus:ring-indigo-500"
                />
              </div>

              <div>
                <label className="block font-semibold text-slate-700 mb-1">Summary / Notes</label>
                <textarea
                  rows="3"
                  placeholder="Key discussion points, customer reaction, next steps..."
                  value={activityForm.description}
                  onChange={(e) => setActivityForm({ ...activityForm, description: e.target.value })}
                  className="w-full px-3 py-2 border border-slate-200 rounded-xl bg-slate-50 outline-none focus:ring-2 focus:ring-indigo-500 resize-none"
                />
              </div>

              <button
                type="submit"
                disabled={activityLoading}
                className="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-xs transition disabled:opacity-50"
              >
                {activityLoading ? 'Saving...' : 'Log Activity'}
              </button>
            </form>
          </div>

          {/* Activity Timeline List */}
          <div className="lg:col-span-2 bg-white p-6 rounded-2xl border border-slate-100 shadow-xs space-y-4">
            <h3 className="font-bold text-slate-900 text-sm border-b border-slate-100 pb-3">
              Activity History
            </h3>
            <div className="space-y-4">
              <div className="p-4 bg-slate-50 rounded-xl border border-slate-100 flex items-start space-x-3">
                <span className="text-xl">🌟</span>
                <div>
                  <p className="font-bold text-slate-800 text-xs">Lead Captured & Registered</p>
                  <p className="text-[11px] text-slate-500 mt-0.5">Initial record created in CRM system.</p>
                  <span className="text-[10px] text-slate-400 font-mono mt-1 block">
                    {new Date(lead.created_at).toLocaleString()}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      )}

      {/* Tab 3: Tasks */}
      {activeTab === 'tasks' && (
        <div className="bg-white p-6 rounded-2xl border border-slate-100 shadow-xs space-y-4">
          <div className="flex justify-between items-center border-b border-slate-100 pb-3">
            <h3 className="font-bold text-slate-900 text-sm">Actionable Tasks</h3>
            <button
              onClick={() => setIsTaskModalOpen(true)}
              className="px-3 py-1.5 bg-indigo-600 text-white text-xs font-bold rounded-xl hover:bg-indigo-700 transition"
            >
              + Add Task
            </button>
          </div>

          <div className="p-8 text-center text-slate-400 text-xs">
            No pending tasks for this lead. Click "+ Add Task" to schedule follow-ups.
          </div>
        </div>
      )}

      {/* Edit Modal */}
      <LeadModal
        isOpen={isEditModalOpen}
        onClose={() => setIsEditModalOpen(false)}
        onSuccess={() => {
          showToast('Lead updated.');
          fetchLeadDetails();
        }}
        lead={lead}
        metadata={metadata}
      />

      {/* Add Task Modal */}
      {isTaskModalOpen && (
        <div className="fixed inset-0 z-50 bg-slate-900/60 flex items-center justify-center p-4">
          <div className="bg-white rounded-2xl p-6 max-w-md w-full shadow-xl space-y-4">
            <h3 className="font-bold text-slate-900 text-base">Create Task for {lead.name}</h3>
            <form onSubmit={handleCreateTask} className="space-y-3 text-xs">
              <div>
                <label className="block font-semibold text-slate-700 mb-1">Task Title *</label>
                <input
                  type="text"
                  required
                  placeholder="e.g. Send technical whitepaper"
                  value={taskForm.title}
                  onChange={(e) => setTaskForm({ ...taskForm, title: e.target.value })}
                  className="w-full px-3 py-2 border rounded-xl"
                />
              </div>
              <div>
                <label className="block font-semibold text-slate-700 mb-1">Priority</label>
                <select
                  value={taskForm.priority}
                  onChange={(e) => setTaskForm({ ...taskForm, priority: e.target.value })}
                  className="w-full px-3 py-2 border rounded-xl"
                >
                  <option value="Low">Low</option>
                  <option value="Medium">Medium</option>
                  <option value="High">High</option>
                  <option value="Urgent">Urgent</option>
                </select>
              </div>
              <div>
                <label className="block font-semibold text-slate-700 mb-1">Due Date</label>
                <input
                  type="date"
                  value={taskForm.due_date}
                  onChange={(e) => setTaskForm({ ...taskForm, due_date: e.target.value })}
                  className="w-full px-3 py-2 border rounded-xl"
                />
              </div>
              <div className="flex justify-end space-x-2 pt-2">
                <button
                  type="button"
                  onClick={() => setIsTaskModalOpen(false)}
                  className="px-4 py-2 text-slate-600 hover:bg-slate-100 rounded-xl"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  disabled={taskLoading}
                  className="px-4 py-2 bg-indigo-600 text-white rounded-xl font-bold"
                >
                  {taskLoading ? 'Creating...' : 'Create Task'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Convert to Deal Modal */}
      {isDealModalOpen && (
        <div className="fixed inset-0 z-50 bg-slate-900/60 flex items-center justify-center p-4">
          <div className="bg-white rounded-2xl p-6 max-w-md w-full shadow-xl space-y-4">
            <h3 className="font-bold text-slate-900 text-base">Convert Lead to Deal</h3>
            <p className="text-xs text-slate-500">Create an active sales deal in your revenue pipeline.</p>
            <form onSubmit={handleConvertToDeal} className="space-y-3 text-xs">
              <div>
                <label className="block font-semibold text-slate-700 mb-1">Deal Name *</label>
                <input
                  type="text"
                  required
                  value={dealForm.name}
                  onChange={(e) => setDealForm({ ...dealForm, name: e.target.value })}
                  className="w-full px-3 py-2 border rounded-xl"
                />
              </div>
              <div>
                <label className="block font-semibold text-slate-700 mb-1">Estimated Value ($) *</label>
                <input
                  type="number"
                  required
                  min="0"
                  value={dealForm.value}
                  onChange={(e) => setDealForm({ ...dealForm, value: e.target.value })}
                  className="w-full px-3 py-2 border rounded-xl"
                />
              </div>
              <div>
                <label className="block font-semibold text-slate-700 mb-1">Probability (%)</label>
                <input
                  type="number"
                  min="0"
                  max="100"
                  value={dealForm.probability}
                  onChange={(e) => setDealForm({ ...dealForm, probability: e.target.value })}
                  className="w-full px-3 py-2 border rounded-xl"
                />
              </div>
              <div className="flex justify-end space-x-2 pt-2">
                <button
                  type="button"
                  onClick={() => setIsDealModalOpen(false)}
                  className="px-4 py-2 text-slate-600 hover:bg-slate-100 rounded-xl"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  disabled={dealLoading}
                  className="px-4 py-2 bg-emerald-600 text-white rounded-xl font-bold hover:bg-emerald-700"
                >
                  {dealLoading ? 'Converting...' : 'Create Deal'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}
