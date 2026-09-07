import React, { useState, useEffect } from 'react';
import { api } from '../services/api';

export default function DealModal({
  isOpen,
  onClose,
  onSaved,
  dealToEdit = null,
  stages = [],
  companies = [],
  contacts = [],
  leads = [],
  owners = [],
  initialStageId = null,
}) {
  const [formData, setFormData] = useState({
    name: '',
    value: '',
    probability: 50,
    deal_stage_id: '',
    status: 'open',
    close_date: '',
    company_id: '',
    contact_id: '',
    lead_id: '',
    owner_id: '',
    notes: '',
  });

  const [loading, setLoading] = useState(false);
  const [errors, setErrors] = useState({});
  const [generalError, setGeneralError] = useState('');

  useEffect(() => {
    if (dealToEdit) {
      setFormData({
        name: dealToEdit.name || dealToEdit.title || '',
        value: dealToEdit.value !== undefined ? dealToEdit.value : '',
        probability: dealToEdit.probability !== undefined ? dealToEdit.probability : 50,
        deal_stage_id: dealToEdit.deal_stage_id || (dealToEdit.stage?.id || ''),
        status: dealToEdit.status || 'open',
        close_date: dealToEdit.close_date ? String(dealToEdit.close_date).split('T')[0] : '',
        company_id: dealToEdit.company_id || (dealToEdit.company?.id || ''),
        contact_id: dealToEdit.contact_id || (dealToEdit.contact?.id || ''),
        lead_id: dealToEdit.lead_id || (dealToEdit.lead?.id || ''),
        owner_id: dealToEdit.owner_id || (dealToEdit.owner?.id || ''),
        notes: dealToEdit.notes || '',
      });
    } else {
      const defaultStage = initialStageId || (stages.length > 0 ? stages[0].id : '');
      setFormData({
        name: '',
        value: '',
        probability: 50,
        deal_stage_id: defaultStage,
        status: 'open',
        close_date: new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
        company_id: '',
        contact_id: '',
        lead_id: '',
        owner_id: '',
        notes: '',
      });
    }
    setErrors({});
    setGeneralError('');
  }, [dealToEdit, isOpen, initialStageId, stages]);

  if (!isOpen) return null;

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: value,
    }));
    if (errors[name]) {
      setErrors(prev => ({ ...prev, [name]: null }));
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setErrors({});
    setGeneralError('');

    try {
      const payload = {
        ...formData,
        value: parseFloat(formData.value) || 0,
        probability: parseInt(formData.probability, 10) || 0,
        deal_stage_id: formData.deal_stage_id ? parseInt(formData.deal_stage_id, 10) : null,
        company_id: formData.company_id ? parseInt(formData.company_id, 10) : null,
        contact_id: formData.contact_id ? parseInt(formData.contact_id, 10) : null,
        lead_id: formData.lead_id ? parseInt(formData.lead_id, 10) : null,
        owner_id: formData.owner_id ? parseInt(formData.owner_id, 10) : null,
        close_date: formData.close_date || null,
      };

      let response;
      if (dealToEdit && dealToEdit.id) {
        response = await api.deals.update(dealToEdit.id, payload);
      } else {
        response = await api.deals.create(payload);
      }

      if (response.data.success || response.status === 200 || response.status === 201) {
        onSaved(response.data.data || response.data);
        onClose();
      }
    } catch (err) {
      if (err.response?.data?.errors) {
        setErrors(err.response.data.errors);
      } else {
        setGeneralError(err.response?.data?.message || 'Failed to save deal. Please verify input fields.');
      }
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4 overflow-y-auto animate-fade-in">
      <div className="bg-white rounded-2xl shadow-2xl w-full max-w-2xl overflow-hidden border border-slate-100 flex flex-col max-h-[90vh]">
        {/* Header */}
        <div className="px-6 py-5 border-b border-gray-100 flex items-center justify-between bg-slate-50/50">
          <div>
            <h2 className="text-xl font-bold text-gray-900">
              {dealToEdit ? 'Edit Opportunity / Deal' : 'Create New Opportunity / Deal'}
            </h2>
            <p className="text-xs text-gray-500 mt-0.5">
              {dealToEdit ? `Updating commercial parameters for ${dealToEdit.name}` : 'Track revenue potential, pipeline stage, and customer accounts.'}
            </p>
          </div>
          <button
            type="button"
            onClick={onClose}
            className="w-8 h-8 rounded-full flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition"
          >
            ✕
          </button>
        </div>

        {/* Form */}
        <form onSubmit={handleSubmit} className="flex-1 overflow-y-auto p-6 space-y-4">
          {generalError && (
            <div className="p-3 bg-rose-50 text-rose-700 text-xs rounded-xl border border-rose-200">
              {generalError}
            </div>
          )}

          {/* Deal Name */}
          <div>
            <label className="block text-xs font-semibold text-gray-700 mb-1">
              Deal Title <span className="text-red-500">*</span>
            </label>
            <input
              type="text"
              name="name"
              required
              value={formData.name}
              onChange={handleChange}
              placeholder="e.g. Enterprise Cloud Migration Contract"
              className={`w-full px-3.5 py-2 text-sm border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 ${
                errors.name ? 'border-rose-400 bg-rose-50/30' : 'border-gray-200'
              }`}
            />
            {errors.name && <p className="text-[11px] text-rose-600 mt-1">{errors.name[0]}</p>}
          </div>

          {/* Value & Probability */}
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label className="block text-xs font-semibold text-gray-700 mb-1">
                Deal Value ($ USD) <span className="text-red-500">*</span>
              </label>
              <input
                type="number"
                step="0.01"
                name="value"
                required
                value={formData.value}
                onChange={handleChange}
                placeholder="75000"
                className={`w-full px-3.5 py-2 text-sm border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 ${
                  errors.value ? 'border-rose-400 bg-rose-50/30' : 'border-gray-200'
                }`}
              />
              {errors.value && <p className="text-[11px] text-rose-600 mt-1">{errors.value[0]}</p>}
            </div>

            <div>
              <div className="flex items-center justify-between mb-1">
                <label className="text-xs font-semibold text-gray-700">Win Probability</label>
                <span className="text-xs font-bold text-indigo-600">{formData.probability}%</span>
              </div>
              <input
                type="range"
                min="0"
                max="100"
                step="5"
                name="probability"
                value={formData.probability}
                onChange={handleChange}
                className="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-indigo-600"
              />
            </div>
          </div>

          {/* Pipeline Stage & Status */}
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label className="block text-xs font-semibold text-gray-700 mb-1">
                Pipeline Stage <span className="text-red-500">*</span>
              </label>
              <select
                name="deal_stage_id"
                required
                value={formData.deal_stage_id}
                onChange={handleChange}
                className="w-full px-3.5 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
              >
                <option value="">-- Select Stage --</option>
                {stages.map(st => (
                  <option key={st.id} value={st.id}>
                    {st.name} (Step {st.order_index})
                  </option>
                ))}
              </select>
            </div>

            <div>
              <label className="block text-xs font-semibold text-gray-700 mb-1">Commercial Status</label>
              <select
                name="status"
                value={formData.status}
                onChange={handleChange}
                className="w-full px-3.5 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
              >
                <option value="open">🟢 Open (Active Pipeline)</option>
                <option value="won">🏆 Won (Closed & Invoiced)</option>
                <option value="lost">❌ Lost (Archived)</option>
              </select>
            </div>
          </div>

          {/* Target Close Date & Deal Owner */}
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label className="block text-xs font-semibold text-gray-700 mb-1">Target Close Date</label>
              <input
                type="date"
                name="close_date"
                value={formData.close_date}
                onChange={handleChange}
                className="w-full px-3.5 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
              />
            </div>

            <div>
              <label className="block text-xs font-semibold text-gray-700 mb-1">Deal Owner / Rep</label>
              <select
                name="owner_id"
                value={formData.owner_id}
                onChange={handleChange}
                className="w-full px-3.5 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
              >
                <option value="">-- Unassigned (Default to You) --</option>
                {owners.map(u => (
                  <option key={u.id} value={u.id}>{u.name} ({u.email})</option>
                ))}
              </select>
            </div>
          </div>

          {/* Connected Company & Contact */}
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label className="block text-xs font-semibold text-gray-700 mb-1">Associated Company</label>
              <select
                name="company_id"
                value={formData.company_id}
                onChange={handleChange}
                className="w-full px-3.5 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
              >
                <option value="">-- None Linked --</option>
                {companies.map(comp => (
                  <option key={comp.id} value={comp.id}>{comp.name}</option>
                ))}
              </select>
            </div>

            <div>
              <label className="block text-xs font-semibold text-gray-700 mb-1">Primary Contact Person</label>
              <select
                name="contact_id"
                value={formData.contact_id}
                onChange={handleChange}
                className="w-full px-3.5 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
              >
                <option value="">-- None Linked --</option>
                {contacts.map(c => (
                  <option key={c.id} value={c.id}>{c.name}</option>
                ))}
              </select>
            </div>
          </div>

          {/* Lead Origin */}
          <div>
            <label className="block text-xs font-semibold text-gray-700 mb-1">Originating Lead (Optional)</label>
            <select
              name="lead_id"
              value={formData.lead_id}
              onChange={handleChange}
              className="w-full px-3.5 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
            >
              <option value="">-- Direct Deal (No Lead Origin) --</option>
              {leads.map(l => (
                <option key={l.id} value={l.id}>{l.name}</option>
              ))}
            </select>
          </div>

          {/* Notes & Commercial Terms */}
          <div>
            <label className="block text-xs font-semibold text-gray-700 mb-1">Notes & Scope Details</label>
            <textarea
              name="notes"
              rows={3}
              value={formData.notes}
              onChange={handleChange}
              placeholder="Commercial parameters, deliverables, negotiation highlights..."
              className="w-full px-3.5 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>

          {/* Footer actions */}
          <div className="pt-4 border-t border-gray-100 flex items-center justify-end space-x-3">
            <button
              type="button"
              onClick={onClose}
              className="px-4 py-2 text-sm font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl transition"
            >
              Cancel
            </button>
            <button
              type="submit"
              disabled={loading}
              className="px-5 py-2 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-lg shadow-blue-500/20 transition disabled:opacity-50 flex items-center"
            >
              {loading ? (
                <>
                  <svg className="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                    <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                    <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                  </svg>
                  Saving Deal...
                </>
              ) : dealToEdit ? 'Save Changes' : 'Create Opportunity'}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}
