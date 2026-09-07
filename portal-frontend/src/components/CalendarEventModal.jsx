import React, { useState, useEffect } from 'react';
import { api } from '../services/api';

export default function CalendarEventModal({
  isOpen,
  onClose,
  onSave,
  event = null,
  initialDate = null,
  initialRelated = null,
}) {
  const [formData, setFormData] = useState({
    title: '',
    description: '',
    event_type: 'meeting',
    status: 'scheduled',
    start_time: '',
    end_time: '',
    is_all_day: false,
    location: '',
    user_id: '',
    eventable_type: '',
    eventable_id: '',
  });

  const [options, setOptions] = useState({
    users: [],
    leads: [],
    contacts: [],
    deals: [],
    tasks: [],
  });

  const [loading, setLoading] = useState(false);
  const [errors, setErrors] = useState({});

  useEffect(() => {
    if (isOpen) {
      fetchMetadata();
      if (event) {
        setFormData({
          title: event.title || '',
          description: event.description || '',
          event_type: event.event_type || 'meeting',
          status: event.status || 'scheduled',
          start_time: event.start_time ? formatDateTimeLocal(event.start_time) : '',
          end_time: event.end_time ? formatDateTimeLocal(event.end_time) : '',
          is_all_day: Boolean(event.is_all_day),
          location: event.location || '',
          user_id: event.user_id || event.user?.id || '',
          eventable_type: event.eventable_type
            ? event.eventable_type.includes('Lead')
              ? 'Lead'
              : event.eventable_type.includes('Contact')
              ? 'Contact'
              : event.eventable_type.includes('Deal')
              ? 'Deal'
              : event.eventable_type.includes('Task')
              ? 'Task'
              : event.eventable_type
            : '',
          eventable_id: event.eventable_id || event.eventable?.id || '',
        });
      } else {
        const baseDate = initialDate ? new Date(initialDate) : new Date();
        const start = new Date(baseDate);
        if (!initialDate) {
          start.setHours(start.getHours() + 1, 0, 0, 0);
        }
        const end = new Date(start);
        end.setHours(end.getHours() + 1);

        setFormData({
          title: '',
          description: '',
          event_type: 'meeting',
          status: 'scheduled',
          start_time: formatDateTimeLocal(start.toISOString()),
          end_time: formatDateTimeLocal(end.toISOString()),
          is_all_day: false,
          location: 'Google Meet / Zoom',
          user_id: '',
          eventable_type: initialRelated?.type || '',
          eventable_id: initialRelated?.id || '',
        });
      }
      setErrors({});
    }
  }, [isOpen, event, initialDate, initialRelated]);

  const formatDateTimeLocal = (dateStr) => {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    const pad = (n) => String(n).padStart(2, '0');
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(
      d.getMinutes()
    )}`;
  };

  const fetchMetadata = async () => {
    try {
      const res = await api.calendar.metadata();
      if (res.data?.options) {
        setOptions(res.data.options);
      }
    } catch (err) {
      console.error('Failed to load calendar metadata:', err);
    }
  };

  const handleChange = (e) => {
    const { name, value, type, checked } = e.target;
    setFormData((prev) => {
      const next = { ...prev, [name]: type === 'checkbox' ? checked : value };
      if (name === 'eventable_type' && !value) {
        next.eventable_id = '';
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
      if (!payload.eventable_type || !payload.eventable_id) {
        payload.eventable_type = null;
        payload.eventable_id = null;
      }
      if (!payload.user_id) {
        delete payload.user_id;
      }

      if (event) {
        await api.calendar.update(event.id, payload);
      } else {
        await api.calendar.create(payload);
      }
      onSave();
      onClose();
    } catch (err) {
      if (err.response && err.response.data && err.response.data.errors) {
        setErrors(err.response.data.errors);
      } else {
        setErrors({ general: err.response?.data?.message || 'Failed to save event.' });
      }
    } finally {
      setLoading(false);
    }
  };

  if (!isOpen) return null;

  return (
    <div className="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
      <div className="bg-white rounded-2xl shadow-2xl max-w-xl w-full overflow-hidden border border-slate-100 animate-in fade-in zoom-in-95 duration-200">
        {/* Header */}
        <div className="flex justify-between items-center px-6 py-5 border-b border-slate-100 bg-slate-50/50">
          <div className="flex items-center space-x-3">
            <div className="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center font-bold text-lg">
              📅
            </div>
            <div>
              <h2 className="text-lg font-bold text-slate-900">
                {event ? 'Edit Calendar Event' : 'Schedule Event / Meeting'}
              </h2>
              <p className="text-xs text-slate-500">
                {event ? 'Reschedule or modify meeting parameters' : 'Create calendar booking linked to CRM pipeline records'}
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

        {/* Form */}
        <form onSubmit={handleSubmit} className="p-6 space-y-4">
          {errors.general && (
            <div className="p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-xs font-medium">
              {errors.general}
            </div>
          )}

          {/* Title */}
          <div>
            <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
              Event Title <span className="text-red-500">*</span>
            </label>
            <input
              type="text"
              name="title"
              value={formData.title}
              onChange={handleChange}
              placeholder="e.g., Product Demo Call, Contract Negotiation, Quarterly Review"
              className={`w-full px-3.5 py-2.5 bg-slate-50 border ${
                errors.title ? 'border-red-400 focus:ring-red-500' : 'border-slate-200 focus:ring-blue-500'
              } rounded-xl text-sm focus:outline-none focus:ring-2 focus:bg-white transition`}
              required
            />
            {errors.title && <p className="text-red-500 text-xs mt-1">{errors.title[0]}</p>}
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            {/* Event Type */}
            <div>
              <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                Event Type
              </label>
              <select
                name="event_type"
                value={formData.event_type}
                onChange={handleChange}
                className="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition capitalize"
              >
                <option value="meeting">🤝 Meeting</option>
                <option value="call">📞 Phone Call</option>
                <option value="demo">🖥️ Product Demo</option>
                <option value="task">✓ Task Deadline</option>
                <option value="webinar">🎓 Webinar</option>
                <option value="other">📌 Other</option>
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
                className="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition capitalize"
              >
                <option value="scheduled">Scheduled</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
              </select>
            </div>
          </div>

          {/* Timing */}
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                Start Time <span className="text-red-500">*</span>
              </label>
              <input
                type="datetime-local"
                name="start_time"
                value={formData.start_time}
                onChange={handleChange}
                className="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition"
                required
              />
              {errors.start_time && <p className="text-red-500 text-xs mt-1">{errors.start_time[0]}</p>}
            </div>

            <div>
              <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                End Time <span className="text-red-500">*</span>
              </label>
              <input
                type="datetime-local"
                name="end_time"
                value={formData.end_time}
                onChange={handleChange}
                className="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition"
                required
              />
              {errors.end_time && <p className="text-red-500 text-xs mt-1">{errors.end_time[0]}</p>}
            </div>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            {/* Location */}
            <div>
              <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                Location / Link
              </label>
              <input
                type="text"
                name="location"
                value={formData.location}
                onChange={handleChange}
                placeholder="Zoom, Google Meet, Room 302..."
                className="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition"
              />
            </div>

            {/* Host / Attendees (User) */}
            <div>
              <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                Organizer / Host
              </label>
              <select
                name="user_id"
                value={formData.user_id}
                onChange={handleChange}
                className="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition"
              >
                <option value="">Current User</option>
                {options.users?.map((u) => (
                  <option key={u.id} value={u.id}>
                    {u.name} ({u.email})
                  </option>
                ))}
              </select>
            </div>
          </div>

          {/* Related Entity (Polymorphic Link: Lead, Contact, Deal, Task) */}
          <div className="p-3.5 bg-slate-50 border border-slate-200 rounded-xl space-y-3">
            <p className="text-xs font-bold text-slate-700 uppercase tracking-wider">
              Link with CRM Entity
            </p>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
              <div>
                <label className="block text-[11px] font-semibold text-slate-600 mb-1">Entity Type</label>
                <select
                  name="eventable_type"
                  value={formData.eventable_type}
                  onChange={handleChange}
                  className="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
                >
                  <option value="">None (General Calendar Booking)</option>
                  <option value="Lead">Lead</option>
                  <option value="Contact">Contact</option>
                  <option value="Deal">Deal / Pipeline</option>
                  <option value="Task">Task Item</option>
                </select>
              </div>

              {formData.eventable_type && (
                <div>
                  <label className="block text-[11px] font-semibold text-slate-600 mb-1">
                    Select {formData.eventable_type}
                  </label>
                  <select
                    name="eventable_id"
                    value={formData.eventable_id}
                    onChange={handleChange}
                    className="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
                  >
                    <option value="">Choose a {formData.eventable_type}...</option>
                    {formData.eventable_type === 'Lead' &&
                      options.leads?.map((l) => (
                        <option key={l.id} value={l.id}>
                          {l.name}
                        </option>
                      ))}
                    {formData.eventable_type === 'Contact' &&
                      options.contacts?.map((c) => (
                        <option key={c.id} value={c.id}>
                          {c.name}
                        </option>
                      ))}
                    {formData.eventable_type === 'Deal' &&
                      options.deals?.map((d) => (
                        <option key={d.id} value={d.id}>
                          {d.name}
                        </option>
                      ))}
                    {formData.eventable_type === 'Task' &&
                      options.tasks?.map((t) => (
                        <option key={t.id} value={t.id}>
                          {t.title}
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
              Meeting Agenda & Description
            </label>
            <textarea
              name="description"
              rows={3}
              value={formData.description}
              onChange={handleChange}
              placeholder="Add key discussion topics, links, or meeting notes..."
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
              className="px-6 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-bold rounded-xl shadow-lg shadow-purple-500/20 transition flex items-center space-x-2 disabled:opacity-50"
            >
              {loading ? (
                <span>Saving...</span>
              ) : (
                <span>{event ? 'Update Event' : 'Create Event'}</span>
              )}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}
