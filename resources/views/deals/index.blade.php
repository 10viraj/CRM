@extends('layouts.app')

@section('title', 'Deals Pipeline - SmartCRM')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Sales Pipeline</h1>
        <p class="text-slate-500 mt-1 text-sm font-medium">Manage your deals through the sales process.</p>
    </div>
    <div class="flex space-x-3">
        <a href="#" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-medium shadow-sm transition">
            + Add Deal
        </a>
    </div>
</div>

<!-- Alpine + SortableJS Kanban Board -->
<div 
    x-data="kanbanBoard()" 
    class="flex space-x-4 overflow-x-auto pb-8 pt-2 hide-scrollbar h-[calc(100vh-200px)]"
>
    @foreach($stages as $stage)
        <div class="flex-shrink-0 w-80 flex flex-col bg-slate-50/80 rounded-2xl border border-slate-200">
            <!-- Column Header -->
            <div class="p-4 border-b border-slate-200 flex justify-between items-center bg-white rounded-t-2xl">
                <div class="flex items-center space-x-2">
                    <div class="w-3 h-3 rounded-full bg-{{ $stage->color }}-500"></div>
                    <h3 class="font-semibold text-slate-800">{{ $stage->name }}</h3>
                </div>
                @php
                    $stageDeals = $deals->get($stage->id) ?? collect();
                    $stageTotal = $stageDeals->sum('value');
                @endphp
                <div class="flex flex-col items-end">
                    <span class="text-xs font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded-full">{{ $stageDeals->count() }}</span>
                    <span class="text-xs text-slate-400 font-medium mt-1">${{ number_format($stageTotal) }}</span>
                </div>
            </div>

            <!-- Draggable Container -->
            <div 
                class="p-3 flex-1 overflow-y-auto space-y-3 sortable-list min-h-[150px]"
                data-stage-id="{{ $stage->id }}"
                x-ref="list-{{ $stage->id }}"
            >
                @foreach($stageDeals as $deal)
                    <!-- Deal Card -->
                    <div 
                        class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm cursor-grab active:cursor-grabbing hover:border-indigo-300 transition group"
                        data-deal-id="{{ $deal->id }}"
                    >
                        <div class="flex justify-between items-start mb-2">
                            <h4 class="font-bold text-slate-800 text-sm leading-tight group-hover:text-indigo-600 transition">
                                <a href="{{ route('deals.show', $deal) }}">{{ $deal->name }}</a>
                            </h4>
                            <div class="relative group/menu">
                                <button class="text-slate-400 hover:text-slate-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path></svg>
                                </button>
                                <!-- Dropdown (hidden for now) -->
                            </div>
                        </div>
                        
                        <div class="text-xs font-medium text-slate-500 mb-3 flex items-center">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                            {{ $deal->lead->company ?? ($deal->lead->first_name . ' ' . $deal->lead->last_name) }}
                        </div>

                        <div class="flex items-center justify-between border-t border-slate-100 pt-3 mt-1">
                            <div class="text-sm font-bold text-slate-800">
                                ${{ number_format($deal->value) }}
                            </div>
                            <div class="flex items-center text-xs font-medium text-slate-400">
                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                {{ $deal->close_date ? $deal->close_date->format('M j') : 'No date' }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</div>

<!-- Load SortableJS from CDN -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('kanbanBoard', () => ({
            init() {
                const lists = document.querySelectorAll('.sortable-list');
                
                lists.forEach(list => {
                    new Sortable(list, {
                        group: 'kanban', // set both lists to same group
                        animation: 150,
                        ghostClass: 'bg-indigo-50',
                        onEnd: (evt) => {
                            if (evt.from === evt.to) return; // No change in column

                            const dealId = evt.item.dataset.dealId;
                            const newStageId = evt.to.dataset.stageId;

                            // Send AJAX request to Laravel to update the stage
                            fetch(`/deals/${dealId}/stage`, {
                                method: 'PATCH',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({
                                    deal_stage_id: newStageId
                                })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if(!data.success) {
                                    alert('Failed to update deal stage');
                                    // Revert if failed
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                            });
                        }
                    });
                });
            }
        }));
    });
</script>

<style>
    /* Hide scrollbar for Chrome, Safari and Opera */
    .hide-scrollbar::-webkit-scrollbar {
        display: none;
    }
    /* Hide scrollbar for IE, Edge and Firefox */
    .hide-scrollbar {
        -ms-overflow-style: none;  /* IE and Edge */
        scrollbar-width: none;  /* Firefox */
    }
</style>
@endsection
