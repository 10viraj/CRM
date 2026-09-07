@extends('layouts.app')

@section('title', 'Edit ' . $company->name . ' - SmartCRM')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-2 text-xs text-slate-400">
            <a href="{{ route('companies.index') }}" class="hover:text-brand-400 font-medium">Companies</a>
            <span>/</span>
            <a href="{{ route('companies.show', $company) }}" class="hover:text-brand-400 font-medium">{{ $company->name }}</a>
            <span>/</span>
            <span class="text-slate-200 font-semibold">Edit</span>
        </div>
        
        <a href="{{ route('companies.show', $company) }}" class="text-xs text-slate-400 hover:text-white transition">
            &larr; Back to Profile
        </a>
    </div>

    <div class="bg-dark-900 rounded-2xl border border-slate-800 shadow-xl overflow-hidden">
        <div class="p-6 border-b border-slate-800 bg-dark-900/60">
            <h1 class="text-xl font-bold text-white tracking-tight">Edit Company Profile</h1>
            <p class="text-xs text-slate-400 mt-1">Update organizational details, communication channels, and address.</p>
        </div>

        <form action="{{ route('companies.update', $company) }}" method="POST" class="p-6 space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Company Name -->
                <div class="space-y-1.5 md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-300">Company Name <span class="text-rose-400">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $company->name) }}" required class="w-full bg-dark-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-slate-200 focus:border-brand-500 outline-none transition">
                    @error('name')<p class="text-[11px] text-rose-400">{{ $message }}</p>@enderror
                </div>

                <!-- Industry -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-slate-300">Industry Sector</label>
                    <input type="text" name="industry" value="{{ old('industry', $company->industry) }}" placeholder="e.g. Technology, Finance, Healthcare" class="w-full bg-dark-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-slate-200 focus:border-brand-500 outline-none transition">
                </div>

                <!-- Website -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-slate-300">Website</label>
                    <input type="text" name="website" value="{{ old('website', $company->website) }}" placeholder="e.g. https://acme.com" class="w-full bg-dark-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-slate-200 focus:border-brand-500 outline-none transition">
                </div>

                <!-- Email -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-slate-300">Primary Email</label>
                    <input type="email" name="email" value="{{ old('email', $company->email) }}" placeholder="e.g. contact@acme.com" class="w-full bg-dark-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-slate-200 focus:border-brand-500 outline-none transition">
                </div>

                <!-- Phone -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-slate-300">Phone Number</label>
                    <input type="text" name="phone" value="{{ old('phone', $company->phone) }}" placeholder="e.g. +1 555-0199" class="w-full bg-dark-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-slate-200 focus:border-brand-500 outline-none transition">
                </div>

                <!-- Street Address -->
                <div class="space-y-1.5 md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-300">Street Address</label>
                    <input type="text" name="address" value="{{ old('address', $company->address) }}" placeholder="e.g. 100 Main St, Suite 400" class="w-full bg-dark-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-slate-200 focus:border-brand-500 outline-none transition">
                </div>

                <!-- City -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-slate-300">City</label>
                    <input type="text" name="city" value="{{ old('city', $company->city) }}" class="w-full bg-dark-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-slate-200 focus:border-brand-500 outline-none transition">
                </div>

                <!-- State -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-slate-300">State / Region</label>
                    <input type="text" name="state" value="{{ old('state', $company->state) }}" class="w-full bg-dark-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-slate-200 focus:border-brand-500 outline-none transition">
                </div>

                <!-- Zip -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-slate-300">Postal / Zip Code</label>
                    <input type="text" name="zip" value="{{ old('zip', $company->zip) }}" class="w-full bg-dark-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-slate-200 focus:border-brand-500 outline-none transition">
                </div>

                <!-- Country -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-slate-300">Country</label>
                    <input type="text" name="country" value="{{ old('country', $company->country) }}" class="w-full bg-dark-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-slate-200 focus:border-brand-500 outline-none transition">
                </div>
            </div>

            <div class="flex items-center justify-end space-x-3 pt-6 border-t border-slate-800">
                <a href="{{ route('companies.show', $company) }}" class="px-4 py-2 rounded-xl bg-dark-950 border border-slate-800 text-xs font-semibold text-slate-400 hover:text-white transition">
                    Cancel
                </a>
                <button type="submit" class="px-5 py-2 rounded-xl bg-gradient-to-r from-brand-600 to-indigo-600 hover:from-brand-500 hover:to-indigo-500 text-white text-xs font-semibold shadow-lg shadow-brand-600/25 transition">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
