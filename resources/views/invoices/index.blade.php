@extends('layouts.app')

@section('title', 'Invoices - SmartCRM')

@section('content')
<div class="mb-8 flex justify-between items-center">
    <div>
        <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Invoices</h1>
        <p class="text-slate-500 mt-1 text-sm font-medium">Manage billing and track customer payments.</p>
    </div>
    <div class="flex space-x-3">
        <a href="{{ route('invoices.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-medium shadow-sm transition">
            + Create Invoice
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
                    <th class="p-4">Invoice #</th>
                    <th class="p-4">Customer</th>
                    <th class="p-4">Date / Due</th>
                    <th class="p-4 text-right">Total</th>
                    <th class="p-4 text-right">Balance Due</th>
                    <th class="p-4">Status</th>
                    <th class="p-4 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm">
                @forelse($invoices as $invoice)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="p-4 font-bold text-slate-800">
                            {{ $invoice->invoice_number }}
                        </td>
                        <td class="p-4">
                            <div class="font-semibold text-slate-900">{{ $invoice->lead->company ?? ($invoice->lead->first_name . ' ' . $invoice->lead->last_name) }}</div>
                            <div class="text-slate-500 text-xs mt-0.5">{{ $invoice->lead->email }}</div>
                        </td>
                        <td class="p-4 text-slate-600">
                            <div>{{ $invoice->date->format('M d, Y') }}</div>
                            <div class="text-xs {{ $invoice->due_date && $invoice->due_date->isPast() && $invoice->status != 'paid' ? 'text-red-500 font-medium' : 'text-slate-400' }}">
                                Due: {{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : 'N/A' }}
                            </div>
                        </td>
                        <td class="p-4 font-bold text-slate-800 text-right">
                            ${{ number_format($invoice->grand_total, 2) }}
                        </td>
                        <td class="p-4 text-right">
                            <span class="font-bold {{ $invoice->amount_due > 0 ? 'text-orange-600' : 'text-green-600' }}">
                                ${{ number_format($invoice->amount_due, 2) }}
                            </span>
                        </td>
                        <td class="p-4">
                            @php
                                $statusColors = [
                                    'draft' => 'bg-gray-100 text-gray-700',
                                    'sent' => 'bg-blue-100 text-blue-700',
                                    'paid' => 'bg-green-100 text-green-700',
                                    'overdue' => 'bg-red-100 text-red-700',
                                    'cancelled' => 'bg-slate-100 text-slate-500'
                                ];
                                $badgeClass = $statusColors[$invoice->status] ?? 'bg-gray-100 text-gray-700';
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium {{ $badgeClass }} capitalize">
                                {{ $invoice->status }}
                            </span>
                        </td>
                        <td class="p-4 text-center space-x-3">
                            <a href="{{ route('invoices.show', $invoice) }}" class="text-indigo-600 hover:text-indigo-900 font-medium text-xs">View/Pay</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="p-12 text-center text-slate-500">
                            <p class="text-base font-medium text-slate-900">No invoices found</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($invoices->hasPages())
        <div class="p-4 border-t border-slate-200 bg-slate-50">
            {{ $invoices->links() }}
        </div>
    @endif
</div>
@endsection
