@extends('layouts.app')

@section('title', 'Invoice ' . $invoice->invoice_number . ' - SmartCRM')

@section('content')
<!-- Action Bar (Hidden on Print) -->
<div class="mb-6 flex justify-between items-center print:hidden">
    <a href="{{ route('invoices.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800 flex items-center">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        Back to Invoices
    </a>
    <div class="flex space-x-3">
        @if($invoice->amount_due > 0)
            <button x-data @click="$dispatch('open-payment-modal')" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium shadow-sm transition flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Record Payment
            </button>
        @endif
        <button onclick="window.print()" class="bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 px-4 py-2 rounded-lg font-medium shadow-sm transition flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
            Print
        </button>
        <button onclick="window.print()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-medium shadow-sm transition flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
            Download PDF
        </button>
    </div>
</div>

@if(session('success'))
    <div class="mb-6 bg-green-50 text-green-700 p-4 rounded-lg border border-green-200 print:hidden">
        {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="mb-6 bg-red-50 text-red-700 p-4 rounded-lg border border-red-200 print:hidden">
        <ul class="list-disc pl-5">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<!-- Printable Invoice Document -->
<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden print:border-none print:shadow-none print:m-0 print:p-0">
    <!-- Status Banner -->
    @if($invoice->status == 'paid')
        <div class="bg-green-500 text-white text-center py-2 font-bold tracking-widest uppercase print:border-y print:border-green-500 print:text-green-600 print:bg-white">
            PAID IN FULL
        </div>
    @elseif($invoice->status == 'overdue')
        <div class="bg-red-500 text-white text-center py-2 font-bold tracking-widest uppercase print:border-y print:border-red-500 print:text-red-600 print:bg-white">
            OVERDUE
        </div>
    @endif

    <div class="p-12 print:p-0">
        <!-- Header Section -->
        <div class="flex justify-between items-start mb-16 border-b border-slate-100 pb-10">
            <div>
                <!-- Company Logo Placeholder -->
                <div class="flex items-center space-x-2 mb-6">
                    <div class="w-10 h-10 bg-indigo-600 rounded-lg flex items-center justify-center text-white font-bold text-xl">
                        S
                    </div>
                    <span class="text-2xl font-bold text-slate-900 tracking-tight">SmartCRM Inc.</span>
                </div>
                
                <div class="text-slate-500 text-sm space-y-1">
                    <p>123 Business Avenue, Suite 100</p>
                    <p>San Francisco, CA 94107</p>
                    <p>billing@smartcrm.com</p>
                    <p>(555) 123-4567</p>
                </div>
            </div>
            
            <div class="text-right">
                <h1 class="text-4xl font-black text-slate-200 uppercase tracking-widest mb-4">Invoice</h1>
                
                <div class="grid grid-cols-2 gap-x-8 gap-y-2 text-sm">
                    <div class="text-slate-500 font-medium">Invoice Number:</div>
                    <div class="font-bold text-slate-900">{{ $invoice->invoice_number }}</div>
                    
                    <div class="text-slate-500 font-medium">Date:</div>
                    <div class="font-bold text-slate-900">{{ $invoice->date->format('M d, Y') }}</div>
                    
                    <div class="text-slate-500 font-medium">Due Date:</div>
                    <div class="font-bold text-slate-900">{{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : 'Due on Receipt' }}</div>
                </div>
            </div>
        </div>

        <!-- Customer Section -->
        <div class="mb-12">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Bill To</h3>
            <div class="text-slate-800 text-sm space-y-1">
                <p class="font-bold text-lg text-slate-900">{{ $invoice->lead->company ?? 'Company Not Specified' }}</p>
                <p class="font-medium text-slate-600">{{ $invoice->lead->first_name }} {{ $invoice->lead->last_name }}</p>
                <p>{{ $invoice->lead->email }}</p>
                <p>{{ $invoice->lead->phone }}</p>
            </div>
        </div>

        <!-- Items Table -->
        <div class="mb-12">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b-2 border-slate-800 text-xs font-bold text-slate-800 uppercase tracking-wider">
                        <th class="pb-3 w-1/2">Description</th>
                        <th class="pb-3 text-center">Qty</th>
                        <th class="pb-3 text-right">Unit Price</th>
                        <th class="pb-3 text-right">Tax</th>
                        <th class="pb-3 text-right">Discount</th>
                        <th class="pb-3 text-right">Line Total</th>
                    </tr>
                </thead>
                <tbody class="text-sm text-slate-700 divide-y divide-slate-100">
                    @foreach($invoice->items as $item)
                        <tr>
                            <td class="py-4">
                                <p class="font-bold text-slate-900">{{ $item->product_name }}</p>
                                <p class="text-xs text-slate-500 mt-1">{{ $item->product->description ?? '' }}</p>
                            </td>
                            <td class="py-4 text-center">{{ $item->quantity }}</td>
                            <td class="py-4 text-right">${{ number_format($item->unit_price, 2) }}</td>
                            <td class="py-4 text-right">${{ number_format($item->tax, 2) }}</td>
                            <td class="py-4 text-right">${{ number_format($item->discount, 2) }}</td>
                            <td class="py-4 text-right font-bold text-slate-900">${{ number_format($item->line_total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Totals Section -->
        <div class="flex flex-col md:flex-row justify-between items-start mb-12">
            <div class="w-full md:w-1/2 mb-8 md:mb-0 pr-8">
                @if($invoice->notes)
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Notes & Payment Instructions</h3>
                    <p class="text-sm text-slate-600 leading-relaxed bg-slate-50 p-4 rounded-lg border border-slate-100">
                        {{ $invoice->notes }}
                    </p>
                @endif
            </div>
            
            <div class="w-full md:w-1/3 space-y-4 text-sm">
                <div class="flex justify-between items-center text-slate-600">
                    <span>Subtotal</span>
                    <span class="font-semibold text-slate-900">${{ number_format($invoice->subtotal, 2) }}</span>
                </div>
                <div class="flex justify-between items-center text-slate-600">
                    <span>Tax</span>
                    <span class="font-semibold text-slate-900">${{ number_format($invoice->tax, 2) }}</span>
                </div>
                @if($invoice->discount > 0)
                    <div class="flex justify-between items-center text-red-500">
                        <span>Discount</span>
                        <span class="font-semibold">-${{ number_format($invoice->discount, 2) }}</span>
                    </div>
                @endif
                <div class="flex justify-between items-center pt-4 border-t border-slate-200">
                    <span class="font-bold text-slate-900 uppercase">Grand Total</span>
                    <span class="font-bold text-slate-900">${{ number_format($invoice->grand_total, 2) }}</span>
                </div>
                <div class="flex justify-between items-center text-slate-600 pb-4 border-b border-slate-200">
                    <span>Amount Paid</span>
                    <span class="font-semibold text-green-600">${{ number_format($invoice->amount_paid, 2) }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-base font-bold text-slate-900 uppercase">Amount Due</span>
                    <span class="text-2xl font-black {{ $invoice->amount_due > 0 ? 'text-indigo-700' : 'text-green-600' }}">
                        ${{ number_format($invoice->amount_due, 2) }}
                    </span>
                </div>
            </div>
        </div>

        @if($invoice->payments->count() > 0)
            <!-- Payment History -->
            <div class="mt-8 print:mt-4">
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-4 border-b border-slate-200 pb-2">Payment History</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600">
                        <thead>
                            <tr class="bg-slate-50 font-medium">
                                <th class="p-3">Date</th>
                                <th class="p-3">Method</th>
                                <th class="p-3">Reference</th>
                                <th class="p-3 text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($invoice->payments as $payment)
                                <tr>
                                    <td class="p-3">{{ $payment->payment_date->format('M d, Y') }}</td>
                                    <td class="p-3">{{ $payment->payment_method }}</td>
                                    <td class="p-3">{{ $payment->reference_number ?? '-' }}</td>
                                    <td class="p-3 text-right font-bold text-slate-900">${{ number_format($payment->amount, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
        
        <!-- Footer -->
        <div class="mt-16 pt-8 border-t border-slate-100 text-center text-xs text-slate-400">
            <p>Thank you for your business.</p>
        </div>
    </div>
</div>

<!-- Record Payment Modal (Alpine.js) -->
<div x-data="{ modalOpen: false }" @open-payment-modal.window="modalOpen = true" class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div x-show="modalOpen" x-transition.opacity class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity"></div>

    <div x-show="modalOpen" class="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div x-show="modalOpen" x-transition.scale.origin.bottom class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                            <h3 class="text-lg font-bold leading-6 text-slate-900" id="modal-title">Record Payment</h3>
                            <div class="mt-4">
                                <form action="{{ route('payments.store', $invoice) }}" method="POST" id="paymentForm">
                                    @csrf
                                    
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 mb-1">Payment Amount ($)</label>
                                            <input type="number" name="amount" value="{{ $invoice->amount_due }}" step="0.01" max="{{ $invoice->amount_due }}" class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm focus:border-indigo-500 outline-none" required>
                                        </div>
                                        
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 mb-1">Payment Date</label>
                                            <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm focus:border-indigo-500 outline-none" required>
                                        </div>
                                        
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 mb-1">Payment Method</label>
                                            <select name="payment_method" class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm focus:border-indigo-500 outline-none bg-white" required>
                                                <option value="Credit Card">Credit Card</option>
                                                <option value="Bank Transfer">Bank Transfer</option>
                                                <option value="PayPal">PayPal</option>
                                                <option value="Cash">Cash</option>
                                                <option value="Cheque">Cheque</option>
                                            </select>
                                        </div>
                                        
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 mb-1">Reference Number <span class="text-slate-400 font-normal">(Optional)</span></label>
                                            <input type="text" name="reference_number" class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm focus:border-indigo-500 outline-none">
                                        </div>
                                        
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 mb-1">Notes <span class="text-slate-400 font-normal">(Optional)</span></label>
                                            <textarea name="notes" rows="2" class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm focus:border-indigo-500 outline-none"></textarea>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-slate-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                    <button type="submit" form="paymentForm" class="inline-flex w-full justify-center rounded-lg bg-green-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-green-700 sm:ml-3 sm:w-auto transition">Save Payment</button>
                    <button type="button" @click="modalOpen = false" class="mt-3 inline-flex w-full justify-center rounded-lg bg-white px-3 py-2 text-sm font-medium text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto transition">Cancel</button>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
