import axios from 'axios';

const API_BASE_URL = 'http://127.0.0.1:8000/api';

const apiClient = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  },
});

// Request interceptor to attach bearer token
apiClient.interceptors.request.use((config) => {
  const token = localStorage.getItem('crm_token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Response interceptor to handle 401 unauthenticated
apiClient.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response && error.response.status === 401) {
      localStorage.removeItem('crm_token');
      if (window.location.pathname !== '/' && window.location.pathname !== '/register') {
        window.location.href = '/';
      }
    }
    return Promise.reject(error);
  }
);

export const api = {
  // Auth
  auth: {
    login: (credentials) => apiClient.post('/login', credentials),
    register: (data) => apiClient.post('/register', data),
    logout: () => apiClient.post('/logout'),
    user: () => apiClient.get('/user'),
  },

  // Dashboard
  dashboard: {
    get: (params) => apiClient.get('/dashboard', { params }),
  },

  // Reports
  reports: {
    get: (params) => apiClient.get('/reports', { params }),
  },

  // Leads
  leads: {
    list: (params) => apiClient.get('/leads', { params }),
    get: (id) => apiClient.get(`/leads/${id}`),
    create: (data) => apiClient.post('/leads/auth-store', data),
    publicCapture: (data) => apiClient.post('/leads', data),
    update: (id, data) => apiClient.put(`/leads/${id}`, data),
    delete: (id) => apiClient.delete(`/leads/${id}`),
    bulkDelete: (ids) => apiClient.delete('/leads/bulk-delete', { data: { ids } }),
    metadata: () => apiClient.get('/leads/metadata'),
  },

  // Deals
  deals: {
    list: (params) => apiClient.get('/deals', { params }),
    get: (id) => apiClient.get(`/deals/${id}`),
    create: (data) => apiClient.post('/deals', data),
    update: (id, data) => apiClient.put(`/deals/${id}`, data),
    updateStage: (id, stageData) => apiClient.patch(`/deals/${id}/stage`, stageData),
    delete: (id) => apiClient.delete(`/deals/${id}`),
    bulkDelete: (ids) => apiClient.delete('/deals/bulk-delete', { data: { ids } }),
    metadata: () => apiClient.get('/deals/metadata'),
  },

  // Contacts
  contacts: {
    list: (params) => apiClient.get('/contacts', { params }),
    get: (id) => apiClient.get(`/contacts/${id}`),
    create: (data) => apiClient.post('/contacts', data),
    update: (id, data) => apiClient.put(`/contacts/${id}`, data),
    delete: (id) => apiClient.delete(`/contacts/${id}`),
    bulkDelete: (ids) => apiClient.delete('/contacts/bulk-delete', { data: { ids } }),
    metadata: () => apiClient.get('/contacts/metadata'),
  },

  // Tasks
  tasks: {
    list: (params) => apiClient.get('/tasks', { params }),
    get: (id) => apiClient.get(`/tasks/${id}`),
    create: (data) => apiClient.post('/tasks', data),
    update: (id, data) => apiClient.put(`/tasks/${id}`, data),
    updateStatus: (id, status) => apiClient.patch(`/tasks/${id}/status`, { status }),
    delete: (id) => apiClient.delete(`/tasks/${id}`),
    bulkDelete: (ids) => apiClient.delete('/tasks/bulk-delete', { data: { ids } }),
    bulkComplete: (ids) => apiClient.post('/tasks/bulk-complete', { ids }),
    metadata: () => apiClient.get('/tasks/metadata'),
  },

  // Calendar
  calendar: {
    list: (params) => apiClient.get('/calendar-events', { params }),
    get: (id) => apiClient.get(`/calendar-events/${id}`),
    create: (data) => apiClient.post('/calendar-events', data),
    update: (id, data) => apiClient.put(`/calendar-events/${id}`, data),
    reschedule: (id, data) => apiClient.patch(`/calendar-events/${id}/reschedule`, data),
    delete: (id) => apiClient.delete(`/calendar-events/${id}`),
    bulkDelete: (ids) => apiClient.delete('/calendar-events/bulk-delete', { data: { ids } }),
    metadata: () => apiClient.get('/calendar-events/metadata'),
  },

  // Activities
  activities: {
    list: (params) => apiClient.get('/activities', { params }),
    get: (id) => apiClient.get(`/activities/${id}`),
    create: (data) => apiClient.post('/activities', data),
    update: (id, data) => apiClient.put(`/activities/${id}`, data),
    delete: (id) => apiClient.delete(`/activities/${id}`),
  },

  // Notifications
  notifications: {
    list: (params) => apiClient.get('/notifications', { params }),
    markAsRead: (id) => apiClient.patch(`/notifications/${id}/read`),
    markAllAsRead: () => apiClient.post('/notifications/mark-all-read'),
    delete: (id) => apiClient.delete(`/notifications/${id}`),
  },

  // Users
  users: {
    list: (params) => apiClient.get('/users', { params }),
    get: (id) => apiClient.get(`/users/${id}`),
    create: (data) => apiClient.post('/users', data),
    update: (id, data) => apiClient.put(`/users/${id}`, data),
    delete: (id) => apiClient.delete(`/users/${id}`),
    roles: () => apiClient.get('/roles'),
  },

  // Roles & Permissions
  roles: {
    list: () => apiClient.get('/roles'),
    create: (data) => apiClient.post('/roles', data),
    update: (id, data) => apiClient.put(`/roles/${id}`, data),
    delete: (id) => apiClient.delete(`/roles/${id}`),
    permissions: () => apiClient.get('/permissions'),
  },

  // Deal Stages / Pipelines
  dealStages: {
    list: () => apiClient.get('/deal-stages'),
    create: (data) => apiClient.post('/deal-stages', data),
    update: (id, data) => apiClient.put(`/deal-stages/${id}`, data),
    reorder: (stages) => apiClient.post('/deal-stages/reorder', { stages }),
    delete: (id) => apiClient.delete(`/deal-stages/${id}`),
  },

  // Custom Fields
  customFields: {
    list: (params) => apiClient.get('/custom-fields', { params }),
    create: (data) => apiClient.post('/custom-fields', data),
    update: (id, data) => apiClient.put(`/custom-fields/${id}`, data),
    delete: (id) => apiClient.delete(`/custom-fields/${id}`),
  },

  // Settings
  settings: {
    list: (params) => apiClient.get('/settings', { params }),
    save: (settings) => apiClient.post('/settings', { settings }),
  },
};

export default apiClient;
