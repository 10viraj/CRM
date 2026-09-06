<div x-data="{ 
        modalOpen: false, 
        title: '', 
        message: '', 
        confirmText: 'Confirm', 
        confirmColor: 'bg-red-600 hover:bg-red-700',
        actionUrl: '',
        method: 'POST'
    }" 
     @open-modal.window="
        modalOpen = true; 
        title = $event.detail.title || 'Are you sure?'; 
        message = $event.detail.message || 'This action cannot be undone.';
        confirmText = $event.detail.confirmText || 'Delete';
        confirmColor = $event.detail.confirmColor || 'bg-red-600 hover:bg-red-700';
        actionUrl = $event.detail.actionUrl;
        method = $event.detail.method || 'POST';
     " 
     class="relative z-50" 
     aria-labelledby="modal-title" 
     role="dialog" 
     aria-modal="true">
     
    <div x-show="modalOpen" x-transition.opacity class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity" style="display: none;"></div>

    <div x-show="modalOpen" style="display: none;" class="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div x-show="modalOpen" x-transition.scale.origin.bottom class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                            <h3 class="text-lg font-bold leading-6 text-slate-900" id="modal-title" x-text="title"></h3>
                            <div class="mt-2">
                                <p class="text-sm text-slate-500" x-text="message"></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-slate-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                    <form :action="actionUrl" method="POST" class="inline">
                        @csrf
                        <template x-if="method === 'DELETE'">
                            <input type="hidden" name="_method" value="DELETE">
                        </template>
                        <template x-if="method === 'PUT'">
                            <input type="hidden" name="_method" value="PUT">
                        </template>
                        <template x-if="method === 'PATCH'">
                            <input type="hidden" name="_method" value="PATCH">
                        </template>
                        
                        <button type="submit" :class="confirmColor" class="inline-flex w-full justify-center rounded-lg px-3 py-2 text-sm font-medium text-white shadow-sm sm:ml-3 sm:w-auto transition" x-text="confirmText"></button>
                    </form>
                    <button type="button" @click="modalOpen = false" class="mt-3 inline-flex w-full justify-center rounded-lg bg-white px-3 py-2 text-sm font-medium text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto transition">Cancel</button>
                </div>
            </div>
        </div>
    </div>
</div>
