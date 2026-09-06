@extends('layouts.app')

@section('title', 'Create Invoice - SmartCRM')

@section('content')
<div class="mb-8">
    <a href="{{ route('invoices.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800 flex items-center mb-4">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        Back to Invoices
    </a>
    <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Create Invoice</h1>
</div>

<form action="{{ route('invoices.store') }}" method="POST" x-data="invoiceCalculator()" class="space-y-6">
    @csrf
    
    @if($errors->any())
        <div class="bg-red-50 text-red-700 p-4 rounded-lg border border-red-200">
            <p class="font-bold mb-1">Please fix the following errors:</p>
            <ul class="list-disc pl-5 text-sm space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-8">
            <h2 class="text-lg font-semibold text-slate-800 mb-6 border-b border-slate-100 pb-2">Invoice Details</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Invoice Number</label>
                    <input type="text" name="invoice_number" value="{{ old('invoice_number', $nextNumber) }}" class="w-full rounded-lg border-slate-300 border px-4 py-2 text-sm bg-slate-50 focus:outline-none" readonly>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Customer / Lead <span class="text-red-500">*</span></label>
                    <select name="lead_id" class="w-full rounded-lg border-slate-300 border px-4 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition bg-white">
                        <option value="">Select Customer...</option>
                        @foreach($leads as $lead)
                            <option value="{{ $lead->id }}" {{ old('lead_id') == $lead->id ? 'selected' : '' }}>
                                {{ $lead->company ? $lead->company . ' (' . $lead->first_name . ' ' . $lead->last_name . ')' : $lead->first_name . ' ' . $lead->last_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Invoice Date <span class="text-red-500">*</span></label>
                    <input type="date" name="date" value="{{ old('date', date('Y-m-d')) }}" class="w-full rounded-lg border-slate-300 border px-4 py-2 text-sm focus:border-indigo-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Due Date</label>
                    <input type="date" name="due_date" value="{{ old('due_date', date('Y-m-d', strtotime('+30 days'))) }}" class="w-full rounded-lg border-slate-300 border px-4 py-2 text-sm focus:border-indigo-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Status <span class="text-red-500">*</span></label>
                    <select name="status" class="w-full rounded-lg border-slate-300 border px-4 py-2 text-sm focus:border-indigo-500 outline-none bg-white">
                        <option value="draft" selected>Draft</option>
                        <option value="sent">Sent</option>
                        <option value="paid">Paid</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-8">
            <div class="flex justify-between items-center mb-6 border-b border-slate-100 pb-2">
                <h2 class="text-lg font-semibold text-slate-800">Line Items</h2>
                <button type="button" @click="addItem()" class="text-sm font-medium text-indigo-600 hover:text-indigo-800 flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Add Product
                </button>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse mb-6">
                    <thead>
                        <tr class="bg-slate-50 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                            <th class="p-3 rounded-tl-lg">Product</th>
                            <th class="p-3 w-24">Qty</th>
                            <th class="p-3 w-32">Unit Price ($)</th>
                            <th class="p-3 w-32">Tax ($)</th>
                            <th class="p-3 w-32">Discount ($)</th>
                            <th class="p-3 w-32 text-right">Line Total</th>
                            <th class="p-3 w-10 rounded-tr-lg"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <template x-for="(item, index) in items" :key="item.id">
                            <tr>
                                <td class="p-3">
                                    <select x-model="item.product_id" :name="'items['+index+'][product_id]'" @change="updatePrice(item)" class="w-full rounded-lg border-slate-300 border px-3 py-1.5 text-sm focus:border-indigo-500 outline-none bg-white">
                                        <option value="">Select...</option>
                                        @foreach($products as $prod)
                                            <option value="{{ $prod->id }}" data-price="{{ $prod->price }}">{{ $prod->name }} - ${{ number_format($prod->price, 2) }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="p-3">
                                    <input type="number" x-model.number="item.quantity" :name="'items['+index+'][quantity]'" min="1" class="w-full rounded-lg border-slate-300 border px-3 py-1.5 text-sm focus:border-indigo-500 outline-none">
                                </td>
                                <td class="p-3">
                                    <input type="number" x-model.number="item.unit_price" :name="'items['+index+'][unit_price]'" step="0.01" min="0" class="w-full rounded-lg border-slate-300 border px-3 py-1.5 text-sm focus:border-indigo-500 outline-none">
                                </td>
                                <td class="p-3">
                                    <input type="number" x-model.number="item.tax" :name="'items['+index+'][tax]'" step="0.01" min="0" class="w-full rounded-lg border-slate-300 border px-3 py-1.5 text-sm focus:border-indigo-500 outline-none">
                                </td>
                                <td class="p-3">
                                    <input type="number" x-model.number="item.discount" :name="'items['+index+'][discount]'" step="0.01" min="0" class="w-full rounded-lg border-slate-300 border px-3 py-1.5 text-sm focus:border-indigo-500 outline-none">
                                </td>
                                <td class="p-3 text-right font-bold text-slate-800">
                                    $<span x-text="lineTotal(item).toFixed(2)"></span>
                                </td>
                                <td class="p-3 text-center">
                                    <button type="button" @click="removeItem(item.id)" class="text-red-400 hover:text-red-600" x-show="items.length > 1">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <div class="flex flex-col md:flex-row justify-between items-start pt-4 border-t border-slate-200">
                <div class="w-full md:w-1/2 mb-6 md:mb-0 pr-0 md:pr-8">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Additional Notes</label>
                    <textarea name="notes" rows="4" class="w-full rounded-lg border-slate-300 border px-4 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition" placeholder="Payment instructions, thank you note, etc...">{{ old('notes') }}</textarea>
                </div>
                <div class="w-full md:w-1/3 space-y-3 bg-slate-50 p-6 rounded-xl border border-slate-100">
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-slate-500 font-medium">Subtotal</span>
                        <span class="text-slate-900 font-bold">$<span x-text="subtotal().toFixed(2)"></span></span>
                    </div>
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-slate-500 font-medium">Total Tax</span>
                        <span class="text-slate-900 font-bold">$<span x-text="totalTax().toFixed(2)"></span></span>
                    </div>
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-slate-500 font-medium">Total Discount</span>
                        <span class="text-red-500 font-bold">-$<span x-text="totalDiscount().toFixed(2)"></span></span>
                    </div>
                    <div class="flex justify-between items-center pt-3 border-t border-slate-200">
                        <span class="text-base font-bold text-slate-900">Amount Due</span>
                        <span class="text-xl font-black text-indigo-700">$<span x-text="grandTotal().toFixed(2)"></span></span>
                    </div>
                </div>
            </div>
            
            <div class="flex justify-end space-x-3 mt-8 pt-6 border-t border-slate-100">
                <a href="{{ route('invoices.index') }}" class="px-5 py-2.5 rounded-lg text-sm font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 transition">Cancel</a>
                <button type="submit" class="px-5 py-2.5 rounded-lg text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm transition">Save Invoice</button>
            </div>
        </div>
    </div>
</form>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('invoiceCalculator', () => ({
            items: [
                { id: Date.now(), product_id: '', quantity: 1, unit_price: 0, tax: 0, discount: 0 }
            ],
            
            addItem() {
                this.items.push({
                    id: Date.now(),
                    product_id: '',
                    quantity: 1,
                    unit_price: 0,
                    tax: 0,
                    discount: 0
                });
            },
            
            removeItem(id) {
                if (this.items.length > 1) {
                    this.items = this.items.filter(item => item.id !== id);
                }
            },

            updatePrice(item) {
                if(item.product_id) {
                    const selectEl = document.querySelector(`select[name="items[${this.items.indexOf(item)}][product_id]"]`);
                    if(selectEl && selectEl.selectedOptions[0]) {
                        const price = selectEl.selectedOptions[0].getAttribute('data-price');
                        if(price) {
                            item.unit_price = parseFloat(price);
                            if(item.tax === 0) item.tax = item.unit_price * 0.10;
                        }
                    }
                }
            },
            
            lineTotal(item) {
                let qty = parseFloat(item.quantity) || 0;
                let price = parseFloat(item.unit_price) || 0;
                let tax = parseFloat(item.tax) || 0;
                let discount = parseFloat(item.discount) || 0;
                return (qty * price) + tax - discount;
            },
            
            subtotal() {
                return this.items.reduce((acc, item) => {
                    let qty = parseFloat(item.quantity) || 0;
                    let price = parseFloat(item.unit_price) || 0;
                    return acc + (qty * price);
                }, 0);
            },
            
            totalTax() {
                return this.items.reduce((acc, item) => acc + (parseFloat(item.tax) || 0), 0);
            },
            
            totalDiscount() {
                return this.items.reduce((acc, item) => acc + (parseFloat(item.discount) || 0), 0);
            },
            
            grandTotal() {
                return this.subtotal() + this.totalTax() - this.totalDiscount();
            }
        }));
    });
</script>
@endsection
