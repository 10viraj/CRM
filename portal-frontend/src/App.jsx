import React, { useState, useEffect, createContext, useContext, useCallback } from 'react';
import { BrowserRouter as Router, Routes, Route, Link, Navigate, useNavigate, useLocation } from 'react-router-dom';
import axios from 'axios';
import { api } from './services/api';

import DashboardHome from './pages/DashboardHome';
import LeadsList from './pages/LeadsList';
import LeadDetails from './pages/LeadDetails';
import ContactsList from './pages/ContactsList';
import ContactDetails from './pages/ContactDetails';
import DealsList from './pages/DealsList';
import DealDetails from './pages/DealDetails';
import TasksList from './pages/TasksList';
import CalendarView from './pages/CalendarView';
import ReportsPage from './pages/ReportsPage';
import SettingsPage from './pages/SettingsPage';

import './index.css';

// --- AUTH CONTEXT ---
const AuthContext = createContext({
  user: null,
  notifications: [],
  unreadCount: 0,
  refreshUser: () => { },
  refreshNotifications: () => { },
  logout: () => { },
});

export const useAuth = () => useContext(AuthContext);

// --- UTILITIES ---
const setAuthToken = (token) => {
  if (token) {
    axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
    localStorage.setItem('crm_token', token);
  } else {
    delete axios.defaults.headers.common['Authorization'];
    localStorage.removeItem('crm_token');
  }
};

// --- AUTH PROVIDER ---
function AuthProvider({ children }) {
  const [user, setUser] = useState(null);
  const [notifications, setNotifications] = useState([]);
  const [unreadCount, setUnreadCount] = useState(0);
  const [loading, setLoading] = useState(true);

  const refreshUser = useCallback(async () => {
    const token = localStorage.getItem('crm_token');
    if (!token) {
      setUser(null);
      setLoading(false);
      return;
    }
    setAuthToken(token);
    try {
      const res = await api.auth.user();
      if (res.data?.data) {
        setUser(res.data.data);
      }
    } catch (err) {
      console.error('Failed to load authenticated user profile', err);
      if (err.response?.status === 401) {
        setAuthToken(null);
        setUser(null);
      }
    } finally {
      setLoading(false);
    }
  }, []);

  const refreshNotifications = useCallback(async () => {
    const token = localStorage.getItem('crm_token');
    if (!token) return;
    try {
      const res = await api.notifications.list({ per_page: 8 });
      if (res.data?.data) {
        setNotifications(res.data.data);
        setUnreadCount(res.data.unread_count || 0);
      }
    } catch (err) {
      console.error('Failed to load notifications', err);
    }
  }, []);

  useEffect(() => {
    refreshUser();
    refreshNotifications();
  }, [refreshUser, refreshNotifications]);

  const logout = () => {
    setAuthToken(null);
    setUser(null);
    setNotifications([]);
    setUnreadCount(0);
  };

  return (
    <AuthContext.Provider value={{ user, notifications, unreadCount, refreshUser, refreshNotifications, logout, loading }}>
      {children}
    </AuthContext.Provider>
  );
}

// 1. Protected Route Wrapper
const ProtectedRoute = ({ children }) => {
  const token = localStorage.getItem('crm_token');
  if (!token) {
    return <Navigate to="/" replace />;
  }
  setAuthToken(token);
  return children;
};

