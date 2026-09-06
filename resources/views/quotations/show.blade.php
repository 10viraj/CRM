@extends('layouts.app')

@section('title', 'Quotation ' . $quotation->quote_number . ' - SmartCRM')

@section('content')
<!-- Action Bar (Hidden on Print) -->
<div class="mb-6 flex justify-between items-center print:hidden">
    <a href="{{ route('quotations.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800 flex items-center">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        Back to Quotations
    </a>
    <div class="flex space-x-3">
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

<!-- Printable Quotation Document -->
<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden print:border-none print:shadow-none print:m-0 print:p-0">
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
                    <p>contact@smartcrm.com</p>
                    <p>(555) 123-4567</p>
                </div>
            </div>
            
            <div class="text-right">
                <h1 class="text-4xl font-black text-slate-200 uppercase tracking-widest mb-4">Quotation</h1>
                
                <div class="grid grid-cols-2 gap-x-8 gap-y-2 text-sm">
                    <div class="text-slate-500 font-medium">Quote Number:</div>
                    <div class="font-bold text-slate-900">{{ $quotation->quote_number }}</div>
                    
                    <div class="text-slate-500 font-medium">Date:</div>
                    <div class="font-bold text-slate-900">{{ $quotation->date->format('M d, Y') }}</div>
                    
                    <div class="text-slate-500 font-medium">Expiry Date:</div>
                    <div class="font-bold text-slate-900">{{ $quotation->expiry_date ? $quotation->expiry_date->format('M d, Y') : 'N/A' }}</div>
                </div>
            </div>
        </div>

        <!-- Customer Section -->
        <div class="mb-12">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Prepared For</h3>
            <div class="text-slate-800 text-sm space-y-1">
                <p class="font-bold text-lg text-slate-900">{{ $quotation->lead->company ?? 'Company Not Specified' }}</p>
                <p class="font-medium text-slate-600">{{ $quotation->lead->first_name }} {{ $quotation->lead->last_name }}</p>
                <p>{{ $quotation->lead->email }}</p>
                <p>{{ $quotation->lead->phone }}</p>
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
                    @foreach($quotation->items as $item)
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
        <div class="flex flex-col md:flex-row justify-between items-start">
            <div class="w-full md:w-1/2 mb-8 md:mb-0 pr-8">
                @if($quotation->notes)
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Notes & Terms</h3>
                    <p class="text-sm text-slate-600 leading-relaxed bg-slate-50 p-4 rounded-lg border border-slate-100">
                        {{ $quotation->notes }}
                    </p>
                @endif
            </div>
            
            <div class="w-full md:w-1/3 space-y-4 text-sm">
                <div class="flex justify-between items-center text-slate-600">
                    <span>Subtotal</span>
                    <span class="font-semibold text-slate-900">${{ number_format($quotation->subtotal, 2) }}</span>
                </div>
                <div class="flex justify-between items-center text-slate-600">
                    <span>Tax</span>
                    <span class="font-semibold text-slate-900">${{ number_format($quotation->tax, 2) }}</span>
                </div>
                @if($quotation->discount > 0)
                    <div class="flex justify-between items-center text-red-500">
                        <span>Discount</span>
                        <span class="font-semibold">-${{ number_format($quotation->discount, 2) }}</span>
                    </div>
                @endif
                <div class="flex justify-between items-center pt-4 border-t-2 border-slate-800">
                    <span class="text-base font-bold text-slate-900 uppercase">Grand Total</span>
                    <span class="text-2xl font-black text-indigo-700">${{ number_format($quotation->grand_total, 2) }}</span>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="mt-20 pt-8 border-t border-slate-100 text-center text-xs text-slate-400">
            <p>Thank you for considering SmartCRM for your business needs.</p>
            <p>If you have any questions concerning this quotation, contact our sales department at contact@smartcrm.com.</p>
        </div>
    </div>
</div>
@endsection
