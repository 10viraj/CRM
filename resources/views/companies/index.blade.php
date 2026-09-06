@extends('layouts.app')

@section('title', 'Companies - SmartCRM')

@section('content')
<div class="mb-8 flex justify-between items-center">
    <div>
        <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Companies</h1>
        <p class="text-slate-500 mt-1 text-sm font-medium">Manage your company directory and B2B accounts.</p>
    </div>
    <a href="{{ route('companies.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-medium shadow-sm transition">
        + Add Company
    </a>
</div>

@if(session('success'))
    <div class="mb-6 bg-green-50 text-green-700 p-4 rounded-lg border border-green-200">
        {{ session('success') }}
    </div>
@endif

<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200 text-sm font-medium text-slate-500">
                    <th class="p-4">Name</th>
                    <th class="p-4">Industry</th>
                    <th class="p-4">Email</th>
                    <th class="p-4">Location</th>
                    <th class="p-4 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm">
                @forelse($companies as $company)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="p-4 font-semibold text-slate-900">{{ $company->name }}</td>
                        <td class="p-4 text-slate-500">{{ $company->industry ?? 'N/A' }}</td>
                        <td class="p-4 text-slate-500">{{ $company->email ?? 'N/A' }}</td>
                        <td class="p-4 text-slate-500">{{ $company->city ? $company->city . ', ' . $company->country : 'N/A' }}</td>
                        <td class="p-4 text-center">
                            <button class="text-indigo-600 hover:text-indigo-900 font-medium">View</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="p-8 text-center text-slate-500">
                            No companies found. Click "Add Company" to get started.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($companies->hasPages())
        <div class="p-4 border-t border-slate-100 bg-slate-50">
            {{ $companies->links() }}
        </div>
    @endif
</div>
@endsection
