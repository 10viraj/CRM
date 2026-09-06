@extends('layouts.app')

@section('title', 'System Settings - SmartCRM')

@section('content')
<div class="mb-8">
    <h1 class="text-3xl font-bold text-slate-900 tracking-tight">System Settings</h1>
    <p class="text-slate-500 mt-1 text-sm font-medium">Configure your CRM globally.</p>
</div>

@if(session('success'))
    <div class="mb-6 bg-green-50 text-green-700 p-4 rounded-lg border border-green-200">
        {{ session('success') }}
    </div>
@endif

<div x-data="{ tab: 'general' }" class="flex flex-col md:flex-row gap-8 items-start">
    <!-- Sidebar Tabs -->
    <div class="w-full md:w-64 flex-shrink-0 bg-white rounded-2xl border border-slate-200 shadow-sm p-4 sticky top-6">
        <nav class="space-y-1">
            <button @click="tab = 'general'" :class="{'bg-indigo-50 text-indigo-700 font-bold': tab === 'general', 'text-slate-600 hover:bg-slate-50': tab !== 'general'}" class="w-full text-left px-4 py-2.5 rounded-lg text-sm font-medium transition flex items-center">
                <svg class="w-5 h-5 mr-3" :class="{'text-indigo-600': tab === 'general', 'text-slate-400': tab !== 'general'}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                General Settings
            </button>
            <button @click="tab = 'company'" :class="{'bg-indigo-50 text-indigo-700 font-bold': tab === 'company', 'text-slate-600 hover:bg-slate-50': tab !== 'company'}" class="w-full text-left px-4 py-2.5 rounded-lg text-sm font-medium transition flex items-center">
                <svg class="w-5 h-5 mr-3" :class="{'text-indigo-600': tab === 'company', 'text-slate-400': tab !== 'company'}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                Company Profile
            </button>
            <button @click="tab = 'crm'" :class="{'bg-indigo-50 text-indigo-700 font-bold': tab === 'crm', 'text-slate-600 hover:bg-slate-50': tab !== 'crm'}" class="w-full text-left px-4 py-2.5 rounded-lg text-sm font-medium transition flex items-center">
                <svg class="w-5 h-5 mr-3" :class="{'text-indigo-600': tab === 'crm', 'text-slate-400': tab !== 'crm'}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                CRM & Finance
            </button>
        </nav>
    </div>

    <!-- Tab Content -->
    <div class="flex-1 w-full bg-white rounded-2xl border border-slate-200 shadow-sm p-8">
        <form action="{{ route('settings.store') }}" method="POST">
            @csrf
            
            @php
                function getSetting($settings, $group, $key) {
                    if (isset($settings[$group])) {
                        $match = $settings[$group]->firstWhere('key', $key);
                        return $match ? $match->value : '';
                    }
                    return '';
                }
            @endphp
            
            <!-- General Settings -->
            <div x-show="tab === 'general'" x-transition.opacity>
                <h2 class="text-xl font-bold text-slate-800 mb-6 pb-4 border-b border-slate-100">General Settings</h2>
                <div class="space-y-5 max-w-xl">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Application Name</label>
                        <input type="text" name="app_name" value="{{ getSetting($settings, 'general', 'app_name') }}" class="w-full rounded-lg border-slate-300 border px-4 py-2 text-sm focus:border-indigo-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Timezone</label>
                        <select name="timezone" class="w-full rounded-lg border-slate-300 border px-4 py-2 text-sm focus:border-indigo-500 outline-none bg-white">
                            <option value="UTC" {{ getSetting($settings, 'general', 'timezone') == 'UTC' ? 'selected' : '' }}>UTC</option>
                            <option value="America/New_York" {{ getSetting($settings, 'general', 'timezone') == 'America/New_York' ? 'selected' : '' }}>Eastern Time (ET)</option>
                            <option value="America/Los_Angeles" {{ getSetting($settings, 'general', 'timezone') == 'America/Los_Angeles' ? 'selected' : '' }}>Pacific Time (PT)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Date Format</label>
                        <select name="date_format" class="w-full rounded-lg border-slate-300 border px-4 py-2 text-sm focus:border-indigo-500 outline-none bg-white">
                            <option value="Y-m-d" {{ getSetting($settings, 'general', 'date_format') == 'Y-m-d' ? 'selected' : '' }}>YYYY-MM-DD</option>
                            <option value="m/d/Y" {{ getSetting($settings, 'general', 'date_format') == 'm/d/Y' ? 'selected' : '' }}>MM/DD/YYYY</option>
                            <option value="d/m/Y" {{ getSetting($settings, 'general', 'date_format') == 'd/m/Y' ? 'selected' : '' }}>DD/MM/YYYY</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Company Profile -->
            <div x-show="tab === 'company'" style="display: none;" x-transition.opacity>
                <h2 class="text-xl font-bold text-slate-800 mb-6 pb-4 border-b border-slate-100">Company Profile</h2>
                <div class="space-y-5 max-w-xl">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Company Name</label>
                        <input type="text" name="company_name" value="{{ getSetting($settings, 'company', 'company_name') }}" class="w-full rounded-lg border-slate-300 border px-4 py-2 text-sm focus:border-indigo-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Support Email</label>
                        <input type="email" name="company_email" value="{{ getSetting($settings, 'company', 'company_email') }}" class="w-full rounded-lg border-slate-300 border px-4 py-2 text-sm focus:border-indigo-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Phone Number</label>
                        <input type="text" name="company_phone" value="{{ getSetting($settings, 'company', 'company_phone') }}" class="w-full rounded-lg border-slate-300 border px-4 py-2 text-sm focus:border-indigo-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Billing Address</label>
                        <textarea name="company_address" rows="3" class="w-full rounded-lg border-slate-300 border px-4 py-2 text-sm focus:border-indigo-500 outline-none">{{ getSetting($settings, 'company', 'company_address') }}</textarea>
                        <p class="text-xs text-slate-400 mt-1">This address will appear on your Quotations and Invoices.</p>
                    </div>
                </div>
            </div>

            <!-- CRM Settings -->
            <div x-show="tab === 'crm'" style="display: none;" x-transition.opacity>
                <h2 class="text-xl font-bold text-slate-800 mb-6 pb-4 border-b border-slate-100">CRM & Finance Defaults</h2>
                <div class="space-y-5 max-w-xl">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Currency</label>
                        <select name="currency" class="w-full rounded-lg border-slate-300 border px-4 py-2 text-sm focus:border-indigo-500 outline-none bg-white">
                            <option value="USD" {{ getSetting($settings, 'crm', 'currency') == 'USD' ? 'selected' : '' }}>USD ($)</option>
                            <option value="EUR" {{ getSetting($settings, 'crm', 'currency') == 'EUR' ? 'selected' : '' }}>EUR (€)</option>
                            <option value="GBP" {{ getSetting($settings, 'crm', 'currency') == 'GBP' ? 'selected' : '' }}>GBP (£)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Default Tax Rate (%)</label>
                        <input type="number" step="0.1" name="tax_rate" value="{{ getSetting($settings, 'crm', 'tax_rate') }}" class="w-full rounded-lg border-slate-300 border px-4 py-2 text-sm focus:border-indigo-500 outline-none">
                    </div>
                </div>
            </div>
            
            <div class="mt-8 pt-6 border-t border-slate-100 flex justify-end">
                <button type="submit" class="px-6 py-2.5 rounded-lg text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm transition">Save Changes</button>
            </div>
        </form>
    </div>
</div>
@endsection