// Sidebar Component
const Sidebar = () => {
  const location = useLocation();
  const { user } = useAuth();

  const isActive = (path) => {
    if (path === '/dashboard/leads') {
      return (location.pathname === '/leads' || location.pathname === '/dashboard/leads' || location.pathname.startsWith('/leads/') || location.pathname.startsWith('/dashboard/leads/'))
        ? 'bg-blue-600 text-white'
        : 'text-gray-400 hover:text-white hover:bg-gray-800';
    }
    if (path === '/dashboard/deals') {
      return (location.pathname === '/deals' || location.pathname === '/dashboard/deals' || location.pathname.startsWith('/deals/') || location.pathname.startsWith('/dashboard/deals/'))
        ? 'bg-blue-600 text-white'
        : 'text-gray-400 hover:text-white hover:bg-gray-800';
    }
    if (path === '/dashboard/contacts') {
      return (location.pathname === '/contacts' || location.pathname === '/dashboard/contacts' || location.pathname.startsWith('/contacts/') || location.pathname.startsWith('/dashboard/contacts/'))
        ? 'bg-blue-600 text-white'
        : 'text-gray-400 hover:text-white hover:bg-gray-800';
    }
    if (path === '/dashboard/calendar') {
      return (location.pathname === '/calendar' || location.pathname === '/dashboard/calendar')
        ? 'bg-blue-600 text-white'
        : 'text-gray-400 hover:text-white hover:bg-gray-800';
    }
    if (path === '/dashboard/tasks') {
      return (location.pathname === '/tasks' || location.pathname === '/dashboard/tasks')
        ? 'bg-blue-600 text-white'
        : 'text-gray-400 hover:text-white hover:bg-gray-800';
    }
    if (path === '/dashboard/reports') {
      return (location.pathname === '/reports' || location.pathname === '/dashboard/reports')
        ? 'bg-blue-600 text-white'
        : 'text-gray-400 hover:text-white hover:bg-gray-800';
    }
    if (path === '/dashboard/settings') {
      return (location.pathname === '/settings' || location.pathname === '/dashboard/settings')
        ? 'bg-blue-600 text-white'
        : 'text-gray-400 hover:text-white hover:bg-gray-800';
    }
    return location.pathname === path ? 'bg-blue-600 text-white' : 'text-gray-400 hover:text-white hover:bg-gray-800';
  };

  const primaryRole = user?.role || (user?.roles && user.roles[0]) || 'User';

  return (
    <aside className="w-64 bg-[#1a1e29] text-white flex flex-col flex-shrink-0 h-screen">
      <div className="p-4 text-2xl font-bold border-b border-gray-700 flex items-center justify-between">
        CRM <span className="text-gray-400 text-lg">≡</span>
      </div>
      <nav className="flex-1 p-4 space-y-2 overflow-y-auto">
        <Link to="/dashboard" className={`flex items-center px-4 py-2 rounded-lg font-medium transition ${isActive('/dashboard')}`}>
          <span className="mr-3">📊</span> Dashboard
        </Link>
        <Link to="/dashboard/leads" className={`flex items-center px-4 py-2 rounded-lg font-medium transition ${isActive('/dashboard/leads')}`}>
          <span className="mr-3">👥</span> Leads
        </Link>
        <Link to="/dashboard/deals" className={`flex items-center px-4 py-2 rounded-lg font-medium transition ${isActive('/dashboard/deals')}`}>
          <span className="mr-3">🤝</span> Deals
        </Link>
        <Link to="/dashboard/contacts" className={`flex items-center px-4 py-2 rounded-lg font-medium transition ${isActive('/dashboard/contacts')}`}>
          <span className="mr-3">📇</span> Contacts
        </Link>
        <Link to="/dashboard/calendar" className={`flex items-center px-4 py-2 rounded-lg font-medium transition ${isActive('/dashboard/calendar')}`}>
          <span className="mr-3">📅</span> Calendar
        </Link>
        <Link to="/dashboard/tasks" className={`flex items-center px-4 py-2 rounded-lg font-medium transition ${isActive('/dashboard/tasks')}`}>
          <span className="mr-3">✓</span> Tasks
        </Link>
        <Link to="/dashboard/reports" className={`flex items-center px-4 py-2 rounded-lg font-medium transition ${isActive('/dashboard/reports')}`}>
          <span className="mr-3">📈</span> Reports
        </Link>
        <Link to="/dashboard/settings" className={`flex items-center px-4 py-2 rounded-lg font-medium transition ${isActive('/dashboard/settings')}`}>
          <span className="mr-3">⚙️</span> Settings
        </Link>
      </nav>

      {/* Dynamic Logged-in User Card */}
      <div className="p-4 border-t border-gray-700 flex items-center space-x-3">
        <div className="w-10 h-10 rounded-full bg-indigo-600 text-white font-black text-sm flex items-center justify-center flex-shrink-0">
          {user?.name ? user.name.charAt(0).toUpperCase() : 'U'}
        </div>
        <div className="overflow-hidden flex-1">
          <p className="text-sm font-bold truncate text-white">{user?.name || 'CRM User'}</p>
          <div className="flex items-center space-x-1.5">
            <span className="inline-block w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
            <p className="text-[11px] text-gray-400 truncate">{primaryRole}</p>
          </div>
        </div>
      </div>
    </aside>
  );
};

