@extends('layouts.app')

@section('title', 'Products - SmartCRM')

@section('content')
<div class="mb-8 flex justify-between items-center">
    <div>
        <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Products & Services</h1>
        <p class="text-slate-500 mt-1 text-sm font-medium">Manage your catalog of items available for quotation.</p>
    </div>
    <div class="flex space-x-3">
        <a href="{{ route('products.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-medium shadow-sm transition">
            + Add Product
        </a>
    </div>
</div>

@if(session('success'))
    <div class="mb-6 bg-green-50 text-green-700 p-4 rounded-lg border border-green-200">
        {{ session('success') }}
    </div>
@endif

<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <!-- Filters -->
    <div class="p-4 border-b border-slate-200 bg-slate-50 flex flex-wrap gap-4 items-center justify-between">
        <form method="GET" action="{{ route('products.index') }}" class="flex flex-wrap gap-3 items-center w-full">
            <div class="relative flex-1 max-w-sm">
                <svg class="w-4 h-4 absolute left-3 top-2.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search products or SKU..." class="w-full pl-9 pr-4 py-2 border border-slate-300 rounded-lg text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none">
            </div>
            
            <select name="category_id" onchange="this.form.submit()" class="rounded-lg border-slate-300 border px-3 py-2 text-sm focus:border-indigo-500 outline-none text-slate-700">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>

            <select name="status" onchange="this.form.submit()" class="rounded-lg border-slate-300 border px-3 py-2 text-sm focus:border-indigo-500 outline-none text-slate-700">
                <option value="">All Statuses</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
            
            @if(request()->hasAny(['search', 'category_id', 'status']))
                <a href="{{ route('products.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium ml-2">Clear Filters</a>
            @endif
        </form>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-white border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                    <th class="p-4">Product Name</th>
                    <th class="p-4">SKU</th>
                    <th class="p-4">Category</th>
                    <th class="p-4">Price</th>
                    <th class="p-4">Status</th>
                    <th class="p-4 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm">
                @forelse($products as $product)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="p-4">
                            <div class="font-semibold text-slate-900">{{ $product->name }}</div>
                            <div class="text-slate-500 text-xs mt-0.5 truncate max-w-xs">{{ $product->description }}</div>
                        </td>
                        <td class="p-4 text-slate-600 font-medium">
                            {{ $product->sku ?? 'N/A' }}
                        </td>
                        <td class="p-4 text-slate-600">
                            {{ $product->category->name ?? 'Uncategorized' }}
                        </td>
                        <td class="p-4 font-bold text-slate-800">
                            ${{ number_format($product->price, 2) }}
                        </td>
                        <td class="p-4">
                            @if($product->status == 'active')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-700">Active</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-700">Inactive</span>
                            @endif
                        </td>
                        <td class="p-4 text-center space-x-2">
                            <a href="#" class="text-slate-600 hover:text-slate-900 font-medium text-xs">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-12 text-center text-slate-500">
                            <p class="text-base font-medium text-slate-900">No products found</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($products->hasPages())
        <div class="p-4 border-t border-slate-200 bg-slate-50">
            {{ $products->links() }}
        </div>
    @endif
</div>
@endsection
