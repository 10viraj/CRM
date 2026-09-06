@extends('layouts.app')

@section('title', 'Add Product - SmartCRM')

@section('content')
<div class="mb-8">
    <a href="{{ route('products.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800 flex items-center mb-4">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        Back to Products
    </a>
    <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Add New Product</h1>
</div>

<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden max-w-3xl">
    <form action="{{ route('products.store') }}" method="POST" class="p-8">
        @csrf
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div class="md:col-span-2">
                <label for="name" class="block text-sm font-medium text-slate-700 mb-1">Product Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" class="w-full rounded-lg border-slate-300 border px-4 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition @error('name') border-red-500 @enderror">
                @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            
            <div>
                <label for="product_category_id" class="block text-sm font-medium text-slate-700 mb-1">Category <span class="text-red-500">*</span></label>
                <select name="product_category_id" id="product_category_id" class="w-full rounded-lg border-slate-300 border px-4 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition bg-white @error('product_category_id') border-red-500 @enderror">
                    <option value="">Select Category...</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('product_category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
                @error('product_category_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="price" class="block text-sm font-medium text-slate-700 mb-1">Base Price <span class="text-red-500">*</span></label>
                <div class="relative">
                    <span class="absolute left-3 top-2 text-slate-500">$</span>
                    <input type="number" step="0.01" name="price" id="price" value="{{ old('price') }}" class="w-full rounded-lg border-slate-300 border pl-8 pr-4 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition @error('price') border-red-500 @enderror">
                </div>
                @error('price') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="sku" class="block text-sm font-medium text-slate-700 mb-1">SKU</label>
                <input type="text" name="sku" id="sku" value="{{ old('sku') }}" class="w-full rounded-lg border-slate-300 border px-4 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition @error('sku') border-red-500 @enderror">
                @error('sku') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            
            <div>
                <label for="status" class="block text-sm font-medium text-slate-700 mb-1">Status <span class="text-red-500">*</span></label>
                <select name="status" id="status" class="w-full rounded-lg border-slate-300 border px-4 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition bg-white @error('status') border-red-500 @enderror">
                    <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
                @error('status') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="md:col-span-2">
                <label for="description" class="block text-sm font-medium text-slate-700 mb-1">Description</label>
                <textarea name="description" id="description" rows="3" class="w-full rounded-lg border-slate-300 border px-4 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition">{{ old('description') }}</textarea>
                @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="flex justify-end space-x-3 pt-6 border-t border-slate-100">
            <a href="{{ route('products.index') }}" class="px-5 py-2.5 rounded-lg text-sm font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 transition">Cancel</a>
            <button type="submit" class="px-5 py-2.5 rounded-lg text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm transition">Save Product</button>
        </div>
    </form>
</div>
@endsection
