@extends('layouts.app')

@section('title', 'Attachments - SmartCRM')

@section('content')
<div class="mb-8">
    <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Attachments</h1>
    <p class="text-slate-500 mt-1 text-sm font-medium">Global document and file manager.</p>
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
                    <th class="p-4">File Name</th>
                    <th class="p-4">Attached To</th>
                    <th class="p-4">Uploaded By</th>
                    <th class="p-4">Size</th>
                    <th class="p-4 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm">
                @forelse($attachments as $attachment)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="p-4">
                            <div class="flex items-center">
                                <svg class="w-8 h-8 text-slate-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                <div>
                                    <div class="font-bold text-indigo-600">{{ $attachment->file_name }}</div>
                                    <div class="text-xs text-slate-500 uppercase">{{ $attachment->file_type }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="p-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-slate-100 text-slate-700">
                                {{ class_basename($attachment->attachable_type) }} #{{ $attachment->attachable_id }}
                            </span>
                        </td>
                        <td class="p-4 font-medium text-slate-900">
                            {{ $attachment->user->name ?? 'System' }}
                            <div class="text-xs text-slate-500 font-normal">{{ $attachment->created_at->format('M d, Y h:i A') }}</div>
                        </td>
                        <td class="p-4 text-slate-600">
                            {{ round($attachment->file_size / 1024, 2) }} KB
                        </td>
                        <td class="p-4 text-center space-x-3">
                            <a href="{{ route('attachments.download', $attachment) }}" class="text-indigo-600 hover:text-indigo-900 font-medium text-xs">Download</a>
                            <form action="{{ route('attachments.destroy', $attachment) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this file?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700 font-medium text-xs">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="p-12 text-center text-slate-500">
                            <p class="text-base font-medium text-slate-900">No attachments found</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($attachments->hasPages())
        <div class="p-4 border-t border-slate-200 bg-slate-50">
            {{ $attachments->links() }}
        </div>
    @endif
</div>
@endsection
