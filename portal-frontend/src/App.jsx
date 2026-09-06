import React, { useState, useEffect } from 'react';
import { BrowserRouter as Router, Routes, Route, Link, Navigate, useNavigate, useLocation } from 'react-router-dom';
import axios from 'axios';
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  Title,
  Tooltip,
  Legend,
  Filler
} from 'chart.js';
import { Line, Bar } from 'react-chartjs-2';

ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  Title,
  Tooltip,
  Legend,
  Filler
);
import './index.css';

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

// --- COMPONENTS ---

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
  const isActive = (path) => location.pathname === path ? 'bg-blue-600 text-white' : 'text-gray-400 hover:text-white hover:bg-gray-800';

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
        {/* <Link to="#" className={`flex items-center px-4 py-2 rounded-lg font-medium transition ${isActive('/dashboard/contacts')}`}>
          <span className="mr-3">📇</span> Contacts
        </Link> */}
        <Link to="/dashboard/calendar" className={`flex items-center px-4 py-2 rounded-lg font-medium transition ${isActive('/dashboard/calendar')}`}>
          <span className="mr-3">📅</span> Calendar
        </Link>
        <Link to="/dashboard/tasks" className={`flex items-center px-4 py-2 rounded-lg font-medium transition ${isActive('/dashboard/tasks')}`}>
          <span className="mr-3">✓</span> Tasks
        </Link>
        <Link to="/dashboard/reports" className={`flex items-center px-4 py-2 rounded-lg font-medium transition ${isActive('/dashboard/reports')}`}>
          <span className="mr-3">📈</span> Reports
        </Link>
        <Link to="#" className={`flex items-center px-4 py-2 rounded-lg font-medium transition ${isActive('/dashboard/settings')}`}>
          <span className="mr-3">⚙️</span> Settings
        </Link>
      </nav>
      <div className="p-4 border-t border-gray-700 flex items-center space-x-3">
        <div className="w-10 h-10 rounded-full bg-gray-600"></div>
        <div className="overflow-hidden">
          <p className="text-sm font-medium truncate">Admin User</p>
          <p className="text-xs text-gray-400 truncate">admin@example.com</p>
        </div>
      </div>
    </aside>
  );
};

