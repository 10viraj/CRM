import React, { useState, useEffect } from 'react';
import { api } from '../services/api';

export default function ContactModal({ isOpen, onClose, onSaved, contactToEdit = null, companies = [], owners = [], leads = [] }) {
  const [formData, setFormData] = useState({
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    mobile: '',
    job_title: '',
    department: '',
    company_id: '',
    owner_id: '',
    lead_id: '',
    address: '',
    city: '',
    state: '',
    zip: '',
    country: '',
    is_primary: false,
    notes: '',
  });

  const [loading, setLoading] = useState(false);
  const [errors, setErrors] = useState({});
  const [generalError, setGeneralError] = useState('');

  useEffect(() => {
    if (contactToEdit) {
      setFormData({
        first_name: contactToEdit.first_name || '',
        last_name: contactToEdit.last_name || '',
        email: contactToEdit.email || '',
        phone: contactToEdit.phone || '',
        mobile: contactToEdit.mobile || '',
        job_title: contactToEdit.job_title || '',
        department: contactToEdit.department || '',
        company_id: contactToEdit.company_id || (contactToEdit.company?.id || ''),
        owner_id: contactToEdit.owner_id || (contactToEdit.owner?.id || ''),
        lead_id: contactToEdit.lead_id || (contactToEdit.lead?.id || ''),
        address: contactToEdit.address || '',
        city: contactToEdit.city || '',
        state: contactToEdit.state || '',
        zip: contactToEdit.zip || '',
        country: contactToEdit.country || '',
        is_primary: Boolean(contactToEdit.is_primary),
        notes: contactToEdit.notes || '',
      });
    } else {
      setFormData({
        first_name: '',
        last_name: '',
        email: '',
        phone: '',
        mobile: '',
        job_title: '',
        department: '',
        company_id: '',
        owner_id: '',
        lead_id: '',
        address: '',
        city: '',
        state: '',
        zip: '',
        country: '',
        is_primary: false,
        notes: '',
      });
    }
    setErrors({});
    setGeneralError('');
  }, [contactToEdit, isOpen]);

  if (!isOpen) return null;

  const handleChange = (e) => {
    const { name, value, type, checked } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: type === 'checkbox' ? checked : value,
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
        company_id: formData.company_id ? parseInt(formData.company_id, 10) : null,
        owner_id: formData.owner_id ? parseInt(formData.owner_id, 10) : null,
        lead_id: formData.lead_id ? parseInt(formData.lead_id, 10) : null,
      };

      let response;
      if (contactToEdit && contactToEdit.id) {
        response = await api.contacts.update(contactToEdit.id, payload);
      } else {
        response = await api.contacts.create(payload);
      }

      if (response.data.success || response.status === 200 || response.status === 201) {
        onSaved(response.data.data || response.data);
        onClose();
      }
    } catch (err) {
      if (err.response?.data?.errors) {
        setErrors(err.response.data.errors);
      } else {
        setGeneralError(err.response?.data?.message || 'Failed to save contact. Please check your inputs.');
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
              {contactToEdit ? 'Edit Contact' : 'Create New Contact'}
            </h2>
            <p className="text-xs text-gray-500 mt-0.5">
              {contactToEdit ? `Updating profile for ${contactToEdit.name || contactToEdit.first_name}` : 'Fill in contact information and link to company or lead.'}
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

          {/* Name fields */}
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label className="block text-xs font-semibold text-gray-700 mb-1">
                First Name <span className="text-red-500">*</span>
              </label>
              <input
                type="text"
                name="first_name"
                required
                value={formData.first_name}
                onChange={handleChange}
                placeholder="e.g. Sarah"
                className={`w-full px-3.5 py-2 text-sm border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 ${
                  errors.first_name ? 'border-rose-400 bg-rose-50/30' : 'border-gray-200'
                }`}
              />
              {errors.first_name && <p className="text-[11px] text-rose-600 mt-1">{errors.first_name[0]}</p>}
            </div>

            <div>
              <label className="block text-xs font-semibold text-gray-700 mb-1">
                Last Name <span className="text-red-500">*</span>
              </label>
              <input
                type="text"
                name="last_name"
                required
                value={formData.last_name}
                onChange={handleChange}
                placeholder="e.g. Connor"
                className={`w-full px-3.5 py-2 text-sm border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 ${
                  errors.last_name ? 'border-rose-400 bg-rose-50/30' : 'border-gray-200'
                }`}
              />
              {errors.last_name && <p className="text-[11px] text-rose-600 mt-1">{errors.last_name[0]}</p>}
            </div>
          </div>

          {/* Email & Phone */}
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label className="block text-xs font-semibold text-gray-700 mb-1">Email Address</label>
              <input
                type="email"
                name="email"
                value={formData.email}
                onChange={handleChange}
                placeholder="s.connor@example.com"
                className={`w-full px-3.5 py-2 text-sm border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 ${
                  errors.email ? 'border-rose-400 bg-rose-50/30' : 'border-gray-200'
                }`}
              />
              {errors.email && <p className="text-[11px] text-rose-600 mt-1">{errors.email[0]}</p>}
            </div>

            <div>
              <label className="block text-xs font-semibold text-gray-700 mb-1">Work Phone</label>
              <input
                type="text"
                name="phone"
                value={formData.phone}
                onChange={handleChange}
                placeholder="+1-555-0199"
                className="w-full px-3.5 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
          </div>

          {/* Mobile & Job Title */}
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label className="block text-xs font-semibold text-gray-700 mb-1">Mobile</label>
              <input
                type="text"
                name="mobile"
                value={formData.mobile}
                onChange={handleChange}
                placeholder="+1-555-0144"
                className="w-full px-3.5 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>

            <div>
              <label className="block text-xs font-semibold text-gray-700 mb-1">Job Title</label>
              <input
                type="text"
                name="job_title"
                value={formData.job_title}
                onChange={handleChange}
                placeholder="Chief Technology Officer"
                className="w-full px-3.5 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
          </div>

          {/* Department & Company Link */}
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label className="block text-xs font-semibold text-gray-700 mb-1">Department</label>
              <input
                type="text"
                name="department"
                value={formData.department}
                onChange={handleChange}
                placeholder="Engineering / Executive"
                className="w-full px-3.5 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>

            <div>
              <label className="block text-xs font-semibold text-gray-700 mb-1">Associated Company</label>
              <select
                name="company_id"
                value={formData.company_id}
                onChange={handleChange}
                className="w-full px-3.5 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
              >
                <option value="">-- No Company Assigned --</option>
                {companies.map(comp => (
                  <option key={comp.id} value={comp.id}>{comp.name}</option>
                ))}
              </select>
            </div>
          </div>

          {/* Lead Link & Account Owner */}
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label className="block text-xs font-semibold text-gray-700 mb-1">Associated Lead (Optional)</label>
              <select
                name="lead_id"
                value={formData.lead_id}
                onChange={handleChange}
                className="w-full px-3.5 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
              >
                <option value="">-- None --</option>
                {leads.map(lead => (
                  <option key={lead.id} value={lead.id}>{lead.name || `Lead #${lead.id}`}</option>
                ))}
              </select>
            </div>

            <div>
              <label className="block text-xs font-semibold text-gray-700 mb-1">Contact Owner</label>
              <select
                name="owner_id"
                value={formData.owner_id}
                onChange={handleChange}
                className="w-full px-3.5 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
              >
                <option value="">-- Unassigned (Default to You) --</option>
                {owners.map(user => (
                  <option key={user.id} value={user.id}>{user.name} ({user.email})</option>
                ))}
              </select>
            </div>
          </div>

          {/* Primary Contact Checkbox */}
          <div className="pt-2">
            <label className="flex items-center space-x-2.5 cursor-pointer bg-slate-50 p-3 rounded-xl border border-slate-200">
              <input
                type="checkbox"
                name="is_primary"
                checked={formData.is_primary}
                onChange={handleChange}
                className="w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500"
              />
              <span className="text-xs font-semibold text-gray-800">
                Set as Primary Contact for this organization
              </span>
            </label>
          </div>

          {/* Address fields */}
          <div className="pt-2 border-t border-gray-100">
            <p className="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Location & Address</p>
            <div className="grid grid-cols-1 md:grid-cols-3 gap-3">
              <div className="md:col-span-3">
                <input
                  type="text"
                  name="address"
                  value={formData.address}
                  onChange={handleChange}
                  placeholder="Street Address"
                  className="w-full px-3.5 py-1.5 text-xs border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
              <div>
                <input
                  type="text"
                  name="city"
                  value={formData.city}
                  onChange={handleChange}
                  placeholder="City"
                  className="w-full px-3.5 py-1.5 text-xs border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
              <div>
                <input
                  type="text"
                  name="state"
                  value={formData.state}
                  onChange={handleChange}
                  placeholder="State / Province"
                  className="w-full px-3.5 py-1.5 text-xs border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
              <div>
                <input
                  type="text"
                  name="country"
                  value={formData.country}
                  onChange={handleChange}
                  placeholder="Country"
                  className="w-full px-3.5 py-1.5 text-xs border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
            </div>
          </div>

          {/* Notes */}
          <div>
            <label className="block text-xs font-semibold text-gray-700 mb-1">Notes & Bio</label>
            <textarea
              name="notes"
              rows={2}
              value={formData.notes}
              onChange={handleChange}
              placeholder="Key relationship notes, communication preferences, background..."
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
                  Saving...
                </>
              ) : contactToEdit ? 'Save Changes' : 'Create Contact'}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}
