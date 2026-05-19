<div class="flex flex-col flex-1 min-h-0 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
    {{-- Header --}}
    <div class="shrink-0 px-3 sm:px-4 py-3 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
        <h2 class="font-bold text-slate-700 text-sm">
            Daftar Pemesanan
            <span class="ml-2 bg-teal-100 text-teal-700 text-xs font-semibold px-2 py-0.5 rounded-full" x-text="filtered.length"></span>
        </h2>
        <div class="flex items-center gap-2 shrink-0">
            <button
                type="button"
                @click="exportCsv()"
                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs sm:text-sm font-medium border border-slate-200 text-slate-600 hover:bg-slate-50 hover:border-teal-200 hover:text-teal-700 transition-colors"
                title="Export daftar ke CSV"
            >
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Export CSV
            </button>
            <p class="text-[11px] sm:text-xs text-slate-400 hidden sm:block">Terbaru di atas</p>
        </div>
    </div>

    {{-- Mobile: card list --}}
    <div class="md:hidden flex-1 min-h-0 overflow-y-auto divide-y divide-slate-100">
        <template x-if="filtered.length === 0">
            <div class="px-4 py-12 text-center">
                <svg class="w-10 h-10 mx-auto mb-3 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <p class="text-slate-500 font-medium text-sm" x-text="search.trim() ? 'Tidak ada pemesanan yang cocok dengan pencarian' : 'Tidak ada pemesanan'"></p>
            </div>
        </template>
        <template x-for="b in filtered" :key="'m-' + b.id">
            <article class="p-3 sm:p-4 space-y-2.5">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0 flex-1">
                        <p class="font-semibold text-slate-800 truncate" x-text="b.name"></p>
                        <p class="text-slate-500 text-xs truncate" x-text="b.contact"></p>
                    </div>
                    <button
                        type="button"
                        @click="openStatusModal(b)"
                        class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-[11px] font-semibold border shrink-0"
                        :class="statusStyle[b.status].pill"
                    >
                        <span class="w-1.5 h-1.5 rounded-full" :class="statusStyle[b.status].dot"></span>
                        <span x-text="b.status"></span>
                    </button>
                </div>
                <div class="flex flex-wrap items-center gap-2 text-xs">
                    <span class="inline-flex items-center gap-1 bg-teal-50 text-teal-800 font-medium px-2 py-0.5 rounded-md border border-teal-100">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                        <span x-text="b.package"></span>
                    </span>
                    <span class="text-slate-500" x-text="fmtDate(b.departureDate)"></span>
                    <span class="text-slate-500" x-text="b.participants + ' org'"></span>
                </div>
                <div class="flex items-center justify-between gap-2">
                    <p class="font-bold text-slate-800 text-sm tabular-nums" x-text="fmtCurrency(b.participants * b.pricePerPerson)"></p>
                    <div class="flex items-center gap-1 shrink-0">
                        <button type="button" @click="openEdit(b)" class="p-2 rounded-lg border border-slate-200 text-slate-500 hover:bg-teal-50 hover:text-teal-700" title="Edit">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </button>
                        <button type="button" @click="delConfirm = b" class="p-2 rounded-lg border border-slate-200 text-slate-500 hover:bg-red-50 hover:text-red-600" title="Hapus">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                </div>
                <p x-show="b.notes" class="text-slate-400 text-[11px] italic truncate" x-text="b.notes"></p>
            </article>
        </template>
    </div>

    {{-- Desktop: table --}}
    <div class="hidden md:flex flex-1 min-h-0 flex-col">
        <div class="flex-1 min-h-0 overflow-auto">
            <table class="booking-table w-full text-sm min-w-[860px]">
                <colgroup>
                    <col style="width: 22%" />
                    <col style="width: 14%" />
                    <col style="width: 11%" />
                    <col style="width: 8%" />
                    <col style="width: 16%" />
                    <col style="width: 14%" />
                    <col style="width: 9%" />
                </colgroup>
                <thead class="sticky top-0 z-10 bg-slate-50 shadow-[0_1px_0_0_rgb(226_232_240)]">
                    <tr>
                        <th class="px-3 lg:px-4 py-2.5 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Pemesan</th>
                        <th class="px-3 lg:px-4 py-2.5 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Paket</th>
                        <th class="px-3 lg:px-4 py-2.5 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Berangkat</th>
                        <th class="px-3 lg:px-4 py-2.5 text-center text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Peserta</th>
                        <th class="px-3 lg:px-4 py-2.5 text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Total</th>
                        <th class="px-3 lg:px-4 py-2.5 text-center text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Status</th>
                        <th class="px-3 lg:px-4 py-2.5 text-center text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="filtered.length === 0">
                        <tr>
                            <td colspan="7" class="px-4 py-14 text-center">
                                <p class="text-slate-500 font-medium" x-text="search.trim() ? 'Tidak ada pemesanan yang cocok dengan pencarian' : 'Tidak ada pemesanan ditemukan'"></p>
                            </td>
                        </tr>
                    </template>
                    <template x-for="(b, idx) in filtered" :key="b.id">
                        <tr
                            class="border-b border-slate-100 hover:bg-teal-50/50 transition-colors"
                            :class="idx % 2 === 0 ? 'bg-white' : 'bg-slate-50/40'"
                        >
                            <td class="px-3 lg:px-4 py-2.5 max-w-0">
                                <p class="font-semibold text-slate-800 truncate" x-text="b.name"></p>
                                <p class="text-slate-500 text-xs truncate mt-0.5" x-text="b.contact"></p>
                                <p x-show="b.notes" class="text-slate-400 text-[11px] mt-0.5 truncate italic" :title="b.notes" x-text="b.notes"></p>
                            </td>
                            <td class="px-3 lg:px-4 py-2.5">
                                <span class="inline-flex items-center gap-1 bg-teal-50 text-teal-800 font-medium text-xs px-2 py-1 rounded-md border border-teal-100 max-w-full">
                                    <svg class="w-3 h-3 shrink-0 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    <span class="truncate" x-text="b.package"></span>
                                </span>
                            </td>
                            <td class="px-3 lg:px-4 py-2.5 whitespace-nowrap">
                                <p class="text-slate-700 font-medium text-sm" x-text="fmtDate(b.departureDate)"></p>
                            </td>
                            <td class="px-3 lg:px-4 py-2.5 text-center whitespace-nowrap">
                                <span class="text-slate-600 font-medium text-sm" x-text="b.participants"></span>
                            </td>
                            <td class="px-3 lg:px-4 py-2.5 text-right whitespace-nowrap tabular-nums">
                                <p class="font-bold text-slate-800" x-text="fmtCurrency(b.participants * b.pricePerPerson)"></p>
                                <p class="text-slate-400 text-[11px]" x-text="fmtCurrency(b.pricePerPerson) + '/org'"></p>
                            </td>
                            <td class="px-3 lg:px-4 py-2.5 text-center">
                                <button
                                    type="button"
                                    @click="openStatusModal(b)"
                                    class="inline-flex items-center justify-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold border whitespace-nowrap min-w-[7.5rem]"
                                    :class="[
                                        statusStyle[b.status].pill,
                                        transitionsFor(b.status).length > 0 ? 'cursor-pointer hover:opacity-80' : 'cursor-default opacity-90',
                                    ]"
                                    :title="transitionsFor(b.status).length === 0 ? 'Status final' : 'Klik untuk ubah status'"
                                >
                                    <span class="w-1.5 h-1.5 rounded-full shrink-0" :class="statusStyle[b.status].dot"></span>
                                    <span x-text="b.status"></span>
                                    <svg x-show="transitionsFor(b.status).length > 0" class="w-3 h-3 shrink-0 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                            </td>
                            <td class="px-3 lg:px-4 py-2.5">
                                <div class="flex items-center justify-center gap-1">
                                    <button type="button" @click="openEdit(b)" class="p-2 rounded-lg border border-slate-200 text-slate-500 hover:text-teal-700 hover:border-teal-200 hover:bg-teal-50 transition-colors" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    <button type="button" @click="delConfirm = b" class="p-2 rounded-lg border border-slate-200 text-slate-500 hover:text-red-600 hover:border-red-200 hover:bg-red-50 transition-colors" title="Hapus">
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

    {{-- Footer (selalu di bawah kartu) --}}
    <div
        class="shrink-0 px-3 sm:px-4 py-2.5 border-t border-slate-200 bg-slate-50 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-1 text-[11px] sm:text-xs text-slate-500"
    >
        <span x-text="'Menampilkan ' + filtered.length + ' dari ' + bookings.length + ' pemesanan'"></span>
        <span class="whitespace-nowrap">
            Pendapatan terfilter:
            <span class="font-bold text-emerald-600 tabular-nums" x-text="fmtCurrency(summary.revenue)"></span>
        </span>
    </div>
</div>