// Dashboard Layout Wrapper
const DashboardLayout = ({ children, title }) => {
  const navigate = useNavigate();
  const { user, logout, notifications, unreadCount, refreshNotifications } = useAuth();
  const [showNotifications, setShowNotifications] = useState(false);

  const handleLogout = () => {
    logout();
    navigate('/');
  };

  const handleMarkAsRead = async (id) => {
    try {
      await api.notifications.markAsRead(id);
      refreshNotifications();
    } catch (err) {
      console.error('Failed to mark notification read', err);
    }
  };

  return (
    <div className="h-screen flex bg-slate-50 font-sans overflow-hidden">
      <Sidebar />
      <main className="flex-1 flex flex-col min-w-0">
        <header className="bg-white border-b border-gray-200 p-4 flex justify-between items-center h-16 flex-shrink-0">
          <h1 className="text-xl font-bold text-gray-800">{title}</h1>
          <div className="flex items-center space-x-4">
            <div className="relative hidden md:block">
              <svg className="w-4 h-4 absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
              <input type="text" placeholder="Quick search..." className="bg-gray-50 border border-gray-200 rounded-full pl-9 pr-4 py-1.5 text-sm w-64 focus:outline-none focus:ring-1 focus:ring-blue-500" />
            </div>

            {/* Notification Bell with Live Unread Badge */}
            <div className="relative">
              <button
                onClick={() => setShowNotifications(!showNotifications)}
                className="text-gray-500 hover:text-gray-700 relative p-1.5 rounded-lg hover:bg-slate-100 transition"
              >
                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                {unreadCount > 0 && (
                  <span className="absolute top-0 right-0 w-4 h-4 bg-red-500 text-white rounded-full text-[10px] font-bold flex items-center justify-center">
                    {unreadCount > 9 ? '9+' : unreadCount}
                  </span>
                )}
              </button>

              {/* Dropdown */}
              {showNotifications && (
                <div className="absolute right-0 mt-2 w-80 bg-white rounded-2xl shadow-2xl border border-slate-100 py-3 z-50">
                  <div className="px-4 py-2 border-b border-slate-100 flex items-center justify-between">
                    <span className="text-xs font-bold text-slate-800">Notifications ({unreadCount} unread)</span>
                    <button onClick={() => setShowNotifications(false)} className="text-slate-400 hover:text-slate-600 text-xs">✕</button>
                  </div>
                  <div className="max-h-64 overflow-y-auto divide-y divide-slate-50">
                    {notifications.length === 0 ? (
                      <p className="text-xs text-slate-400 py-6 text-center italic">No new notifications</p>
                    ) : (
                      notifications.map(n => (
                        <div key={n.id} className={`p-3 text-xs hover:bg-slate-50 transition cursor-pointer ${!n.read_at ? 'bg-blue-50/40' : ''}`} onClick={() => handleMarkAsRead(n.id)}>
                          <p className="font-bold text-slate-800">{n.title || 'CRM Alert'}</p>
                          <p className="text-slate-500 mt-0.5">{n.message || n.data?.message}</p>
                          <span className="text-[10px] text-slate-400 mt-1 block">{n.created_at || 'Just now'}</span>
                        </div>
                      ))
                    )}
                  </div>
                </div>
              )}
            </div>

            {/* Dynamic User Avatar */}
            <div className="w-8 h-8 rounded-full bg-indigo-600 text-white flex items-center justify-center font-bold text-xs">
              {user?.name ? user.name.charAt(0).toUpperCase() : 'A'}
            </div>

            <button onClick={handleLogout} className="text-sm text-gray-600 hover:text-red-600 font-medium transition">
              Logout
            </button>
          </div>
        </header>
        <div className="flex-1 overflow-auto bg-slate-50 relative">
          {children}
        </div>
      </main>
    </div>
  );
};

