<!DOCTYPE html>
<html lang="en" class="theme-default dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SmartCRM - Executive Sales & Analytics')</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS CDN + Alpine.js + Chart.js -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // Apply theme immediately before rendering to prevent flicker
        (function() {
            const savedTheme = localStorage.getItem('crm_theme') || 'default';
            document.documentElement.classList.remove('theme-default', 'theme-black', 'theme-white', 'dark', 'light');
            if (savedTheme === 'white') {
                document.documentElement.classList.add('theme-white', 'light');
            } else if (savedTheme === 'black') {
                document.documentElement.classList.add('theme-black', 'dark');
            } else {
                document.documentElement.classList.add('theme-default', 'dark');
            }
        })();

        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'Inter', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            200: '#c7d2fe',
                            400: '#818cf8',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                        },
                        dark: {
                            950: 'var(--bg-page)',
                            900: 'var(--bg-card)',
                            850: 'var(--bg-card-alt)',
                            800: 'var(--border-color)',
                            750: 'var(--border-hover)',
                            700: 'var(--text-muted)',
                        }
                    }
                }
            }
        }
    </script>
    
    <style>
        /* Theme 1: Default (Sophisticated Executive Slate / Navy) */
        :root, html.theme-default {
            --bg-page: #0b0f19;
            --bg-sidebar: #0f172a;
            --bg-card: #111827;
            --bg-card-alt: #162032;
            --bg-subcard: #0b0f19;
            --bg-subcard-hover: rgba(30, 41, 59, 0.6);
            --border-color: #1f293d;
            --border-hover: #334155;
            --text-heading: #f8fafc;
            --text-body: #e2e8f0;
            --text-secondary: #94a3b8;
            --text-muted: #64748b;
            --active-nav-bg: rgba(79, 70, 229, 0.12);
            --active-nav-text: #818cf8;
            --active-nav-border: rgba(99, 102, 241, 0.3);
            --btn-primary-bg: #4f46e5;
            --btn-primary-hover: #4338ca;
            --btn-primary-text: #ffffff;
        }

        /* Theme 2: Black (OLED Minimal Pitch Black) */
        html.theme-black {
            --bg-page: #000000;
            --bg-sidebar: #050505;
            --bg-card: #0a0a0a;
            --bg-card-alt: #121212;
            --bg-subcard: #000000;
            --bg-subcard-hover: rgba(38, 38, 38, 0.7);
            --border-color: #1f1f1f;
            --border-hover: #333333;
            --text-heading: #ffffff;
            --text-body: #e5e5e5;
            --text-secondary: #a3a3a3;
            --text-muted: #737373;
            --active-nav-bg: #171717;
            --active-nav-text: #ffffff;
            --active-nav-border: #333333;
            --btn-primary-bg: #262626;
            --btn-primary-hover: #404040;
            --btn-primary-text: #ffffff;
        }

        /* Theme 3: White (Clean Elegant Modern Light) */
        html.theme-white {
            --bg-page: #f8fafc;
            --bg-sidebar: #ffffff;
            --bg-card: #ffffff;
            --bg-card-alt: #f1f5f9;
            --bg-subcard: #f8fafc;
            --bg-subcard-hover: #f1f5f9;
            --border-color: #e2e8f0;
            --border-hover: #cbd5e1;
            --text-heading: #0f172a;
            --text-body: #1e293b;
            --text-secondary: #475569;
            --text-muted: #64748b;
            --active-nav-bg: #eef2ff;
            --active-nav-text: #4338ca;
            --active-nav-border: #c7d2fe;
            --btn-primary-bg: #0f172a;
            --btn-primary-hover: #1e293b;
            --btn-primary-text: #ffffff;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--bg-page);
            color: var(--text-body);
        }

        /* Dynamic Light Theme Overrides */
        html.theme-white .text-white,
        html.theme-white .text-slate-100 {
            color: #0f172a !important;
        }
        html.theme-white .text-slate-200 {
            color: #1e293b !important;
        }
        html.theme-white .text-slate-300 {
            color: #334155 !important;
        }
        html.theme-white .text-slate-400 {
            color: #64748b !important;
        }
        html.theme-white .text-slate-500 {
            color: #94a3b8 !important;
        }
        html.theme-white .bg-dark-950 {
            background-color: #f8fafc !important;
        }
        html.theme-white .bg-dark-900 {
            background-color: #ffffff !important;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05), 0 1px 2px -1px rgba(0, 0, 0, 0.03) !important;
        }
        html.theme-white .bg-dark-850 {
            background-color: #f8fafc !important;
        }
        html.theme-white .border-slate-800,
        html.theme-white .border-slate-800\/80,
        html.theme-white .border-slate-800\/90,
        html.theme-white .border-slate-800\/60 {
            border-color: #e2e8f0 !important;
        }
        html.theme-white .bg-dark-950\/60,
        html.theme-white .bg-dark-950\/80,
        html.theme-white .bg-dark-950\/90 {
            background-color: #f1f5f9 !important;
        }
        html.theme-white .hover\:bg-slate-800\/40:hover,
        html.theme-white .hover\:bg-slate-800\/50:hover,
        html.theme-white .hover\:bg-slate-800\/80:hover,
        html.theme-white .hover\:bg-slate-800:hover {
            background-color: #f8fafc !important;
        }
        html.theme-white input,
        html.theme-white select,
        html.theme-white textarea {
            background-color: #ffffff !important;
            border-color: #cbd5e1 !important;
            color: #0f172a !important;
        }

        html.theme-white .bg-slate-800,
        html.theme-white .bg-slate-800\/80,
        html.theme-white .bg-slate-800\/70,
        html.theme-white .bg-slate-800\/60,
        html.theme-white .bg-slate-800\/50,
        html.theme-white .bg-slate-700 {
            background-color: #f1f5f9 !important;
            border-color: #e2e8f0 !important;
            color: #334155 !important;
        }

        /* Clean adaptive badges */
        .badge-tag {
            display: inline-flex;
            align-items: center;
            padding: 2px 8px;
            font-size: 11px;
            font-weight: 500;
            border-radius: 6px;
            background-color: rgba(30, 41, 59, 0.6);
            color: #cbd5e1;
            border: 1px solid rgba(51, 65, 85, 0.6);
        }

        .badge-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 24px;
            padding: 2px 6px;
            font-size: 11px;
            font-weight: 600;
            border-radius: 6px;
            background-color: rgba(30, 41, 59, 0.6);
            color: #94a3b8;
            border: 1px solid rgba(51, 65, 85, 0.6);
        }

        html.theme-white .badge-tag {
            background-color: #eef2f6 !important;
            color: #1e293b !important;
            border-color: #cbd5e1 !important;
            font-weight: 600 !important;
        }

        html.theme-white .badge-count {
            background-color: #f1f5f9 !important;
            color: #334155 !important;
            border-color: #cbd5e1 !important;
            font-weight: 600 !important;
        }

        html.theme-black .badge-tag {
            background-color: #171717 !important;
            color: #d4d4d4 !important;
            border-color: #262626 !important;
        }

        html.theme-black .badge-count {
            background-color: #171717 !important;
            color: #a3a3a3 !important;
            border-color: #262626 !important;
        }

        /* Black Theme Clean OLED Styling */
        html.theme-black .bg-dark-950 {
            background-color: #000000 !important;
        }
        html.theme-black .bg-dark-900 {
            background-color: #0a0a0a !important;
        }
        html.theme-black .border-slate-800,
        html.theme-black .border-slate-800\/80,
        html.theme-black .border-slate-800\/90 {
            border-color: #1f1f1f !important;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: var(--bg-page);
        }
        ::-webkit-scrollbar-thumb {
            background: var(--border-color);
            border-radius: 9999px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: var(--border-hover);
        }
    </style>
