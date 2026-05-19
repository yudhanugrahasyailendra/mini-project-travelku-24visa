<div class="flex flex-col flex-1 min-h-0 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="shrink-0 px-3 sm:px-4 py-3 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
        <h2 class="font-bold text-slate-700 text-sm">
            Daftar Paket Wisata
            <span class="ml-2 bg-teal-100 text-teal-700 text-xs font-semibold px-2 py-0.5 rounded-full" x-text="filtered.length"></span>
        </h2>
        <p class="text-[11px] sm:text-xs text-slate-400">Diurutkan berdasarkan nama</p>
    </div>

    <div class="md:hidden flex-1 min-h-0 overflow-y-auto divide-y divide-slate-100">
        <template x-if="filtered.length === 0">
            <div class="px-4 py-12 text-center">
                <p class="text-slate-500 font-medium text-sm" x-text="hasFilter ? 'Tidak ada paket yang cocok' : 'Belum ada paket wisata'"></p>
            </div>
        </template>
        <template x-for="p in filtered" :key="'m-' + p.id">
            <article class="p-3 sm:p-4 space-y-2.5">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0 flex-1">
                        <p class="font-semibold text-slate-800 truncate" x-text="p.name"></p>
                        <p class="text-slate-500 text-xs truncate" x-text="p.code + ' · ' + (p.destination || '-') + ' · ' + p.durasi"></p>
                        <p class="text-teal-700 text-xs font-semibold mt-0.5" x-text="fmtCurrency(p.basePrice) + '/org'"></p>
                    </div>
                    <span
                        class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-[11px] font-semibold border shrink-0"
                        :class="p.isActive ? 'bg-emerald-100 text-emerald-800 border-emerald-200' : 'bg-slate-100 text-slate-600 border-slate-200'"
                        x-text="p.isActive ? 'Aktif' : 'Nonaktif'"
                    ></span>
                </div>
                <p class="text-xs text-slate-500"><span x-text="p.bookingsCount"></span> pemesanan terkait</p>
                <div class="flex items-center gap-1">
                    <button type="button" @click="toggleActive(p)" class="flex-1 py-2 rounded-lg border border-slate-200 text-xs font-medium text-slate-600 hover:bg-slate-50" x-text="p.isActive ? 'Nonaktifkan' : 'Aktifkan'"></button>
                    <button type="button" @click="openEdit(p)" class="p-2 rounded-lg border border-slate-200 text-slate-500 hover:bg-teal-50 hover:text-teal-700" title="Edit">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </button>
                    <button type="button" @click="delConfirm = p" class="p-2 rounded-lg border border-slate-200 text-slate-500 hover:bg-red-50 hover:text-red-600" title="Hapus">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
            </article>
        </template>
    </div>

    <div class="hidden md:flex flex-1 min-h-0 flex-col">
        <div class="flex-1 min-h-0 overflow-auto">
            <table class="w-full text-sm min-w-[720px]">
                <thead class="sticky top-0 z-10 bg-slate-50 shadow-[0_1px_0_0_rgb(226_232_240)]">
                    <tr>
                        <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Kode / Nama</th>
                        <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Kategori</th>
                        <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Destinasi</th>
                        <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Durasi</th>
                        <th class="px-4 py-2.5 text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Harga Dasar</th>
                        <th class="px-4 py-2.5 text-center text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Pemesanan</th>
                        <th class="px-4 py-2.5 text-center text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Status</th>
                        <th class="px-4 py-2.5 text-center text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="filtered.length === 0">
                        <tr>
                            <td colspan="8" class="px-4 py-14 text-center text-slate-500 font-medium" x-text="hasFilter ? 'Tidak ada paket yang cocok' : 'Belum ada paket wisata'"></td>
                        </tr>
                    </template>
                    <template x-for="(p, idx) in filtered" :key="p.id">
                        <tr class="border-b border-slate-100 hover:bg-teal-50/50 transition-colors" :class="idx % 2 === 0 ? 'bg-white' : 'bg-slate-50/40'">
                            <td class="px-4 py-2.5">
                                <p class="font-semibold text-slate-800" x-text="p.name"></p>
                                <p class="text-xs text-slate-400 font-mono" x-text="p.code"></p>
                            </td>
                            <td class="px-4 py-2.5 text-slate-600 text-xs" x-text="p.categoryName || '-'"></td>
                            <td class="px-4 py-2.5 text-slate-600" x-text="p.destination || '-'"></td>
                            <td class="px-4 py-2.5 text-slate-600" x-text="p.durasi"></td>
                            <td class="px-4 py-2.5 text-right font-semibold text-teal-700" x-text="fmtCurrency(p.basePrice)"></td>
                            <td class="px-4 py-2.5 text-center text-slate-600" x-text="p.bookingsCount"></td>
                            <td class="px-4 py-2.5 text-center">
                                <button
                                    type="button"
                                    @click="toggleActive(p)"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold border"
                                    :class="p.isActive ? 'bg-emerald-100 text-emerald-800 border-emerald-200 hover:opacity-80' : 'bg-slate-100 text-slate-600 border-slate-200 hover:opacity-80'"
                                >
                                    <span class="w-1.5 h-1.5 rounded-full" :class="p.isActive ? 'bg-emerald-500' : 'bg-slate-400'"></span>
                                    <span x-text="p.isActive ? 'Aktif' : 'Nonaktif'"></span>
                                </button>
                            </td>
                            <td class="px-4 py-2.5">
                                <div class="flex items-center justify-center gap-1">
                                    <button type="button" @click="openEdit(p)" class="p-2 rounded-lg border border-slate-200 text-slate-500 hover:text-teal-700 hover:border-teal-200 hover:bg-teal-50" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    <button type="button" @click="delConfirm = p" class="p-2 rounded-lg border border-slate-200 text-slate-500 hover:text-red-600 hover:border-red-200 hover:bg-red-50" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <div class="shrink-0 px-3 sm:px-4 py-2.5 border-t border-slate-200 bg-slate-50 text-[11px] sm:text-xs text-slate-500">
        <span x-text="'Menampilkan ' + filtered.length + ' dari ' + packages.length + ' paket'"></span>
    </div>
</div>
