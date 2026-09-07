import React, { useState, useEffect } from 'react';
import { api } from '../services/api';

export default function TaskModal({ isOpen, onClose, onSave, task = null, initialRelated = null }) {
  const [formData, setFormData] = useState({
    title: '',
    description: '',
    priority: 'Medium',
    status: 'Pending',
    type: 'Task',
    due_date: '',
    assign_to_id: '',
    related_to_type: '',
    related_to_id: '',
  });

  const [options, setOptions] = useState({
    users: [],
    leads: [],
    contacts: [],
    deals: [],
  });

  const [loading, setLoading] = useState(false);
  const [fetchingOptions, setFetchingOptions] = useState(false);
  const [errors, setErrors] = useState({});

  useEffect(() => {
    if (isOpen) {
      fetchMetadata();
      if (task) {
        setFormData({
          title: task.title || '',
          description: task.description || '',
          priority: task.priority || 'Medium',
          status: task.status || 'Pending',
          type: task.type || 'Task',
          due_date: task.due_date ? task.due_date.substring(0, 10) : '',
          assign_to_id: task.assign_to_id || task.assignee?.id || '',
          related_to_type: task.related_to_type ? (task.related_to_type.includes('Lead') ? 'Lead' : task.related_to_type.includes('Contact') ? 'Contact' : task.related_to_type.includes('Deal') ? 'Deal' : task.related_to_type) : '',
          related_to_id: task.related_to_id || task.related_to?.id || '',
        });
      } else {
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        setFormData({
          title: '',
          description: '',
          priority: 'Medium',
          status: 'Pending',
          type: 'Task',
          due_date: tomorrow.toISOString().substring(0, 10),
          assign_to_id: '',
          related_to_type: initialRelated?.type || '',
          related_to_id: initialRelated?.id || '',
        });
      }
      setErrors({});
    }
  }, [isOpen, task, initialRelated]);

  const fetchMetadata = async () => {
    try {
      setFetchingOptions(true);
      const res = await api.tasks.metadata();
      if (res.data?.options) {
        setOptions(res.data.options);
      }
    } catch (err) {
      console.error('Failed to load task options:', err);
    } finally {
      setFetchingOptions(false);
    }
  };

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData((prev) => {
      const next = { ...prev, [name]: value };
      if (name === 'related_to_type' && !value) {
        next.related_to_id = '';
      }
      return next;
    });
    if (errors[name]) {
      setErrors((prev) => ({ ...prev, [name]: null }));
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setErrors({});

    try {
      const payload = { ...formData };
      if (!payload.related_to_type || !payload.related_to_id) {
        payload.related_to_type = null;
        payload.related_to_id = null;
      }
      if (!payload.assign_to_id) {
        delete payload.assign_to_id;
      }

      if (task) {
        await api.tasks.update(task.id, payload);
      } else {
        await api.tasks.create(payload);
      }
      onSave();
      onClose();
    } catch (err) {
      if (err.response && err.response.data && err.response.data.errors) {
        setErrors(err.response.data.errors);
      } else {
        setErrors({ general: err.response?.data?.message || 'Failed to save task.' });
      }
    } finally {
      setLoading(false);
    }
  };

  if (!isOpen) return null;

  return (
    <div className="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
      <div className="bg-white rounded-2xl shadow-2xl max-w-xl w-full overflow-hidden border border-slate-100 animate-in fade-in zoom-in-95 duration-200">
        <div className="flex justify-between items-center px-6 py-5 border-b border-slate-100 bg-slate-50/50">
          <div className="flex items-center space-x-3">
            <div className="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-lg">
              ✓
            </div>
            <div>
              <h2 className="text-lg font-bold text-slate-900">
                {task ? 'Edit Task' : 'Create New Task'}
              </h2>
              <p className="text-xs text-slate-500">
                {task ? 'Update task requirements, priority, and assignees' : 'Add action items and assign to team members'}
              </p>
            </div>
          </div>
          <button
            onClick={onClose}
            className="text-slate-400 hover:text-slate-600 w-8 h-8 rounded-lg flex items-center justify-center hover:bg-slate-100 transition"
          >
            ✕
          </button>
        </div>

        <form onSubmit={handleSubmit} className="p-6 space-y-4">
          {errors.general && (
            <div className="p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-xs font-medium">
              {errors.general}
            </div>
          )}

          {/* Title */}
          <div>
            <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
              Task Title <span className="text-red-500">*</span>
            </label>
            <input
              type="text"
              name="title"
              value={formData.title}
              onChange={handleChange}
              placeholder="e.g., Prepare contract proposal, Follow up on demo call"
              className={`w-full px-3.5 py-2.5 bg-slate-50 border ${
                errors.title ? 'border-red-400 focus:ring-red-500' : 'border-slate-200 focus:ring-blue-500'
              } rounded-xl text-sm focus:outline-none focus:ring-2 focus:bg-white transition`}
              required
            />
            {errors.title && <p className="text-red-500 text-xs mt-1">{errors.title[0]}</p>}
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            {/* Priority */}
            <div>
              <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                Priority
              </label>
              <select
                name="priority"
                value={formData.priority}
                onChange={handleChange}
                className="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition"
              >
                <option value="Low">Low</option>
                <option value="Medium">Medium</option>
                <option value="High">High</option>
                <option value="Urgent">Urgent 🔥</option>
              </select>
            </div>

            {/* Status */}
            <div>
              <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                Status
              </label>
              <select
                name="status"
                value={formData.status}
                onChange={handleChange}
                className="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition"
              >
                <option value="Pending">Pending</option>
                <option value="In Progress">In Progress</option>
                <option value="Completed">Completed</option>
                <option value="Cancelled">Cancelled</option>
              </select>
            </div>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            {/* Due Date */}
            <div>
              <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                Due Date <span className="text-red-500">*</span>
              </label>
              <input
                type="date"
                name="due_date"
                value={formData.due_date}
                onChange={handleChange}
                className="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition"
                required
              />
              {errors.due_date && <p className="text-red-500 text-xs mt-1">{errors.due_date[0]}</p>}
            </div>

            {/* Assignee */}
            <div>
              <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                Assignee
              </label>
              <select
                name="assign_to_id"
                value={formData.assign_to_id}
                onChange={handleChange}
                className="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition"
              >
                <option value="">Unassigned</option>
                {options.users?.map((u) => (
                  <option key={u.id} value={u.id}>
                    {u.name} ({u.email})
                  </option>
                ))}
              </select>
            </div>
          </div>

          {/* Related To (Polymorphic Link) */}
          <div className="p-3.5 bg-slate-50 border border-slate-200 rounded-xl space-y-3">
            <p className="text-xs font-bold text-slate-700 uppercase tracking-wider">
              Linked Entity (Optional)
            </p>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
              <div>
                <label className="block text-[11px] font-semibold text-slate-600 mb-1">Entity Type</label>
                <select
                  name="related_to_type"
                  value={formData.related_to_type}
                  onChange={handleChange}
                  className="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
                >
                  <option value="">None (General Task)</option>
                  <option value="Lead">Lead</option>
                  <option value="Contact">Contact</option>
                  <option value="Deal">Deal / Opportunity</option>
                </select>
              </div>

              {formData.related_to_type && (
                <div>
                  <label className="block text-[11px] font-semibold text-slate-600 mb-1">
                    Select {formData.related_to_type}
                  </label>
                  <select
                    name="related_to_id"
                    value={formData.related_to_id}
                    onChange={handleChange}
                    className="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
                  >
                    <option value="">Choose a {formData.related_to_type}...</option>
                    {formData.related_to_type === 'Lead' &&
                      options.leads?.map((l) => (
                        <option key={l.id} value={l.id}>
                          {l.name}
                        </option>
                      ))}
                    {formData.related_to_type === 'Contact' &&
                      options.contacts?.map((c) => (
                        <option key={c.id} value={c.id}>
                          {c.name}
                        </option>
                      ))}
                    {formData.related_to_type === 'Deal' &&
                      options.deals?.map((d) => (
                        <option key={d.id} value={d.id}>
                          {d.name}
                        </option>
                      ))}
                  </select>
                </div>
              )}
            </div>
          </div>

          {/* Description */}
          <div>
            <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
              Task Notes / Instructions
            </label>
            <textarea
              name="description"
              rows={3}
              value={formData.description}
              onChange={handleChange}
              placeholder="Add deliverables, meeting agenda, or follow-up details..."
              className="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition resize-none"
            ></textarea>
          </div>

          <div className="flex justify-end space-x-3 pt-3 border-t border-slate-100">
            <button
              type="button"
              onClick={onClose}
              className="px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition"
            >
              Cancel
            </button>
            <button
              type="submit"
              disabled={loading}
              className="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl shadow-lg shadow-blue-500/20 transition flex items-center space-x-2 disabled:opacity-50"
            >
              {loading ? (
                <span>Saving...</span>
              ) : (
                <span>{task ? 'Update Task' : 'Create Task'}</span>
              )}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}
