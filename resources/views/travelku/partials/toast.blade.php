<div
    x-show="toast"
    x-transition
    x-cloak
    class="fixed top-3 right-3 sm:top-4 sm:right-4 z-[9999] flex items-center gap-3 px-4 py-3 rounded-xl shadow-lg text-sm font-medium max-w-[calc(100vw-1.5rem)]"
    :class="toast?.type === 'success' ? 'bg-emerald-600 text-white' : 'bg-slate-700 text-white'"
>
    <svg x-show="toast?.type === 'success'" class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    <svg x-show="toast && toast.type !== 'success'" class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
    <span x-text="toast?.msg"></span>
</div>