// Dashboard Layout Wrapper
const DashboardLayout = ({ children, title }) => {
  const navigate = useNavigate();
  const handleLogout = () => {
    setAuthToken(null);
    navigate('/');
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
              <input type="text" placeholder="Search..." className="bg-gray-50 border border-gray-200 rounded-full pl-9 pr-4 py-1.5 text-sm w-64 focus:outline-none focus:ring-1 focus:ring-blue-500" />
            </div>
            <button className="w-8 h-8 bg-blue-600 text-white rounded-lg flex items-center justify-center font-bold hover:bg-blue-700 transition">+</button>
            <button className="text-gray-500 hover:text-gray-700">
              <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
            </button>
            <div className="w-8 h-8 rounded-full bg-gray-200"></div>
            <button onClick={handleLogout} className="text-sm text-gray-600 hover:text-red-600 font-medium transition">Logout</button>
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
              <input name="login" type="text" required value={credentials.login} onChange={handleChange} className="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="admin@example.com or 1" />
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

// 3. Lead Capture Form (Moved from root)
function CaptureForm() {
  const [formData, setFormData] = useState({ first_name: '', last_name: '', email: '', phone: '', company_name: '', notes: '' });
  const [status, setStatus] = useState('idle');
  const [errors, setErrors] = useState({});

  const handleChange = (e) => setFormData({ ...formData, [e.target.name]: e.target.value });

  const handleSubmit = async (e) => {
    e.preventDefault();
    setStatus('loading');
    setErrors({});
    try {
      const response = await axios.post('http://127.0.0.1:8000/api/leads', formData, {
        headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' }
      });
      if (response.data.success) {
        setStatus('success');
        setFormData({ first_name: '', last_name: '', email: '', phone: '', company_name: '', notes: '' });
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
            <button type="submit" disabled={status === 'loading'} className="w-full py-3 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition disabled:opacity-70">
              {status === 'loading' ? 'Submitting...' : 'Submit Request'}
            </button>
          </form>
        )}
      </div>
    </div>
  );
}

// 4. Dashboard (Protected View - Overview)
function Dashboard() {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  const navigate = useNavigate();

  useEffect(() => {
    const fetchDashboard = async () => {
      try {
        const response = await axios.get('http://127.0.0.1:8000/api/dashboard', {
          headers: { 'Accept': 'application/json' }
        });
        if (response.data.success) {
          setData(response.data.data);
        }
      } catch (error) {
        console.error("Error fetching dashboard", error);
        if (error.response && error.response.status === 401) {
          setAuthToken(null);
          navigate('/');
        }
      } finally {
        setLoading(false);
      }
    };
    fetchDashboard();
  }, [navigate]);

  if (loading || !data) {
    return (
      <DashboardLayout title="Dashboard">
        <div className="flex h-full items-center justify-center">Loading dashboard...</div>
      </DashboardLayout>
    );
  }

  // Chart configs
  const leadsChartData = {
    labels: data.leads_overview.labels,
    datasets: [
      {
        label: 'New Leads',
        data: data.leads_overview.new_leads,
        borderColor: '#3b82f6',
        backgroundColor: '#3b82f6',
        tension: 0.4
      },
      {
        label: 'Converted Leads',
        data: data.leads_overview.converted_leads,
        borderColor: '#22c55e',
        backgroundColor: '#22c55e',
        tension: 0.4
      }
    ]
  };

  const dealsChartData = {
    labels: data.deals_by_stage.map(s => s.name),
    datasets: [{
      label: 'Deals',
      data: data.deals_by_stage.map(s => s.count),
      backgroundColor: ['#3b82f6', '#14b8a6', '#f59e0b', '#8b5cf6', '#ef4444'],
      borderRadius: 4
    }]
  };

  return (
    <DashboardLayout title="Dashboard">
      <div className="p-6">
        <div className="flex justify-between items-end mb-6">
          <div>
            <h2 className="text-2xl font-bold text-gray-900">Welcome back, Admin! 👋</h2>
            <p className="text-gray-500 text-sm mt-1">Here's what's happening with your business today.</p>
          </div>
          <div className="bg-white border border-gray-200 px-4 py-2 rounded-lg text-sm text-gray-600 flex items-center shadow-sm">
            May 12, 2024 - Jun 12, 2024 <span className="ml-2">📅</span>
          </div>
        </div>

        {/* KPI Cards */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
          <div className="bg-white p-5 rounded-xl border border-gray-100 shadow-sm flex flex-col relative">
            <div className="flex justify-between">
              <p className="text-gray-500 text-sm font-medium">Total Leads</p>
              <div className="w-8 h-8 rounded-full bg-blue-50 flex items-center justify-center text-blue-500">👥</div>
            </div>
            <p className="text-2xl font-bold text-gray-900 mt-2">{data.kpis.total_leads.value}</p>
            <p className="text-xs text-green-500 font-medium mt-2">{data.kpis.total_leads.change} <span className="text-gray-400">from last month</span></p>
          </div>
          <div className="bg-white p-5 rounded-xl border border-gray-100 shadow-sm flex flex-col relative">
            <div className="flex justify-between">
              <p className="text-gray-500 text-sm font-medium">Open Deals</p>
              <div className="w-8 h-8 rounded-full bg-green-50 flex items-center justify-center text-green-500">💰</div>
            </div>
            <p className="text-2xl font-bold text-gray-900 mt-2">{data.kpis.open_deals.value}</p>
            <p className="text-xs text-green-500 font-medium mt-2">{data.kpis.open_deals.change} <span className="text-gray-400">from last month</span></p>
          </div>
          <div className="bg-white p-5 rounded-xl border border-gray-100 shadow-sm flex flex-col relative">
            <div className="flex justify-between">
              <p className="text-gray-500 text-sm font-medium">Won Deals</p>
              <div className="w-8 h-8 rounded-full bg-purple-50 flex items-center justify-center text-purple-500">🏆</div>
            </div>
            <p className="text-2xl font-bold text-gray-900 mt-2">{data.kpis.won_deals.value}</p>
            <p className="text-xs text-green-500 font-medium mt-2">{data.kpis.won_deals.change} <span className="text-gray-400">from last month</span></p>
          </div>
          <div className="bg-white p-5 rounded-xl border border-gray-100 shadow-sm flex flex-col relative">
            <div className="flex justify-between">
              <p className="text-gray-500 text-sm font-medium">Revenue</p>
              <div className="w-8 h-8 rounded-full bg-yellow-50 flex items-center justify-center text-yellow-500">🪙</div>
            </div>
            <p className="text-2xl font-bold text-gray-900 mt-2">$ {data.kpis.revenue.value.toLocaleString()}</p>
            <p className="text-xs text-green-500 font-medium mt-2">{data.kpis.revenue.change} <span className="text-gray-400">from last month</span></p>
          </div>
        </div>

        {/* Charts Row */}
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
          <div className="bg-white p-5 rounded-xl border border-gray-100 shadow-sm">
            <div className="flex justify-between items-center mb-4">
              <h3 className="font-bold text-gray-800">Leads Overview</h3>
              <select className="text-sm bg-gray-50 border border-gray-200 rounded px-2 py-1 outline-none"><option>This Month</option></select>
            </div>
            <div className="h-64">
              <Line data={leadsChartData} options={{ maintainAspectRatio: false, plugins: { legend: { position: 'top' } }, scales: { y: { beginAtZero: true } } }} />
            </div>
          </div>
          <div className="bg-white p-5 rounded-xl border border-gray-100 shadow-sm">
            <h3 className="font-bold text-gray-800 mb-4">Deals by Stage</h3>
            <div className="flex items-center">
              <div className="h-64 flex-1">
                <Bar data={dealsChartData} options={{ indexAxis: 'y', maintainAspectRatio: false, plugins: { legend: { display: false } } }} />
              </div>
              <div className="w-1/3 ml-4">
                <ul className="space-y-3">
                  {data.deals_by_stage.map((stg, i) => (
                    <li key={i} className="flex justify-between text-sm">
                      <span className="text-gray-600 flex items-center"><span className="w-2 h-2 rounded-full mr-2" style={{ backgroundColor: ['#3b82f6', '#14b8a6', '#f59e0b', '#8b5cf6', '#ef4444'][i % 5] }}></span> {stg.name}</span>
                      <span className="text-gray-900 font-medium">{stg.count} <span className="text-gray-400 text-xs">({stg.percentage}%)</span></span>
                    </li>
                  ))}
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </DashboardLayout>
  );
}

// 5. Dashboard Leads
function DashboardLeads() {
  const [leads, setLeads] = useState([]);
  const [loading, setLoading] = useState(true);
  const [selectedLeads, setSelectedLeads] = useState([]);

  // Edit Modal State
  const [editingLead, setEditingLead] = useState(null);
  const [editFormData, setEditFormData] = useState({});
  const [editLoading, setEditLoading] = useState(false);

  const navigate = useNavigate();

  const fetchLeads = async () => {
    try {
      const response = await axios.get('http://127.0.0.1:8000/api/leads', {
        headers: { 'Accept': 'application/json' }
      });
      if (response.data.success) {
        setLeads(response.data.data);
      }
    } catch (error) {
      if (error.response && error.response.status === 401) {
        setAuthToken(null);
        navigate('/');
      }
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchLeads();
  }, [navigate]);

  const handleSelectAll = (e) => {
    if (e.target.checked) {
      setSelectedLeads(leads.map(l => l.id));
    } else {
      setSelectedLeads([]);
    }
  };

  const handleSelectLead = (e, id) => {
    if (e.target.checked) {
      setSelectedLeads([...selectedLeads, id]);
    } else {
      setSelectedLeads(selectedLeads.filter(leadId => leadId !== id));
    }
  };

  const handleBulkDelete = async () => {
    if (!window.confirm(`Are you sure you want to delete ${selectedLeads.length} leads?`)) return;
    try {
      await axios.delete('http://127.0.0.1:8000/api/leads/bulk-delete', {
        data: { ids: selectedLeads },
        headers: { 'Accept': 'application/json' }
      });
      setSelectedLeads([]);
      fetchLeads();
    } catch (error) {
      alert('Failed to delete leads.');
    }
  };

  const handleDelete = async (id) => {
    if (!window.confirm('Are you sure you want to delete this lead?')) return;
    try {
      await axios.delete(`http://127.0.0.1:8000/api/leads/${id}`, {
        headers: { 'Accept': 'application/json' }
      });
      fetchLeads();
    } catch (error) {
      alert('Failed to delete lead.');
    }
  };

  const handleEditClick = (lead) => {
    setEditingLead(lead);
    setEditFormData({
      first_name: lead.first_name,
      last_name: lead.last_name,
      email: lead.email,
      phone: lead.phone || '',
      company_name: lead.company_name || '',
      notes: lead.notes || ''
    });
  };

  const handleEditChange = (e) => {
    setEditFormData({ ...editFormData, [e.target.name]: e.target.value });
  };

  const handleEditSubmit = async (e) => {
    e.preventDefault();
    setEditLoading(true);
    try {
      await axios.put(`http://127.0.0.1:8000/api/leads/${editingLead.id}`, editFormData, {
        headers: { 'Accept': 'application/json' }
      });
      setEditingLead(null);
      fetchLeads();
    } catch (error) {
      alert('Failed to update lead.');
    } finally {
      setEditLoading(false);
    }
  };

  return (
    <DashboardLayout title="Leads">
      {editingLead && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50">
          <div className="bg-white p-6 rounded-xl shadow-xl w-full max-w-md">
            <h2 className="text-xl font-bold mb-4 text-slate-800">Edit Lead</h2>
            <form onSubmit={handleEditSubmit} className="space-y-4">
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block text-xs font-medium text-slate-500 mb-1">First Name</label>
                  <input name="first_name" required value={editFormData.first_name} onChange={handleEditChange} className="w-full border rounded px-3 py-2 outline-none focus:border-indigo-500 text-sm" />
                </div>
                <div>
                  <label className="block text-xs font-medium text-slate-500 mb-1">Last Name</label>
                  <input name="last_name" required value={editFormData.last_name} onChange={handleEditChange} className="w-full border rounded px-3 py-2 outline-none focus:border-indigo-500 text-sm" />
                </div>
              </div>
              <div>
                <label className="block text-xs font-medium text-slate-500 mb-1">Email</label>
                <input name="email" type="email" required value={editFormData.email} onChange={handleEditChange} className="w-full border rounded px-3 py-2 outline-none focus:border-indigo-500 text-sm" />
              </div>
              <div>
                <label className="block text-xs font-medium text-slate-500 mb-1">Company</label>
                <input name="company_name" value={editFormData.company_name} onChange={handleEditChange} className="w-full border rounded px-3 py-2 outline-none focus:border-indigo-500 text-sm" />
              </div>
              <div className="flex justify-end space-x-3 mt-6">
                <button type="button" onClick={() => setEditingLead(null)} className="px-4 py-2 text-sm text-slate-600 hover:bg-slate-100 rounded">Cancel</button>
                <button type="submit" disabled={editLoading} className="px-4 py-2 text-sm bg-indigo-600 text-white rounded hover:bg-indigo-700 disabled:opacity-50">Save Changes</button>
              </div>
            </form>
          </div>
        </div>
      )}

      <div className="p-6">
        <div className="mb-6 flex justify-between items-end">
          <div>
            <h2 className="text-2xl font-bold text-slate-900">Lead Management</h2>
            <p className="text-slate-500 mt-1">Manage your incoming leads.</p>
          </div>
          {selectedLeads.length > 0 && (
            <button onClick={handleBulkDelete} className="bg-red-600 text-white px-4 py-2 rounded shadow-sm hover:bg-red-700 font-medium text-sm transition flex items-center">
              Delete Selected ({selectedLeads.length})
            </button>
          )}
        </div>

        {loading ? (
          <div className="flex justify-center py-20"><p className="text-slate-500">Loading leads...</p></div>
        ) : (
          <div className="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            {leads.length === 0 ? (
              <div className="p-12 text-center text-slate-500 flex flex-col items-center">
                <div className="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mb-4 text-slate-400 text-2xl">📋</div>
                <p className="text-lg font-semibold text-slate-700 mb-1">No Leads Found</p>
                <p className="text-sm">There are currently no leads in the database.</p>
              </div>
            ) : (
              <table className="w-full text-left">
                <thead className="bg-slate-50 border-b border-slate-200 text-xs uppercase text-slate-500 font-semibold">
                  <tr>
                    <th className="px-6 py-4 w-12 text-center">
                      <input
                        type="checkbox"
                        checked={selectedLeads.length === leads.length && leads.length > 0}
                        onChange={handleSelectAll}
                        className="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                      />
                    </th>
                    <th className="px-6 py-4">Lead Info</th>
                    <th className="px-6 py-4">Company & Source</th>
                    <th className="px-6 py-4">Status & Score</th>
                    <th className="px-6 py-4">Owner</th>
                    <th className="px-6 py-4">Created</th>
                    <th className="px-6 py-4 text-right">Actions</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-slate-100">
                  {leads.map(lead => (
                    <tr key={lead.id} className="hover:bg-slate-50 transition">
                      <td className="px-6 py-4 text-center">
                        <input
                          type="checkbox"
                          checked={selectedLeads.includes(lead.id)}
                          onChange={(e) => handleSelectLead(e, lead.id)}
                          className="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer"
                        />
                      </td>
                      <td className="px-6 py-4">
                        <p className="font-bold text-slate-900">{lead.first_name} {lead.last_name}</p>
                        <p className="text-sm text-slate-500">{lead.email}</p>
                      </td>
                      <td className="px-6 py-4">
                        <p className="font-medium text-slate-800">{lead.company_name || 'Unknown'}</p>
                        <p className="text-sm text-slate-500">{lead.source?.name || 'Website'}</p>
                      </td>
                      <td className="px-6 py-4">
                        <span className="inline-flex px-2 py-1 rounded text-xs font-semibold bg-indigo-50 text-indigo-700">
                          {lead.status?.name || 'New'}
                        </span>
                        <p className="text-xs text-slate-500 mt-1">Score: {lead.score}</p>
                      </td>
                      <td className="px-6 py-4">
                        <p className="font-medium text-slate-800">{lead.owner?.name || 'Unassigned'}</p>
                      </td>
                      <td className="px-6 py-4 text-sm text-slate-500">
                        {new Date(lead.created_at).toLocaleDateString()}
                      </td>
                      <td className="px-6 py-4 text-right text-sm font-medium">
                        <button onClick={() => handleEditClick(lead)} className="text-indigo-600 hover:text-indigo-900 mr-3 transition">Edit</button>
                        <button onClick={() => handleDelete(lead.id)} className="text-red-600 hover:text-red-900 transition">Delete</button>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            )}
          </div>
        )}
      </div>
    </DashboardLayout>
  );
}

// 6. Dashboard Deals (User Created Real Data)
function DashboardDeals() {
  const [deals, setDeals] = useState([]);
  const [loading, setLoading] = useState(true);
  const [activeTab, setActiveTab] = useState('All Deals');
  const [usersList, setUsersList] = useState([]);
  const [leadsList, setLeadsList] = useState([]);
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [editingDeal, setEditingDeal] = useState(null);
  const [openDropdownId, setOpenDropdownId] = useState(null);
  const [formData, setFormData] = useState({
    name: '',
    value: '',
    deal_stage_id: 'New',
    lead_id: '',
    owner_id: '',
    close_date: new Date(Date.now() + 30 * 86400000).toISOString().split('T')[0]
  });

  const navigate = useNavigate();

  const tabs = ['All Deals', 'New', 'Qualified', 'Proposal', 'Negotiation', 'Won', 'Lost'];

  const fetchDeals = async () => {
    setLoading(true);
    try {
      const response = await axios.get(`http://127.0.0.1:8000/api/deals?stage=${activeTab}`, {
        headers: {
          'Accept': 'application/json',
          'Authorization': `Bearer ${localStorage.getItem('crm_token')}`
        }
      });
      if (response.data.success) {
        setDeals(response.data.data);
      }
    } catch (error) {
      if (error.response && error.response.status === 401) {
        setAuthToken(null);
        navigate('/');
      }
    } finally {
      setLoading(false);
    }
  };

  const fetchUsersAndLeads = async () => {
    try {
      const headers = { Authorization: `Bearer ${localStorage.getItem('crm_token')}` };
      const [usersRes, relatedRes] = await Promise.all([
        axios.get(`http://127.0.0.1:8000/api/users`, { headers }).catch(() => ({ data: { data: [] } })),
        axios.get(`http://127.0.0.1:8000/api/related-options`, { headers }).catch(() => ({ data: { leads: [] } }))
      ]);
      if (usersRes.data && usersRes.data.data) {
        setUsersList(usersRes.data.data);
      }
      if (relatedRes.data && relatedRes.data.leads) {
        setLeadsList(relatedRes.data.leads);
      }
    } catch (err) {
      console.error("Error fetching users or leads", err);
    }
  };

  useEffect(() => {
    fetchDeals();
    fetchUsersAndLeads();
  }, [activeTab, navigate]);

  const handleOpenModal = (deal = null) => {
    if (deal) {
      setEditingDeal(deal);
      setFormData({
        name: deal.name || '',
        value: deal.value || '',
        deal_stage_id: deal.stage?.name || 'New',
        lead_id: deal.lead_id || '',
        owner_id: deal.owner_id || '',
        close_date: deal.close_date ? String(deal.close_date).split('T')[0] : ''
      });
    } else {
      setEditingDeal(null);
      setFormData({
        name: '',
        value: '',
        deal_stage_id: 'New',
        lead_id: '',
        owner_id: '',
        close_date: new Date(Date.now() + 30 * 86400000).toISOString().split('T')[0]
      });
    }
    setIsModalOpen(true);
    setOpenDropdownId(null);
  };

  const handleCloseModal = () => {
    setIsModalOpen(false);
    setEditingDeal(null);
  };

  const handleInputChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({ ...prev, [name]: value }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    try {
      const headers = { Authorization: `Bearer ${localStorage.getItem('crm_token')}` };
      if (editingDeal) {
        await axios.put(`http://127.0.0.1:8000/api/deals/${editingDeal.id}`, formData, { headers });
      } else {
        await axios.post(`http://127.0.0.1:8000/api/deals`, formData, { headers });
      }
      handleCloseModal();
      fetchDeals();
    } catch (error) {
      console.error("Error saving deal", error);
      alert("Failed to save deal. Please verify the input values.");
    }
  };

  const handleDelete = async (id) => {
    if (!window.confirm("Are you sure you want to delete this deal?")) return;
    try {
      const headers = { Authorization: `Bearer ${localStorage.getItem('crm_token')}` };
      await axios.delete(`http://127.0.0.1:8000/api/deals/${id}`, { headers });
      setOpenDropdownId(null);
      fetchDeals();
    } catch (error) {
      console.error("Error deleting deal", error);
      alert("Failed to delete deal.");
    }
  };

  const getStageColor = (stageName) => {
    const lower = stageName ? stageName.toLowerCase() : '';
    if (lower === 'proposal' || lower === 'qualified') return 'bg-purple-100 text-purple-700 border-purple-200';
    if (lower === 'negotiation') return 'bg-amber-100 text-amber-700 border-amber-200';
    if (lower === 'won') return 'bg-emerald-100 text-emerald-700 border-emerald-200';
    if (lower === 'new') return 'bg-blue-100 text-blue-700 border-blue-200';
    if (lower === 'lost') return 'bg-rose-100 text-rose-700 border-rose-200';
    return 'bg-gray-100 text-gray-700 border-gray-200';
  };

  return (
    <DashboardLayout title="Deals">
      <div className="p-6">
        <div className="flex justify-between items-center mb-6">
          <div className="flex space-x-6 border-b border-gray-200 w-full overflow-x-auto">
            {tabs.map(tab => (
              <button
                key={tab}
                onClick={() => setActiveTab(tab)}
                className={`py-2 px-1 text-sm font-medium border-b-2 transition-colors ${activeTab === tab ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'}`}
              >
                {tab}
              </button>
            ))}
          </div>
          <div className="flex items-center space-x-3 ml-4 flex-shrink-0">
            <button
              onClick={() => handleOpenModal()}
              className="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition flex items-center shadow-sm border border-blue-600 cursor-pointer"
            >
              + Add Deal
            </button>
          </div>
        </div>

        {loading ? (
          <div className="flex justify-center py-20"><p className="text-slate-500">Loading deals...</p></div>
        ) : (
          <div className="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            {deals.length === 0 ? (
              <div className="p-16 text-center">
                <div className="w-16 h-16 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl shadow-inner">
                  💼
                </div>
                <h3 className="text-base font-bold text-gray-900 mb-1">No deals yet</h3>
                <p className="text-gray-500 text-xs max-w-sm mx-auto mb-5">
                  Only the deals you create or insert will appear here. Start by creating your first deal.
                </p>
                <button
                  onClick={() => handleOpenModal()}
                  className="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-xs font-semibold shadow transition"
                >
                  + Create Your First Deal
                </button>
              </div>
            ) : (
              <table className="w-full text-left text-sm">
                <thead className="bg-slate-50 border-b border-gray-200 text-gray-500 font-semibold">
                  <tr>
                    <th className="px-6 py-4">Deal Name</th>
                    <th className="px-6 py-4">Company</th>
                    <th className="px-6 py-4">Value</th>
                    <th className="px-6 py-4">Stage</th>
                    <th className="px-6 py-4">Close Date</th>
                    <th className="px-6 py-4">Owner</th>
                    <th className="px-6 py-4">Created At</th>
                    <th className="px-6 py-4 text-center">Action</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-100">
                  {deals.map(deal => (
                    <tr key={deal.id} className="hover:bg-slate-50 transition">
                      <td className="px-6 py-4 font-semibold text-gray-900">{deal.name}</td>
                      <td className="px-6 py-4 text-gray-700 font-medium">
                        {deal.lead?.company || deal.lead?.company_name || 'Direct'}
                      </td>
                      <td className="px-6 py-4 text-gray-900 font-medium">${parseFloat(deal.value).toLocaleString()}</td>
                      <td className="px-6 py-4">
                        <span className={`inline-flex px-2.5 py-1 border rounded text-xs font-semibold ${getStageColor(deal.stage?.name)}`}>
                          {deal.stage?.name || 'New'}
                        </span>
                      </td>
                      <td className="px-6 py-4 text-gray-600">{deal.close_date ? new Date(deal.close_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : '—'}</td>
                      <td className="px-6 py-4">
                        <div className="flex items-center">
                          <div className="w-6 h-6 rounded-full bg-indigo-500 text-white flex items-center justify-center text-xs font-bold mr-2">
                            {deal.owner?.name?.charAt(0) || 'U'}
                          </div>
                          <span className="text-gray-700 font-medium">{deal.owner?.name || 'Unassigned'}</span>
                        </div>
                      </td>
                      <td className="px-6 py-4 text-gray-500">{new Date(deal.created_at).toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' })}</td>
                      <td className="px-6 py-4 text-center relative">
                        <button
                          onClick={() => setOpenDropdownId(openDropdownId === deal.id ? null : deal.id)}
                          className="w-7 h-7 rounded-lg hover:bg-gray-100 text-gray-500 hover:text-gray-800 font-bold transition flex items-center justify-center mx-auto cursor-pointer"
                        >
                          ⋮
                        </button>
                        {openDropdownId === deal.id && (
                          <div className="absolute right-4 mt-1 w-32 bg-white rounded-xl shadow-xl z-50 border border-gray-100 py-1 divide-y divide-gray-100 animate-in fade-in zoom-in duration-100">
                            <button
                              onClick={() => handleOpenModal(deal)}
                              className="w-full text-left px-3 py-2 text-xs text-gray-700 hover:bg-gray-50 flex items-center gap-2 cursor-pointer"
                            >
                              <span>✏️</span> Edit
                            </button>
                            <button
                              onClick={() => handleDelete(deal.id)}
                              className="w-full text-left px-3 py-2 text-xs text-red-600 hover:bg-red-50 flex items-center gap-2 cursor-pointer"
                            >
                              <span>🗑️</span> Delete
                            </button>
                          </div>
                        )}
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            )}
          </div>
        )}
      </div>

      {/* Add / Edit Deal Modal */}
      {isModalOpen && (
        <div className="fixed inset-0 bg-black/50 backdrop-blur-xs flex items-center justify-center z-50 p-4">
          <div className="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 border border-gray-100 animate-in fade-in zoom-in duration-150">
            <div className="flex justify-between items-center pb-3 border-b border-gray-100 mb-5">
              <h2 className="text-lg font-bold text-gray-900">
                {editingDeal ? 'Edit Deal' : '+ Add New Deal'}
              </h2>
              <button onClick={handleCloseModal} className="text-gray-400 hover:text-gray-600 text-xl font-bold cursor-pointer">
                &times;
              </button>
            </div>

            <form onSubmit={handleSubmit} className="space-y-4 text-xs">
              <div>
                <label className="block text-[11px] font-bold text-gray-700 uppercase tracking-wider mb-1">
                  Deal Name *
                </label>
                <input
                  type="text"
                  name="name"
                  value={formData.name}
                  onChange={handleInputChange}
                  placeholder="e.g. Cloud Infrastructure Upgrade"
                  required
                  className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition"
                />
              </div>

              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-[11px] font-bold text-gray-700 uppercase tracking-wider mb-1">
                    Value ($) *
                  </label>
                  <input
                    type="number"
                    step="0.01"
                    name="value"
                    value={formData.value}
                    onChange={handleInputChange}
                    placeholder="e.g. 50000"
                    required
                    className="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition"
                  />
                </div>

                <div>
                  <label className="block text-[11px] font-bold text-gray-700 uppercase tracking-wider mb-1">
                    Stage
                  </label>
                  <select
                    name="deal_stage_id"
                    value={formData.deal_stage_id}
                    onChange={handleInputChange}
                    className="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs bg-white outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition"
                  >
                    <option value="New">New</option>
                    <option value="Qualified">Qualified</option>
                    <option value="Proposal">Proposal</option>
                    <option value="Negotiation">Negotiation</option>
                    <option value="Won">Won</option>
                    <option value="Lost">Lost</option>
                  </select>
                </div>
              </div>

              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-[11px] font-bold text-gray-700 uppercase tracking-wider mb-1">
                    Lead / Company
                  </label>
                  <select
                    name="lead_id"
                    value={formData.lead_id}
                    onChange={handleInputChange}
                    className="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs bg-white outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition"
                  >
                    <option value="">-- Select Lead / Client --</option>
                    {leadsList.map(l => (
                      <option key={l.id} value={l.id}>
                        {l.name} {l.company_name ? `(${l.company_name})` : ''}
                      </option>
                    ))}
                  </select>
                </div>

                <div>
                  <label className="block text-[11px] font-bold text-gray-700 uppercase tracking-wider mb-1">
                    Deal Owner
                  </label>
                  <select
                    name="owner_id"
                    value={formData.owner_id}
                    onChange={handleInputChange}
                    className="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs bg-white outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition"
                  >
                    <option value="">-- Current User --</option>
                    {usersList.map(u => (
                      <option key={u.id} value={u.id}>
                        {u.name}
                      </option>
                    ))}
                  </select>
                </div>
              </div>

              <div>
                <label className="block text-[11px] font-bold text-gray-700 uppercase tracking-wider mb-1">
                  Expected Close Date
                </label>
                <input
                  type="date"
                  name="close_date"
                  value={formData.close_date}
                  onChange={handleInputChange}
                  className="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition"
                />
              </div>

              <div className="flex justify-end space-x-3 pt-3 border-t border-gray-100 mt-5">
                <button
                  type="button"
                  onClick={handleCloseModal}
                  className="px-4 py-2 border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50 transition cursor-pointer font-medium"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-sm transition cursor-pointer"
                >
                  {editingDeal ? 'Save Changes' : 'Create Deal'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </DashboardLayout>
  );
}

// 7. Dashboard Calendar (Real Data Integration)
function DashboardCalendar() {
  const [currentDate, setCurrentDate] = useState(new Date());
  const [viewMode, setViewMode] = useState('week'); // 'month' | 'week' | 'day'
  const [tasks, setTasks] = useState([]);
  const [loading, setLoading] = useState(false);
  const [typeFilters, setTypeFilters] = useState({
    Meeting: true,
    Call: true,
    Task: true,
    Reminder: true,
    Other: true
  });
  const [isAddModalOpen, setIsAddModalOpen] = useState(false);
  const [selectedTask, setSelectedTask] = useState(null);
  const [formData, setFormData] = useState({
    title: '',
    type: 'Meeting',
    priority: 'Medium',
    due_date: new Date().toISOString().split('T')[0],
    status: 'Pending'
  });

  const fetchCalendarTasks = async () => {
    setLoading(true);
    try {
      const response = await axios.get('http://127.0.0.1:8000/api/tasks?status=All', {
        headers: { Authorization: `Bearer ${localStorage.getItem('crm_token')}` }
      });
      if (response.data && response.data.data) {
        setTasks(response.data.data);
      }
    } catch (error) {
      console.error("Error fetching calendar tasks:", error);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchCalendarTasks();
  }, []);

  const handleToggleFilter = (type) => {
    setTypeFilters(prev => ({ ...prev, [type]: !prev[type] }));
  };

  // Date navigation helpers
  const handlePrev = () => {
    const d = new Date(currentDate);
    if (viewMode === 'month') {
      d.setMonth(d.getMonth() - 1);
    } else if (viewMode === 'week') {
      d.setDate(d.getDate() - 7);
    } else {
      d.setDate(d.getDate() - 1);
    }
    setCurrentDate(d);
  };

  const handleNext = () => {
    const d = new Date(currentDate);
    if (viewMode === 'month') {
      d.setMonth(d.getMonth() + 1);
    } else if (viewMode === 'week') {
      d.setDate(d.getDate() + 7);
    } else {
      d.setDate(d.getDate() + 1);
    }
    setCurrentDate(d);
  };

  const handleToday = () => {
    setCurrentDate(new Date());
  };

  // Format date helper: YYYY-MM-DD
  const formatDateKey = (d) => {
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
  };

  // Get week days for week view
  const getWeekDates = (date) => {
    const d = new Date(date);
    const day = d.getDay();
    const diff = d.getDate() - day;
    const startOfWeek = new Date(d.setDate(diff));
    const days = [];
    for (let i = 0; i < 7; i++) {
      const nextDay = new Date(startOfWeek);
      nextDay.setDate(startOfWeek.getDate() + i);
      days.push(nextDay);
    }
    return days;
  };

  // Get month days for month view
  const getMonthDays = (date) => {
    const year = date.getFullYear();
    const month = date.getMonth();
    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    const startingDayOfWeek = firstDay.getDay(); // 0 is Sunday
    const totalDays = lastDay.getDate();

    const days = [];
    // Previous month filler days
    const prevMonthLastDay = new Date(year, month, 0).getDate();
    for (let i = startingDayOfWeek - 1; i >= 0; i--) {
      days.push({
        date: new Date(year, month - 1, prevMonthLastDay - i),
        isCurrentMonth: false
      });
    }

    // Current month days
    for (let i = 1; i <= totalDays; i++) {
      days.push({
        date: new Date(year, month, i),
        isCurrentMonth: true
      });
    }

    // Next month filler days to complete 35 or 42 grid cells
    const remaining = (7 - (days.length % 7)) % 7;
    for (let i = 1; i <= remaining; i++) {
      days.push({
        date: new Date(year, month + 1, i),
        isCurrentMonth: false
      });
    }

    return days;
  };

  const weekDays = getWeekDates(currentDate);
  const monthDays = getMonthDays(currentDate);

  // Filter tasks based on selected categories
  const filteredTasks = tasks.filter(task => {
    const type = task.type || 'Other';
    return typeFilters[type] ?? true;
  });

  const getTasksForDate = (d) => {
    const dateStr = formatDateKey(d);
    return filteredTasks.filter(t => {
      if (!t.due_date) return false;
      const tDate = String(t.due_date).split('T')[0].split(' ')[0];
      return tDate === dateStr;
    });
  };

  const getTypeBadge = (type) => {
    switch (type) {
      case 'Meeting': return 'bg-purple-100 text-purple-700 border-purple-200 hover:bg-purple-200';
      case 'Call': return 'bg-green-100 text-green-700 border-green-200 hover:bg-green-200';
      case 'Task': return 'bg-blue-100 text-blue-700 border-blue-200 hover:bg-blue-200';
      case 'Reminder': return 'bg-orange-100 text-orange-700 border-orange-200 hover:bg-orange-200';
      default: return 'bg-teal-100 text-teal-700 border-teal-200 hover:bg-teal-200';
    }
  };

  const handleOpenAddModal = (defaultDate = null) => {
    setFormData({
      title: '',
      type: 'Meeting',
      priority: 'Medium',
      due_date: defaultDate ? formatDateKey(defaultDate) : formatDateKey(currentDate),
      status: 'Pending'
    });
    setIsAddModalOpen(true);
  };

  const handleSaveEvent = async (e) => {
    e.preventDefault();
    try {
      await axios.post('http://127.0.0.1:8000/api/tasks', formData, {
        headers: { Authorization: `Bearer ${localStorage.getItem('crm_token')}` }
      });
      setIsAddModalOpen(false);
      fetchCalendarTasks();
    } catch (error) {
      console.error("Error creating calendar event:", error);
      alert("Failed to save event. Please check required fields.");
    }
  };

  const handleDeleteTask = async (id) => {
    if (window.confirm("Are you sure you want to delete this event?")) {
      try {
        await axios.delete(`http://127.0.0.1:8000/api/tasks/${id}`, {
          headers: { Authorization: `Bearer ${localStorage.getItem('crm_token')}` }
        });
        setSelectedTask(null);
        fetchCalendarTasks();
      } catch (error) {
        console.error("Error deleting event:", error);
      }
    }
  };

  const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
  const dayNamesShort = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];
  const hours = [8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18];

  const formatHour = (h) => {
    const ampm = h >= 12 ? 'PM' : 'AM';
    const hour = h > 12 ? h - 12 : h === 0 ? 12 : h;
    return `${hour} ${ampm}`;
  };

  const isToday = (d) => {
    const today = new Date();
    return d.getDate() === today.getDate() &&
      d.getMonth() === today.getMonth() &&
      d.getFullYear() === today.getFullYear();
  };

  return (
    <DashboardLayout title="Calendar">
      <div className="flex h-full bg-white relative">
        {/* Left Sidebar */}
        <div className="w-64 border-r border-gray-200 flex-shrink-0 p-5 overflow-y-auto hidden lg:block bg-gray-50/50">
          {/* Mini Interactive Calendar */}
          <div className="mb-6 border border-gray-200 rounded-xl p-3 shadow-sm bg-white">
            <div className="flex justify-between items-center mb-3">
              <button onClick={handlePrev} className="p-1 hover:bg-gray-100 rounded text-gray-500 hover:text-gray-800 text-xs font-bold">&lt;</button>
              <span className="font-semibold text-sm text-gray-800">
                {monthNames[currentDate.getMonth()]} {currentDate.getFullYear()}
              </span>
              <button onClick={handleNext} className="p-1 hover:bg-gray-100 rounded text-gray-500 hover:text-gray-800 text-xs font-bold">&gt;</button>
            </div>
            <div className="grid grid-cols-7 gap-1 text-center text-xs text-gray-400 mb-2 font-medium">
              <div>S</div><div>M</div><div>T</div><div>W</div><div>T</div><div>F</div><div>S</div>
            </div>
            <div className="grid grid-cols-7 gap-1 text-center text-xs font-medium">
              {monthDays.slice(0, 35).map((item, idx) => {
                const isSelected = formatDateKey(item.date) === formatDateKey(currentDate);
                const hasEvents = getTasksForDate(item.date).length > 0;
                return (
                  <button
                    key={idx}
                    onClick={() => setCurrentDate(new Date(item.date))}
                    className={`py-1.5 rounded-full flex flex-col items-center justify-center transition ${
                      isSelected
                        ? 'bg-blue-600 text-white font-bold shadow-sm'
                        : isToday(item.date)
                        ? 'border border-blue-500 text-blue-600 font-bold'
                        : item.isCurrentMonth
                        ? 'text-gray-700 hover:bg-gray-100'
                        : 'text-gray-300 hover:bg-gray-50'
                    }`}
                  >
                    <span>{item.date.getDate()}</span>
                    {hasEvents && !isSelected && (
                      <span className="w-1 h-1 bg-blue-500 rounded-full mt-0.5"></span>
                    )}
                  </button>
                );
              })}
            </div>
          </div>

          {/* Calendar Categories Filter */}
          <div className="mb-6 bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
            <h3 className="font-bold text-gray-800 text-sm mb-3">Filter By Type</h3>
            <div className="space-y-2.5 text-sm text-gray-700 font-medium">
              <label className="flex items-center space-x-3 cursor-pointer">
                <input
                  type="checkbox"
                  checked={typeFilters.Meeting}
                  onChange={() => handleToggleFilter('Meeting')}
                  className="w-4 h-4 rounded text-purple-600 focus:ring-purple-500 border-gray-300"
                />
                <span className="flex items-center gap-2">
                  <span className="w-2.5 h-2.5 rounded-full bg-purple-500"></span> Meetings
                </span>
              </label>
              <label className="flex items-center space-x-3 cursor-pointer">
                <input
                  type="checkbox"
                  checked={typeFilters.Call}
                  onChange={() => handleToggleFilter('Call')}
                  className="w-4 h-4 rounded text-green-600 focus:ring-green-500 border-gray-300"
                />
                <span className="flex items-center gap-2">
                  <span className="w-2.5 h-2.5 rounded-full bg-green-500"></span> Calls
                </span>
              </label>
              <label className="flex items-center space-x-3 cursor-pointer">
                <input
                  type="checkbox"
                  checked={typeFilters.Task}
                  onChange={() => handleToggleFilter('Task')}
                  className="w-4 h-4 rounded text-blue-600 focus:ring-blue-500 border-gray-300"
                />
                <span className="flex items-center gap-2">
                  <span className="w-2.5 h-2.5 rounded-full bg-blue-500"></span> Tasks
                </span>
              </label>
              <label className="flex items-center space-x-3 cursor-pointer">
                <input
                  type="checkbox"
                  checked={typeFilters.Reminder}
                  onChange={() => handleToggleFilter('Reminder')}
                  className="w-4 h-4 rounded text-orange-500 focus:ring-orange-400 border-gray-300"
                />
                <span className="flex items-center gap-2">
                  <span className="w-2.5 h-2.5 rounded-full bg-orange-500"></span> Reminders
                </span>
              </label>
              <label className="flex items-center space-x-3 cursor-pointer">
                <input
                  type="checkbox"
                  checked={typeFilters.Other}
                  onChange={() => handleToggleFilter('Other')}
                  className="w-4 h-4 rounded text-teal-600 focus:ring-teal-500 border-gray-300"
                />
                <span className="flex items-center gap-2">
                  <span className="w-2.5 h-2.5 rounded-full bg-teal-500"></span> Other Events
                </span>
              </label>
            </div>
          </div>

          <div className="bg-blue-50 border border-blue-200 rounded-xl p-3 text-xs text-blue-800">
            <p className="font-semibold mb-1">Live Database Records</p>
            <p>Showing {filteredTasks.length} original task/event items from your database.</p>
          </div>
        </div>

        {/* Main Calendar View Area */}
        <div className="flex-1 flex flex-col overflow-hidden">
          {/* Top Controls Bar */}
          <div className="p-4 border-b border-gray-200 flex flex-wrap justify-between items-center bg-white gap-3">
            <div className="flex items-center space-x-3">
              <div className="flex space-x-1">
                <button
                  onClick={handlePrev}
                  className="px-2.5 py-1 border border-gray-200 rounded hover:bg-gray-50 text-gray-700 font-bold transition shadow-sm text-sm"
                  title="Previous"
                >
                  &lt;
                </button>
                <button
                  onClick={handleNext}
                  className="px-2.5 py-1 border border-gray-200 rounded hover:bg-gray-50 text-gray-700 font-bold transition shadow-sm text-sm"
                  title="Next"
                >
                  &gt;
                </button>
              </div>
              <button
                onClick={handleToday}
                className="px-3 py-1 border border-gray-200 rounded-md text-xs font-semibold text-gray-600 hover:bg-gray-50 transition"
              >
                Today
              </button>
              <h2 className="text-lg font-bold text-gray-800">
                {monthNames[currentDate.getMonth()]} {currentDate.getFullYear()}
              </h2>
              {loading && <span className="text-xs text-gray-400 animate-pulse">Syncing...</span>}
            </div>

            <div className="flex items-center space-x-3">
              <div className="flex border border-gray-200 rounded-lg overflow-hidden text-sm font-medium shadow-sm bg-white">
                <button
                  onClick={() => setViewMode('month')}
                  className={`px-3.5 py-1.5 transition ${viewMode === 'month' ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-gray-600 hover:bg-gray-50'}`}
                >
                  Month
                </button>
                <button
                  onClick={() => setViewMode('week')}
                  className={`px-3.5 py-1.5 border-l border-r border-gray-200 transition ${viewMode === 'week' ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-gray-600 hover:bg-gray-50'}`}
                >
                  Week
                </button>
                <button
                  onClick={() => setViewMode('day')}
                  className={`px-3.5 py-1.5 transition ${viewMode === 'day' ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-gray-600 hover:bg-gray-50'}`}
                >
                  Day
                </button>
              </div>
              <button
                onClick={() => handleOpenAddModal()}
                className="bg-blue-600 text-white px-4 py-1.5 rounded-lg text-sm font-medium hover:bg-blue-700 transition shadow-sm flex items-center gap-1"
              >
                <span>+</span> Add Event
              </button>
            </div>
          </div>

          {/* Dynamic Calendar Views */}
          <div className="flex-1 overflow-auto bg-white flex flex-col">
            {/* 1. MONTH VIEW */}
            {viewMode === 'month' && (
              <div className="flex-1 flex flex-col min-w-[700px]">
                <div className="grid grid-cols-7 border-b border-gray-200 bg-gray-50 sticky top-0 z-10 text-center text-xs font-bold text-gray-500 uppercase tracking-wider py-2.5">
                  {dayNamesShort.map((day, i) => (
                    <div key={i}>{day}</div>
                  ))}
                </div>
                <div className="grid grid-cols-7 flex-1 auto-rows-fr divide-x divide-y divide-gray-200 border-b border-gray-200">
                  {monthDays.map((item, idx) => {
                    const dayTasks = getTasksForDate(item.date);
                    const isCurrentDay = isToday(item.date);
                    return (
                      <div
                        key={idx}
                        onClick={() => handleOpenAddModal(item.date)}
                        className={`min-h-[110px] p-2 transition flex flex-col cursor-pointer ${
                          item.isCurrentMonth ? 'bg-white hover:bg-blue-50/20' : 'bg-gray-50/60 text-gray-400'
                        }`}
                      >
                        <div className="flex justify-between items-center mb-1">
                          <span
                            className={`text-xs font-semibold rounded-full w-6 h-6 flex items-center justify-center ${
                              isCurrentDay ? 'bg-blue-600 text-white' : item.isCurrentMonth ? 'text-gray-800' : 'text-gray-400'
                            }`}
                          >
                            {item.date.getDate()}
                          </span>
                          {dayTasks.length > 0 && (
                            <span className="text-[10px] text-gray-400 font-medium">
                              {dayTasks.length} {dayTasks.length === 1 ? 'item' : 'items'}
                            </span>
                          )}
                        </div>
                        <div className="flex-1 space-y-1 overflow-y-auto max-h-[85px] custom-scrollbar">
                          {dayTasks.map(t => (
                            <div
                              key={t.id}
                              onClick={(e) => {
                                e.stopPropagation();
                                setSelectedTask(t);
                              }}
                              className={`px-2 py-1 rounded text-xs border font-medium truncate cursor-pointer transition shadow-xs ${getTypeBadge(t.type)}`}
                              title={`${t.title} (${t.status})`}
                            >
                              <span className="font-semibold mr-1">[{t.type || 'Task'}]</span>
                              {t.title}
                            </div>
                          ))}
                        </div>
                      </div>
                    );
                  })}
                </div>
              </div>
            )}

            {/* 2. WEEK VIEW */}
            {viewMode === 'week' && (
              <div className="flex-1 flex flex-col min-w-[800px]">
                {/* Header Row */}
                <div className="flex border-b border-gray-200 sticky top-0 z-20 bg-white shadow-xs">
                  <div className="w-16 flex-shrink-0 border-r border-gray-200 py-3 text-center text-xs font-bold text-gray-400">
                    TIME
                  </div>
                  {weekDays.map((d, i) => {
                    const isCurrentDay = isToday(d);
                    return (
                      <div
                        key={i}
                        onClick={() => setCurrentDate(new Date(d))}
                        className={`flex-1 py-3 text-center border-r border-gray-200 last:border-0 font-medium text-sm cursor-pointer transition hover:bg-gray-50 ${
                          isCurrentDay ? 'bg-blue-50/40' : ''
                        }`}
                      >
                        <span className={`text-xs block font-bold uppercase tracking-wider ${isCurrentDay ? 'text-blue-600' : 'text-gray-500'}`}>
                          {dayNamesShort[d.getDay()]}
                        </span>
                        <span
                          className={`inline-block mt-0.5 text-base font-bold px-2 py-0.5 rounded-full ${
                            isCurrentDay ? 'bg-blue-600 text-white shadow-xs' : 'text-gray-800'
                          }`}
                        >
                          {d.getDate()}
                        </span>
                      </div>
                    );
                  })}
                </div>

                {/* Week Day Columns */}
                <div className="flex flex-1 relative min-h-[700px]">
                  {/* Time labels column */}
                  <div className="w-16 flex-shrink-0 border-r border-gray-200 bg-white z-10">
                    {hours.map(hour => (
                      <div key={hour} className="h-20 border-b border-gray-100 flex items-start justify-end pr-2 pt-1 text-xs text-gray-400 font-medium">
                        {formatHour(hour)}
                      </div>
                    ))}
                  </div>

                  {/* 7 Day Grid */}
                  <div className="flex-1 flex relative">
                    {weekDays.map((d, dayIdx) => {
                      const dayTasks = getTasksForDate(d);
                      const isCurrentDay = isToday(d);
                      return (
                        <div
                          key={dayIdx}
                          onClick={() => handleOpenAddModal(d)}
                          className={`flex-1 border-r border-gray-200 relative group transition ${
                            isCurrentDay ? 'bg-blue-50/10' : 'hover:bg-gray-50/30'
                          }`}
                        >
                          {hours.map(hour => (
                            <div key={hour} className="h-20 border-b border-gray-100"></div>
                          ))}

                          {/* Placed Real Tasks for this day */}
                          <div className="absolute inset-0 p-1.5 space-y-1.5 overflow-y-auto">
                            {dayTasks.map(t => (
                              <div
                                key={t.id}
                                onClick={(e) => {
                                  e.stopPropagation();
                                  setSelectedTask(t);
                                }}
                                className={`rounded-lg border p-2 text-xs shadow-xs cursor-pointer transition ${getTypeBadge(t.type)}`}
                              >
                                <div className="flex items-center justify-between font-bold">
                                  <span>{t.type || 'Task'}</span>
                                  <span className="text-[10px] uppercase px-1.5 py-0.5 rounded bg-white/70 border border-gray-200">
                                    {t.priority || 'Medium'}
                                  </span>
                                </div>
                                <p className="font-semibold text-gray-900 mt-1 leading-snug">{t.title}</p>
                                <div className="mt-1 flex items-center justify-between text-[11px] opacity-90">
                                  <span>Status: {t.status}</span>
                                </div>
                              </div>
                            ))}
                          </div>
                        </div>
                      );
                    })}
                  </div>
                </div>
              </div>
            )}

            {/* 3. DAY VIEW */}
            {viewMode === 'day' && (
              <div className="flex-1 flex flex-col p-6 max-w-4xl mx-auto w-full">
                <div className="flex items-center justify-between mb-6 pb-4 border-b border-gray-200">
                  <div>
                    <h3 className="text-xl font-bold text-gray-900">
                      {dayNamesShort[currentDate.getDay()]}, {monthNames[currentDate.getMonth()]} {currentDate.getDate()}, {currentDate.getFullYear()}
                    </h3>
                    <p className="text-xs text-gray-500 mt-1">
                      {getTasksForDate(currentDate).length} events/tasks scheduled for this day
                    </p>
                  </div>
                  <button
                    onClick={() => handleOpenAddModal(currentDate)}
                    className="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition shadow-sm"
                  >
                    + Add Task for Today
                  </button>
                </div>

                <div className="space-y-3">
                  {getTasksForDate(currentDate).length === 0 ? (
                    <div className="text-center py-16 bg-gray-50 rounded-xl border border-dashed border-gray-300">
                      <p className="text-gray-500 text-sm">No tasks or events scheduled for this day.</p>
                      <button
                        onClick={() => handleOpenAddModal(currentDate)}
                        className="mt-3 text-blue-600 font-semibold text-sm hover:underline"
                      >
                        + Create a task now
                      </button>
                    </div>
                  ) : (
                    getTasksForDate(currentDate).map(t => (
                      <div
                        key={t.id}
                        onClick={() => setSelectedTask(t)}
                        className={`p-4 rounded-xl border shadow-xs cursor-pointer transition flex items-center justify-between ${getTypeBadge(t.type)}`}
                      >
                        <div>
                          <div className="flex items-center space-x-2">
                            <span className="text-xs font-bold uppercase tracking-wider bg-white/80 px-2 py-0.5 rounded border border-gray-200">
                              {t.type || 'Task'}
                            </span>
                            <span className={`text-xs px-2 py-0.5 rounded font-semibold ${
                              t.priority === 'High' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700'
                            }`}>
                              {t.priority || 'Medium'} Priority
                            </span>
                            <span className="text-xs text-gray-600 font-medium">Status: {t.status}</span>
                          </div>
                          <h4 className="text-base font-bold text-gray-900 mt-1.5">{t.title}</h4>
                        </div>
                        <div className="text-right">
                          <button
                            onClick={(e) => {
                              e.stopPropagation();
                              handleDeleteTask(t.id);
                            }}
                            className="text-red-500 hover:text-red-700 text-xs font-semibold px-2 py-1 rounded hover:bg-red-50 transition"
                          >
                            Delete
                          </button>
                        </div>
                      </div>
                    ))
                  )}
                </div>
              </div>
            )}
          </div>
        </div>

        {/* Task Details Modal */}
        {selectedTask && (
          <div className="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 backdrop-blur-xs">
            <div className="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-gray-100 animate-in fade-in zoom-in duration-150">
              <div className="flex justify-between items-start mb-4">
                <div>
                  <span className="text-xs font-bold uppercase tracking-wider bg-blue-50 text-blue-700 px-2 py-0.5 rounded border border-blue-200">
                    {selectedTask.type || 'Task'}
                  </span>
                  <h3 className="text-lg font-bold text-gray-900 mt-2">{selectedTask.title}</h3>
                </div>
                <button onClick={() => setSelectedTask(null)} className="text-gray-400 hover:text-gray-600 text-xl font-bold">
                  &times;
                </button>
              </div>

              <div className="space-y-3 py-2 text-sm text-gray-600 border-y border-gray-100 my-4">
                <div className="flex justify-between">
                  <span className="font-medium text-gray-400">Due Date:</span>
                  <span className="font-semibold text-gray-800">{selectedTask.due_date || 'No Date'}</span>
                </div>
                <div className="flex justify-between">
                  <span className="font-medium text-gray-400">Priority:</span>
                  <span className="font-semibold text-gray-800">{selectedTask.priority || 'Medium'}</span>
                </div>
                <div className="flex justify-between">
                  <span className="font-medium text-gray-400">Status:</span>
                  <span className="font-semibold text-gray-800">{selectedTask.status || 'Pending'}</span>
                </div>
                {selectedTask.assignee && (
                  <div className="flex justify-between">
                    <span className="font-medium text-gray-400">Assigned To:</span>
                    <span className="font-semibold text-gray-800">{selectedTask.assignee.name}</span>
                  </div>
                )}
              </div>

              <div className="flex justify-between items-center pt-2">
                <button
                  onClick={() => handleDeleteTask(selectedTask.id)}
                  className="text-red-600 hover:text-red-800 text-sm font-semibold hover:bg-red-50 px-3 py-2 rounded-lg transition"
                >
                  Delete Task
                </button>
                <button
                  onClick={() => setSelectedTask(null)}
                  className="bg-gray-100 hover:bg-gray-200 text-gray-800 px-4 py-2 rounded-lg text-sm font-medium transition"
                >
                  Close
                </button>
              </div>
            </div>
          </div>
        )}

        {/* Add Event Modal */}
        {isAddModalOpen && (
          <div className="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 backdrop-blur-xs">
            <div className="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-gray-100 animate-in fade-in zoom-in duration-150">
              <div className="flex justify-between items-center mb-5 pb-3 border-b border-gray-100">
                <h3 className="text-lg font-bold text-gray-900">+ Add New Event / Task</h3>
                <button onClick={() => setIsAddModalOpen(false)} className="text-gray-400 hover:text-gray-600 text-xl font-bold">
                  &times;
                </button>
              </div>

              <form onSubmit={handleSaveEvent} className="space-y-4">
                <div>
                  <label className="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                    Event / Task Title *
                  </label>
                  <input
                    type="text"
                    required
                    value={formData.title}
                    onChange={(e) => setFormData({ ...formData, title: e.target.value })}
                    placeholder="e.g. Client Call or Follow-up meeting"
                    className="w-full px-3.5 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none"
                  />
                </div>

                <div className="grid grid-cols-2 gap-3">
                  <div>
                    <label className="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                      Type
                    </label>
                    <select
                      value={formData.type}
                      onChange={(e) => setFormData({ ...formData, type: e.target.value })}
                      className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none bg-white"
                    >
                      <option value="Meeting">Meeting</option>
                      <option value="Call">Call</option>
                      <option value="Task">Task</option>
                      <option value="Reminder">Reminder</option>
                      <option value="Other">Other</option>
                    </select>
                  </div>
                  <div>
                    <label className="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                      Priority
                    </label>
                    <select
                      value={formData.priority}
                      onChange={(e) => setFormData({ ...formData, priority: e.target.value })}
                      className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none bg-white"
                    >
                      <option value="High">High</option>
                      <option value="Medium">Medium</option>
                      <option value="Low">Low</option>
                    </select>
                  </div>
                </div>

                <div className="grid grid-cols-2 gap-3">
                  <div>
                    <label className="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                      Due Date *
                    </label>
                    <input
                      type="date"
                      required
                      value={formData.due_date}
                      onChange={(e) => setFormData({ ...formData, due_date: e.target.value })}
                      className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none bg-white"
                    />
                  </div>
                  <div>
                    <label className="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                      Status
                    </label>
                    <select
                      value={formData.status}
                      onChange={(e) => setFormData({ ...formData, status: e.target.value })}
                      className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none bg-white"
                    >
                      <option value="Pending">Pending</option>
                      <option value="In Progress">In Progress</option>
                      <option value="Completed">Completed</option>
                    </select>
                  </div>
                </div>

                <div className="flex justify-end space-x-3 pt-3">
                  <button
                    type="button"
                    onClick={() => setIsAddModalOpen(false)}
                    className="px-4 py-2 border border-gray-200 text-gray-700 rounded-lg text-sm hover:bg-gray-50 transition"
                  >
                    Cancel
                  </button>
                  <button
                    type="submit"
                    className="px-5 py-2 bg-blue-600 text-white font-medium rounded-lg text-sm hover:bg-blue-700 shadow-sm transition"
                  >
                    Save Event
                  </button>
                </div>
              </form>
            </div>
          </div>
        )}
      </div>
    </DashboardLayout>
  );
}

// 8. Dashboard Tasks (Proper Formatting & User Assignment)
function DashboardTasks() {
  const [activeTab, setActiveTab] = useState('All');
  const [tasks, setTasks] = useState([]);
  const [usersList, setUsersList] = useState([]);
  const [relatedOptions, setRelatedOptions] = useState({ leads: [], deals: [], companies: [] });
  const [searchQuery, setSearchQuery] = useState('');
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [editingTask, setEditingTask] = useState(null);
  const [formData, setFormData] = useState({
    title: '',
    type: 'Call',
    priority: 'Medium',
    due_date: '',
    status: 'Pending',
    assign_to_id: '',
    related_to_type: '',
    related_to_id: ''
  });
  const [openDropdownId, setOpenDropdownId] = useState(null);

  const tabs = ['All', 'Pending', 'In Progress', 'Completed', 'Overdue'];

  const fetchTasks = async () => {
    try {
      const response = await axios.get(`http://127.0.0.1:8000/api/tasks?status=${activeTab}`, {
        headers: { Authorization: `Bearer ${localStorage.getItem('crm_token')}` }
      });
      setTasks(response.data.data || []);
    } catch (error) {
      console.error("Error fetching tasks", error);
    }
  };

  const fetchUsers = async () => {
    try {
      const response = await axios.get(`http://127.0.0.1:8000/api/users`, {
        headers: { Authorization: `Bearer ${localStorage.getItem('crm_token')}` }
      });
      if (response.data && response.data.data) {
        setUsersList(response.data.data);
      }
    } catch (error) {
      console.error("Error fetching users for assignment", error);
    }
  };

  const fetchRelatedOptions = async () => {
    try {
      const response = await axios.get(`http://127.0.0.1:8000/api/related-options`, {
        headers: { Authorization: `Bearer ${localStorage.getItem('crm_token')}` }
      });
      if (response.data) {
        setRelatedOptions({
          leads: response.data.leads || [],
          deals: response.data.deals || [],
          companies: response.data.companies || []
        });
      }
    } catch (error) {
      console.error("Error fetching related options", error);
    }
  };

  useEffect(() => {
    fetchTasks();
    fetchUsers();
    fetchRelatedOptions();
  }, [activeTab]);

  const handleInputChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({ ...prev, [name]: value }));
  };

  const normalizeModelType = (type) => {
    if (!type) return '';
    if (type.toLowerCase().includes('company')) return 'Company';
    if (type.toLowerCase().includes('lead')) return 'Lead';
    if (type.toLowerCase().includes('deal')) return 'Deal';
    return type;
  };

  const handleOpenModal = (task = null) => {
    if (task) {
      setEditingTask(task);
      setFormData({
        title: task.title || '',
        type: task.type || 'Call',
        priority: task.priority || 'Medium',
        due_date: task.due_date ? String(task.due_date).split('T')[0] : '',
        status: task.status || 'Pending',
        assign_to_id: task.assign_to_id || '',
        related_to_type: normalizeModelType(task.related_to_type),
        related_to_id: task.related_to_id || ''
      });
    } else {
      setEditingTask(null);
      setFormData({
        title: '',
        type: 'Call',
        priority: 'Medium',
        due_date: new Date().toISOString().split('T')[0],
        status: 'Pending',
        assign_to_id: '',
        related_to_type: '',
        related_to_id: ''
      });
    }
    setIsModalOpen(true);
    setOpenDropdownId(null);
  };

  const handleCloseModal = () => {
    setIsModalOpen(false);
    setEditingTask(null);
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    try {
      const headers = { Authorization: `Bearer ${localStorage.getItem('crm_token')}` };
      if (editingTask) {
        await axios.put(`http://127.0.0.1:8000/api/tasks/${editingTask.id}`, formData, { headers });
      } else {
        await axios.post(`http://127.0.0.1:8000/api/tasks`, formData, { headers });
      }
      handleCloseModal();
      fetchTasks();
    } catch (error) {
      console.error("Error saving task", error);
    }
  };

  const handleDelete = async (id) => {
    if (window.confirm("Are you sure you want to delete this task?")) {
      try {
        await axios.delete(`http://127.0.0.1:8000/api/tasks/${id}`, {
          headers: { Authorization: `Bearer ${localStorage.getItem('crm_token')}` }
        });
        fetchTasks();
      } catch (error) {
        console.error("Error deleting task", error);
      }
    }
    setOpenDropdownId(null);
  };

  const handleToggleStatus = async (task) => {
    try {
      const nextStatus = task.status === 'Completed' ? 'Pending' : 'Completed';
      await axios.put(`http://127.0.0.1:8000/api/tasks/${task.id}`, { status: nextStatus }, {
        headers: { Authorization: `Bearer ${localStorage.getItem('crm_token')}` }
      });
      fetchTasks();
    } catch (error) {
      console.error("Error toggling task status", error);
    }
  };

  const toggleDropdown = (id, e) => {
    e.stopPropagation();
    setOpenDropdownId(openDropdownId === id ? null : id);
  };

  // Close dropdown on outside click
  useEffect(() => {
    const handleOutsideClick = () => setOpenDropdownId(null);
    window.addEventListener('click', handleOutsideClick);
    return () => window.removeEventListener('click', handleOutsideClick);
  }, []);

  // Format Due Date with proper readability and badges
  const formatDueDate = (dateStr, status) => {
    if (!dateStr) return <span className="text-gray-400 font-normal text-xs">-</span>;
    try {
      const dateParts = String(dateStr).split('T')[0].split('-');
      if (dateParts.length !== 3) return <span className="text-gray-700">{dateStr}</span>;
      
      const d = new Date(dateParts[0], parseInt(dateParts[1], 10) - 1, dateParts[2]);
      const months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
      const formattedDate = `${months[d.getMonth()]} ${String(d.getDate()).padStart(2, '0')}, ${d.getFullYear()}`;

      const today = new Date();
      today.setHours(0, 0, 0, 0);
      const isPast = d < today;
      const isToday = d.getTime() === today.getTime();

      if (status !== 'Completed' && isPast) {
        return (
          <div className="flex items-center space-x-1.5">
            <span className="font-semibold text-red-600">{formattedDate}</span>
            <span className="px-1.5 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-700">Overdue</span>
          </div>
        );
      }

      if (isToday) {
        return (
          <div className="flex items-center space-x-1.5">
            <span className="font-semibold text-blue-600">{formattedDate}</span>
            <span className="px-1.5 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-700">Today</span>
          </div>
        );
      }

      return <span className="font-medium text-gray-700">{formattedDate}</span>;
    } catch (e) {
      return <span className="text-gray-700">{dateStr}</span>;
    }
  };

  const getTypeBadge = (type) => {
    switch (type) {
      case 'Call':
        return (
          <span className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
            <span>📞</span> Call
          </span>
        );
      case 'Email':
        return (
          <span className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-purple-50 text-purple-700 border border-purple-200">
            <span>✉️</span> Email
          </span>
        );
      case 'Meeting':
        return (
          <span className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
            <span>👥</span> Meeting
          </span>
        );
      case 'Review':
        return (
          <span className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
            <span>📝</span> Review
          </span>
        );
      case 'Reminder':
        return (
          <span className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
            <span>⏰</span> Reminder
          </span>
        );
      default:
        return (
          <span className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
            <span>📋</span> {type || 'Task'}
          </span>
        );
    }
  };

  const getPriorityBadge = (priority) => {
    switch (priority) {
      case 'High':
        return (
          <span className="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-50 text-red-700 border border-red-200">
            <span className="w-1.5 h-1.5 rounded-full bg-red-500"></span> High
          </span>
        );
      case 'Medium':
        return (
          <span className="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
            <span className="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Medium
          </span>
        );
      case 'Low':
        return (
          <span className="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
            <span className="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Low
          </span>
        );
      default:
        return (
          <span className="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-50 text-gray-600 border border-gray-200">
            {priority || 'Medium'}
          </span>
        );
    }
  };

  const getStatusBadge = (status) => {
    switch (status) {
      case 'Pending':
        return (
          <span className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
            <span className="w-2 h-2 rounded-full bg-amber-400"></span> Pending
          </span>
        );
      case 'In Progress':
        return (
          <span className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
            <span className="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span> In Progress
          </span>
        );
      case 'Completed':
        return (
          <span className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
            <span className="text-emerald-600 font-bold">✓</span> Completed
          </span>
        );
      case 'Overdue':
        return (
          <span className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-red-50 text-red-700 border border-red-200">
            <span className="w-2 h-2 rounded-full bg-red-500"></span> Overdue
          </span>
        );
      default:
        return (
          <span className="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-gray-100 text-gray-700 border border-gray-200">
            {status}
          </span>
        );
    }
  };

  // Filter tasks by search
  const filteredTasks = tasks.filter(t => {
    if (!searchQuery) return true;
    const q = searchQuery.toLowerCase();
    return (
      (t.title && t.title.toLowerCase().includes(q)) ||
      (t.type && t.type.toLowerCase().includes(q)) ||
      (t.assignee && t.assignee.name.toLowerCase().includes(q))
    );
  });

  return (
    <DashboardLayout title="Tasks">
      <div className="p-6 max-w-7xl mx-auto w-full">
        {/* Top Header & Search Bar */}
        <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
          <div>
            <h1 className="text-2xl font-bold text-gray-900 tracking-tight">Tasks Management</h1>
            <p className="text-gray-500 text-xs mt-0.5">Track, schedule, and complete your CRM tasks and follow-ups.</p>
          </div>
          <div className="flex items-center space-x-3">
            <button
              onClick={() => handleOpenModal()}
              className="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-blue-700 transition flex items-center gap-1.5 shadow-sm"
            >
              <span className="text-base font-bold">+</span>
              <span>Add Task</span>
            </button>
          </div>
        </div>

        {/* Tabs & Search Controls */}
        <div className="flex flex-col md:flex-row md:items-center justify-between border-b border-gray-200 pb-3 mb-6 gap-4">
          <div className="flex space-x-2 overflow-x-auto">
            {tabs.map(tab => (
              <button
                key={tab}
                onClick={() => setActiveTab(tab)}
                className={`py-2 px-3 text-xs font-semibold rounded-lg transition whitespace-nowrap ${
                  activeTab === tab
                    ? 'bg-blue-600 text-white shadow-xs'
                    : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'
                }`}
              >
                {tab}
              </button>
            ))}
          </div>

          <div className="w-full md:w-72 relative">
            <input
              type="text"
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              placeholder="Search tasks..."
              className="w-full pl-8 pr-3 py-1.5 text-xs bg-gray-50 border border-gray-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none transition"
            />
            <span className="absolute left-2.5 top-2 text-gray-400 text-xs">🔍</span>
          </div>
        </div>

        {/* Properly Formatted Tasks Table */}
        <div className="bg-white rounded-xl shadow-xs border border-gray-200 overflow-visible">
          <div className="overflow-x-auto">
            <table className="w-full text-left text-xs border-collapse">
              <thead>
                <tr className="bg-gray-50/80 border-b border-gray-200 text-gray-500 uppercase tracking-wider font-semibold">
                  <th className="px-5 py-3.5">Task Title</th>
                  <th className="px-5 py-3.5">Related To</th>
                  <th className="px-5 py-3.5">Type</th>
                  <th className="px-5 py-3.5">Priority</th>
                  <th className="px-5 py-3.5">Due Date</th>
                  <th className="px-5 py-3.5">Assignee</th>
                  <th className="px-5 py-3.5">Status</th>
                  <th className="px-5 py-3.5 text-center">Action</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-100">
                {filteredTasks.length === 0 ? (
                  <tr>
                    <td colSpan="8" className="px-6 py-16 text-center text-gray-400">
                      <div className="flex flex-col items-center justify-center">
                        <span className="text-3xl mb-2">📋</span>
                        <p className="text-sm font-medium text-gray-600">No tasks found</p>
                        <p className="text-xs text-gray-400 mt-1">Click '+ Add Task' to create a new task.</p>
                      </div>
                    </td>
                  </tr>
                ) : (
                  filteredTasks.map(task => (
                    <tr key={task.id} className="hover:bg-slate-50/80 transition group">
                      {/* Task Title with Complete Checkbox */}
                      <td className="px-5 py-4">
                        <div className="flex items-center space-x-3">
                          <input
                            type="checkbox"
                            checked={task.status === 'Completed'}
                            onChange={() => handleToggleStatus(task)}
                            className="w-4 h-4 rounded text-blue-600 focus:ring-blue-500 border-gray-300 cursor-pointer"
                            title="Toggle complete"
                          />
                          <div>
                            <span
                              className={`font-semibold text-sm transition ${
                                task.status === 'Completed'
                                  ? 'line-through text-gray-400'
                                  : 'text-gray-900 group-hover:text-blue-600'
                              }`}
                            >
                              {task.title}
                            </span>
                            <div className="text-[11px] text-gray-400 mt-0.5">ID: #{task.id}</div>
                          </div>
                        </div>
                      </td>

                      {/* Related To */}
                      <td className="px-5 py-4">
                        {task.related_to ? (
                          <div className="flex items-center space-x-1.5">
                            <span className="px-2 py-0.5 rounded bg-blue-50 text-blue-700 border border-blue-200 font-bold text-[10px] uppercase">
                              {task.related_to_type ? task.related_to_type.split('\\').pop() : 'Record'}
                            </span>
                            <span className="font-semibold text-gray-800 truncate max-w-[140px]">
                              {task.related_to.name || task.related_to.title || `#${task.related_to_id}`}
                            </span>
                          </div>
                        ) : (
                          <span className="text-gray-400 font-normal italic text-[11px]">Unlinked</span>
                        )}
                      </td>

                      {/* Type Badge */}
                      <td className="px-5 py-4">{getTypeBadge(task.type)}</td>

                      {/* Priority Badge */}
                      <td className="px-5 py-4">{getPriorityBadge(task.priority)}</td>

                      {/* Formatted Due Date */}
                      <td className="px-5 py-4">{formatDueDate(task.due_date, task.status)}</td>

                      {/* Assignee Card */}
                      <td className="px-5 py-4">
                        {task.assignee ? (
                          <div className="flex items-center space-x-2">
                            <div className="w-7 h-7 rounded-full bg-indigo-600 text-white flex items-center justify-center text-xs font-bold shadow-xs">
                              {task.assignee.name ? task.assignee.name.charAt(0).toUpperCase() : 'U'}
                            </div>
                            <div className="overflow-hidden">
                              <p className="font-semibold text-gray-800 text-xs truncate max-w-[110px]">
                                {task.assignee.name}
                              </p>
                            </div>
                          </div>
                        ) : (
                          <span className="px-2 py-0.5 rounded bg-gray-100 text-gray-500 text-[11px] font-medium border border-gray-200">
                            Unassigned
                          </span>
                        )}
                      </td>

                      {/* Status */}
                      <td className="px-5 py-4">{getStatusBadge(task.status)}</td>

                      {/* Actions */}
                      <td className="px-5 py-4 text-center relative">
                        <button
                          onClick={(e) => toggleDropdown(task.id, e)}
                          className="w-7 h-7 rounded-lg hover:bg-gray-100 text-gray-500 hover:text-gray-800 font-bold transition flex items-center justify-center mx-auto"
                        >
                          ⋮
                        </button>
                        {openDropdownId === task.id && (
                          <div
                            onClick={(e) => e.stopPropagation()}
                            className="absolute right-4 mt-1 w-36 bg-white rounded-xl shadow-xl z-50 border border-gray-100 py-1 divide-y divide-gray-100 animate-in fade-in zoom-in duration-100"
                          >
                            <button
                              onClick={() => handleOpenModal(task)}
                              className="w-full text-left px-3 py-2 text-xs text-gray-700 hover:bg-gray-50 flex items-center gap-2"
                            >
                              <span>✏️</span> Edit Task
                            </button>
                            <button
                              onClick={() => handleToggleStatus(task)}
                              className="w-full text-left px-3 py-2 text-xs text-gray-700 hover:bg-gray-50 flex items-center gap-2"
                            >
                              <span>{task.status === 'Completed' ? '🔄 Mark Pending' : '✔️ Complete'}</span>
                            </button>
                            <button
                              onClick={() => handleDelete(task.id)}
                              className="w-full text-left px-3 py-2 text-xs text-red-600 hover:bg-red-50 flex items-center gap-2"
                            >
                              <span>🗑️</span> Delete
                            </button>
                          </div>
                        )}
                      </td>
                    </tr>
                  ))
                )}
              </tbody>
            </table>
          </div>
        </div>
      </div>

      {/* Add / Edit Task Modal */}
      {isModalOpen && (
        <div className="fixed inset-0 bg-black/50 backdrop-blur-xs flex items-center justify-center z-50 p-4">
          <div className="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 border border-gray-100 animate-in fade-in zoom-in duration-150">
            <div className="flex justify-between items-center pb-3 border-b border-gray-100 mb-5">
              <h2 className="text-lg font-bold text-gray-900">
                {editingTask ? 'Edit Task' : '+ Add New Task'}
              </h2>
              <button onClick={handleCloseModal} className="text-gray-400 hover:text-gray-600 text-xl font-bold">
                &times;
              </button>
            </div>

            <form onSubmit={handleSubmit} className="space-y-4 text-xs">
              <div>
                <label className="block text-[11px] font-bold text-gray-700 uppercase tracking-wider mb-1">
                  Task Title *
                </label>
                <input
                  type="text"
                  name="title"
                  value={formData.title}
                  onChange={handleInputChange}
                  placeholder="e.g. Call Client for Contract Review"
                  required
                  className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition"
                />
              </div>

              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-[11px] font-bold text-gray-700 uppercase tracking-wider mb-1">
                    Related Model
                  </label>
                  <select
                    name="related_to_type"
                    value={formData.related_to_type}
                    onChange={(e) => setFormData({ ...formData, related_to_type: e.target.value, related_to_id: '' })}
                    className="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs bg-white outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition"
                  >
                    <option value="">-- None (Unlinked) --</option>
                    <option value="Lead">Lead</option>
                    <option value="Deal">Deal</option>
                    <option value="Company">Company</option>
                  </select>
                </div>

                <div>
                  <label className="block text-[11px] font-bold text-gray-700 uppercase tracking-wider mb-1">
                    Select Record
                  </label>
                  <select
                    name="related_to_id"
                    disabled={!formData.related_to_type}
                    value={formData.related_to_id}
                    onChange={handleInputChange}
                    className="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs bg-white outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition disabled:bg-gray-100 disabled:text-gray-400"
                  >
                    <option value="">-- Select Record --</option>
                    {formData.related_to_type?.toLowerCase().includes('lead') && relatedOptions.leads.map(l => (
                      <option key={l.id} value={l.id}>
                        {l.name} {l.company_name ? `(${l.company_name})` : ''}
                      </option>
                    ))}
                    {formData.related_to_type?.toLowerCase().includes('deal') && relatedOptions.deals.map(d => (
                      <option key={d.id} value={d.id}>
                        {d.name || d.title} {d.value ? `($${parseFloat(d.value).toLocaleString()})` : ''}
                      </option>
                    ))}
                    {formData.related_to_type?.toLowerCase().includes('company') && relatedOptions.companies.map(c => (
                      <option key={c.id} value={c.id}>
                        {c.name}
                      </option>
                    ))}
                  </select>
                </div>
              </div>

              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-[11px] font-bold text-gray-700 uppercase tracking-wider mb-1">
                    Type
                  </label>
                  <select
                    name="type"
                    value={formData.type}
                    onChange={handleInputChange}
                    className="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs bg-white outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition"
                  >
                    <option value="Call">📞 Call</option>
                    <option value="Email">✉️ Email</option>
                    <option value="Meeting">👥 Meeting</option>
                    <option value="Review">📝 Review</option>
                    <option value="Reminder">⏰ Reminder</option>
                    <option value="Task">📋 Task</option>
                  </select>
                </div>
                <div>
                  <label className="block text-[11px] font-bold text-gray-700 uppercase tracking-wider mb-1">
                    Priority
                  </label>
                  <select
                    name="priority"
                    value={formData.priority}
                    onChange={handleInputChange}
                    className="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs bg-white outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition"
                  >
                    <option value="High">🔴 High</option>
                    <option value="Medium">🟡 Medium</option>
                    <option value="Low">🟢 Low</option>
                  </select>
                </div>
              </div>

              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-[11px] font-bold text-gray-700 uppercase tracking-wider mb-1">
                    Due Date
                  </label>
                  <input
                    type="date"
                    name="due_date"
                    value={formData.due_date}
                    onChange={handleInputChange}
                    className="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs bg-white outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition"
                  />
                </div>
                <div>
                  <label className="block text-[11px] font-bold text-gray-700 uppercase tracking-wider mb-1">
                    Status
                  </label>
                  <select
                    name="status"
                    value={formData.status}
                    onChange={handleInputChange}
                    className="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs bg-white outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition"
                  >
                    <option value="Pending">Pending</option>
                    <option value="In Progress">In Progress</option>
                    <option value="Completed">Completed</option>
                    <option value="Overdue">Overdue</option>
                  </select>
                </div>
              </div>

              <div>
                <label className="block text-[11px] font-bold text-gray-700 uppercase tracking-wider mb-1">
                  Assign To User
                </label>
                <select
                  name="assign_to_id"
                  value={formData.assign_to_id}
                  onChange={handleInputChange}
                  className="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs bg-white outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition"
                >
                  <option value="">-- Unassigned --</option>
                  {usersList.map(u => (
                    <option key={u.id} value={u.id}>
                      {u.name} ({u.email})
                    </option>
                  ))}
                </select>
              </div>

              <div className="flex justify-end space-x-3 pt-3 border-t border-gray-100">
                <button
                  type="button"
                  onClick={handleCloseModal}
                  className="px-4 py-2 border border-gray-200 rounded-lg text-gray-700 hover:bg-gray-50 transition font-medium"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className="px-5 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold transition shadow-sm"
                >
                  {editingTask ? 'Save Changes' : 'Create Task'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </DashboardLayout>
  );
}

// 8. Dashboard Reports & Analytics
function DashboardReports() {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [activeTab, setActiveTab] = useState('Overview');
  const navigate = useNavigate();

  const tabs = ['Overview', 'Sales & Pipeline', 'Lead Acquisition', 'Task Productivity', 'Team Leaderboard'];

  const fetchReports = async () => {
    setLoading(true);
    try {
      const response = await axios.get(`http://127.0.0.1:8000/api/reports`, {
        headers: {
          'Accept': 'application/json',
          'Authorization': `Bearer ${localStorage.getItem('crm_token')}`
        }
      });
      if (response.data.success) {
        setData(response.data.data);
      }
    } catch (error) {
      if (error.response && error.response.status === 401) {
        setAuthToken(null);
        navigate('/');
      }
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchReports();
  }, [navigate]);

  const exportReportCSV = () => {
    if (!data) return;
    const rows = [
      ['Metric', 'Value'],
      ['Total Deals', data.sales.total_deals],
      ['Won Deals', data.sales.won_deals],
      ['Lost Deals', data.sales.lost_deals],
      ['Won Revenue ($)', data.sales.won_revenue],
      ['Pipeline Value ($)', data.sales.pipeline_value],
      ['Win Rate (%)', data.sales.win_rate + '%'],
      ['Total Leads', data.leads.total_leads],
      ['Lead Conversion Rate (%)', data.leads.conversion_rate + '%'],
      ['Total Tasks', data.tasks.total_tasks],
      ['Completed Tasks', data.tasks.completed_tasks],
      ['Task Completion Rate (%)', data.tasks.completion_rate + '%'],
    ];

    const csvContent = "data:text/csv;charset=utf-8," + rows.map(e => e.join(",")).join("\n");
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", `crm_analytics_report_${new Date().toISOString().split('T')[0]}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  };

  return (
    <DashboardLayout title="Reports & Analytics">
      <div className="p-6 space-y-6 overflow-y-auto">
        {/* Top Header & Export Action */}
        <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-gray-200 pb-4">
          <div className="flex space-x-6 overflow-x-auto w-full sm:w-auto">
            {tabs.map(tab => (
              <button
                key={tab}
                onClick={() => setActiveTab(tab)}
                className={`py-2 px-1 text-sm font-medium border-b-2 transition-colors whitespace-nowrap ${
                  activeTab === tab 
                    ? 'border-blue-600 text-blue-600' 
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                }`}
              >
                {tab}
              </button>
            ))}
          </div>
          <div className="flex items-center space-x-3">
            <button
              onClick={exportReportCSV}
              className="bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg text-xs font-semibold hover:bg-gray-50 transition flex items-center shadow-xs cursor-pointer"
            >
              📥 Export CSV
            </button>
            <button
              onClick={fetchReports}
              className="bg-blue-600 text-white px-4 py-2 rounded-lg text-xs font-semibold hover:bg-blue-700 transition flex items-center shadow-xs cursor-pointer"
            >
              🔄 Refresh
            </button>
          </div>
        </div>

        {loading ? (
          <div className="flex justify-center py-24"><p className="text-slate-500">Generating analytics reports...</p></div>
        ) : !data ? (
          <div className="p-12 text-center text-gray-500">Failed to load reports data.</div>
        ) : (
          <>
            {/* Top KPI Cards */}
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
              {/* Won Revenue */}
              <div className="bg-white p-5 rounded-2xl shadow-xs border border-gray-100 flex items-center space-x-4">
                <div className="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl font-bold">
                  💰
                </div>
                <div>
                  <p className="text-xs font-medium text-gray-500">Won Revenue</p>
                  <p className="text-2xl font-bold text-gray-900">${data.sales.won_revenue.toLocaleString()}</p>
                  <p className="text-[11px] text-emerald-600 font-semibold mt-0.5">{data.sales.won_deals} closed won deals</p>
                </div>
              </div>

              {/* Active Pipeline */}
              <div className="bg-white p-5 rounded-2xl shadow-xs border border-gray-100 flex items-center space-x-4">
                <div className="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl font-bold">
                  💼
                </div>
                <div>
                  <p className="text-xs font-medium text-gray-500">Active Pipeline</p>
                  <p className="text-2xl font-bold text-gray-900">${data.sales.pipeline_value.toLocaleString()}</p>
                  <p className="text-[11px] text-blue-600 font-semibold mt-0.5">{data.sales.open_deals} open deals</p>
                </div>
              </div>

              {/* Win Rate */}
              <div className="bg-white p-5 rounded-2xl shadow-xs border border-gray-100 flex items-center space-x-4">
                <div className="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl font-bold">
                  🎯
                </div>
                <div>
                  <p className="text-xs font-medium text-gray-500">Deal Win Rate</p>
                  <p className="text-2xl font-bold text-gray-900">{data.sales.win_rate}%</p>
                  <p className="text-[11px] text-purple-600 font-semibold mt-0.5">Avg Deal: ${Number(data.sales.avg_deal_size).toLocaleString()}</p>
                </div>
              </div>

              {/* Task Productivity */}
              <div className="bg-white p-5 rounded-2xl shadow-xs border border-gray-100 flex items-center space-x-4">
                <div className="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl font-bold">
                  ✓
                </div>
                <div>
                  <p className="text-xs font-medium text-gray-500">Task Completion</p>
                  <p className="text-2xl font-bold text-gray-900">{data.tasks.completion_rate}%</p>
                  <p className="text-[11px] text-indigo-600 font-semibold mt-0.5">{data.tasks.completed_tasks} of {data.tasks.total_tasks} completed</p>
                </div>
              </div>
            </div>

            {/* (Tab 1 & Default) Overview & Main Breakdown */}
            {(activeTab === 'Overview' || activeTab === 'Sales & Pipeline') && (
              <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {/* Deals Pipeline by Stage */}
                <div className="bg-white p-6 rounded-2xl shadow-xs border border-gray-100">
                  <div className="flex justify-between items-center mb-5">
                    <h3 className="font-bold text-gray-900 text-sm">Deals Pipeline by Stage</h3>
                    <span className="text-xs text-gray-400 font-medium">{data.sales.total_deals} Total Deals</span>
                  </div>
                  <div className="space-y-4">
                    {data.sales.by_stage.map(stage => (
                      <div key={stage.name}>
                        <div className="flex justify-between text-xs font-semibold mb-1">
                          <span className="text-gray-700">{stage.name}</span>
                          <span className="text-gray-900">{stage.count} deals ({stage.percentage}%)</span>
                        </div>
                        <div className="w-full bg-gray-100 rounded-full h-2.5 overflow-hidden">
                          <div
                            className={`h-2.5 rounded-full transition-all duration-500 ${
                              stage.name === 'Won' ? 'bg-emerald-500' :
                              stage.name === 'Lost' ? 'bg-rose-500' :
                              stage.name === 'Negotiation' ? 'bg-amber-500' :
                              stage.name === 'Proposal' ? 'bg-purple-500' : 'bg-blue-500'
                            }`}
                            style={{ width: `${Math.max(stage.percentage, 4)}%` }}
                          ></div>
                        </div>
                      </div>
                    ))}
                  </div>
                </div>

                {/* 6-Month Revenue & Activity Trend */}
                <div className="bg-white p-6 rounded-2xl shadow-xs border border-gray-100">
                  <div className="flex justify-between items-center mb-5">
                    <h3 className="font-bold text-gray-900 text-sm">Monthly Revenue Trend</h3>
                    <span className="text-xs text-gray-400 font-medium">Last 6 Months</span>
                  </div>
                  <div className="space-y-3">
                    {data.trends.labels.map((month, idx) => (
                      <div key={month} className="flex items-center justify-between text-xs py-2 border-b border-gray-50 last:border-0">
                        <span className="font-medium text-gray-700 w-24">{month}</span>
                        <div className="flex-1 mx-4">
                          <div className="w-full bg-gray-100 rounded-full h-2">
                            <div
                              className="bg-blue-600 h-2 rounded-full"
                              style={{ width: `${data.sales.won_revenue > 0 ? (data.trends.revenue[idx] / data.sales.won_revenue) * 100 : 0}%` }}
                            ></div>
                          </div>
                        </div>
                        <span className="font-bold text-gray-900 w-24 text-right">
                          ${data.trends.revenue[idx].toLocaleString()}
                        </span>
                      </div>
                    ))}
                  </div>
                </div>
              </div>
            )}

            {/* (Tab 2) Lead Acquisition Breakdown */}
            {(activeTab === 'Overview' || activeTab === 'Lead Acquisition') && (
              <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {/* Leads by Source */}
                <div className="bg-white p-6 rounded-2xl shadow-xs border border-gray-100">
                  <div className="flex justify-between items-center mb-5">
                    <h3 className="font-bold text-gray-900 text-sm">Lead Sources Distribution</h3>
                    <span className="text-xs text-gray-400 font-medium">{data.leads.total_leads} Total Leads</span>
                  </div>
                  <div className="space-y-4">
                    {data.leads.by_source.map(src => (
                      <div key={src.name}>
                        <div className="flex justify-between text-xs font-semibold mb-1">
                          <span className="text-gray-700">{src.name}</span>
                          <span className="text-gray-900">{src.count} ({src.percentage}%)</span>
                        </div>
                        <div className="w-full bg-gray-100 rounded-full h-2.5 overflow-hidden">
                          <div
                            className="bg-indigo-500 h-2.5 rounded-full transition-all duration-500"
                            style={{ width: `${Math.max(src.percentage, 3)}%` }}
                          ></div>
                        </div>
                      </div>
                    ))}
                  </div>
                </div>

                {/* Leads by Status */}
                <div className="bg-white p-6 rounded-2xl shadow-xs border border-gray-100">
                  <div className="flex justify-between items-center mb-5">
                    <h3 className="font-bold text-gray-900 text-sm">Leads by Status</h3>
                    <span className="text-xs text-indigo-600 font-bold">Conversion Rate: {data.leads.conversion_rate}%</span>
                  </div>
                  <div className="space-y-4">
                    {data.leads.by_status.map(status => (
                      <div key={status.name}>
                        <div className="flex justify-between text-xs font-semibold mb-1">
                          <span className="text-gray-700">{status.name}</span>
                          <span className="text-gray-900">{status.count} ({status.percentage}%)</span>
                        </div>
                        <div className="w-full bg-gray-100 rounded-full h-2.5 overflow-hidden">
                          <div
                            className="bg-teal-500 h-2.5 rounded-full transition-all duration-500"
                            style={{ width: `${Math.max(status.percentage, 3)}%` }}
                          ></div>
                        </div>
                      </div>
                    ))}
                  </div>
                </div>
              </div>
            )}

            {/* (Tab 3 & Tab 4) Team Leaderboard & Productivity */}
            {(activeTab === 'Overview' || activeTab === 'Team Leaderboard' || activeTab === 'Task Productivity') && (
              <div className="bg-white rounded-2xl shadow-xs border border-gray-100 overflow-hidden">
                <div className="p-6 border-b border-gray-100 flex justify-between items-center">
                  <div>
                    <h3 className="font-bold text-gray-900 text-sm">Sales & Team Performance Leaderboard</h3>
                    <p className="text-xs text-gray-400 mt-0.5">Individual deal closures, revenue contribution, and task delivery</p>
                  </div>
                  <span className="px-3 py-1 rounded-full bg-blue-50 text-blue-700 font-bold text-xs">
                    {data.team.length} Active Members
                  </span>
                </div>
                <table className="w-full text-left text-xs">
                  <thead className="bg-slate-50 border-b border-gray-100 text-gray-500 font-semibold">
                    <tr>
                      <th className="px-6 py-3">Team Member</th>
                      <th className="px-6 py-3">Assigned Deals</th>
                      <th className="px-6 py-3">Won Revenue</th>
                      <th className="px-6 py-3">Completed Tasks</th>
                      <th className="px-6 py-3 text-right">Performance Status</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-gray-100">
                    {data.team.map(member => (
                      <tr key={member.id} className="hover:bg-slate-50/80 transition">
                        <td className="px-6 py-4 flex items-center space-x-3">
                          <div className="w-8 h-8 rounded-full bg-indigo-600 text-white flex items-center justify-center font-bold text-xs">
                            {member.name.charAt(0)}
                          </div>
                          <div>
                            <p className="font-bold text-gray-900">{member.name}</p>
                            <p className="text-[11px] text-gray-400">{member.email}</p>
                          </div>
                        </td>
                        <td className="px-6 py-4 font-semibold text-gray-800">{member.deals_count} Deals</td>
                        <td className="px-6 py-4 font-bold text-emerald-600">${member.won_revenue.toLocaleString()}</td>
                        <td className="px-6 py-4 font-medium text-gray-700">{member.completed_tasks} Tasks</td>
                        <td className="px-6 py-4 text-right">
                          <span className="inline-flex px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            Active
                          </span>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            )}
          </>
        )}
      </div>
    </DashboardLayout>
  );
}

// --- MAIN ROUTER ---
function App() {
  return (
    <Router>
      <Routes>
        <Route path="/" element={<Login />} />
        <Route path="/register" element={<Register />} />
        <Route path="/partner" element={<CaptureForm />} />
        <Route path="/dashboard" element={<ProtectedRoute><Dashboard /></ProtectedRoute>} />
        <Route path="/dashboard/leads" element={<ProtectedRoute><DashboardLeads /></ProtectedRoute>} />
        <Route path="/dashboard/deals" element={<ProtectedRoute><DashboardDeals /></ProtectedRoute>} />
        <Route path="/dashboard/calendar" element={<ProtectedRoute><DashboardCalendar /></ProtectedRoute>} />
        <Route path="/dashboard/tasks" element={<ProtectedRoute><DashboardTasks /></ProtectedRoute>} />
        <Route path="/dashboard/reports" element={<ProtectedRoute><DashboardReports /></ProtectedRoute>} />
      </Routes>
    </Router>
  );
}

export default App;
