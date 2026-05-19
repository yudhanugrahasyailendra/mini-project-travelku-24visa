<div class="grid grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-3">
    <div class="bg-white rounded-2xl border border-slate-200 p-3 sm:p-4 flex items-start gap-3 shadow-sm">
        <div class="text-teal-600 bg-teal-50 w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
        </div>
        <div class="min-w-0">
            <p class="text-xs text-slate-500 font-medium truncate">Total Pemesanan</p>
            <p class="text-lg sm:text-xl font-extrabold text-slate-800 leading-tight" x-text="summary.total"></p>
            <p class="text-[11px] text-slate-400 truncate" x-text="hasFilter ? 'hasil filter aktif' : 'semua pemesanan'"></p>
        </div>
    </div>
    <div class="bg-white rounded-2xl border border-slate-200 p-3 sm:p-4 flex items-start gap-3 shadow-sm">
        <div class="text-emerald-600 bg-emerald-50 w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div class="min-w-0">
            <p class="text-xs text-slate-500 font-medium truncate">Estimasi Pendapatan</p>
            <p class="text-sm sm:text-xl font-extrabold text-slate-800 leading-tight truncate" x-text="fmtCurrency(summary.revenue)"></p>
            <p class="text-[11px] text-slate-400 truncate">Dikonfirmasi + Selesai</p>
        </div>
    </div>
    <div class="bg-white rounded-2xl border border-slate-200 p-3 sm:p-4 flex items-start gap-3 shadow-sm">
        <div class="text-amber-600 bg-amber-50 w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div class="min-w-0">
            <p class="text-xs text-slate-500 font-medium truncate">Menunggu Konfirmasi</p>
            <p class="text-lg sm:text-xl font-extrabold text-slate-800 leading-tight" x-text="summary.byStatus['Menunggu']"></p>
            <p class="text-[11px] text-slate-400 truncate">perlu tindak lanjut</p>
        </div>
    </div>
    <div class="bg-white rounded-2xl border border-slate-200 p-3 sm:p-4 flex items-start gap-3 shadow-sm">
        <div class="text-sky-600 bg-sky-50 w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div class="min-w-0">
            <p class="text-xs text-slate-500 font-medium truncate">Sudah Dikonfirmasi</p>
            <p class="text-lg sm:text-xl font-extrabold text-slate-800 leading-tight" x-text="summary.byStatus['Dikonfirmasi']"></p>
            <p class="text-[11px] text-slate-400 truncate">siap berangkat</p>
        </div>
    </div>
</div>


