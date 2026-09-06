@extends('layouts.app')

@section('title', 'Add Company - SmartCRM')

@section('content')
<div class="mb-8">
    <a href="{{ route('companies.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800 flex items-center mb-4">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        Back to Companies
    </a>
    <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Add New Company</h1>
    <p class="text-slate-500 mt-1 text-sm font-medium">Create a new company profile in your CRM.</p>
</div>

<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden max-w-4xl">
    <form action="{{ route('companies.store') }}" method="POST" class="p-8">
        @csrf
        
        <h2 class="text-lg font-semibold text-slate-800 mb-4 border-b border-slate-100 pb-2">Basic Information</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div>
                <label for="name" class="block text-sm font-medium text-slate-700 mb-1">Company Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" id="name" required class="w-full rounded-lg border-slate-300 border px-4 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition" placeholder="e.g. Acme Corp">
            </div>
            <div>
                <label for="industry" class="block text-sm font-medium text-slate-700 mb-1">Industry</label>
                <input type="text" name="industry" id="industry" class="w-full rounded-lg border-slate-300 border px-4 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition" placeholder="e.g. Technology">
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-slate-700 mb-1">Email Address</label>
                <input type="email" name="email" id="email" class="w-full rounded-lg border-slate-300 border px-4 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition" placeholder="contact@company.com">
            </div>
            <div>
                <label for="phone" class="block text-sm font-medium text-slate-700 mb-1">Phone Number</label>
                <input type="text" name="phone" id="phone" class="w-full rounded-lg border-slate-300 border px-4 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition" placeholder="(555) 123-4567">
            </div>
            <div class="md:col-span-2">
                <label for="website" class="block text-sm font-medium text-slate-700 mb-1">Website</label>
                <input type="url" name="website" id="website" class="w-full rounded-lg border-slate-300 border px-4 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition" placeholder="https://www.company.com">
            </div>
        </div>

        <h2 class="text-lg font-semibold text-slate-800 mb-4 border-b border-slate-100 pb-2">Location</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="md:col-span-2">
                <label for="address" class="block text-sm font-medium text-slate-700 mb-1">Street Address</label>
                <textarea name="address" id="address" rows="2" class="w-full rounded-lg border-slate-300 border px-4 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition" placeholder="123 Main St."></textarea>
            </div>
            <div>
                <label for="city" class="block text-sm font-medium text-slate-700 mb-1">City</label>
                <input type="text" name="city" id="city" class="w-full rounded-lg border-slate-300 border px-4 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition" placeholder="San Francisco">
            </div>
            <div>
                <label for="state" class="block text-sm font-medium text-slate-700 mb-1">State / Province</label>
                <input type="text" name="state" id="state" class="w-full rounded-lg border-slate-300 border px-4 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition" placeholder="CA">
            </div>
            <div>
                <label for="zip" class="block text-sm font-medium text-slate-700 mb-1">ZIP / Postal Code</label>
                <input type="text" name="zip" id="zip" class="w-full rounded-lg border-slate-300 border px-4 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition" placeholder="94105">
            </div>
            <div>
                <label for="country" class="block text-sm font-medium text-slate-700 mb-1">Country</label>
                <input type="text" name="country" id="country" class="w-full rounded-lg border-slate-300 border px-4 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition" placeholder="United States">
            </div>
        </div>

        <div class="flex justify-end space-x-3 pt-6 border-t border-slate-100">
            <a href="{{ route('companies.index') }}" class="px-5 py-2.5 rounded-lg text-sm font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 transition">Cancel</a>
            <button type="submit" class="px-5 py-2.5 rounded-lg text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm transition">Save Company</button>
        </div>
    </form>
</div>
@endsection
