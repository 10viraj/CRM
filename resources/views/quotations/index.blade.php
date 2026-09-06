@extends('layouts.app')

@section('title', 'Quotations - SmartCRM')

@section('content')
<div class="mb-8 flex justify-between items-center">
    <div>
        <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Quotations</h1>
        <p class="text-slate-500 mt-1 text-sm font-medium">Manage and send quotes to your prospective customers.</p>
    </div>
    <div class="flex space-x-3">
        <a href="{{ route('quotations.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-medium shadow-sm transition">
            + Create Quotation
        </a>
    </div>
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
                <tr class="bg-white border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                    <th class="p-4">Quote Number</th>
                    <th class="p-4">Customer</th>
                    <th class="p-4">Date</th>
                    <th class="p-4">Amount</th>
                    <th class="p-4">Status</th>
                    <th class="p-4 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm">
                @forelse($quotations as $quote)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="p-4 font-bold text-slate-800">
                            {{ $quote->quote_number }}
                        </td>
                        <td class="p-4">
                            <div class="font-semibold text-slate-900">{{ $quote->lead->company ?? ($quote->lead->first_name . ' ' . $quote->lead->last_name) }}</div>
                            <div class="text-slate-500 text-xs mt-0.5">{{ $quote->lead->email }}</div>
                        </td>
                        <td class="p-4 text-slate-600">
                            {{ $quote->date->format('M d, Y') }}
                        </td>
                        <td class="p-4 font-bold text-slate-800">
                            ${{ number_format($quote->grand_total, 2) }}
                        </td>
                        <td class="p-4">
                            @php
                                $statusColors = [
                                    'draft' => 'bg-gray-100 text-gray-700',
                                    'sent' => 'bg-blue-100 text-blue-700',
                                    'accepted' => 'bg-green-100 text-green-700',
                                    'rejected' => 'bg-red-100 text-red-700',
                                    'expired' => 'bg-orange-100 text-orange-700'
                                ];
                                $badgeClass = $statusColors[$quote->status] ?? 'bg-gray-100 text-gray-700';
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium {{ $badgeClass }} capitalize">
                                {{ $quote->status }}
                            </span>
                        </td>
                        <td class="p-4 text-center space-x-3">
                            <a href="{{ route('quotations.show', $quote) }}" class="text-indigo-600 hover:text-indigo-900 font-medium text-xs">View/Print</a>
                            <a href="#" class="text-slate-600 hover:text-slate-900 font-medium text-xs">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-12 text-center text-slate-500">
                            <p class="text-base font-medium text-slate-900">No quotations found</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($quotations->hasPages())
        <div class="p-4 border-t border-slate-200 bg-slate-50">
            {{ $quotations->links() }}
        </div>
    @endif
</div>
@endsection