// 2. Login Page
function Login() {
  const [credentials, setCredentials] = useState({ login: '', password: '' });
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);
  const navigate = useNavigate();
  const { refreshUser } = useAuth();

  useEffect(() => {
    if (localStorage.getItem('crm_token')) {
      navigate('/dashboard');
    }
  }, [navigate]);

  const handleChange = (e) => {
    setCredentials({ ...credentials, [e.target.name]: e.target.value });
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setError('');
    try {
      const response = await axios.post('http://127.0.0.1:8000/api/login', credentials, {
        headers: { 'Accept': 'application/json' }
      });
      if (response.data.success) {
        setAuthToken(response.data.access_token);
        await refreshUser();
        navigate('/dashboard');
      }
    } catch (err) {
      setError(err.response?.data?.message || 'Login failed. Please check your credentials.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="min-h-screen flex items-center justify-center bg-slate-50 py-12 px-4 sm:px-6 lg:px-8">
      <div className="max-w-md w-full space-y-8 bg-white p-10 rounded-2xl shadow-xl border border-slate-100">
        <div className="text-center">
          <div className="w-16 h-16 bg-indigo-600 text-white rounded-2xl flex items-center justify-center text-3xl font-bold mx-auto mb-4 shadow-lg shadow-indigo-200">C</div>
          <h2 className="text-3xl font-extrabold text-slate-900 tracking-tight">Sign in to SmartCRM</h2>
          <p className="mt-2 text-sm text-slate-500">Access your live dashboard.</p>
        </div>
        <form className="mt-8 space-y-6" onSubmit={handleSubmit}>
          {error && <div className="bg-red-50 text-red-700 p-3 rounded text-sm text-center border border-red-200">{error}</div>}
          <div className="space-y-4">
            <div>
              <label className="block text-sm font-medium text-slate-700 mb-1">Email address or User ID</label>
              <input name="login" type="text" required value={credentials.login} onChange={handleChange} className="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="Email or User ID" />
            </div>
            <div>
              <label className="block text-sm font-medium text-slate-700 mb-1">Password</label>
              <input name="password" type="password" required value={credentials.password} onChange={handleChange} className="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="••••••••" />
            </div>
          </div>
          <button type="submit" disabled={loading} className="w-full py-3 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition disabled:opacity-70">
            {loading ? 'Signing in...' : 'Sign In'}
          </button>
        </form>
        <div className="mt-6 text-center">
          <p className="text-sm text-slate-500">Don't have an account? <Link to="/register" className="text-indigo-600 hover:underline font-medium">Register</Link></p>
          <p className="text-sm text-slate-500 mt-2">Looking to submit a request? <Link to="/partner" className="text-indigo-600 hover:underline font-medium">Partner with Us</Link></p>
        </div>
      </div>
    </div>
  );
}

// 2b. Register Page
function Register() {
  const [data, setData] = useState({ name: '', email: '', password: '' });
  const [error, setError] = useState('');
  const [validationErrors, setValidationErrors] = useState({});
  const [loading, setLoading] = useState(false);
  const navigate = useNavigate();
  const { refreshUser } = useAuth();

  useEffect(() => {
    if (localStorage.getItem('crm_token')) {
      navigate('/dashboard');
    }
  }, [navigate]);

  const handleChange = (e) => setData({ ...data, [e.target.name]: e.target.value });

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setError('');
    try {
      const response = await axios.post('http://127.0.0.1:8000/api/register', data, {
        headers: { 'Accept': 'application/json' }
      });
      if (response.data.success) {
        setAuthToken(response.data.access_token);
        await refreshUser();
        navigate('/dashboard');
      }
    } catch (err) {
      if (err.response?.data?.errors) {
        setValidationErrors(err.response.data.errors);
        setError(err.response.data.message || 'Validation failed.');
      } else {
        setError(err.response?.data?.message || 'Registration failed.');
      }
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="min-h-screen flex items-center justify-center bg-slate-50 py-12 px-4 sm:px-6 lg:px-8">
      <div className="max-w-md w-full space-y-8 bg-white p-10 rounded-2xl shadow-xl border border-slate-100">
        <div className="text-center">
          <div className="w-16 h-16 bg-indigo-600 text-white rounded-2xl flex items-center justify-center text-3xl font-bold mx-auto mb-4 shadow-lg shadow-indigo-200">C</div>
          <h2 className="text-3xl font-extrabold text-slate-900 tracking-tight">Create an Account</h2>
          <p className="mt-2 text-sm text-slate-500">Join SmartCRM today.</p>
        </div>
        <form className="mt-8 space-y-6" onSubmit={handleSubmit}>
          {error && <div className="bg-red-50 text-red-700 p-3 rounded text-sm text-center border border-red-200">{error}</div>}
          <div className="space-y-4">
            <div>
              <label className="block text-sm font-medium text-slate-700 mb-1">Full Name</label>
              <input name="name" type="text" required value={data.name} onChange={handleChange} className="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="John Doe" />
              {validationErrors.name && <p className="mt-1 text-xs text-red-500">{validationErrors.name[0]}</p>}
            </div>
            <div>
              <label className="block text-sm font-medium text-slate-700 mb-1">Email address</label>
              <input name="email" type="email" required value={data.email} onChange={handleChange} className="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="john@example.com" />
              {validationErrors.email && <p className="mt-1 text-xs text-red-500">{validationErrors.email[0]}</p>}
            </div>
            <div>
              <label className="block text-sm font-medium text-slate-700 mb-1">Password</label>
              <input name="password" type="password" required value={data.password} minLength={8} onChange={handleChange} className="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="••••••••" />
              {validationErrors.password && <p className="mt-1 text-xs text-red-500">{validationErrors.password[0]}</p>}
            </div>
          </div>
          <button type="submit" disabled={loading} className="w-full py-3 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition disabled:opacity-70">
            {loading ? 'Registering...' : 'Register'}
          </button>
        </form>
        <div className="mt-6 text-center">
          <p className="text-sm text-slate-500">Already have an account? <Link to="/" className="text-indigo-600 hover:underline font-medium">Sign in</Link></p>
        </div>
      </div>
    </div>
  );
}

// 3. Lead Capture Form
function CaptureForm() {
  const [formData, setFormData] = useState({ first_name: '', last_name: '', email: '', phone: '', company: '', notes: '' });
  const [status, setStatus] = useState('idle');
  const [errors, setErrors] = useState({});

  const handleChange = (e) => setFormData({ ...formData, [e.target.name]: e.target.value });

  const handleSubmit = async (e) => {
    e.preventDefault();
    setStatus('loading');
    setErrors({});
    try {
      const response = await api.leads.publicCapture(formData);
      if (response.data.success) {
        setStatus('success');
        setFormData({ first_name: '', last_name: '', email: '', phone: '', company: '', notes: '' });
      }
    } catch (error) {
      setStatus('error');
      if (error.response?.data?.errors) setErrors(error.response.data.errors);
    }
  };

  return (
    <div className="min-h-screen flex flex-col justify-center items-center py-12 px-4 sm:px-6 lg:px-8 bg-slate-50">
      <div className="max-w-md w-full space-y-8 bg-white p-10 rounded-2xl shadow-xl border border-slate-100">
        <div className="text-center">
          <div className="w-16 h-16 bg-indigo-600 text-white rounded-2xl flex items-center justify-center text-3xl font-bold mx-auto mb-4 shadow-lg shadow-indigo-200">C</div>
          <h2 className="text-3xl font-extrabold text-slate-900 tracking-tight">Partner with Us</h2>
          <p className="mt-2 text-sm text-slate-500">Tell us about your project.</p>
        </div>
        {status === 'success' ? (
          <div className="bg-green-50 border border-green-200 text-green-800 rounded-xl p-6 text-center animate-pulse">
            <h3 className="text-lg font-bold">Request Received!</h3>
            <p className="text-sm mt-1">Our sales team will contact you shortly.</p>
            <button onClick={() => setStatus('idle')} className="mt-4 text-sm font-semibold text-green-700 underline">Submit another request</button>
          </div>
        ) : (
          <form className="mt-8 space-y-6" onSubmit={handleSubmit}>
            <div className="grid grid-cols-2 gap-4">
              <div>
                <label className="block text-sm font-medium text-slate-700 mb-1">First Name *</label>
                <input name="first_name" type="text" required value={formData.first_name} onChange={handleChange} className="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none" />
              </div>
              <div>
                <label className="block text-sm font-medium text-slate-700 mb-1">Last Name *</label>
                <input name="last_name" type="text" required value={formData.last_name} onChange={handleChange} className="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none" />
              </div>
            </div>
            <div>
              <label className="block text-sm font-medium text-slate-700 mb-1">Email Address *</label>
              <input name="email" type="email" required value={formData.email} onChange={handleChange} className="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none" />
              {errors.email && <p className="mt-1 text-xs text-red-500">{errors.email[0]}</p>}
            </div>
            <div>
              <label className="block text-sm font-medium text-slate-700 mb-1">Phone Number</label>
              <input name="phone" type="text" value={formData.phone} onChange={handleChange} className="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none" />
            </div>
            <div>
              <label className="block text-sm font-medium text-slate-700 mb-1">Company</label>
              <input name="company" type="text" value={formData.company} onChange={handleChange} className="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none" />
            </div>
            <button type="submit" disabled={status === 'loading'} className="w-full py-3 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition disabled:opacity-70">
              {status === 'loading' ? 'Submitting...' : 'Submit Request'}
            </button>
          </form>
        )}
      </div>
    </div>
  );
}

// --- MAIN ROUTER ---
function App() {
  return (
    <AuthProvider>
      <Router>
        <Routes>
          <Route path="/" element={<Login />} />
          <Route path="/register" element={<Register />} />
          <Route path="/partner" element={<CaptureForm />} />
          <Route path="/dashboard" element={<ProtectedRoute><DashboardLayout title="Executive Dashboard"><DashboardHome /></DashboardLayout></ProtectedRoute>} />
          <Route path="/leads" element={<ProtectedRoute><DashboardLayout title="Leads Management"><LeadsList /></DashboardLayout></ProtectedRoute>} />
          <Route path="/leads/:id" element={<ProtectedRoute><DashboardLayout title="Lead Profile"><LeadDetails /></DashboardLayout></ProtectedRoute>} />
          <Route path="/dashboard/leads" element={<ProtectedRoute><DashboardLayout title="Leads Management"><LeadsList /></DashboardLayout></ProtectedRoute>} />
          <Route path="/dashboard/leads/:id" element={<ProtectedRoute><DashboardLayout title="Lead Profile"><LeadDetails /></DashboardLayout></ProtectedRoute>} />
          <Route path="/contacts" element={<ProtectedRoute><DashboardLayout title="Contacts Directory"><ContactsList /></DashboardLayout></ProtectedRoute>} />
          <Route path="/contacts/:id" element={<ProtectedRoute><DashboardLayout title="Contact Profile"><ContactDetails /></DashboardLayout></ProtectedRoute>} />
          <Route path="/dashboard/contacts" element={<ProtectedRoute><DashboardLayout title="Contacts Directory"><ContactsList /></DashboardLayout></ProtectedRoute>} />
          <Route path="/dashboard/contacts/:id" element={<ProtectedRoute><DashboardLayout title="Contact Profile"><ContactDetails /></DashboardLayout></ProtectedRoute>} />
          <Route path="/deals" element={<ProtectedRoute><DashboardLayout title="Deals Pipeline"><DealsList /></DashboardLayout></ProtectedRoute>} />
          <Route path="/deals/:id" element={<ProtectedRoute><DashboardLayout title="Deal Profile"><DealDetails /></DashboardLayout></ProtectedRoute>} />
          <Route path="/dashboard/deals" element={<ProtectedRoute><DashboardLayout title="Deals Pipeline"><DealsList /></DashboardLayout></ProtectedRoute>} />
          <Route path="/dashboard/deals/:id" element={<ProtectedRoute><DashboardLayout title="Deal Profile"><DealDetails /></DashboardLayout></ProtectedRoute>} />
          <Route path="/calendar" element={<ProtectedRoute><DashboardLayout title="Calendar Schedule"><CalendarView /></DashboardLayout></ProtectedRoute>} />
          <Route path="/dashboard/calendar" element={<ProtectedRoute><DashboardLayout title="Calendar Schedule"><CalendarView /></DashboardLayout></ProtectedRoute>} />
          <Route path="/tasks" element={<ProtectedRoute><DashboardLayout title="Tasks Management"><TasksList /></DashboardLayout></ProtectedRoute>} />
          <Route path="/dashboard/tasks" element={<ProtectedRoute><DashboardLayout title="Tasks Management"><TasksList /></DashboardLayout></ProtectedRoute>} />
          <Route path="/reports" element={<ProtectedRoute><DashboardLayout title="Advanced Reports & Analytics"><ReportsPage /></DashboardLayout></ProtectedRoute>} />
          <Route path="/dashboard/reports" element={<ProtectedRoute><DashboardLayout title="Advanced Reports & Analytics"><ReportsPage /></DashboardLayout></ProtectedRoute>} />
          <Route path="/settings" element={<ProtectedRoute><DashboardLayout title="System Settings & Administration"><SettingsPage /></DashboardLayout></ProtectedRoute>} />
          <Route path="/dashboard/settings" element={<ProtectedRoute><DashboardLayout title="System Settings & Administration"><SettingsPage /></DashboardLayout></ProtectedRoute>} />
          {/* Catch-all fallback */}
          <Route path="*" element={<Navigate to="/dashboard" replace />} />
        </Routes>
      </Router>
    </AuthProvider>
  );
}

export default App;
