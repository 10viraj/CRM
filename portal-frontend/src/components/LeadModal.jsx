import React, { useState, useEffect } from 'react';
import { api } from '../services/api';

export default function LeadModal({ isOpen, onClose, onSuccess, lead = null, metadata = {} }) {
  const isEdit = Boolean(lead && lead.id);

  const [formData, setFormData] = useState({
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    company: '',
    company_id: '',
    lead_status_id: '',
    lead_source_id: '',
    owner_id: '',
    score: 50,
    notes: '',
  });

  const [loading, setLoading] = useState(false);
  const [errors, setErrors] = useState({});

  useEffect(() => {
    if (lead) {
      setFormData({
        first_name: lead.first_name || '',
        last_name: lead.last_name || '',
        email: lead.email || '',
        phone: lead.phone || '',
        company: lead.company || lead.company_name || '',
        company_id: lead.company_id || '',
        lead_status_id: lead.lead_status_id || (metadata.statuses?.[0]?.id || ''),
        lead_source_id: lead.lead_source_id || (metadata.sources?.[0]?.id || ''),
        owner_id: lead.owner_id || '',
        score: lead.score ?? 50,
        notes: lead.notes || '',
      });
    } else {
      setFormData({
        first_name: '',
        last_name: '',
        email: '',
        phone: '',
        company: '',
        company_id: '',
        lead_status_id: metadata.statuses?.[0]?.id || '',
        lead_source_id: metadata.sources?.[0]?.id || '',
        owner_id: metadata.users?.[0]?.id || '',
        score: 50,
        notes: '',
      });
    }
    setErrors({});
  }, [lead, metadata, isOpen]);

  if (!isOpen) return null;

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: name === 'score' ? Number(value) : value
    }));
    if (errors[name]) {
      setErrors(prev => ({ ...prev, [name]: null }));
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setErrors({});

    try {
      const payload = {
        ...formData,
        company_id: formData.company_id || null,
        lead_status_id: formData.lead_status_id || null,
        lead_source_id: formData.lead_source_id || null,
        owner_id: formData.owner_id || null,
      };

      let response;
      if (isEdit) {
        response = await api.leads.update(lead.id, payload);
      } else {
        response = await api.leads.create(payload);
      }

      if (response.data.success) {
        onSuccess(response.data.data, isEdit ? 'updated' : 'created');
        onClose();
      }
    } catch (err) {
      if (err.response?.data?.errors) {
        setErrors(err.response.data.errors);
      } else {
        setErrors({ general: err.response?.data?.message || 'An error occurred while saving lead.' });
      }
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
      <div className="bg-white rounded-2xl shadow-2xl max-w-2xl w-full overflow-hidden border border-slate-100 transition-all">
        {/* Header */}
        <div className="px-6 py-5 bg-gradient-to-r from-slate-900 to-slate-800 text-white flex justify-between items-center">
          <div>
            <h3 className="text-lg font-bold">
              {isEdit ? `Edit Lead #${lead.id}` : 'Create New Lead'}
            </h3>
            <p className="text-xs text-slate-300 mt-0.5">
              {isEdit ? 'Update lead profile, pipeline status, and contact info' : 'Add a new prospect to your CRM sales funnel'}
            </p>
          </div>
          <button
            onClick={onClose}
            type="button"
            className="text-slate-400 hover:text-white rounded-lg p-1.5 transition hover:bg-slate-700/50"
          >
            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        {/* Body */}
        <form onSubmit={handleSubmit} className="p-6 space-y-4 max-h-[80vh] overflow-y-auto">
          {errors.general && (
            <div className="p-3 bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl">
              {errors.general}
            </div>
          )}

          {/* Name Row */}
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">First Name *</label>
              <input
                name="first_name"
                type="text"
                required
                value={formData.first_name}
                onChange={handleChange}
                placeholder="e.g. John"
                className={`w-full px-3.5 py-2 text-sm border rounded-xl outline-none transition focus:ring-2 focus:ring-indigo-500 ${
                  errors.first_name ? 'border-red-400 bg-red-50/30' : 'border-slate-200 bg-slate-50/50 focus:bg-white'
                }`}
              />
              {errors.first_name && <p className="text-xs text-red-500 mt-1">{errors.first_name[0]}</p>}
            </div>
            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">Last Name *</label>
              <input
                name="last_name"
                type="text"
                required
                value={formData.last_name}
                onChange={handleChange}
                placeholder="e.g. Doe"
                className={`w-full px-3.5 py-2 text-sm border rounded-xl outline-none transition focus:ring-2 focus:ring-indigo-500 ${
                  errors.last_name ? 'border-red-400 bg-red-50/30' : 'border-slate-200 bg-slate-50/50 focus:bg-white'
                }`}
              />
              {errors.last_name && <p className="text-xs text-red-500 mt-1">{errors.last_name[0]}</p>}
            </div>
          </div>

          {/* Contact Row */}
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">Email Address *</label>
              <input
                name="email"
                type="email"
                required
                value={formData.email}
                onChange={handleChange}
                placeholder="e.g. john.doe@example.com"
                className={`w-full px-3.5 py-2 text-sm border rounded-xl outline-none transition focus:ring-2 focus:ring-indigo-500 ${
                  errors.email ? 'border-red-400 bg-red-50/30' : 'border-slate-200 bg-slate-50/50 focus:bg-white'
                }`}
              />
              {errors.email && <p className="text-xs text-red-500 mt-1">{errors.email[0]}</p>}
            </div>
            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">Phone Number</label>
              <input
                name="phone"
                type="tel"
                value={formData.phone}
                onChange={handleChange}
                placeholder="e.g. +1 (555) 019-2834"
                className="w-full px-3.5 py-2 text-sm border border-slate-200 rounded-xl bg-slate-50/50 focus:bg-white outline-none focus:ring-2 focus:ring-indigo-500 transition"
              />
            </div>
          </div>

          {/* Company */}
          <div>
            <label className="block text-xs font-semibold text-slate-700 mb-1">Company / Organization</label>
            <input
              name="company"
              type="text"
              value={formData.company}
              onChange={handleChange}
              placeholder="e.g. Acme Global Industries"
              className="w-full px-3.5 py-2 text-sm border border-slate-200 rounded-xl bg-slate-50/50 focus:bg-white outline-none focus:ring-2 focus:ring-indigo-500 transition"
            />
          </div>

          {/* Status & Source Row */}
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">Pipeline Status</label>
              <select
                name="lead_status_id"
                value={formData.lead_status_id}
                onChange={handleChange}
                className="w-full px-3.5 py-2 text-sm border border-slate-200 rounded-xl bg-slate-50/50 focus:bg-white outline-none focus:ring-2 focus:ring-indigo-500 transition"
              >
                <option value="">Select Status...</option>
                {metadata.statuses?.map(status => (
                  <option key={status.id} value={status.id}>
                    {status.name}
                  </option>
                ))}
              </select>
            </div>
            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">Acquisition Source</label>
              <select
                name="lead_source_id"
                value={formData.lead_source_id}
                onChange={handleChange}
                className="w-full px-3.5 py-2 text-sm border border-slate-200 rounded-xl bg-slate-50/50 focus:bg-white outline-none focus:ring-2 focus:ring-indigo-500 transition"
              >
                <option value="">Select Source...</option>
                {metadata.sources?.map(source => (
                  <option key={source.id} value={source.id}>
                    {source.name}
                  </option>
                ))}
              </select>
            </div>
          </div>

          {/* Owner & Score */}
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 items-center">
            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">Assigned Account Owner</label>
              <select
                name="owner_id"
                value={formData.owner_id}
                onChange={handleChange}
                className="w-full px-3.5 py-2 text-sm border border-slate-200 rounded-xl bg-slate-50/50 focus:bg-white outline-none focus:ring-2 focus:ring-indigo-500 transition"
              >
                <option value="">Unassigned</option>
                {metadata.users?.map(user => (
                  <option key={user.id} value={user.id}>
                    {user.name} ({user.email})
                  </option>
                ))}
              </select>
            </div>
            <div>
              <div className="flex justify-between items-center mb-1">
                <label className="text-xs font-semibold text-slate-700">Lead Score</label>
                <span className={`text-xs font-bold px-2 py-0.5 rounded-full ${
                  formData.score >= 70 ? 'bg-emerald-100 text-emerald-800' :
                  formData.score >= 40 ? 'bg-amber-100 text-amber-800' :
                  'bg-slate-100 text-slate-800'
                }`}>
                  {formData.score} / 100
                </span>
              </div>
              <input
                type="range"
                name="score"
                min="0"
                max="100"
                value={formData.score}
                onChange={handleChange}
                className="w-full accent-indigo-600 h-2 bg-slate-200 rounded-lg cursor-pointer"
              />
            </div>
          </div>

          {/* Notes */}
          <div>
            <label className="block text-xs font-semibold text-slate-700 mb-1">Notes & Requirements</label>
            <textarea
              name="notes"
              rows="3"
              value={formData.notes}
              onChange={handleChange}
              placeholder="Add discovery notes, key requirements, or conversation summaries..."
              className="w-full px-3.5 py-2 text-sm border border-slate-200 rounded-xl bg-slate-50/50 focus:bg-white outline-none focus:ring-2 focus:ring-indigo-500 transition resize-none"
            />
          </div>

          {/* Footer Actions */}
          <div className="pt-4 border-t border-slate-100 flex justify-end space-x-3">
            <button
              type="button"
              onClick={onClose}
              className="px-5 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition"
            >
              Cancel
            </button>
            <button
              type="submit"
              disabled={loading}
              className="px-6 py-2.5 text-sm font-semibold bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl shadow-md shadow-indigo-200 transition disabled:opacity-50 flex items-center space-x-2"
            >
              {loading ? (
                <>
                  <svg className="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                    <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
                    <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" />
                  </svg>
                  <span>Saving...</span>
                </>
              ) : (
                <span>{isEdit ? 'Save Changes' : 'Create Lead'}</span>
              )}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}