</head>
<body class="bg-dark-950 text-slate-200 antialiased h-screen overflow-hidden flex flex-col selection:bg-brand-500 selection:text-white" 
      x-data="{ 
          sidebarOpen: false, 
          currentTheme: localStorage.getItem('crm_theme') || 'default',
          setTheme(theme) {
              this.currentTheme = theme;
              localStorage.setItem('crm_theme', theme);
              document.documentElement.classList.remove('theme-default', 'theme-black', 'theme-white', 'dark', 'light');
              if (theme === 'white') {
                  document.documentElement.classList.add('theme-white', 'light');
              } else if (theme === 'black') {
                  document.documentElement.classList.add('theme-black', 'dark');
              } else {
                  document.documentElement.classList.add('theme-default', 'dark');
              }
              window.dispatchEvent(new CustomEvent('theme-changed', { detail: { theme } }));
          }
      }">
    @auth
    <div class="flex h-full overflow-hidden">
        <!-- Mobile Sidebar Overlay -->
        <div x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-40 bg-black/60 backdrop-blur-xs lg:hidden" @click="sidebarOpen = false" style="display: none;"></div>

        <!-- Sidebar Navigation -->
        <aside :class="{'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen}" class="fixed inset-y-0 left-0 z-50 w-64 bg-dark-900 border-r border-slate-800 flex flex-col h-full transform transition-transform duration-200 lg:translate-x-0 lg:static lg:flex-shrink-0">
            <!-- Brand Logo -->
            <div class="p-5 flex items-center justify-between border-b border-slate-800">
                <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 group">
                    <div class="w-9 h-9 rounded-xl bg-indigo-600 text-white font-bold text-sm flex items-center justify-center shadow-sm">
                        CRM
                    </div>
                    <div>
                        <div class="flex items-center space-x-1.5">
                            <span class="text-sm font-bold text-white tracking-tight">SmartCRM</span>
                            <span class="px-1.5 py-0.2 text-[9px] font-semibold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 rounded">PRO</span>
                        </div>
                        <p class="text-[11px] text-slate-400">Enterprise Suite</p>
                    </div>
                </a>
                <button @click="sidebarOpen = false" class="lg:hidden text-slate-400 hover:text-white p-1 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <!-- React Portal Link -->
            <div class="px-4 pt-3 pb-1">
                <a href="http://localhost:5173/dashboard" target="_blank" class="flex items-center justify-between px-3 py-2 rounded-xl bg-dark-950 border border-slate-800 text-xs font-semibold text-slate-300 hover:text-brand-400 hover:border-slate-700 transition group">
                    <div class="flex items-center space-x-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                        <span>React SPA Portal</span>
                    </div>
                    <svg class="w-3.5 h-3.5 text-slate-400 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                </a>
            </div>

            <!-- Nav Links Scrollable Area -->
            <div class="flex-1 overflow-y-auto p-4 space-y-5">
                <!-- CORE CRM -->
                <div>
                    <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-2">Core CRM</p>
                    <nav class="space-y-1">
                        <a href="{{ route('dashboard') }}" class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-medium transition {{ request()->routeIs('dashboard') ? 'bg-indigo-600/15 text-indigo-400 border border-indigo-500/20 font-semibold' : 'text-slate-400 hover:bg-dark-950 hover:text-slate-200' }}">
                            <div class="flex items-center space-x-3">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                                <span>Dashboard</span>
                            </div>
                        </a>

                        <a href="{{ route('leads.index') }}" class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-medium transition {{ request()->routeIs('leads.*') ? 'bg-indigo-600/15 text-indigo-400 border border-indigo-500/20 font-semibold' : 'text-slate-400 hover:bg-dark-950 hover:text-slate-200' }}">
                            <div class="flex items-center space-x-3">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                <span>Leads</span>
                            </div>
                            <span class="px-1.5 py-0.2 text-[10px] font-bold rounded-md bg-dark-950 text-slate-400 border border-slate-800">{{ \App\Models\Lead::count() }}</span>
                        </a>

                        <a href="{{ route('companies.index') }}" class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-medium transition {{ request()->routeIs('companies.*') ? 'bg-indigo-600/15 text-indigo-400 border border-indigo-500/20 font-semibold' : 'text-slate-400 hover:bg-dark-950 hover:text-slate-200' }}">
                            <div class="flex items-center space-x-3">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                <span>Companies</span>
                            </div>
                            <span class="px-1.5 py-0.2 text-[10px] font-bold rounded-md bg-dark-950 text-slate-400 border border-slate-800">{{ \App\Models\Company::count() }}</span>
                        </a>

                        <a href="{{ route('deals.index') }}" class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-medium transition {{ request()->routeIs('deals.*') ? 'bg-indigo-600/15 text-indigo-400 border border-indigo-500/20 font-semibold' : 'text-slate-400 hover:bg-dark-950 hover:text-slate-200' }}">
                            <div class="flex items-center space-x-3">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <span>Deals & Pipeline</span>
                            </div>
                            <span class="px-1.5 py-0.2 text-[10px] font-bold rounded-md bg-dark-950 text-slate-400 border border-slate-800">{{ \App\Models\Deal::count() }}</span>
                        </a>
                    </nav>
                </div>

                <!-- OPERATIONS -->
                <div>
                    <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-2">Operations</p>
                    <nav class="space-y-1">
                        <a href="{{ route('products.index') }}" class="flex items-center space-x-3 px-3 py-2 rounded-xl text-xs font-medium transition {{ request()->routeIs('products.*') ? 'bg-indigo-600/15 text-indigo-400 border border-indigo-500/20 font-semibold' : 'text-slate-400 hover:bg-dark-950 hover:text-slate-200' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                            <span>Products</span>
                        </a>

                        <a href="{{ route('quotations.index') }}" class="flex items-center space-x-3 px-3 py-2 rounded-xl text-xs font-medium transition {{ request()->routeIs('quotations.*') ? 'bg-indigo-600/15 text-indigo-400 border border-indigo-500/20 font-semibold' : 'text-slate-400 hover:bg-dark-950 hover:text-slate-200' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            <span>Quotations</span>
                        </a>

                        <a href="{{ route('invoices.index') }}" class="flex items-center space-x-3 px-3 py-2 rounded-xl text-xs font-medium transition {{ request()->routeIs('invoices.*') ? 'bg-indigo-600/15 text-indigo-400 border border-indigo-500/20 font-semibold' : 'text-slate-400 hover:bg-dark-950 hover:text-slate-200' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                            <span>Invoices</span>
                        </a>

                        <a href="{{ route('payments.index') }}" class="flex items-center space-x-3 px-3 py-2 rounded-xl text-xs font-medium transition {{ request()->routeIs('payments.*') ? 'bg-indigo-600/15 text-indigo-400 border border-indigo-500/20 font-semibold' : 'text-slate-400 hover:bg-dark-950 hover:text-slate-200' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                            <span>Payments</span>
                        </a>
                    </nav>
                </div>

                <!-- ANALYTICS & ADMIN -->
                <div>
                    <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-2">Analytics & System</p>
                    <nav class="space-y-1">
                        <a href="{{ route('reports.index') }}" class="flex items-center space-x-3 px-3 py-2 rounded-xl text-xs font-medium transition {{ request()->routeIs('reports.*') ? 'bg-indigo-600/15 text-indigo-400 border border-indigo-500/20 font-semibold' : 'text-slate-400 hover:bg-dark-950 hover:text-slate-200' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                            <span>Analytics & Reports</span>
                        </a>

                        <a href="{{ route('users.index') }}" class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-medium transition {{ request()->routeIs('users.*') ? 'bg-indigo-600/15 text-indigo-400 border border-indigo-500/20 font-semibold' : 'text-slate-400 hover:bg-dark-950 hover:text-slate-200' }}">
                            <div class="flex items-center space-x-3">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                <span>Users & Roles</span>
                            </div>
                            <span class="px-1.5 py-0.2 text-[10px] font-bold rounded-md bg-dark-950 text-slate-400 border border-slate-800">{{ \App\Models\User::count() }}</span>
                        </a>

                        <a href="{{ route('activity-logs.index') }}" class="flex items-center space-x-3 px-3 py-2 rounded-xl text-xs font-medium transition {{ request()->routeIs('activity-logs.*') ? 'bg-indigo-600/15 text-indigo-400 border border-indigo-500/20 font-semibold' : 'text-slate-400 hover:bg-dark-950 hover:text-slate-200' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span>Activity Logs</span>
                        </a>

                        <a href="{{ route('attachments.index') }}" class="flex items-center space-x-3 px-3 py-2 rounded-xl text-xs font-medium transition {{ request()->routeIs('attachments.*') ? 'bg-indigo-600/15 text-indigo-400 border border-indigo-500/20 font-semibold' : 'text-slate-400 hover:bg-dark-950 hover:text-slate-200' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                            <span>Attachments</span>
                        </a>

                        <a href="{{ route('settings.index') }}" class="flex items-center space-x-3 px-3 py-2 rounded-xl text-xs font-medium transition {{ request()->routeIs('settings.*') ? 'bg-indigo-600/15 text-indigo-400 border border-indigo-500/20 font-semibold' : 'text-slate-400 hover:bg-dark-950 hover:text-slate-200' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path></svg>
                            <span>Settings</span>
                        </a>
                    </nav>
                </div>
            </div>

            <!-- User Profile Bottom Bar -->
            <div class="p-4 border-t border-slate-800 bg-dark-950">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3 min-w-0">
                        <div class="w-8 h-8 rounded-lg bg-slate-800 text-slate-200 font-bold flex items-center justify-center text-xs border border-slate-700">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-semibold text-white truncate">{{ Auth::user()->name }}</p>
                            <span class="text-[10px] text-slate-400">
                                {{ Auth::user()->getRoleNames()->first() ?? 'Administrator' }}
                            </span>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="flex-shrink-0">
                        @csrf
                        <button type="submit" title="Log Out" class="p-1.5 text-slate-400 hover:text-rose-400 rounded-lg hover:bg-rose-500/10 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Workspace Area -->
        <div class="flex-1 flex flex-col h-full bg-dark-950 overflow-hidden min-w-0">
            <!-- Top Navbar -->
            <header class="h-14 border-b border-slate-800 bg-dark-900 flex items-center justify-between px-4 lg:px-8 flex-shrink-0 z-30">
                <div class="flex items-center space-x-4 flex-1">
                    <!-- Mobile Hamburger -->
                    <button @click="sidebarOpen = true" class="lg:hidden p-1.5 text-slate-400 hover:text-white rounded-lg hover:bg-slate-800 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>
                    
                    <!-- Search Input -->
                    <div class="w-full max-w-md relative hidden sm:block">
                        <form action="{{ route('search.index') }}" method="GET">
                            <svg class="w-4 h-4 absolute left-3.5 top-1/2 transform -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            <input type="text" name="q" placeholder="Search leads, deals, companies..." class="w-full bg-dark-950 border border-slate-800 rounded-lg pl-9 pr-10 py-1.5 text-xs text-slate-200 placeholder-slate-400 focus:border-indigo-500 outline-none transition" value="{{ request('q') }}">
                            <span class="absolute right-2.5 top-1/2 transform -translate-y-1/2 text-[10px] font-mono text-slate-400 bg-dark-900 px-1 py-0.2 rounded border border-slate-800">⌘K</span>
                        </form>
                    </div>
                </div>
                
                <!-- Right Controls & Theme Switcher -->
                <div class="flex items-center space-x-3">
                    <!-- Modern Segmented Theme Switcher -->
                    <div class="inline-flex items-center bg-dark-950 p-0.5 rounded-lg border border-slate-800 text-xs">
                        <button type="button" 
                                @click="setTheme('default')" 
                                :class="currentTheme === 'default' ? 'bg-dark-900 text-indigo-400 shadow-sm font-semibold' : 'text-slate-400 hover:text-slate-200'"
                                class="px-2.5 py-1 rounded-md transition text-[11px] flex items-center space-x-1.5">
                            <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                            <span class="hidden md:inline">Slate</span>
                        </button>

                        <button type="button" 
                                @click="setTheme('black')" 
                                :class="currentTheme === 'black' ? 'bg-neutral-900 text-white shadow-sm font-semibold' : 'text-slate-400 hover:text-slate-200'"
                                class="px-2.5 py-1 rounded-md transition text-[11px] flex items-center space-x-1.5">
                            <span class="w-2 h-2 rounded-full bg-neutral-400"></span>
                            <span class="hidden md:inline">Black</span>
                        </button>

                        <button type="button" 
                                @click="setTheme('white')" 
                                :class="currentTheme === 'white' ? 'bg-white text-slate-900 shadow-sm font-semibold border border-slate-200' : 'text-slate-400 hover:text-slate-200'"
                                class="px-2.5 py-1 rounded-md transition text-[11px] flex items-center space-x-1.5">
                            <span class="w-2 h-2 rounded-full bg-slate-300 border border-slate-400"></span>
                            <span class="hidden md:inline">White</span>
                        </button>
                    </div>

                    <!-- DB Live Status -->
                    <div class="hidden md:flex items-center space-x-1.5 px-2.5 py-1 rounded-lg bg-emerald-500/10 text-emerald-400 text-[11px] font-semibold border border-emerald-500/20">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span>Live DB</span>
                    </div>

                    <!-- User Menu -->
                    <div class="w-7 h-7 rounded-lg bg-slate-800 text-slate-200 font-bold flex items-center justify-center text-xs border border-slate-700">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                </div>
            </header>

            <!-- Main Content Area -->
            <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 bg-dark-950">
                @yield('content')
            </main>
        </div>
    </div>
    @else
    <!-- Guest Layout -->
    <main class="w-full h-full flex items-center justify-center p-6 bg-dark-950">
        @yield('content')
    </main>
    @endauth

    <x-ui.toast />
    <x-ui.modal />

    @yield('scripts')
</body>
</html>
