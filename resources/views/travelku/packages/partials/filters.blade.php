<div class="bg-white rounded-2xl border border-slate-200 p-3 sm:p-4 shadow-sm">
    <div class="flex items-center gap-3 flex-wrap">
        <div class="flex-1 min-w-[13rem] relative">
            <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="search" class="w-full pl-9 pr-3 py-2.5 border border-slate-200 rounded-xl text-sm bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-teal-500 transition-all" placeholder="Cari kode, nama, destinasi..." x-model="search" aria-label="Cari paket wisata" />
        </div>
        <select class="border border-slate-200 rounded-xl px-3 py-2.5 text-sm bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-teal-500 min-w-[10rem]" x-model="categoryFilter">
            <option value="">Semua Kategori</option>
            <template x-for="c in categories" :key="c.id">
                <option :value="c.id" x-text="c.name"></option>
            </template>
        </select>
        <select class="border border-slate-200 rounded-xl px-3 py-2.5 text-sm bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-teal-500 min-w-[10rem]" x-model="statusFilter">
            <option value="">Semua Status</option>
            <option value="active">Aktif</option>
            <option value="inactive">Nonaktif</option>
        </select>
        <button type="button" x-show="hasFilter" @click="resetFilters()" x-cloak class="flex items-center gap-1.5 text-sm text-red-500 hover:text-red-700 font-medium">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            Reset
        </button>
    </div>
</div>
