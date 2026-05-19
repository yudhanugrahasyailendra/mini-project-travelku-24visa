<div class="grid grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-3">
    <div class="bg-white rounded-2xl border border-slate-200 p-3 sm:p-4 flex items-start gap-3 shadow-sm">
        <div class="text-teal-600 bg-teal-50 w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
        </div>
        <div class="min-w-0">
            <p class="text-xs text-slate-500 font-medium truncate">Total Paket</p>
            <p class="text-lg sm:text-xl font-extrabold text-slate-800 leading-tight" x-text="summary.total"></p>
        </div>
    </div>
    <div class="bg-white rounded-2xl border border-slate-200 p-3 sm:p-4 flex items-start gap-3 shadow-sm">
        <div class="text-emerald-600 bg-emerald-50 w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div class="min-w-0">
            <p class="text-xs text-slate-500 font-medium truncate">Aktif</p>
            <p class="text-lg sm:text-xl font-extrabold text-slate-800 leading-tight" x-text="summary.active"></p>
        </div>
    </div>
    <div class="bg-white rounded-2xl border border-slate-200 p-3 sm:p-4 flex items-start gap-3 shadow-sm">
        <div class="text-slate-500 bg-slate-100 w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
        </div>
        <div class="min-w-0">
            <p class="text-xs text-slate-500 font-medium truncate">Nonaktif</p>
            <p class="text-lg sm:text-xl font-extrabold text-slate-800 leading-tight" x-text="summary.inactive"></p>
        </div>
    </div>
    <div class="bg-white rounded-2xl border border-slate-200 p-3 sm:p-4 flex items-start gap-3 shadow-sm">
        <div class="text-sky-600 bg-sky-50 w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
        </div>
        <div class="min-w-0">
            <p class="text-xs text-slate-500 font-medium truncate">Total Pemesanan</p>
            <p class="text-lg sm:text-xl font-extrabold text-slate-800 leading-tight" x-text="summary.bookings"></p>
        </div>
    </div>
</div>
