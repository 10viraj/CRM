<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SmartCRM')</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-950 text-slate-300 antialiased h-screen overflow-hidden" x-data="{ sidebarOpen: false }">
    @auth
        <div class="flex h-full">
        <!-- Mobile Sidebar Overlay -->
        <div x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-40 bg-slate-900/50 lg:hidden" @click="sidebarOpen = false" style="display: none;"></div>

        <!-- Sidebar -->
        <aside :class="{'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen}" class="fixed inset-y-0 left-0 z-50 w-64 bg-slate-900 border-r border-slate-800 flex flex-col h-full transform transition-transform duration-300 lg:translate-x-0 lg:static lg:flex-shrink-0">
            <div class="p-5 flex items-center space-x-3 border-b border-slate-800">
                <div class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center text-white font-bold">C</div>
                <div>
                    <h1 class="text-sm font-bold text-slate-100 leading-tight">CRM Admin</h1>
                    <p class="text-xs text-slate-500">System Dashboard</p>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto p-4 space-y-6">
                <!-- Overview Section -->
                <div x-data="{ open: true }">
                    <button type="button" @click="open = !open" class="w-full flex justify-between items-center px-3 mb-2 outline-none group cursor-pointer">
                        <p class="text-xs font-semibold text-indigo-600 uppercase tracking-wider">Overview</p>
                        <svg :class="{'rotate-180': open}" class="w-4 h-4 text-indigo-400 group-hover:text-indigo-600 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <ul x-show="open" x-transition.opacity.duration.200ms class="space-y-1">
                        <li>
                            <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg {{ request()->routeIs('dashboard') ? 'bg-indigo-600/20 text-indigo-400 font-medium' : 'text-slate-400 hover:bg-slate-800 transition' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                                <span>Dashboard</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('users.index') }}" class="flex items-center justify-between px-3 py-2 rounded-lg {{ request()->routeIs('users.*') ? 'bg-indigo-600/20 text-indigo-400 font-medium' : 'text-slate-400 hover:bg-slate-800 transition' }}">
                                <div class="flex items-center space-x-3">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                    <span>Total Users</span>
                                </div>
                                <span class="px-2 py-0.5 text-[11px] font-bold rounded-full bg-indigo-500/20 text-indigo-400 border border-indigo-500/30">
                                    {{ \App\Models\User::count() }}
                                </span>
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- CRM Models Section -->
                <div x-data="{ open: true }">
                    <button type="button" @click="open = !open" class="w-full flex justify-between items-center px-3 mb-2 outline-none group cursor-pointer">
                        <p class="text-xs font-semibold text-indigo-600 uppercase tracking-wider">CRM Models</p>
                        <svg :class="{'rotate-180': open}" class="w-4 h-4 text-indigo-400 group-hover:text-indigo-600 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <ul x-show="open" x-transition.opacity.duration.200ms class="space-y-1">
                        <li><a href="{{ route('companies.index') }}" class="block px-3 py-2 rounded-lg {{ request()->routeIs('companies.*') ? 'bg-indigo-100 text-indigo-700 font-medium' : 'text-slate-700 hover:bg-slate-100 transition' }}">Company</a></li>
                        <li><a href="{{ route('module.placeholder', 'contact') }}" class="block px-3 py-2 rounded-lg text-slate-700 hover:bg-slate-100 transition">Contact</a></li>
                        <li><a href="{{ route('module.placeholder', 'customer') }}" class="block px-3 py-2 rounded-lg text-slate-700 hover:bg-slate-100 transition">Customer</a></li>
                    </ul>
                </div>

                <!-- Lead Management -->
                <div x-data="{ open: true }">
                    <button type="button" @click="open = !open" class="w-full flex justify-between items-center px-3 mb-2 outline-none group cursor-pointer">
                        <p class="text-xs font-semibold text-indigo-600 uppercase tracking-wider">Lead Management</p>
                        <svg :class="{'rotate-180': open}" class="w-4 h-4 text-indigo-400 group-hover:text-indigo-600 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <ul x-show="open" x-transition.opacity.duration.200ms class="space-y-1">
                        <li><a href="{{ route('module.placeholder', 'lead-source') }}" class="block px-3 py-2 rounded-lg text-slate-700 hover:bg-slate-100 transition">Lead Source</a></li>
                        <li><a href="{{ route('module.placeholder', 'lead-status') }}" class="block px-3 py-2 rounded-lg text-slate-700 hover:bg-slate-100 transition">Lead Status</a></li>
                        <li><a href="{{ route('leads.index') }}" class="block px-3 py-2 rounded-lg {{ request()->routeIs('leads.*') ? 'bg-indigo-100 text-indigo-700 font-medium' : 'text-slate-700 hover:bg-slate-100 transition' }}">Lead</a></li>
                        <li><a href="{{ route('module.placeholder', 'lead-activity') }}" class="block px-3 py-2 rounded-lg text-slate-700 hover:bg-slate-100 transition">Lead Activity</a></li>
                    </ul>
                </div>

                <!-- Follow-Up -->
                <div x-data="{ open: true }">
                    <button type="button" @click="open = !open" class="w-full flex justify-between items-center px-3 mb-2 outline-none group cursor-pointer">
                        <p class="text-xs font-semibold text-indigo-600 uppercase tracking-wider">Follow-Up</p>
                        <svg :class="{'rotate-180': open}" class="w-4 h-4 text-indigo-400 group-hover:text-indigo-600 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <ul x-show="open" x-transition.opacity.duration.200ms class="space-y-1">
                        <li><a href="{{ route('module.placeholder', 'follow-up') }}" class="block px-3 py-2 rounded-lg text-slate-700 hover:bg-slate-100 transition">Follow Up</a></li>
                    </ul>
                </div>

                <!-- Deal Management -->
                <div x-data="{ open: true }">
                    <button type="button" @click="open = !open" class="w-full flex justify-between items-center px-3 mb-2 outline-none group cursor-pointer">
                        <p class="text-xs font-semibold text-indigo-600 uppercase tracking-wider">Deal Management</p>
                        <svg :class="{'rotate-180': open}" class="w-4 h-4 text-indigo-400 group-hover:text-indigo-600 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <ul x-show="open" x-transition.opacity.duration.200ms class="space-y-1">
                        <li><a href="{{ route('module.placeholder', 'deal-stage') }}" class="block px-3 py-2 rounded-lg text-slate-700 hover:bg-slate-100 transition">Deal Stage</a></li>
                        <li><a href="{{ route('deals.index') }}" class="block px-3 py-2 rounded-lg {{ request()->routeIs('deals.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-slate-700 hover:bg-slate-100' }} transition">Deal</a></li>
                    </ul>
                </div>

                <!-- Product Management -->
                <div x-data="{ open: true }">
                    <button type="button" @click="open = !open" class="w-full flex justify-between items-center px-3 mb-2 outline-none group cursor-pointer">
                        <p class="text-xs font-semibold text-indigo-600 uppercase tracking-wider">Product Management</p>
                        <svg :class="{'rotate-180': open}" class="w-4 h-4 text-indigo-400 group-hover:text-indigo-600 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <ul x-show="open" x-transition.opacity.duration.200ms class="space-y-1">
                        <li><a href="{{ route('module.placeholder', 'product-category') }}" class="block px-3 py-2 rounded-lg text-slate-700 hover:bg-slate-100 transition">Product Category</a></li>
                        <li><a href="{{ route('products.index') }}" class="block px-3 py-2 rounded-lg {{ request()->routeIs('products.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-slate-700 hover:bg-slate-100' }} transition">Product</a></li>
                    </ul>
                </div>

                <!-- Quotation Management -->
                <div x-data="{ open: true }">
                    <button type="button" @click="open = !open" class="w-full flex justify-between items-center px-3 mb-2 outline-none group cursor-pointer">
                        <p class="text-xs font-semibold text-indigo-600 uppercase tracking-wider">Quotation Management</p>
                        <svg :class="{'rotate-180': open}" class="w-4 h-4 text-indigo-400 group-hover:text-indigo-600 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <ul x-show="open" x-transition.opacity.duration.200ms class="space-y-1">
                        <li><a href="{{ route('quotations.index') }}" class="block px-3 py-2 rounded-lg {{ request()->routeIs('quotations.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-slate-700 hover:bg-slate-100' }} transition">Quotation</a></li>
                        <li><a href="{{ route('module.placeholder', 'quotation-item') }}" class="block px-3 py-2 rounded-lg text-slate-700 hover:bg-slate-100 transition">Quotation Item</a></li>
                    </ul>
                </div>

                <!-- Invoice Management -->
                <div x-data="{ open: true }">
                    <button type="button" @click="open = !open" class="w-full flex justify-between items-center px-3 mb-2 outline-none group cursor-pointer">
                        <p class="text-xs font-semibold text-indigo-600 uppercase tracking-wider">Invoice Management</p>
                        <svg :class="{'rotate-180': open}" class="w-4 h-4 text-indigo-400 group-hover:text-indigo-600 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <ul x-show="open" x-transition.opacity.duration.200ms class="space-y-1">
                        <li><a href="{{ route('invoices.index') }}" class="block px-3 py-2 rounded-lg {{ request()->routeIs('invoices.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-slate-700 hover:bg-slate-100' }} transition">Invoice</a></li>
                        <li><a href="{{ route('module.placeholder', 'invoice-item') }}" class="block px-3 py-2 rounded-lg text-slate-700 hover:bg-slate-100 transition">Invoice Item</a></li>
                    </ul>
                </div>

                <!-- Payment Management -->
                <div x-data="{ open: true }">
                    <button type="button" @click="open = !open" class="w-full flex justify-between items-center px-3 mb-2 outline-none group cursor-pointer">
                        <p class="text-xs font-semibold text-indigo-600 uppercase tracking-wider">Payment Management</p>
                        <svg :class="{'rotate-180': open}" class="w-4 h-4 text-indigo-400 group-hover:text-indigo-600 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <ul x-show="open" x-transition.opacity.duration.200ms class="space-y-1">
                        <li><a href="{{ route('payments.index') }}" class="block px-3 py-2 rounded-lg {{ request()->routeIs('payments.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-slate-700 hover:bg-slate-100' }} transition">Payment</a></li>
                    </ul>
                </div>

                @role('Super Admin')
                <!-- System Management -->
                <div x-data="{ open: true }">
                    <button type="button" @click="open = !open" class="w-full flex justify-between items-center px-3 mb-2 outline-none group cursor-pointer">
                        <p class="text-xs font-semibold text-indigo-600 uppercase tracking-wider">System Management</p>
                        <svg :class="{'rotate-180': open}" class="w-4 h-4 text-indigo-400 group-hover:text-indigo-600 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <ul x-show="open" x-transition.opacity.duration.200ms class="space-y-1">
                        <li><a href="{{ route('reports.index') }}" class="block px-3 py-2 rounded-lg {{ request()->routeIs('reports.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-slate-700 hover:bg-slate-100' }} transition">Reports</a></li>
                        <li><a href="{{ route('attachments.index') }}" class="block px-3 py-2 rounded-lg {{ request()->routeIs('attachments.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-slate-700 hover:bg-slate-100' }} transition">Attachments</a></li>
                        <li><a href="{{ route('activity-logs.index') }}" class="block px-3 py-2 rounded-lg {{ request()->routeIs('activity-logs.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-slate-700 hover:bg-slate-100' }} transition">Activity Logs</a></li>
                        <li><a href="{{ route('settings.index') }}" class="block px-3 py-2 rounded-lg {{ request()->routeIs('settings.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-slate-700 hover:bg-slate-100' }} transition">Settings</a></li>
                    </ul>
                </div>
                @endrole
            </div>

            <!-- User Profile Bottom -->
            <div class="p-4 border-t border-slate-800 bg-slate-900">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-full bg-indigo-500 text-white flex items-center justify-center font-bold">
                            {{ substr(Auth::user()->name, 0, 1) }}
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-slate-200">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-slate-500 truncate w-32">{{ Auth::user()->email }}</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-slate-400 hover:text-red-500 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Workspace -->
        <div class="flex-1 flex flex-col h-full bg-slate-950 overflow-hidden w-full">
            <!-- Top Nav -->
            <header class="h-16 border-b border-slate-800 flex items-center justify-between px-4 lg:px-8 flex-shrink-0 bg-slate-950">
                <div class="flex items-center flex-1">
                    <button @click="sidebarOpen = true" class="lg:hidden text-slate-500 hover:text-slate-700 focus:outline-none mr-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>
                    
                    <div class="w-full max-w-md relative hidden sm:block">
                        <form action="{{ route('search.index') }}" method="GET">
                            <svg class="w-5 h-5 absolute left-3 top-1/2 transform -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            <input type="text" name="q" placeholder="Search..." class="w-full bg-slate-900 border border-slate-800 rounded-lg pl-10 pr-4 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none text-slate-300 transition" value="{{ request('q') }}">
                        </form>
                    </div>
                </div>
                
                <div class="flex items-center space-x-5 text-slate-400">
                    <button class="hover:text-slate-600 transition"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg></button>
                    
                    <!-- Notifications Dropdown -->
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="hover:text-slate-600 transition relative outline-none">
                            <span class="absolute top-0 right-0 w-2 h-2 bg-red-500 rounded-full"></span>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                        </button>
                        <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-lg border border-slate-100 py-2 z-50" style="display: none;">
                            <div class="px-4 py-2 border-b border-slate-50">
                                <h3 class="text-sm font-bold text-slate-900">Notifications</h3>
                            </div>
                            <div class="max-h-64 overflow-y-auto">
                                <a href="#" class="block px-4 py-3 hover:bg-slate-50 border-b border-slate-50 transition">
                                    <p class="text-sm font-medium text-slate-800">New Lead Assigned</p>
                                    <p class="text-xs text-slate-500 mt-0.5">John Doe from Acme Corp</p>
                                </a>
                                <a href="#" class="block px-4 py-3 hover:bg-slate-50 border-b border-slate-50 transition">
                                    <p class="text-sm font-medium text-slate-800">Invoice Overdue</p>
                                    <p class="text-xs text-slate-500 mt-0.5">INV-00124 is 3 days overdue</p>
                                </a>
                                <a href="#" class="block px-4 py-3 hover:bg-slate-50 transition">
                                    <p class="text-sm font-medium text-slate-800">Deal Won!</p>
                                    <p class="text-xs text-slate-500 mt-0.5">Enterprise License closed</p>
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Profile Dropdown -->
                    <div x-data="{ open: false }" class="relative sm:hidden">
                        <button @click="open = !open" class="hover:text-slate-600 transition outline-none flex items-center">
                            <div class="w-8 h-8 rounded-full bg-indigo-500 text-white flex items-center justify-center font-bold text-xs">
                                {{ substr(Auth::user()->name, 0, 1) }}
                            </div>
                        </button>
                        <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-slate-100 py-1 z-50" style="display: none;">
                            <div class="px-4 py-2 border-b border-slate-50">
                                <p class="text-sm font-semibold text-slate-900">{{ Auth::user()->name }}</p>
                                <p class="text-xs text-slate-500 truncate">{{ Auth::user()->email }}</p>
                            </div>
                            <a href="#" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Profile</a>
                            <a href="{{ route('settings.index') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Settings</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">Logout</button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Content Area -->
            <main class="flex-1 overflow-y-auto p-8 bg-slate-950">
                @yield('content')
            </main>
        </div>
    @else
        <!-- Guest Layout -->
        <main class="w-full h-full flex items-center justify-center p-6 bg-slate-50">
            @yield('content')
        </main>
    @endauth

    <x-ui.toast />
    <x-ui.modal />

    @yield('scripts')
</body>
</html>
