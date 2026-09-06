<div x-data="{ show: false, message: '', type: 'success' }"
     @notify.window="show = true; message = $event.detail.message; type = $event.detail.type || 'success'; setTimeout(() => show = false, 3000)"
     class="fixed bottom-4 right-4 z-50 transition-all duration-300 transform"
     x-show="show"
     x-transition:enter="ease-out duration-300"
     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
     x-transition:leave="ease-in duration-200"
     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
     style="display: none;">
     
    <div class="rounded-lg shadow-lg overflow-hidden border"
         :class="{
            'bg-green-50 border-green-200': type === 'success',
            'bg-red-50 border-red-200': type === 'error',
            'bg-amber-50 border-amber-200': type === 'warning',
            'bg-blue-50 border-blue-200': type === 'info'
         }">
        <div class="p-4 flex items-center">
            <!-- Success Icon -->
            <svg x-show="type === 'success'" class="w-6 h-6 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <!-- Error Icon -->
            <svg x-show="type === 'error'" class="w-6 h-6 text-red-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <!-- Warning Icon -->
            <svg x-show="type === 'warning'" class="w-6 h-6 text-amber-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            <!-- Info Icon -->
            <svg x-show="type === 'info'" class="w-6 h-6 text-blue-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            
            <p class="text-sm font-medium" 
               :class="{
                   'text-green-800': type === 'success',
                   'text-red-800': type === 'error',
                   'text-amber-800': type === 'warning',
                   'text-blue-800': type === 'info'
               }" x-text="message"></p>
               
            <button @click="show = false" class="ml-4 text-slate-400 hover:text-slate-600 focus:outline-none">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
    </div>
</div>

@if(session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                window.dispatchEvent(new CustomEvent('notify', { detail: { message: "{{ session('success') }}", type: 'success' }}));
            }, 100);
        });
    </script>
@endif

@if(session('error'))
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                window.dispatchEvent(new CustomEvent('notify', { detail: { message: "{{ session('error') }}", type: 'error' }}));
            }, 100);
        });
    </script>
@endif
