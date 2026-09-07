import React, { useState, useEffect, useCallback } from 'react';
import { useParams, Link, useNavigate } from 'react-router-dom';
import { api } from '../services/api';
import DealModal from '../components/DealModal';

export default function DealDetails() {
  const { id } = useParams();
  const navigate = useNavigate();

  const [deal, setDeal] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [activeTab, setActiveTab] = useState('overview');

  // Metadata & Options
  const [stages, setStages] = useState([]);
  const [companies, setCompanies] = useState([]);
  const [contacts, setContacts] = useState([]);
  const [leads, setLeads] = useState([]);
  const [owners, setOwners] = useState([]);

  // Edit Modal State
  const [isEditModalOpen, setIsEditModalOpen] = useState(false);

  // Activity Logger State
  const [activityType, setActivityType] = useState('note');
  const [activitySubject, setActivitySubject] = useState('');
  const [activityNotes, setActivityNotes] = useState('');
  const [activitySubmitting, setActivitySubmitting] = useState(false);

  // Task Creation State
  const [showTaskForm, setShowTaskForm] = useState(false);
  const [taskTitle, setTaskTitle] = useState('');
  const [taskPriority, setTaskPriority] = useState('Medium');
  const [taskDueDate, setTaskDueDate] = useState('');
  const [taskDescription, setTaskDescription] = useState('');
  const [taskSubmitting, setTaskSubmitting] = useState(false);

  // Fetch Deal Details
  const fetchDealDetails = useCallback(async () => {
    setLoading(true);
    setError('');
    try {
      const res = await api.deals.get(id);
      if (res.data.success) {
        setDeal(res.data.data);
      }
    } catch (err) {
      setError(err.response?.data?.message || 'Failed to load deal details.');
    } finally {
      setLoading(false);
    }
  }, [id]);

  // Fetch metadata for stages and options
  const fetchMetadata = async () => {
    try {
      const res = await api.deals.metadata();
      if (res.data.success) {
        setStages(res.data.stages || []);
        setCompanies(res.data.options?.companies || []);
        setContacts(res.data.options?.contacts || []);
        setLeads(res.data.options?.leads || []);
        setOwners(res.data.options?.owners || []);
      }
    } catch (err) {
      console.error('Failed to load deal metadata:', err);
    }
  };

  useEffect(() => {
    fetchDealDetails();
    fetchMetadata();
  }, [fetchDealDetails]);

  // Update Deal Stage
  const handleStageChange = async (targetStageId) => {
    try {
      const matchedStage = stages.find((s) => s.id === targetStageId);
      const isWon = matchedStage?.name?.toLowerCase() === 'won';
      const isLost = matchedStage?.name?.toLowerCase() === 'lost';
      const newStatus = isWon ? 'won' : isLost ? 'lost' : 'open';

      // Optimistic update
      setDeal((prev) => ({
        ...prev,
        deal_stage_id: targetStageId,
        stage: matchedStage || prev.stage,
        status: newStatus,
      }));

      await api.deals.updateStage(id, {
        deal_stage_id: targetStageId,
        status: newStatus,
      });

      fetchDealDetails();
    } catch (err) {
      alert(err.response?.data?.message || 'Failed to update deal stage.');
      fetchDealDetails();
    }
  };

  // Delete Deal
  const handleDeleteDeal = async () => {
    if (!window.confirm(`Are you sure you want to delete deal "${deal.name || deal.title}"?`)) return;
    try {
      await api.deals.delete(id);
      navigate('/deals');
    } catch (err) {
      alert(err.response?.data?.message || 'Failed to delete deal.');
    }
  };

  // Log Activity
  const handleLogActivity = async (e) => {
    e.preventDefault();
    if (!activityNotes.trim()) return;
    setActivitySubmitting(true);
    try {
      await api.activities.create({
        subject_type: 'Deal',
        subject_id: parseInt(id, 10),
        type: activityType,
        subject_title: activitySubject.trim() || `${activityType.toUpperCase()} regarding ${deal.name || deal.title}`,
        description: activityNotes.trim(),
      });
      setActivityNotes('');
      setActivitySubject('');
      fetchDealDetails();
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
        related_to_type: 'Deal',
        related_to_id: parseInt(id, 10),
        status: 'Pending',
      });
      setTaskTitle('');
      setTaskDescription('');
      setTaskDueDate('');
      setShowTaskForm(false);
      fetchDealDetails();
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
      fetchDealDetails();
    } catch (err) {
      alert(err.response?.data?.message || 'Failed to update task.');
    }
  };

  if (loading) {
    return (
      <div className="p-12 text-center text-gray-400">
        <div className="flex items-center justify-center space-x-2">
          <svg className="animate-spin h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24">
            <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
            <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
          </svg>
          <span className="font-semibold text-gray-600">Loading opportunity details...</span>
        </div>
      </div>
    );
  }

  if (error || !deal) {
    return (
      <div className="p-8 max-w-3xl mx-auto text-center space-y-4">
        <div className="bg-rose-50 text-rose-700 p-6 rounded-2xl border border-rose-200">
          <h2 className="text-lg font-bold">Deal Record Not Found</h2>
          <p className="text-sm mt-1">{error || 'This deal may have been removed.'}</p>
        </div>
        <Link
          to="/deals"
          className="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-xl text-sm font-semibold hover:bg-blue-700 transition"
        >
          ← Return to Deals Pipeline
        </Link>
      </div>
    );
  }

  const currentStageIndex = deal.stage?.order_index || 1;

  return (
    <div className="p-6 space-y-6 max-w-7xl mx-auto animate-fade-in">
      {/* Top Breadcrumb & Actions */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div className="flex items-center space-x-2 text-xs font-semibold text-gray-400">
          <Link to="/deals" className="hover:text-blue-600 transition">
            🤝 Deals Pipeline
          </Link>
          <span>/</span>
          <span className="text-gray-800 font-bold">{deal.name || deal.title}</span>
        </div>

        <div className="flex items-center space-x-2">
          {deal.status !== 'won' && (
            <button
              onClick={() => {
                const wonStage = stages.find((s) => s.name.toLowerCase() === 'won');
                if (wonStage) handleStageChange(wonStage.id);
              }}
              className="px-3 py-1.5 text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-300 hover:bg-emerald-100 rounded-xl transition flex items-center gap-1 shadow-xs"
            >
              <span>🏆</span> Mark Won
            </button>
          )}

          {deal.status !== 'lost' && (
            <button
              onClick={() => {
                const lostStage = stages.find((s) => s.name.toLowerCase() === 'lost');
                if (lostStage) handleStageChange(lostStage.id);
              }}
              className="px-3 py-1.5 text-xs font-bold text-rose-700 bg-rose-50 border border-rose-300 hover:bg-rose-100 rounded-xl transition flex items-center gap-1 shadow-xs"
            >
              <span>❌</span> Mark Lost
            </button>
          )}

          <button
            onClick={() => setIsEditModalOpen(true)}
            className="px-3.5 py-1.5 text-xs font-semibold text-gray-700 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 shadow-xs transition"
          >
            ✏️ Edit Deal
          </button>
          <button
            onClick={handleDeleteDeal}
            className="px-3 py-1.5 text-xs font-semibold text-rose-600 bg-rose-50 border border-rose-200 rounded-xl hover:bg-rose-100 transition"
          >
            🗑️ Delete
          </button>
        </div>
      </div>

      {/* Main Deal Header Card */}
      <div className="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 overflow-hidden space-y-6">
        <div className="flex flex-col md:flex-row md:items-center justify-between gap-6 pb-6 border-b border-gray-100">
          <div>
            <div className="flex flex-wrap items-center gap-2.5">
              <h1 className="text-2xl font-black text-gray-900 tracking-tight">{deal.name || deal.title}</h1>
              <span
                className={`inline-flex items-center px-3 py-0.5 rounded-full text-xs font-extrabold capitalize ${
                  deal.status === 'won'
                    ? 'bg-emerald-100 text-emerald-800'
                    : deal.status === 'lost'
                    ? 'bg-rose-100 text-rose-800'
                    : 'bg-blue-100 text-blue-800'
                }`}
              >
                {deal.status === 'won' ? '🏆 Won' : deal.status === 'lost' ? '❌ Lost' : '🟢 Open Opportunity'}
              </span>
            </div>

            <p className="text-xs text-gray-400 mt-1">
              Deal Owner: <strong className="text-gray-700">{deal.owner?.name || 'Unassigned'}</strong> • Created{' '}
              {deal.created_at ? new Date(deal.created_at).toLocaleDateString() : 'recently'}
            </p>
          </div>

          <div className="flex items-center space-x-6">
            <div className="text-right">
              <span className="text-xs font-bold text-gray-400 uppercase tracking-wider block">Deal Value</span>
              <span className="text-3xl font-black text-emerald-600">
                ${parseFloat(deal.value || 0).toLocaleString()}
              </span>
            </div>

            <div className="text-right border-l border-gray-100 pl-6">
              <span className="text-xs font-bold text-gray-400 uppercase tracking-wider block">Win Probability</span>
              <span className="text-2xl font-black text-indigo-600">{deal.probability || 50}%</span>
            </div>
          </div>
        </div>

        {/* Interactive 7-Stage Pipeline Stepper */}
        <div>
          <div className="flex items-center justify-between mb-2">
            <span className="text-xs font-bold text-gray-500 uppercase tracking-wider">
              Pipeline Stage Progression: <strong className="text-indigo-600">{deal.stage?.name || 'New'}</strong>
            </span>
            <span className="text-xs text-gray-400">Click any step to update stage</span>
          </div>

          <div className="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-7 gap-2">
            {stages.map((stage) => {
              const isActive = deal.deal_stage_id === stage.id;
              const isPast = stage.order_index <= currentStageIndex;

              return (
                <button
                  key={stage.id}
                  onClick={() => handleStageChange(stage.id)}
                  className={`p-3 rounded-xl border text-left transition relative flex flex-col justify-between ${
                    isActive
                      ? 'bg-indigo-600 text-white border-indigo-600 shadow-md'
                      : isPast
                      ? 'bg-indigo-50/70 text-indigo-900 border-indigo-200 hover:bg-indigo-100'
                      : 'bg-slate-50 text-gray-500 border-slate-200 hover:bg-slate-100'
                  }`}
                >
                  <div className="flex items-center justify-between w-full text-[10px] font-bold mb-1 opacity-80">
                    <span>Step {stage.order_index}</span>
                    {isActive && <span>✓ Active</span>}
                  </div>
                  <span className="font-bold text-xs">{stage.name}</span>
                </button>
              );
            })}
          </div>
        </div>

        {/* Quick Relationship Bar */}
        <div className="grid grid-cols-2 sm:grid-cols-4 gap-4 pt-4 border-t border-gray-100 text-xs">
          <div className="p-3 bg-slate-50 rounded-xl border border-slate-100">
            <span className="text-gray-400 block font-semibold mb-0.5">Associated Company</span>
            {deal.company ? (
              <span className="font-bold text-gray-800 text-sm">🏢 {deal.company.name}</span>
            ) : (
              <span className="text-gray-400 italic">None linked</span>
            )}
          </div>

          <div className="p-3 bg-slate-50 rounded-xl border border-slate-100">
            <span className="text-gray-400 block font-semibold mb-0.5">Primary Contact Person</span>
            {deal.contact ? (
              <Link to={`/contacts/${deal.contact.id}`} className="font-bold text-blue-600 text-sm hover:underline block truncate">
                👤 {deal.contact.name}
              </Link>
            ) : (
              <span className="text-gray-400 italic">None linked</span>
            )}
          </div>

          <div className="p-3 bg-slate-50 rounded-xl border border-slate-100">
            <span className="text-gray-400 block font-semibold mb-0.5">Target Close Date</span>
            <span className="font-bold text-gray-800 text-sm">
              {deal.close_date ? `📅 ${new Date(deal.close_date).toLocaleDateString()}` : 'Not set'}
            </span>
          </div>

          <div className="p-3 bg-slate-50 rounded-xl border border-slate-100">
            <span className="text-gray-400 block font-semibold mb-0.5">Originating Lead</span>
            {deal.lead ? (
              <Link to={`/leads/${deal.lead.id}`} className="font-bold text-blue-600 text-sm hover:underline block truncate">
                👥 {deal.lead.name || `Lead #${deal.lead.id}`}
              </Link>
            ) : (
              <span className="text-gray-400 italic">Direct Opportunity</span>
            )}
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
          <span>📋 Commercial Overview</span>
        </button>

        <button
          onClick={() => setActiveTab('tasks')}
          className={`pb-3 border-b-2 transition flex items-center space-x-2 ${
            activeTab === 'tasks'
              ? 'border-blue-600 text-blue-600'
              : 'border-transparent text-gray-400 hover:text-gray-700'
          }`}
        >
          <span>✓ Follow-Up Tasks ({(deal.tasks || []).length})</span>
        </button>

        <button
          onClick={() => setActiveTab('activities')}
          className={`pb-3 border-b-2 transition flex items-center space-x-2 ${
            activeTab === 'activities'
              ? 'border-blue-600 text-blue-600'
              : 'border-transparent text-gray-400 hover:text-gray-700'
          }`}
        >
          <span>💬 Interaction & Audit History ({(deal.activities || []).length})</span>
        </button>
      </div>

      {/* Tab 1: Overview */}
      {activeTab === 'overview' && (
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div className="md:col-span-2 space-y-6">
            <div className="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-4">
              <h3 className="text-base font-bold text-gray-900 border-b border-gray-100 pb-3">
                Commercial Parameters
              </h3>
              <div className="grid grid-cols-2 gap-4 text-xs">
                <div>
                  <span className="text-gray-400 block font-medium">Deal Title</span>
                  <p className="font-semibold text-gray-800 text-sm mt-0.5">{deal.name || deal.title}</p>
                </div>
                <div>
                  <span className="text-gray-400 block font-medium">Value</span>
                  <p className="font-black text-emerald-600 text-sm mt-0.5">
                    ${parseFloat(deal.value || 0).toLocaleString()}
                  </p>
                </div>
                <div>
                  <span className="text-gray-400 block font-medium">Pipeline Stage</span>
                  <p className="font-semibold text-indigo-600 text-sm mt-0.5">{deal.stage?.name || 'New'}</p>
                </div>
                <div>
                  <span className="text-gray-400 block font-medium">Win Probability</span>
                  <p className="font-semibold text-gray-800 text-sm mt-0.5">{deal.probability || 50}%</p>
                </div>
                <div>
                  <span className="text-gray-400 block font-medium">Status</span>
                  <p className="font-semibold text-gray-800 text-sm mt-0.5 capitalize">{deal.status || 'open'}</p>
                </div>
                <div>
                  <span className="text-gray-400 block font-medium">Target Close Date</span>
                  <p className="font-semibold text-gray-800 text-sm mt-0.5">
                    {deal.close_date ? new Date(deal.close_date).toLocaleDateString() : 'None'}
                  </p>
                </div>
              </div>
            </div>

            {/* Scope & Notes */}
            <div className="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-3">
              <h3 className="text-base font-bold text-gray-900 border-b border-gray-100 pb-3">
                Scope & Commercial Terms
              </h3>
              <div className="bg-slate-50 p-4 rounded-xl text-xs text-gray-700 leading-relaxed min-h-[90px]">
                {deal.notes || <span className="text-gray-400 italic">No notes recorded for this deal.</span>}
              </div>
            </div>
          </div>

          {/* Right Sidebar: Key Relationships */}
          <div className="space-y-6">
            {/* Associated Contact Card */}
            <div className="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-3">
              <h3 className="text-sm font-bold text-gray-900 border-b border-gray-100 pb-2">
                Key Contact Person
              </h3>
              {deal.contact ? (
                <div className="space-y-2 text-xs">
                  <p className="font-bold text-gray-900 text-sm">{deal.contact.name}</p>
                  {deal.contact.email && (
                    <p className="text-blue-600">✉️ {deal.contact.email}</p>
                  )}
                  {deal.contact.phone && (
                    <p className="text-gray-600">📞 {deal.contact.phone}</p>
                  )}
                  <Link
                    to={`/contacts/${deal.contact.id}`}
                    className="inline-block mt-2 text-xs font-semibold text-blue-600 hover:underline"
                  >
                    View Contact Profile →
                  </Link>
                </div>
              ) : (
                <p className="text-xs text-gray-400 italic">No contact person linked.</p>
              )}
            </div>

            {/* Deal Owner Card */}
            <div className="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-3">
              <h3 className="text-sm font-bold text-gray-900 border-b border-gray-100 pb-2">
                Deal Owner / Representative
              </h3>
              <div className="flex items-center space-x-3">
                <div className="w-10 h-10 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-sm">
                  {deal.owner ? deal.owner.name.charAt(0) : 'U'}
                </div>
                <div>
                  <p className="text-sm font-bold text-gray-900">{deal.owner?.name || 'Unassigned'}</p>
                  <p className="text-xs text-gray-400">{deal.owner?.email || 'No email'}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      )}

      {/* Tab 2: Tasks */}
      {activeTab === 'tasks' && (
        <div className="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-4">
          <div className="flex items-center justify-between">
            <div>
              <h3 className="text-base font-bold text-gray-900">Follow-up Tasks</h3>
              <p className="text-xs text-gray-500 mt-0.5">Manage deliverables and commercial milestones for this deal.</p>
            </div>
            <button
              onClick={() => setShowTaskForm(!showTaskForm)}
              className="px-3.5 py-1.5 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition shadow-xs"
            >
              {showTaskForm ? 'Cancel' : '+ New Task'}
            </button>
          </div>

          {showTaskForm && (
            <form onSubmit={handleCreateTask} className="bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-3">
              <h4 className="text-xs font-bold text-gray-800">Add Deal Follow-up Task</h4>
              <div className="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div className="md:col-span-2">
                  <input
                    type="text"
                    required
                    placeholder="Task title (e.g. Send finalized MSA and SLA agreement)..."
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

          {(deal.tasks || []).length === 0 ? (
            <div className="p-8 text-center text-gray-400 bg-slate-50 rounded-xl border border-dashed border-gray-200">
              <p className="font-semibold text-gray-700 text-sm">No tasks assigned to this deal</p>
            </div>
          ) : (
            <div className="space-y-2">
              {deal.tasks.map((task) => (
                <div
                  key={task.id}
                  className="flex items-center justify-between p-3.5 bg-slate-50/60 rounded-xl border border-slate-100 hover:bg-slate-100/60 transition"
                >
                  <div className="flex items-center space-x-3">
                    <input
                      type="checkbox"
                      checked={task.status?.toLowerCase() === 'completed'}
                      onChange={() => handleToggleTaskStatus(task.id, task.status)}
                      className="w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500"
                    />
                    <div>
                      <p
                        className={`text-xs font-bold ${
                          task.status?.toLowerCase() === 'completed' ? 'line-through text-gray-400' : 'text-gray-900'
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
                      task.priority === 'Urgent'
                        ? 'bg-rose-50 text-rose-700 border border-rose-200'
                        : task.priority === 'High'
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

      {/* Tab 3: Activities & Stage Changes */}
      {activeTab === 'activities' && (
        <div className="space-y-6">
          {/* Quick Logger Box */}
          <div className="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <h3 className="text-base font-bold text-gray-900 mb-4">Log Deal Interaction</h3>
            <form onSubmit={handleLogActivity} className="space-y-3">
              <div className="flex items-center space-x-2">
                {['note', 'call', 'meeting', 'email'].map((type) => (
                  <button
                    key={type}
                    type="button"
                    onClick={() => setActivityType(type)}
                    className={`px-3 py-1.5 rounded-xl text-xs font-bold capitalize transition ${
                      activityType === type
                        ? 'bg-indigo-600 text-white shadow-xs'
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
              Deal Activity & Audit Trail
            </h3>

            {(deal.activities || []).length === 0 ? (
              <div className="p-8 text-center text-gray-400">
                <p className="text-xs">No activity records logged for this deal yet.</p>
              </div>
            ) : (
              <div className="space-y-4">
                {deal.activities.map((act) => (
                  <div key={act.id} className="flex items-start space-x-3 pb-3 border-b border-gray-50 last:border-0">
                    <div className="w-8 h-8 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs font-bold flex-shrink-0">
                      {act.type === 'call'
                        ? '📞'
                        : act.type === 'meeting'
                        ? '🤝'
                        : act.type === 'email'
                        ? '✉️'
                        : act.type === 'Stage Changed'
                        ? '🔄'
                        : '📝'}
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

      {/* Edit Modal */}
      <DealModal
        isOpen={isEditModalOpen}
        onClose={() => setIsEditModalOpen(false)}
        dealToEdit={deal}
        stages={stages}
        companies={companies}
        contacts={contacts}
        leads={leads}
        owners={owners}
        onSaved={() => {
          fetchDealDetails();
          fetchMetadata();
        }}
      />
    </div>
  );
}
