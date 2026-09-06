@extends('layouts.app')

@section('title', 'Payments Ledger - SmartCRM')

@section('content')
<div class="mb-8">
    <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Payments Ledger</h1>
    <p class="text-slate-500 mt-1 text-sm font-medium">Global history of all received payments.</p>
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
                    <th class="p-4">Date</th>
                    <th class="p-4">Invoice #</th>
                    <th class="p-4">Customer</th>
                    <th class="p-4">Method / Ref</th>
                    <th class="p-4 text-right">Amount</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm">
                @forelse($payments as $payment)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="p-4 text-slate-600 font-medium">
                            {{ $payment->payment_date->format('M d, Y') }}
                        </td>
                        <td class="p-4 font-bold text-indigo-600 hover:text-indigo-800">
                            <a href="{{ route('invoices.show', $payment->invoice) }}">{{ $payment->invoice->invoice_number }}</a>
                        </td>
                        <td class="p-4">
                            <div class="font-semibold text-slate-900">{{ $payment->invoice->lead->company ?? ($payment->invoice->lead->first_name . ' ' . $payment->invoice->lead->last_name) }}</div>
                        </td>
                        <td class="p-4">
                            <div class="text-slate-800 font-medium">{{ $payment->payment_method }}</div>
                            @if($payment->reference_number)
                                <div class="text-slate-400 text-xs mt-0.5">Ref: {{ $payment->reference_number }}</div>
                            @endif
                        </td>
                        <td class="p-4 font-bold text-green-600 text-right">
                            + ${{ number_format($payment->amount, 2) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="p-12 text-center text-slate-500">
                            <p class="text-base font-medium text-slate-900">No payments found</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($payments->hasPages())
        <div class="p-4 border-t border-slate-200 bg-slate-50">
            {{ $payments->links() }}
        </div>
    @endif
</div>
@endsection
