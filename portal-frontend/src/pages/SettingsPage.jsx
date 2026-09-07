import React, { useState, useEffect } from 'react';
import { api } from '../services/api';

export default function SettingsPage() {
  const [activeTab, setActiveTab] = useState('system'); // system, users, roles, pipelines, custom_fields, email, integrations
  const [loading, setLoading] = useState(false);
  const [saveSuccess, setSaveSuccess] = useState('');
  const [saveError, setSaveError] = useState('');

  // 1. Settings State (System, Company, Email, Integrations)
  const [settings, setSettings] = useState({
    company: {
      company_name: '',
      company_email: '',
      company_phone: '',
      company_address: '',
      currency_symbol: '$',
      currency_code: 'USD',
      timezone: 'UTC',
      date_format: 'Y-m-d',
    },
    email: {
      mail_mailer: 'smtp',
      mail_host: '',
      mail_port: '587',
      mail_username: '',
      mail_password: '',
      mail_encryption: 'tls',
      mail_from_address: '',
      mail_from_name: '',
    },
    integrations: {
      webhook_url: '',
      zapier_webhook: '',
      slack_webhook: '',
      api_rate_limit: '120',
      enable_api_access: 'true',
    },
    system: {
      app_name: 'Smart CRM',
      allow_registration: 'true',
      session_lifetime: '120',
      maintenance_mode: 'false',
      audit_logging: 'true',
      max_file_upload_mb: '25',
    },
  });

  // 2. Users State
  const [users, setUsers] = useState([]);
  const [rolesList, setRolesList] = useState([]);
  const [userModalOpen, setUserModalOpen] = useState(false);
  const [editingUser, setEditingUser] = useState(null);
  const [userForm, setUserForm] = useState({ name: '', email: '', password: '', roles: [] });

  // 3. Roles & Permissions State
  const [permissionsGrouped, setPermissionsGrouped] = useState({});
  const [roleModalOpen, setRoleModalOpen] = useState(false);
  const [editingRole, setEditingRole] = useState(null);
  const [roleForm, setRoleForm] = useState({ name: '', permissions: [] });

  // 4. Pipelines / Deal Stages State
  const [stages, setStages] = useState([]);
  const [stageModalOpen, setStageModalOpen] = useState(false);
  const [editingStage, setEditingStage] = useState(null);
  const [stageForm, setStageForm] = useState({ name: '', color: '#3b82f6', order_index: 0 });

  // 5. Custom Fields State
  const [customFields, setCustomFields] = useState([]);
  const [selectedModelType, setSelectedModelType] = useState('Lead');
  const [customFieldModalOpen, setCustomFieldModalOpen] = useState(false);
  const [editingField, setEditingField] = useState(null);
  const [customFieldForm, setCustomFieldForm] = useState({
    model_type: 'Lead',
    label: '',
    name: '',
    field_type: 'text',
    is_required: false,
    default_value: '',
  });

  // Load all initial configurations
  useEffect(() => {
    fetchSettings();
    fetchUsers();
    fetchRolesAndPermissions();
    fetchDealStages();
    fetchCustomFields();
  }, []);

  const fetchSettings = async () => {
    try {
      const res = await api.settings.list();
      if (res.data?.grouped) {
        setSettings((prev) => ({
          ...prev,
          ...res.data.grouped,
        }));
      }
    } catch (err) {
      console.error('Failed to load settings:', err);
    }
  };

  const fetchUsers = async () => {
    try {
      const res = await api.users.list({ all: true });
      if (res.data?.data) {
        setUsers(res.data.data);
      }
    } catch (err) {
      console.error('Failed to load users:', err);
    }
  };

  const fetchRolesAndPermissions = async () => {
    try {
      const rolesRes = await api.roles.list();
      if (rolesRes.data?.data) {
        setRolesList(rolesRes.data.data);
      }
      const permRes = await api.roles.permissions();
      if (permRes.data?.grouped) {
        setPermissionsGrouped(permRes.data.grouped);
      }
    } catch (err) {
      console.error('Failed to load roles and permissions:', err);
    }
  };

  const fetchDealStages = async () => {
    try {
      const res = await api.dealStages.list();
      if (res.data?.data) {
        setStages(res.data.data);
      }
    } catch (err) {
      console.error('Failed to load pipeline stages:', err);
    }
  };

  const fetchCustomFields = async () => {
    try {
      const res = await api.customFields.list();
      if (res.data?.data) {
        setCustomFields(res.data.data);
      }
    } catch (err) {
      console.error('Failed to load custom fields:', err);
    }
  };

  // Generic Setting Change
  const handleSettingChange = (group, key, value) => {
    setSettings((prev) => ({
      ...prev,
      [group]: {
        ...prev[group],
        [key]: value,
      },
    }));
  };

  // Save Settings Group
  const handleSaveSettingsGroup = async (group) => {
    setLoading(true);
    setSaveSuccess('');
    setSaveError('');

    try {
      const groupData = settings[group] || {};
      const payload = Object.keys(groupData).map((k) => ({
        key: k,
        value: groupData[k],
        group: group,
      }));

      await api.settings.save(payload);
      setSaveSuccess(`${group.toUpperCase()} settings saved successfully to database!`);
      setTimeout(() => setSaveSuccess(''), 3500);
    } catch (err) {
      setSaveError(err.response?.data?.message || 'Failed to save settings.');
    } finally {
      setLoading(false);
    }
  };

  // User CRUD Handlers
  const handleSaveUser = async (e) => {
    e.preventDefault();
    try {
      setLoading(true);
      if (editingUser) {
        await api.users.update(editingUser.id, userForm);
      } else {
        await api.users.create(userForm);
      }
      setUserModalOpen(false);
      fetchUsers();
    } catch (err) {
      alert(err.response?.data?.message || 'Failed to save user.');
    } finally {
      setLoading(false);
    }
  };

  const handleDeleteUser = async (id) => {
    if (!window.confirm('Are you sure you want to delete this user?')) return;
    try {
      await api.users.delete(id);
      fetchUsers();
    } catch (err) {
      alert(err.response?.data?.message || 'Failed to delete user.');
    }
  };

  // Role CRUD Handlers
  const handleSaveRole = async (e) => {
    e.preventDefault();
    try {
      setLoading(true);
      if (editingRole) {
        await api.roles.update(editingRole.id, roleForm);
      } else {
        await api.roles.create(roleForm);
      }
      setRoleModalOpen(false);
      fetchRolesAndPermissions();
    } catch (err) {
      alert(err.response?.data?.message || 'Failed to save role.');
    } finally {
      setLoading(false);
    }
  };

  const handleDeleteRole = async (id) => {
    if (!window.confirm('Are you sure you want to delete this role?')) return;
    try {
      await api.roles.delete(id);
      fetchRolesAndPermissions();
    } catch (err) {
      alert(err.response?.data?.message || 'Failed to delete role.');
    }
  };

  // Deal Stage CRUD Handlers
  const handleSaveStage = async (e) => {
    e.preventDefault();
    try {
      setLoading(true);
      if (editingStage) {
        await api.dealStages.update(editingStage.id, stageForm);
      } else {
        await api.dealStages.create(stageForm);
      }
      setStageModalOpen(false);
      fetchDealStages();
    } catch (err) {
      alert(err.response?.data?.message || 'Failed to save stage.');
    } finally {
      setLoading(false);
    }
  };

  const handleDeleteStage = async (id) => {
    if (!window.confirm('Are you sure you want to delete this stage?')) return;
    try {
      await api.dealStages.delete(id);
      fetchDealStages();
    } catch (err) {
      alert(err.response?.data?.message || 'Failed to delete stage.');
    }
  };

  // Custom Field CRUD Handlers
  const handleSaveCustomField = async (e) => {
    e.preventDefault();
    try {
      setLoading(true);
      if (editingField) {
        await api.customFields.update(editingField.id, customFieldForm);
      } else {
        await api.customFields.create(customFieldForm);
      }
      setCustomFieldModalOpen(false);
      fetchCustomFields();
    } catch (err) {
      alert(err.response?.data?.message || 'Failed to save custom field.');
    } finally {
      setLoading(false);
    }
  };

  const handleDeleteCustomField = async (id) => {
    if (!window.confirm('Are you sure you want to delete this custom field?')) return;
    try {
      await api.customFields.delete(id);
      fetchCustomFields();
    } catch (err) {
      alert(err.response?.data?.message || 'Failed to delete custom field.');
    }
  };

  const navTabs = [
    { key: 'system', label: '⚙️ System & General' },
    { key: 'company', label: '🏢 Company Profile' },
    { key: 'users', label: '👥 Users & Staff' },
    { key: 'roles', label: '🛡️ Roles & Permissions' },
    { key: 'pipelines', label: '🤝 Pipelines & Stages' },
    { key: 'custom_fields', label: '📋 Custom Fields' },
    { key: 'email', label: '✉️ Email & SMTP' },
    { key: 'integrations', label: '🔌 Webhooks & API' },
  ];

  return (
    <div className="p-6 md:p-8 space-y-6">
      {/* Header */}
      <div>
        <div className="flex items-center space-x-3">
          <h1 className="text-2xl font-black text-slate-900 tracking-tight flex items-center">
            <span className="mr-2.5 text-2xl">⚙️</span> System Settings & Administration
          </h1>
          <span className="px-2.5 py-0.5 rounded-full text-xs font-bold bg-purple-50 text-purple-700 border border-purple-200">
            RBAC Enforced
          </span>
        </div>
        <p className="text-slate-500 text-xs mt-1">
          Configure organization settings, team roles, custom polymorphic fields, email servers, and API integrations.
        </p>
      </div>

      {saveSuccess && (
        <div className="p-3.5 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold rounded-xl animate-in fade-in flex items-center space-x-2">
          <span>✓</span>
          <span>{saveSuccess}</span>
        </div>
      )}

      {saveError && (
        <div className="p-3.5 bg-red-50 border border-red-200 text-red-700 text-xs font-bold rounded-xl animate-in fade-in flex items-center space-x-2">
          <span>✕</span>
          <span>{saveError}</span>
        </div>
      )}

      {/* Navigation Tabs */}
      <div className="flex items-center space-x-1.5 overflow-x-auto pb-1">
        {navTabs.map((t) => (
          <button
            key={t.key}
            onClick={() => setActiveTab(t.key)}
            className={`px-4 py-2 rounded-xl text-xs font-bold transition flex-shrink-0 ${
              activeTab === t.key
                ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20'
                : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-100'
            }`}
          >
            {t.label}
          </button>
        ))}
      </div>

      {/* TAB CONTENT AREA */}
      <div className="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
        {/* 1. SYSTEM & GENERAL */}
        {activeTab === 'system' && (
          <div className="space-y-6">
            <div>
              <h2 className="text-base font-bold text-slate-900">System Preferences & Application Security</h2>
              <p className="text-xs text-slate-500">Core CRM server configurations and system-wide security toggles.</p>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label className="block text-xs font-bold text-slate-700 mb-1">Application Name</label>
                <input
                  type="text"
                  value={settings.system?.app_name || ''}
                  onChange={(e) => handleSettingChange('system', 'app_name', e.target.value)}
                  className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:bg-white"
                />
              </div>

              <div>
                <label className="block text-xs font-bold text-slate-700 mb-1">Session Lifetime (Minutes)</label>
                <input
                  type="number"
                  value={settings.system?.session_lifetime || '120'}
                  onChange={(e) => handleSettingChange('system', 'session_lifetime', e.target.value)}
                  className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:bg-white"
                />
              </div>

              <div>
                <label className="block text-xs font-bold text-slate-700 mb-1">Max Upload File Size (MB)</label>
                <input
                  type="number"
                  value={settings.system?.max_file_upload_mb || '25'}
                  onChange={(e) => handleSettingChange('system', 'max_file_upload_mb', e.target.value)}
                  className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:bg-white"
                />
              </div>

              <div>
                <label className="block text-xs font-bold text-slate-700 mb-1">Audit Trail & Logging</label>
                <select
                  value={settings.system?.audit_logging || 'true'}
                  onChange={(e) => handleSettingChange('system', 'audit_logging', e.target.value)}
                  className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:bg-white"
                >
                  <option value="true">Enabled (Track record history & stage shifts)</option>
                  <option value="false">Disabled</option>
                </select>
              </div>
            </div>

            <div className="pt-4 border-t border-slate-100 flex justify-end">
              <button
                onClick={() => handleSaveSettingsGroup('system')}
                disabled={loading}
                className="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-md shadow-blue-500/20 transition"
              >
                {loading ? 'Saving...' : 'Save System Settings'}
              </button>
            </div>
          </div>
        )}

        {/* 2. COMPANY PROFILE */}
        {activeTab === 'company' && (
          <div className="space-y-6">
            <div>
              <h2 className="text-base font-bold text-slate-900">Organization & Currency Profile</h2>
              <p className="text-xs text-slate-500">Business identification details rendered on invoices and proposals.</p>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label className="block text-xs font-bold text-slate-700 mb-1">Company / Entity Name</label>
                <input
                  type="text"
                  value={settings.company?.company_name || ''}
                  onChange={(e) => handleSettingChange('company', 'company_name', e.target.value)}
                  className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:bg-white"
                />
              </div>

              <div>
                <label className="block text-xs font-bold text-slate-700 mb-1">Official Contact Email</label>
                <input
                  type="email"
                  value={settings.company?.company_email || ''}
                  onChange={(e) => handleSettingChange('company', 'company_email', e.target.value)}
                  className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:bg-white"
                />
              </div>

              <div>
                <label className="block text-xs font-bold text-slate-700 mb-1">Currency Symbol</label>
                <input
                  type="text"
                  value={settings.company?.currency_symbol || '$'}
                  onChange={(e) => handleSettingChange('company', 'currency_symbol', e.target.value)}
                  className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:bg-white"
                />
              </div>

              <div>
                <label className="block text-xs font-bold text-slate-700 mb-1">Currency Code</label>
                <input
                  type="text"
                  value={settings.company?.currency_code || 'USD'}
                  onChange={(e) => handleSettingChange('company', 'currency_code', e.target.value)}
                  className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:bg-white"
                />
              </div>

              <div className="md:col-span-2">
                <label className="block text-xs font-bold text-slate-700 mb-1">HQ Address</label>
                <textarea
                  rows={2}
                  value={settings.company?.company_address || ''}
                  onChange={(e) => handleSettingChange('company', 'company_address', e.target.value)}
                  className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:bg-white resize-none"
                />
              </div>
            </div>

            <div className="pt-4 border-t border-slate-100 flex justify-end">
              <button
                onClick={() => handleSaveSettingsGroup('company')}
                disabled={loading}
                className="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-md shadow-blue-500/20 transition"
              >
                {loading ? 'Saving...' : 'Save Company Profile'}
              </button>
            </div>
          </div>
        )}

        {/* 3. USERS MANAGEMENT */}
        {activeTab === 'users' && (
          <div className="space-y-6">
            <div className="flex justify-between items-center">
              <div>
                <h2 className="text-base font-bold text-slate-900">User Accounts & Team Assignment</h2>
                <p className="text-xs text-slate-500">Manage authenticated staff, assign roles, and grant permissions.</p>
              </div>
              <button
                onClick={() => {
                  setEditingUser(null);
                  setUserForm({ name: '', email: '', password: '', roles: ['Sales Representative'] });
                  setUserModalOpen(true);
                }}
                className="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-md shadow-blue-500/20 transition"
              >
                + Add User
              </button>
            </div>

            <div className="overflow-x-auto">
              <table className="w-full text-left text-xs">
                <thead className="bg-slate-50 border-b border-slate-100 text-slate-500 font-bold uppercase tracking-wider">
                  <tr>
                    <th className="px-4 py-3">User</th>
                    <th className="px-4 py-3">Assigned Role(s)</th>
                    <th className="px-4 py-3">Created</th>
                    <th className="px-4 py-3 text-right">Actions</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-slate-100">
                  {users.map((u) => (
                    <tr key={u.id} className="hover:bg-slate-50/80 transition">
                      <td className="px-4 py-3 flex items-center space-x-3">
                        <div className="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-xs flex-shrink-0">
                          {u.name?.charAt(0) || 'U'}
                        </div>
                        <div>
                          <p className="font-bold text-slate-900">{u.name}</p>
                          <p className="text-[11px] text-slate-400">{u.email}</p>
                        </div>
                      </td>
                      <td className="px-4 py-3">
                        <div className="flex flex-wrap gap-1">
                          {u.roles?.length > 0 ? (
                            u.roles.map((r, i) => (
                              <span
                                key={i}
                                className="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200"
                              >
                                {r.name || r}
                              </span>
                            ))
                          ) : (
                            <span className="text-slate-400 italic">No role</span>
                          )}
                        </div>
                      </td>
                      <td className="px-4 py-3 text-slate-500">{u.created_at ? u.created_at.substring(0, 10) : 'N/A'}</td>
                      <td className="px-4 py-3 text-right space-x-2">
                        <button
                          onClick={() => {
                            setEditingUser(u);
                            setUserForm({
                              name: u.name,
                              email: u.email,
                              password: '',
                              roles: u.roles?.map((r) => r.name || r) || [],
                            });
                            setUserModalOpen(true);
                          }}
                          className="px-2.5 py-1 text-slate-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition font-semibold"
                        >
                          Edit
                        </button>
                        <button
                          onClick={() => handleDeleteUser(u.id)}
                          className="px-2.5 py-1 text-slate-600 hover:text-red-600 hover:bg-red-50 rounded-lg transition font-semibold"
                        >
                          Delete
                        </button>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        )}

        {/* 4. ROLES & PERMISSIONS MATRIX */}
        {activeTab === 'roles' && (
          <div className="space-y-6">
            <div className="flex justify-between items-center">
              <div>
                <h2 className="text-base font-bold text-slate-900">Roles & Granular Permissions Matrix</h2>
                <p className="text-xs text-slate-500">
                  Standard capabilities for Admin, Manager, Sales Representative, and Viewer roles enforced by backend policies.
                </p>
              </div>
              <button
                onClick={() => {
                  setEditingRole(null);
                  setRoleForm({ name: '', permissions: [] });
                  setRoleModalOpen(true);
                }}
                className="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-md shadow-blue-500/20 transition"
              >
                + Create Custom Role
              </button>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
              {rolesList.map((role) => (
                <div key={role.id} className="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-3 flex flex-col justify-between">
                  <div>
                    <div className="flex items-center justify-between">
                      <h3 className="font-bold text-sm text-slate-900">{role.name}</h3>
                      <span className="px-2 py-0.5 rounded-full text-[10px] font-bold bg-white text-slate-700 border">
                        {role.permissions?.length || 0} Perms
                      </span>
                    </div>
                    <p className="text-[11px] text-slate-500 mt-1">
                      {role.name === 'Admin'
                        ? 'Full system access, deletion, and configuration rights.'
                        : role.name === 'Manager'
                        ? 'Manage CRM pipelines, edit deals/leads, manage custom fields, view audit logs.'
                        : role.name === 'Sales Representative'
                        ? 'Create & progress assigned leads, deals, tasks, and client notes.'
                        : 'Read-only access across CRM records without modifying authority.'}
                    </p>
                  </div>

                  <div className="space-y-1.5 pt-2 border-t border-slate-200">
                    <p className="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Key Capabilities</p>
                    <div className="flex flex-wrap gap-1 max-h-24 overflow-y-auto">
                      {role.permissions?.slice(0, 6).map((p, i) => (
                        <span key={i} className="px-1.5 py-0.5 rounded bg-white text-[10px] font-semibold text-slate-700 border">
                          {p}
                        </span>
                      ))}
                      {role.permissions?.length > 6 && (
                        <span className="text-[10px] text-slate-500">+{role.permissions.length - 6} more</span>
                      )}
                    </div>
                  </div>

                  <div className="flex justify-end space-x-2 pt-2 border-t border-slate-200">
                    <button
                      onClick={() => {
                        setEditingRole(role);
                        setRoleForm({
                          name: role.name,
                          permissions: role.permissions || [],
                        });
                        setRoleModalOpen(true);
                      }}
                      className="text-xs font-bold text-blue-600 hover:underline"
                    >
                      Edit Permissions
                    </button>
                    {!['Admin', 'Manager', 'Sales Representative', 'Viewer'].includes(role.name) && (
                      <button
                        onClick={() => handleDeleteRole(role.id)}
                        className="text-xs font-bold text-red-600 hover:underline"
                      >
                        Delete
                      </button>
                    )}
                  </div>
                </div>
              ))}
            </div>
          </div>
        )}

        {/* 5. PIPELINES & DEAL STAGES */}
        {activeTab === 'pipelines' && (
          <div className="space-y-6">
            <div className="flex justify-between items-center">
              <div>
                <h2 className="text-base font-bold text-slate-900">Deals Pipeline Stages</h2>
                <p className="text-xs text-slate-500">
                  Configure commercial stages, display colors, and progression sequence.
                </p>
              </div>
              <button
                onClick={() => {
                  setEditingStage(null);
                  setStageForm({ name: '', color: '#3b82f6', order_index: stages.length + 1 });
                  setStageModalOpen(true);
                }}
                className="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-md shadow-blue-500/20 transition"
              >
                + Add Stage
              </button>
            </div>

            <div className="space-y-2">
              {stages.map((stage, idx) => (
                <div
                  key={stage.id}
                  className="p-3.5 bg-slate-50 rounded-xl border border-slate-200 flex items-center justify-between hover:bg-slate-100 transition"
                >
                  <div className="flex items-center space-x-3">
                    <span className="w-6 h-6 rounded-lg bg-white border flex items-center justify-center text-xs font-bold text-slate-600">
                      #{idx + 1}
                    </span>
                    <span className="w-4 h-4 rounded-full" style={{ backgroundColor: stage.color || '#3b82f6' }}></span>
                    <div>
                      <p className="text-xs font-bold text-slate-900">{stage.name}</p>
                      <p className="text-[10px] text-slate-400">{stage.deals_count || 0} active deals in this stage</p>
                    </div>
                  </div>

                  <div className="flex items-center space-x-2">
                    <button
                      onClick={() => {
                        setEditingStage(stage);
                        setStageForm({
                          name: stage.name,
                          color: stage.color || '#3b82f6',
                          order_index: stage.order_index,
                        });
                        setStageModalOpen(true);
                      }}
                      className="px-2.5 py-1 text-slate-600 hover:text-blue-600 hover:bg-white rounded-lg transition text-xs font-semibold"
                    >
                      Edit
                    </button>
                    <button
                      onClick={() => handleDeleteStage(stage.id)}
                      className="px-2.5 py-1 text-slate-600 hover:text-red-600 hover:bg-white rounded-lg transition text-xs font-semibold"
                    >
                      Delete
                    </button>
                  </div>
                </div>
              ))}
            </div>
          </div>
        )}

        {/* 6. CUSTOM FIELDS */}
        {activeTab === 'custom_fields' && (
          <div className="space-y-6">
            <div className="flex justify-between items-center flex-wrap gap-3">
              <div>
                <h2 className="text-base font-bold text-slate-900">Custom Form Fields</h2>
                <p className="text-xs text-slate-500">
                  Add polymorphic attribute fields to Leads, Deals, Contacts, or Companies.
                </p>
              </div>

              <div className="flex items-center space-x-3">
                <select
                  value={selectedModelType}
                  onChange={(e) => setSelectedModelType(e.target.value)}
                  className="px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-700"
                >
                  <option value="Lead">👤 Leads Fields</option>
                  <option value="Deal">🤝 Deals Fields</option>
                  <option value="Contact">📇 Contacts Fields</option>
                  <option value="Company">🏢 Company Fields</option>
                  <option value="Task">✓ Task Fields</option>
                </select>

                <button
                  onClick={() => {
                    setEditingField(null);
                    setCustomFieldForm({
                      model_type: selectedModelType,
                      label: '',
                      name: '',
                      field_type: 'text',
                      is_required: false,
                      default_value: '',
                    });
                    setCustomFieldModalOpen(true);
                  }}
                  className="px-4 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-md shadow-blue-500/20 transition"
                >
                  + Add Custom Field
                </button>
              </div>
            </div>

            <div className="overflow-x-auto">
              <table className="w-full text-left text-xs">
                <thead className="bg-slate-50 border-b border-slate-100 text-slate-500 font-bold uppercase tracking-wider">
                  <tr>
                    <th className="px-4 py-3">Field Label</th>
                    <th className="px-4 py-3">Database Key</th>
                    <th className="px-4 py-3">Type</th>
                    <th className="px-4 py-3">Required</th>
                    <th className="px-4 py-3 text-right">Actions</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-slate-100">
                  {customFields
                    .filter((f) => f.model_type === selectedModelType)
                    .map((field) => (
                      <tr key={field.id} className="hover:bg-slate-50/80 transition">
                        <td className="px-4 py-3 font-bold text-slate-900">{field.label}</td>
                        <td className="px-4 py-3 font-mono text-[11px] text-slate-500">{field.name}</td>
                        <td className="px-4 py-3">
                          <span className="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 uppercase">
                            {field.field_type}
                          </span>
                        </td>
                        <td className="px-4 py-3">
                          {field.is_required ? (
                            <span className="text-red-500 font-bold">Yes *</span>
                          ) : (
                            <span className="text-slate-400">Optional</span>
                          )}
                        </td>
                        <td className="px-4 py-3 text-right space-x-2">
                          <button
                            onClick={() => {
                              setEditingField(field);
                              setCustomFieldForm({
                                model_type: field.model_type,
                                label: field.label,
                                name: field.name,
                                field_type: field.field_type,
                                is_required: Boolean(field.is_required),
                                default_value: field.default_value || '',
                              });
                              setCustomFieldModalOpen(true);
                            }}
                            className="px-2.5 py-1 text-slate-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition font-semibold"
                          >
                            Edit
                          </button>
                          <button
                            onClick={() => handleDeleteCustomField(field.id)}
                            className="px-2.5 py-1 text-slate-600 hover:text-red-600 hover:bg-red-50 rounded-lg transition font-semibold"
                          >
                            Delete
                          </button>
                        </td>
                      </tr>
                    ))}
                  {customFields.filter((f) => f.model_type === selectedModelType).length === 0 && (
                    <tr>
                      <td colSpan={5} className="px-4 py-8 text-center text-slate-400 italic">
                        No custom fields configured for {selectedModelType}. Click "+ Add Custom Field" to create one.
                      </td>
                    </tr>
                  )}
                </tbody>
              </table>
            </div>
          </div>
        )}

        {/* 7. EMAIL & SMTP */}
        {activeTab === 'email' && (
          <div className="space-y-6">
            <div>
              <h2 className="text-base font-bold text-slate-900">Email Server & SMTP Configuration</h2>
              <p className="text-xs text-slate-500">Configure outbound SMTP servers for automated lead follow-ups and notifications.</p>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label className="block text-xs font-bold text-slate-700 mb-1">SMTP Host</label>
                <input
                  type="text"
                  value={settings.email?.mail_host || ''}
                  onChange={(e) => handleSettingChange('email', 'mail_host', e.target.value)}
                  placeholder="smtp.mailtrap.io"
                  className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:bg-white"
                />
              </div>

              <div>
                <label className="block text-xs font-bold text-slate-700 mb-1">SMTP Port</label>
                <input
                  type="text"
                  value={settings.email?.mail_port || '587'}
                  onChange={(e) => handleSettingChange('email', 'mail_port', e.target.value)}
                  placeholder="587"
                  className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:bg-white"
                />
              </div>

              <div>
                <label className="block text-xs font-bold text-slate-700 mb-1">Username / Auth Key</label>
                <input
                  type="text"
                  value={settings.email?.mail_username || ''}
                  onChange={(e) => handleSettingChange('email', 'mail_username', e.target.value)}
                  className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:bg-white"
                />
              </div>

              <div>
                <label className="block text-xs font-bold text-slate-700 mb-1">Password</label>
                <input
                  type="password"
                  value={settings.email?.mail_password || ''}
                  onChange={(e) => handleSettingChange('email', 'mail_password', e.target.value)}
                  placeholder="••••••••••••"
                  className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:bg-white"
                />
              </div>

              <div>
                <label className="block text-xs font-bold text-slate-700 mb-1">Sender Email (From)</label>
                <input
                  type="email"
                  value={settings.email?.mail_from_address || ''}
                  onChange={(e) => handleSettingChange('email', 'mail_from_address', e.target.value)}
                  placeholder="notifications@crm.com"
                  className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:bg-white"
                />
              </div>

              <div>
                <label className="block text-xs font-bold text-slate-700 mb-1">Sender Display Name</label>
                <input
                  type="text"
                  value={settings.email?.mail_from_name || ''}
                  onChange={(e) => handleSettingChange('email', 'mail_from_name', e.target.value)}
                  placeholder="SmartCRM Cloud"
                  className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:bg-white"
                />
              </div>
            </div>

            <div className="pt-4 border-t border-slate-100 flex justify-end">
              <button
                onClick={() => handleSaveSettingsGroup('email')}
                disabled={loading}
                className="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-md shadow-blue-500/20 transition"
              >
                {loading ? 'Saving...' : 'Save Email Server Settings'}
              </button>
            </div>
          </div>
        )}

        {/* 8. INTEGRATIONS & WEBHOOKS */}
        {activeTab === 'integrations' && (
          <div className="space-y-6">
            <div>
              <h2 className="text-base font-bold text-slate-900">API Webhooks & Third-Party Integrations</h2>
              <p className="text-xs text-slate-500">Connect CRM event triggers with Zapier, Slack, or external webhook endpoints.</p>
            </div>

            <div className="space-y-4">
              <div>
                <label className="block text-xs font-bold text-slate-700 mb-1">Global Inbound Webhook URL</label>
                <input
                  type="text"
                  value={settings.integrations?.webhook_url || ''}
                  onChange={(e) => handleSettingChange('integrations', 'webhook_url', e.target.value)}
                  className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:bg-white font-mono"
                />
              </div>

              <div>
                <label className="block text-xs font-bold text-slate-700 mb-1">Slack Notification Webhook</label>
                <input
                  type="text"
                  value={settings.integrations?.slack_webhook || ''}
                  onChange={(e) => handleSettingChange('integrations', 'slack_webhook', e.target.value)}
                  placeholder="https://hooks.slack.com/services/..."
                  className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:bg-white font-mono"
                />
              </div>

              <div>
                <label className="block text-xs font-bold text-slate-700 mb-1">Zapier Webhook Trigger</label>
                <input
                  type="text"
                  value={settings.integrations?.zapier_webhook || ''}
                  onChange={(e) => handleSettingChange('integrations', 'zapier_webhook', e.target.value)}
                  placeholder="https://hooks.zapier.com/hooks/catch/..."
                  className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:bg-white font-mono"
                />
              </div>
            </div>

            <div className="pt-4 border-t border-slate-100 flex justify-end">
              <button
                onClick={() => handleSaveSettingsGroup('integrations')}
                disabled={loading}
                className="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-md shadow-blue-500/20 transition"
              >
                {loading ? 'Saving...' : 'Save Integrations'}
              </button>
            </div>
          </div>
        )}
      </div>

      {/* MODAL 1: ADD / EDIT USER */}
      {userModalOpen && (
        <div className="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
          <div className="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 space-y-4 border border-slate-100">
            <div className="flex justify-between items-center border-b pb-3">
              <h3 className="text-base font-bold text-slate-900">{editingUser ? 'Edit User' : 'Create New User'}</h3>
              <button onClick={() => setUserModalOpen(false)} className="text-slate-400 hover:text-slate-600 text-sm font-bold">
                ✕
              </button>
            </div>
            <form onSubmit={handleSaveUser} className="space-y-3">
              <div>
                <label className="block text-xs font-bold text-slate-700 mb-1">Full Name *</label>
                <input
                  type="text"
                  required
                  value={userForm.name}
                  onChange={(e) => setUserForm({ ...userForm, name: e.target.value })}
                  className="w-full px-3 py-2 border rounded-xl text-xs focus:ring-2 focus:ring-blue-500"
                />
              </div>
              <div>
                <label className="block text-xs font-bold text-slate-700 mb-1">Email Address *</label>
                <input
                  type="email"
                  required
                  value={userForm.email}
                  onChange={(e) => setUserForm({ ...userForm, email: e.target.value })}
                  className="w-full px-3 py-2 border rounded-xl text-xs focus:ring-2 focus:ring-blue-500"
                />
              </div>
              <div>
                <label className="block text-xs font-bold text-slate-700 mb-1">
                  Password {editingUser ? '(leave blank to keep unchanged)' : '*'}
                </label>
                <input
                  type="password"
                  required={!editingUser}
                  value={userForm.password}
                  onChange={(e) => setUserForm({ ...userForm, password: e.target.value })}
                  className="w-full px-3 py-2 border rounded-xl text-xs focus:ring-2 focus:ring-blue-500"
                />
              </div>
              <div>
                <label className="block text-xs font-bold text-slate-700 mb-1">Assigned Role</label>
                <select
                  value={userForm.roles[0] || ''}
                  onChange={(e) => setUserForm({ ...userForm, roles: [e.target.value] })}
                  className="w-full px-3 py-2 border rounded-xl text-xs focus:ring-2 focus:ring-blue-500"
                >
                  {rolesList.map((r) => (
                    <option key={r.id} value={r.name}>
                      {r.name}
                    </option>
                  ))}
                </select>
              </div>

              <div className="flex justify-end space-x-2 pt-3 border-t">
                <button
                  type="button"
                  onClick={() => setUserModalOpen(false)}
                  className="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  disabled={loading}
                  className="px-5 py-2 bg-blue-600 text-white text-xs font-bold rounded-xl hover:bg-blue-700"
                >
                  {loading ? 'Saving...' : 'Save User'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* MODAL 2: ADD / EDIT ROLE & PERMISSIONS */}
      {roleModalOpen && (
        <div className="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
          <div className="bg-white rounded-2xl shadow-2xl max-w-lg w-full p-6 space-y-4 border border-slate-100 max-h-[90vh] overflow-y-auto">
            <div className="flex justify-between items-center border-b pb-3">
              <h3 className="text-base font-bold text-slate-900">{editingRole ? `Edit Permissions: ${editingRole.name}` : 'Create Role'}</h3>
              <button onClick={() => setRoleModalOpen(false)} className="text-slate-400 hover:text-slate-600 text-sm font-bold">
                ✕
              </button>
            </div>
            <form onSubmit={handleSaveRole} className="space-y-4">
              <div>
                <label className="block text-xs font-bold text-slate-700 mb-1">Role Name *</label>
                <input
                  type="text"
                  required
                  disabled={['Admin', 'Manager', 'Sales Representative', 'Viewer'].includes(editingRole?.name)}
                  value={roleForm.name}
                  onChange={(e) => setRoleForm({ ...roleForm, name: e.target.value })}
                  className="w-full px-3 py-2 border rounded-xl text-xs focus:ring-2 focus:ring-blue-500 disabled:bg-slate-100"
                />
              </div>

              <div>
                <p className="text-xs font-bold text-slate-800 mb-2">Assign Permissions by Module</p>
                <div className="space-y-3">
                  {Object.keys(permissionsGrouped).map((mod) => (
                    <div key={mod} className="p-3 bg-slate-50 rounded-xl border border-slate-200 space-y-2">
                      <p className="text-[11px] font-bold text-slate-700 uppercase tracking-wider">{mod} Module</p>
                      <div className="grid grid-cols-2 gap-2">
                        {permissionsGrouped[mod].map((perm) => {
                          const isChecked = roleForm.permissions.includes(perm.name);
                          return (
                            <label key={perm.id} className="flex items-center space-x-2 text-xs text-slate-700 cursor-pointer">
                              <input
                                type="checkbox"
                                checked={isChecked}
                                onChange={(e) => {
                                  if (e.target.checked) {
                                    setRoleForm({ ...roleForm, permissions: [...roleForm.permissions, perm.name] });
                                  } else {
                                    setRoleForm({
                                      ...roleForm,
                                      permissions: roleForm.permissions.filter((p) => p !== perm.name),
                                    });
                                  }
                                }}
                                className="rounded text-blue-600 focus:ring-blue-500 w-3.5 h-3.5"
                              />
                              <span>{perm.label}</span>
                            </label>
                          );
                        })}
                      </div>
                    </div>
                  ))}
                </div>
              </div>

              <div className="flex justify-end space-x-2 pt-3 border-t">
                <button
                  type="button"
                  onClick={() => setRoleModalOpen(false)}
                  className="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  disabled={loading}
                  className="px-5 py-2 bg-blue-600 text-white text-xs font-bold rounded-xl hover:bg-blue-700"
                >
                  {loading ? 'Saving...' : 'Save Role Permissions'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* MODAL 3: ADD / EDIT PIPELINE STAGE */}
      {stageModalOpen && (
        <div className="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
          <div className="bg-white rounded-2xl shadow-2xl max-w-sm w-full p-6 space-y-4 border border-slate-100">
            <div className="flex justify-between items-center border-b pb-3">
              <h3 className="text-base font-bold text-slate-900">{editingStage ? 'Edit Pipeline Stage' : 'Add Pipeline Stage'}</h3>
              <button onClick={() => setStageModalOpen(false)} className="text-slate-400 hover:text-slate-600 text-sm font-bold">
                ✕
              </button>
            </div>
            <form onSubmit={handleSaveStage} className="space-y-3">
              <div>
                <label className="block text-xs font-bold text-slate-700 mb-1">Stage Name *</label>
                <input
                  type="text"
                  required
                  value={stageForm.name}
                  onChange={(e) => setStageForm({ ...stageForm, name: e.target.value })}
                  className="w-full px-3 py-2 border rounded-xl text-xs focus:ring-2 focus:ring-blue-500"
                />
              </div>
              <div>
                <label className="block text-xs font-bold text-slate-700 mb-1">Theme Color</label>
                <div className="flex items-center space-x-2">
                  <input
                    type="color"
                    value={stageForm.color}
                    onChange={(e) => setStageForm({ ...stageForm, color: e.target.value })}
                    className="w-8 h-8 rounded border p-0 cursor-pointer"
                  />
                  <input
                    type="text"
                    value={stageForm.color}
                    onChange={(e) => setStageForm({ ...stageForm, color: e.target.value })}
                    className="flex-1 px-3 py-2 border rounded-xl text-xs"
                  />
                </div>
              </div>

              <div className="flex justify-end space-x-2 pt-3 border-t">
                <button
                  type="button"
                  onClick={() => setStageModalOpen(false)}
                  className="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  disabled={loading}
                  className="px-5 py-2 bg-blue-600 text-white text-xs font-bold rounded-xl hover:bg-blue-700"
                >
                  {loading ? 'Saving...' : 'Save Stage'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* MODAL 4: ADD / EDIT CUSTOM FIELD */}
      {customFieldModalOpen && (
        <div className="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
          <div className="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 space-y-4 border border-slate-100">
            <div className="flex justify-between items-center border-b pb-3">
              <h3 className="text-base font-bold text-slate-900">
                {editingField ? 'Edit Custom Field' : `Add Field to ${customFieldForm.model_type}`}
              </h3>
              <button onClick={() => setCustomFieldModalOpen(false)} className="text-slate-400 hover:text-slate-600 text-sm font-bold">
                ✕
              </button>
            </div>
            <form onSubmit={handleSaveCustomField} className="space-y-3">
              <div>
                <label className="block text-xs font-bold text-slate-700 mb-1">Field Display Label *</label>
                <input
                  type="text"
                  required
                  placeholder="e.g., Contract Number, Target Budget"
                  value={customFieldForm.label}
                  onChange={(e) => setCustomFieldForm({ ...customFieldForm, label: e.target.value })}
                  className="w-full px-3 py-2 border rounded-xl text-xs focus:ring-2 focus:ring-blue-500"
                />
              </div>

              <div>
                <label className="block text-xs font-bold text-slate-700 mb-1">Field Data Type</label>
                <select
                  value={customFieldForm.field_type}
                  onChange={(e) => setCustomFieldForm({ ...customFieldForm, field_type: e.target.value })}
                  className="w-full px-3 py-2 border rounded-xl text-xs focus:ring-2 focus:ring-blue-500"
                >
                  <option value="text">Text (Single Line)</option>
                  <option value="number">Numeric / Amount</option>
                  <option value="date">Date Picker</option>
                  <option value="textarea">Textarea (Multi-line)</option>
                  <option value="boolean">Boolean Toggle</option>
                </select>
              </div>

              <div className="flex items-center space-x-2 pt-1">
                <input
                  type="checkbox"
                  id="req"
                  checked={customFieldForm.is_required}
                  onChange={(e) => setCustomFieldForm({ ...customFieldForm, is_required: e.target.checked })}
                  className="rounded text-blue-600 focus:ring-blue-500 w-4 h-4"
                />
                <label htmlFor="req" className="text-xs font-semibold text-slate-700 cursor-pointer">
                  Mandatory Required Field
                </label>
              </div>

              <div className="flex justify-end space-x-2 pt-3 border-t">
                <button
                  type="button"
                  onClick={() => setCustomFieldModalOpen(false)}
                  className="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  disabled={loading}
                  className="px-5 py-2 bg-blue-600 text-white text-xs font-bold rounded-xl hover:bg-blue-700"
                >
                  {loading ? 'Saving...' : 'Save Field'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}
