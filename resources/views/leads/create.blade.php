@extends('layouts.app')

@section('title', 'Add Lead - SmartCRM')

@section('content')
<div class="mb-8">
    <a href="{{ route('leads.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800 flex items-center mb-4">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        Back to Leads
    </a>
    <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Add New Lead</h1>
    <p class="text-slate-500 mt-1 text-sm font-medium">Create a new prospective customer profile.</p>
</div>

<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden max-w-4xl">
    <form action="{{ route('leads.store') }}" method="POST" class="p-8">
        @csrf
        
        <h2 class="text-lg font-semibold text-slate-800 mb-4 border-b border-slate-100 pb-2">Contact Information</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div>
                <label for="first_name" class="block text-sm font-medium text-slate-700 mb-1">First Name <span class="text-red-500">*</span></label>
                <input type="text" name="first_name" id="first_name" value="{{ old('first_name') }}" class="w-full rounded-lg border-slate-300 border px-4 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition @error('first_name') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                @error('first_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="last_name" class="block text-sm font-medium text-slate-700 mb-1">Last Name <span class="text-red-500">*</span></label>
                <input type="text" name="last_name" id="last_name" value="{{ old('last_name') }}" class="w-full rounded-lg border-slate-300 border px-4 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition @error('last_name') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                @error('last_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-slate-700 mb-1">Email Address <span class="text-red-500">*</span></label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" class="w-full rounded-lg border-slate-300 border px-4 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition @error('email') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror" placeholder="contact@example.com">
                @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="phone" class="block text-sm font-medium text-slate-700 mb-1">Phone Number</label>
                <input type="text" name="phone" id="phone" value="{{ old('phone') }}" class="w-full rounded-lg border-slate-300 border px-4 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition @error('phone') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror" placeholder="(555) 123-4567">
                @error('phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="md:col-span-2">
                <label for="company" class="block text-sm font-medium text-slate-700 mb-1">Company</label>
                <input type="text" name="company" id="company" value="{{ old('company') }}" class="w-full rounded-lg border-slate-300 border px-4 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition @error('company') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                @error('company') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <h2 class="text-lg font-semibold text-slate-800 mb-4 border-b border-slate-100 pb-2">Lead Information</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div>
                <label for="lead_source_id" class="block text-sm font-medium text-slate-700 mb-1">Source <span class="text-red-500">*</span></label>
                <select name="lead_source_id" id="lead_source_id" class="w-full rounded-lg border-slate-300 border px-4 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition bg-white @error('lead_source_id') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                    <option value="">Select a source...</option>
                    @foreach($sources as $source)
                        <option value="{{ $source->id }}" {{ old('lead_source_id') == $source->id ? 'selected' : '' }}>{{ $source->name }}</option>
                    @endforeach
                </select>
                @error('lead_source_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="lead_status_id" class="block text-sm font-medium text-slate-700 mb-1">Status <span class="text-red-500">*</span></label>
                <select name="lead_status_id" id="lead_status_id" class="w-full rounded-lg border-slate-300 border px-4 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition bg-white @error('lead_status_id') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                    <option value="">Select a status...</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status->id }}" {{ old('lead_status_id') == $status->id ? 'selected' : '' }}>{{ $status->name }}</option>
                    @endforeach
                </select>
                @error('lead_status_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="score" class="block text-sm font-medium text-slate-700 mb-1">Lead Score (0-100) <span class="text-red-500">*</span></label>
                <input type="number" name="score" id="score" min="0" max="100" value="{{ old('score', 0) }}" class="w-full rounded-lg border-slate-300 border px-4 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition @error('score') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                @error('score') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="md:col-span-3">
                <label for="notes" class="block text-sm font-medium text-slate-700 mb-1">Notes</label>
                <textarea name="notes" id="notes" rows="4" class="w-full rounded-lg border-slate-300 border px-4 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition @error('notes') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror" placeholder="Initial context or qualification notes...">{{ old('notes') }}</textarea>
                @error('notes') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="flex justify-end space-x-3 pt-6 border-t border-slate-100">
            <a href="{{ route('leads.index') }}" class="px-5 py-2.5 rounded-lg text-sm font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 transition">Cancel</a>
            <button type="submit" class="px-5 py-2.5 rounded-lg text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm transition">Save Lead</button>
        </div>
    </form>
</div>
@endsection
