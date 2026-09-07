import React, { useState, useEffect, useCallback } from 'react';
import { api } from '../services/api';
import TaskModal from '../components/TaskModal';

export default function TasksList() {
  const [tasks, setTasks] = useState([]);
  const [loading, setLoading] = useState(true);
  const [activeTab, setActiveTab] = useState('All'); // All, Pending, In Progress, Completed, Overdue
  const [search, setSearch] = useState('');
  const [priorityFilter, setPriorityFilter] = useState('All');
  const [assigneeFilter, setAssigneeFilter] = useState('');
  const [sortBy, setSortBy] = useState('due_date');
  const [sortDirection, setSortDirection] = useState('asc');
  const [currentPage, setCurrentPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);
  const [totalCount, setTotalCount] = useState(0);

  // KPIs / Meta
  const [stats, setStats] = useState({
    total: 0,
    pending: 0,
    in_progress: 0,
    completed: 0,
    overdue: 0,
  });
  const [usersList, setUsersList] = useState([]);

  // Selection & Modal
  const [selectedIds, setSelectedIds] = useState([]);
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [editingTask, setEditingTask] = useState(null);
  const [actionLoading, setActionLoading] = useState(false);

  // Fetch metadata / stats
  const fetchMetadata = async () => {
    try {
      const res = await api.tasks.metadata();
      if (res.data?.stats) {
        setStats(res.data.stats);
      }
      if (res.data?.options?.users) {
        setUsersList(res.data.options.users);
      }
    } catch (err) {
      console.error('Failed to fetch tasks metadata:', err);
    }
  };

  // Fetch tasks list
  const fetchTasks = useCallback(async () => {
    try {
      setLoading(true);
      const params = {
        page: currentPage,
        per_page: 15,
        search: search.trim() || undefined,
        status: activeTab !== 'All' ? activeTab : undefined,
        priority: priorityFilter !== 'All' ? priorityFilter : undefined,
        assignee_id: assigneeFilter || undefined,
        sort_by: sortBy,
        sort_direction: sortDirection,
      };

      const res = await api.tasks.list(params);
      if (res.data?.data) {
        setTasks(res.data.data);
        if (res.data.meta) {
          setTotalPages(res.data.meta.last_page || 1);
          setTotalCount(res.data.meta.total || 0);
        }
      }
    } catch (err) {
      console.error('Failed to fetch tasks:', err);
    } finally {
      setLoading(false);
    }
  }, [currentPage, activeTab, search, priorityFilter, assigneeFilter, sortBy, sortDirection]);

  useEffect(() => {
    fetchMetadata();
  }, []);

  useEffect(() => {
    fetchTasks();
  }, [fetchTasks]);

  // Handle Quick Complete / Toggle
  const handleToggleStatus = async (task) => {
    try {
      const newStatus = task.status === 'Completed' ? 'Pending' : 'Completed';
      await api.tasks.updateStatus(task.id, newStatus);
      fetchTasks();
      fetchMetadata();
    } catch (err) {
      console.error('Failed to update task status:', err);
    }
  };

  // Handle Delete Single Task
  const handleDeleteTask = async (id) => {
    if (!window.confirm('Are you sure you want to delete this task?')) return;
    try {
      await api.tasks.delete(id);
      setSelectedIds((prev) => prev.filter((i) => i !== id));
      fetchTasks();
      fetchMetadata();
    } catch (err) {
      alert(err.response?.data?.message || 'Failed to delete task.');
    }
  };

  // Bulk Complete
  const handleBulkComplete = async () => {
    if (!selectedIds.length) return;
    try {
      setActionLoading(true);
      await api.tasks.bulkComplete(selectedIds);
      setSelectedIds([]);
      fetchTasks();
      fetchMetadata();
    } catch (err) {
      alert('Failed to complete selected tasks.');
    } finally {
      setActionLoading(false);
    }
  };

  // Bulk Delete
  const handleBulkDelete = async () => {
    if (!selectedIds.length) return;
    if (!window.confirm(`Are you sure you want to delete ${selectedIds.length} selected tasks?`)) return;
    try {
      setActionLoading(true);
      await api.tasks.bulkDelete(selectedIds);
      setSelectedIds([]);
      fetchTasks();
      fetchMetadata();
    } catch (err) {
      alert('Failed to delete selected tasks.');
    } finally {
      setActionLoading(false);
    }
  };

  const handleSelectAll = (e) => {
    if (e.target.checked) {
      setSelectedIds(tasks.map((t) => t.id));
    } else {
      setSelectedIds([]);
    }
  };

  const handleSelectOne = (id) => {
    setSelectedIds((prev) =>
      prev.includes(id) ? prev.filter((i) => i !== id) : [...prev, id]
    );
  };

  const getPriorityBadge = (priority) => {
    switch (priority) {
      case 'Urgent':
        return 'bg-red-50 text-red-700 border-red-200';
      case 'High':
        return 'bg-amber-50 text-amber-700 border-amber-200';
      case 'Medium':
        return 'bg-blue-50 text-blue-700 border-blue-200';
      default:
        return 'bg-slate-100 text-slate-700 border-slate-200';
    }
  };

  const getStatusBadge = (status) => {
    switch (status) {
      case 'Completed':
        return 'bg-emerald-50 text-emerald-700 border-emerald-200';
      case 'In Progress':
        return 'bg-purple-50 text-purple-700 border-purple-200';
      case 'Pending':
        return 'bg-amber-50 text-amber-700 border-amber-200';
      case 'Cancelled':
        return 'bg-gray-100 text-gray-500 border-gray-200';
      default:
        return 'bg-slate-100 text-slate-700 border-slate-200';
    }
  };

  const isOverdue = (task) => {
    if (task.status === 'Completed' || !task.due_date) return false;
    const due = new Date(task.due_date);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    return due < today;
  };

  return (
    <div className="p-6 md:p-8 space-y-6">
      {/* Header */}
      <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <div className="flex items-center space-x-3">
            <h1 className="text-2xl font-black text-slate-900 tracking-tight flex items-center">
              <span className="mr-2.5 text-2xl">✓</span> Tasks & Action Items
            </h1>
            <span className="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
              {totalCount} Total
            </span>
          </div>
          <p className="text-slate-500 text-xs mt-1">
            Track, assign, and organize team deliverables across CRM leads, deals, and accounts.
          </p>
        </div>
        <div className="flex items-center space-x-3">
          <button
            onClick={() => {
              setEditingTask(null);
              setIsModalOpen(true);
            }}
            className="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-lg shadow-blue-500/20 transition flex items-center space-x-2"
          >
            <span>+ Add Task</span>
          </button>
        </div>
      </div>

      {/* KPI Cards */}
      <div className="grid grid-cols-2 md:grid-cols-5 gap-4">
        <div
          onClick={() => { setActiveTab('All'); setCurrentPage(1); }}
          className={`p-4 rounded-2xl border transition cursor-pointer ${
            activeTab === 'All' ? 'bg-blue-600 text-white border-blue-600 shadow-md shadow-blue-500/20' : 'bg-white border-slate-100 text-slate-800 hover:border-slate-300'
          }`}
        >
          <div className="flex items-center justify-between">
            <p className={`text-[11px] font-bold uppercase tracking-wider ${activeTab === 'All' ? 'text-blue-100' : 'text-slate-400'}`}>
              All Tasks
            </p>
            <span className="text-sm">📋</span>
          </div>
          <p className="text-2xl font-black mt-2">{stats.total || 0}</p>
          <p className={`text-[11px] mt-1 font-medium ${activeTab === 'All' ? 'text-blue-200' : 'text-slate-400'}`}>
            Full task backlog
          </p>
        </div>

        <div
          onClick={() => { setActiveTab('Pending'); setCurrentPage(1); }}
          className={`p-4 rounded-2xl border transition cursor-pointer ${
            activeTab === 'Pending' ? 'bg-amber-500 text-white border-amber-500 shadow-md shadow-amber-500/20' : 'bg-white border-slate-100 text-slate-800 hover:border-slate-300'
          }`}
        >
          <div className="flex items-center justify-between">
            <p className={`text-[11px] font-bold uppercase tracking-wider ${activeTab === 'Pending' ? 'text-amber-100' : 'text-slate-400'}`}>
              Pending
            </p>
            <span className="text-sm">⏳</span>
          </div>
          <p className="text-2xl font-black mt-2">{stats.pending || 0}</p>
          <p className={`text-[11px] mt-1 font-medium ${activeTab === 'Pending' ? 'text-amber-200' : 'text-slate-400'}`}>
            Awaiting action
          </p>
        </div>

        <div
          onClick={() => { setActiveTab('In Progress'); setCurrentPage(1); }}
          className={`p-4 rounded-2xl border transition cursor-pointer ${
            activeTab === 'In Progress' ? 'bg-purple-600 text-white border-purple-600 shadow-md shadow-purple-500/20' : 'bg-white border-slate-100 text-slate-800 hover:border-slate-300'
          }`}
        >
          <div className="flex items-center justify-between">
            <p className={`text-[11px] font-bold uppercase tracking-wider ${activeTab === 'In Progress' ? 'text-purple-100' : 'text-slate-400'}`}>
              In Progress
            </p>
            <span className="text-sm">⚡</span>
          </div>
          <p className="text-2xl font-black mt-2">{stats.in_progress || 0}</p>
          <p className={`text-[11px] mt-1 font-medium ${activeTab === 'In Progress' ? 'text-purple-200' : 'text-slate-400'}`}>
            Currently working
          </p>
        </div>

        <div
          onClick={() => { setActiveTab('Completed'); setCurrentPage(1); }}
          className={`p-4 rounded-2xl border transition cursor-pointer ${
            activeTab === 'Completed' ? 'bg-emerald-600 text-white border-emerald-600 shadow-md shadow-emerald-500/20' : 'bg-white border-slate-100 text-slate-800 hover:border-slate-300'
          }`}
        >
          <div className="flex items-center justify-between">
            <p className={`text-[11px] font-bold uppercase tracking-wider ${activeTab === 'Completed' ? 'text-emerald-100' : 'text-slate-400'}`}>
              Completed
            </p>
            <span className="text-sm">🏆</span>
          </div>
          <p className="text-2xl font-black mt-2">{stats.completed || 0}</p>
          <p className={`text-[11px] mt-1 font-medium ${activeTab === 'Completed' ? 'text-emerald-200' : 'text-slate-400'}`}>
            Finished deliverables
          </p>
        </div>

        <div
          onClick={() => { setActiveTab('Overdue'); setCurrentPage(1); }}
          className={`p-4 rounded-2xl border transition cursor-pointer ${
            activeTab === 'Overdue' ? 'bg-red-600 text-white border-red-600 shadow-md shadow-red-500/20' : 'bg-white border-slate-100 text-slate-800 hover:border-slate-300'
          }`}
        >
          <div className="flex items-center justify-between">
            <p className={`text-[11px] font-bold uppercase tracking-wider ${activeTab === 'Overdue' ? 'text-red-100' : 'text-slate-400'}`}>
              Overdue
            </p>
            <span className="text-sm">🚨</span>
          </div>
          <p className="text-2xl font-black mt-2">{stats.overdue || 0}</p>
          <p className={`text-[11px] mt-1 font-medium ${activeTab === 'Overdue' ? 'text-red-200' : 'text-slate-400'}`}>
            Needs immediate attention
          </p>
        </div>
      </div>

      {/* Tabs & Filters Bar */}
      <div className="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm space-y-4">
        {/* Status Tabs */}
        <div className="flex items-center justify-between border-b border-slate-100 pb-3 flex-wrap gap-2">
          <div className="flex items-center space-x-1.5 overflow-x-auto">
            {['All', 'Pending', 'In Progress', 'Completed', 'Overdue'].map((tab) => (
              <button
                key={tab}
                onClick={() => {
                  setActiveTab(tab);
                  setCurrentPage(1);
                }}
                className={`px-3.5 py-1.5 rounded-lg text-xs font-bold transition flex items-center space-x-2 ${
                  activeTab === tab
                    ? 'bg-blue-50 text-blue-700 border border-blue-200'
                    : 'text-slate-600 hover:bg-slate-50'
                }`}
              >
                <span>{tab}</span>
                <span className="px-1.5 py-0.2 rounded-full text-[10px] bg-slate-200 text-slate-700">
                  {tab === 'All' && stats.total}
                  {tab === 'Pending' && stats.pending}
                  {tab === 'In Progress' && stats.in_progress}
                  {tab === 'Completed' && stats.completed}
                  {tab === 'Overdue' && stats.overdue}
                </span>
              </button>
            ))}
          </div>

          {/* Bulk Actions Indicator */}
          {selectedIds.length > 0 && (
            <div className="flex items-center space-x-2 animate-in fade-in">
              <span className="text-xs font-semibold text-slate-500">
                {selectedIds.length} selected
              </span>
              <button
                onClick={handleBulkComplete}
                disabled={actionLoading}
                className="px-3 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100 rounded-lg text-xs font-bold transition"
              >
                ✓ Complete
              </button>
              <button
                onClick={handleBulkDelete}
                disabled={actionLoading}
                className="px-3 py-1 bg-red-50 text-red-700 border border-red-200 hover:bg-red-100 rounded-lg text-xs font-bold transition"
              >
                🗑 Delete
              </button>
            </div>
          )}
        </div>

        {/* Filter & Search Inputs */}
        <div className="grid grid-cols-1 md:grid-cols-4 gap-3 pt-1">
          <div className="relative md:col-span-2">
            <span className="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs">🔍</span>
            <input
              type="text"
              value={search}
              onChange={(e) => {
                setSearch(e.target.value);
                setCurrentPage(1);
              }}
              placeholder="Search tasks by title, instructions..."
              className="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition"
            />
          </div>

          <div>
            <select
              value={priorityFilter}
              onChange={(e) => {
                setPriorityFilter(e.target.value);
                setCurrentPage(1);
              }}
              className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition"
            >
              <option value="All">All Priorities</option>
              <option value="Urgent">Urgent 🔥</option>
              <option value="High">High Priority</option>
              <option value="Medium">Medium Priority</option>
              <option value="Low">Low Priority</option>
            </select>
          </div>

          <div>
            <select
              value={assigneeFilter}
              onChange={(e) => {
                setAssigneeFilter(e.target.value);
                setCurrentPage(1);
              }}
              className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition"
            >
              <option value="">All Team Assignees</option>
              {usersList.map((u) => (
                <option key={u.id} value={u.id}>
                  {u.name}
                </option>
              ))}
            </select>
          </div>
        </div>
      </div>

      {/* Task List / Table */}
      <div className="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        {loading ? (
          <div className="py-20 text-center">
            <div className="inline-block w-8 h-8 border-4 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
            <p className="mt-3 text-xs font-semibold text-slate-500">Loading tasks from database...</p>
          </div>
        ) : tasks.length === 0 ? (
          <div className="py-20 text-center space-y-3">
            <div className="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-xl mx-auto">
              ✓
            </div>
            <p className="text-sm font-bold text-slate-800">No tasks found</p>
            <p className="text-xs text-slate-500 max-w-sm mx-auto">
              {search || priorityFilter !== 'All' || activeTab !== 'All'
                ? 'No tasks matched your current search and filter criteria.'
                : 'Get started by creating your first actionable team task.'}
            </p>
            <button
              onClick={() => {
                setEditingTask(null);
                setIsModalOpen(true);
              }}
              className="px-4 py-2 bg-blue-600 text-white rounded-xl text-xs font-bold hover:bg-blue-700 transition"
            >
              + Add New Task
            </button>
          </div>
        ) : (
          <div className="divide-y divide-slate-100">
            {/* Table Header */}
            <div className="px-6 py-3.5 bg-slate-50 flex items-center justify-between text-[11px] font-bold text-slate-500 uppercase tracking-wider">
              <div className="flex items-center space-x-3 w-1/2">
                <input
                  type="checkbox"
                  checked={selectedIds.length === tasks.length && tasks.length > 0}
                  onChange={handleSelectAll}
                  className="rounded text-blue-600 focus:ring-blue-500 w-4 h-4"
                />
                <span>Task / Description</span>
              </div>
              <div className="w-1/6">Linked Entity</div>
              <div className="w-1/6">Assignee</div>
              <div className="w-1/6 text-right">Due Date & Actions</div>
            </div>

            {/* Task Rows */}
            {tasks.map((task) => {
              const overdue = isOverdue(task);
              return (
                <div
                  key={task.id}
                  className={`px-6 py-4 flex items-center justify-between hover:bg-slate-50/80 transition ${
                    task.status === 'Completed' ? 'opacity-70 bg-slate-50/40' : ''
                  }`}
                >
                  {/* Left: Checkbox + Status Toggle + Title */}
                  <div className="flex items-start space-x-3 w-1/2">
                    <input
                      type="checkbox"
                      checked={selectedIds.includes(task.id)}
                      onChange={() => handleSelectOne(task.id)}
                      className="mt-1 rounded text-blue-600 focus:ring-blue-500 w-4 h-4"
                    />

                    {/* Quick Complete Button */}
                    <button
                      onClick={() => handleToggleStatus(task)}
                      title={task.status === 'Completed' ? 'Mark Incomplete' : 'Mark Completed'}
                      className={`mt-0.5 w-5 h-5 rounded-md border flex items-center justify-center transition flex-shrink-0 ${
                        task.status === 'Completed'
                          ? 'bg-emerald-600 border-emerald-600 text-white'
                          : 'border-slate-300 hover:border-emerald-500 hover:text-emerald-600'
                      }`}
                    >
                      {task.status === 'Completed' && '✓'}
                    </button>

                    <div>
                      <div className="flex items-center space-x-2">
                        <p
                          className={`text-sm font-bold ${
                            task.status === 'Completed'
                              ? 'line-through text-slate-400'
                              : 'text-slate-900'
                          }`}
                        >
                          {task.title}
                        </p>
                        <span
                          className={`px-2 py-0.5 rounded-full text-[10px] font-bold border ${getPriorityBadge(
                            task.priority
                          )}`}
                        >
                          {task.priority}
                        </span>
                        <span
                          className={`px-2 py-0.5 rounded-full text-[10px] font-bold border ${getStatusBadge(
                            task.status
                          )}`}
                        >
                          {task.status}
                        </span>
                      </div>
                      {task.description && (
                        <p className="text-xs text-slate-500 mt-1 line-clamp-1">
                          {task.description}
                        </p>
                      )}
                    </div>
                  </div>

                  {/* Related Entity */}
                  <div className="w-1/6 text-xs text-slate-600">
                    {task.related_to ? (
                      <div className="inline-flex items-center space-x-1.5 px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 font-medium">
                        <span>
                          {task.related_to_type?.includes('Lead')
                            ? '👤'
                            : task.related_to_type?.includes('Contact')
                            ? '📇'
                            : '🤝'}
                        </span>
                        <span className="truncate max-w-[120px]">{task.related_to.name || task.related_to.title}</span>
                      </div>
                    ) : (
                      <span className="text-slate-400 italic">General</span>
                    )}
                  </div>

                  {/* Assignee */}
                  <div className="w-1/6 flex items-center space-x-2">
                    {task.assignee ? (
                      <>
                        <div className="w-7 h-7 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-xs flex-shrink-0">
                          {task.assignee.name.charAt(0)}
                        </div>
                        <div className="overflow-hidden">
                          <p className="text-xs font-semibold text-slate-800 truncate">
                            {task.assignee.name}
                          </p>
                        </div>
                      </>
                    ) : (
                      <span className="text-xs text-slate-400 italic">Unassigned</span>
                    )}
                  </div>

                  {/* Due Date & Action Buttons */}
                  <div className="w-1/6 flex items-center justify-end space-x-3">
                    <div className="text-right">
                      <p
                        className={`text-xs font-bold ${
                          overdue ? 'text-red-600 flex items-center justify-end space-x-1' : 'text-slate-700'
                        }`}
                      >
                        {overdue && <span>🚨</span>}
                        <span>{task.due_date ? task.due_date.substring(0, 10) : 'No date'}</span>
                      </p>
                      {overdue && (
                        <span className="text-[10px] font-bold text-red-500 uppercase tracking-wider">
                          Overdue
                        </span>
                      )}
                    </div>

                    <div className="flex items-center space-x-1">
                      <button
                        onClick={() => {
                          setEditingTask(task);
                          setIsModalOpen(true);
                        }}
                        className="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition"
                        title="Edit Task"
                      >
                        ✏️
                      </button>
                      <button
                        onClick={() => handleDeleteTask(task.id)}
                        className="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition"
                        title="Delete Task"
                      >
                        🗑
                      </button>
                    </div>
                  </div>
                </div>
              );
            })}
          </div>
        )}

        {/* Pagination Controls */}
        {totalPages > 1 && (
          <div className="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between text-xs">
            <span className="text-slate-500">
              Page {currentPage} of {totalPages} ({totalCount} total tasks)
            </span>
            <div className="flex items-center space-x-1.5">
              <button
                onClick={() => setCurrentPage((p) => Math.max(p - 1, 1))}
                disabled={currentPage === 1}
                className="px-3 py-1.5 rounded-lg border border-slate-200 bg-white font-semibold text-slate-700 disabled:opacity-40 hover:bg-slate-50 transition"
              >
                Previous
              </button>
              <button
                onClick={() => setCurrentPage((p) => Math.min(p + 1, totalPages))}
                disabled={currentPage === totalPages}
                className="px-3 py-1.5 rounded-lg border border-slate-200 bg-white font-semibold text-slate-700 disabled:opacity-40 hover:bg-slate-50 transition"
              >
                Next
              </button>
            </div>
          </div>
        )}
      </div>

      {/* Add / Edit Task Modal */}
      <TaskModal
        isOpen={isModalOpen}
        onClose={() => setIsModalOpen(false)}
        onSave={() => {
          fetchTasks();
          fetchMetadata();
        }}
        task={editingTask}
      />
    </div>
  );
}
