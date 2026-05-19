<div class="bg-white rounded-2xl border border-slate-200 p-3 sm:p-4 shadow-sm space-y-3">
    <div class="flex items-center gap-3 flex-wrap">
        <div class="flex-1 min-w-[13rem] relative">
            <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="search" class="w-full pl-9 pr-3 py-2.5 border border-slate-200 rounded-xl text-sm bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-teal-500 transition-all" placeholder="Cari nama pemesan atau kontak..." x-model="search" aria-label="Cari nama pemesan atau kontak" />
        </div>
        <button type="button" @click="showFilters = !showFilters" class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-medium border transition-colors" :class="showFilters ? 'bg-teal-700 text-white border-teal-700' : 'border-slate-200 text-slate-600 hover:bg-slate-50'">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
            Filter Lanjutan
            <span x-show="hasFilter && !showFilters" x-cloak class="w-5 h-5 bg-red-500 text-white text-[10px] rounded-full flex items-center justify-center font-bold" x-text="filterCount"></span>
        </button>
        <button type="button" x-show="hasFilter" @click="resetAll()" x-cloak class="flex items-center gap-1.5 text-sm text-red-500 hover:text-red-700 font-medium">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            Reset
        </button>
    </div>
    <div x-show="showFilters" x-cloak class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 pt-3 border-t border-slate-100">
        <div>
            <label class="text-xs text-slate-500 font-medium mb-1.5 block">Status</label>
            <select class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-teal-500" x-model="filters.status">
                <option value="">Semua Status</option>
                <template x-for="s in statuses" :key="s"><option :value="s" x-text="s"></option></template>
            </select>
        </div>
        <div>
            <label class="text-xs text-slate-500 font-medium mb-1.5 block">Paket Wisata</label>
            <select class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-teal-500" x-model="filters.packageId">
                <option value="">Semua Paket</option>
                <template x-for="p in packages" :key="p.id"><option :value="p.id" x-text="p.code + ' — ' + p.name"></option></template>
            </select>
        </div>
        <div>
            <label class="text-xs text-slate-500 font-medium mb-1.5 block">Berangkat Dari</label>
            <input type="date" class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-teal-500" x-model="filters.dateFrom" />
        </div>
        <div>
            <label class="text-xs text-slate-500 font-medium mb-1.5 block">Berangkat Sampai</label>
            <input type="date" class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-teal-500" x-model="filters.dateTo" />
        </div>
    </div>
</div>


