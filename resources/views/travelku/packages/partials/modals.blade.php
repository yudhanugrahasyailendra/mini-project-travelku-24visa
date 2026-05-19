{{-- Modal: Tambah / Edit Paket --}}
<div x-show="modal.open" x-cloak class="fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-4" @keydown.escape.window="closeModal()">
    <div @click.outside="closeModal()" class="bg-white rounded-2xl w-full max-w-2xl shadow-2xl flex flex-col max-h-[90vh]">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between shrink-0">
            <div>
                <h2 class="font-bold text-slate-800" x-text="modal.mode === 'add' ? 'Tambah Paket Wisata' : 'Edit Paket Wisata'"></h2>
                <p class="text-xs text-slate-400" x-text="modal.mode === 'edit' ? (modal.pkg?.code || '') : 'Kode paket dibuat otomatis'"></p>
            </div>
            <button type="button" @click="closeModal()" class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="p-6 overflow-y-auto flex-1 space-y-4">
            <div>
                <label class="text-sm font-semibold text-slate-700 mb-1.5 block">Kategori *</label>
                <select :class="inputClass(errors.categoryId)" x-model="form.categoryId">
                    <option value="">Pilih kategori…</option>
                    <template x-for="c in categories" :key="c.id">
                        <option :value="c.id" x-text="c.name"></option>
                    </template>
                </select>
                <p x-show="errors.categoryId" x-cloak class="text-red-500 text-xs mt-1" x-text="errors.categoryId"></p>
            </div>
            <div>
                <label class="text-sm font-semibold text-slate-700 mb-1.5 block">Nama Paket *</label>
                <input type="text" placeholder="mis. Bali 4D3N – Tropical Paradise" :class="inputClass(errors.name)" x-model="form.name" />
                <p x-show="errors.name" x-cloak class="text-red-500 text-xs mt-1" x-text="errors.name"></p>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="sm:col-span-1">
                    <label class="text-sm font-semibold text-slate-700 mb-1.5 block">Destinasi *</label>
                    <input type="text" placeholder="Bali" :class="inputClass(errors.destination)" x-model="form.destination" />
                </div>
                <div>
                    <label class="text-sm font-semibold text-slate-700 mb-1.5 block">Hari *</label>
                    <input type="number" min="1" :class="inputClass(errors.durationDays)" x-model="form.durationDays" />
                </div>
                <div>
                    <label class="text-sm font-semibold text-slate-700 mb-1.5 block">Malam *</label>
                    <input type="number" min="0" :class="inputClass(errors.durationNights)" x-model="form.durationNights" />
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="text-sm font-semibold text-slate-700 mb-1.5 block">Harga Dasar (Rp) *</label>
                    <input type="number" min="10000" :class="inputClass(errors.basePrice)" x-model="form.basePrice" />
                </div>
                <div>
                    <label class="text-sm font-semibold text-slate-700 mb-1.5 block">Harga Weekend</label>
                    <input type="number" min="10000" :class="inputClass(errors.priceWeekend)" x-model="form.priceWeekend" placeholder="Opsional" />
                </div>
                <div>
                    <label class="text-sm font-semibold text-slate-700 mb-1.5 block">Harga Libur</label>
                    <input type="number" min="10000" :class="inputClass(errors.priceHoliday)" x-model="form.priceHoliday" placeholder="Opsional" />
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-semibold text-slate-700 mb-1.5 block">Min. Peserta</label>
                    <input type="number" min="1" :class="inputClass(errors.minParticipants)" x-model="form.minParticipants" />
                </div>
                <div>
                    <label class="text-sm font-semibold text-slate-700 mb-1.5 block">Maks. Peserta</label>
                    <input type="number" min="1" :class="inputClass(errors.maxParticipants)" x-model="form.maxParticipants" />
                </div>
            </div>
            <div>
                <label class="text-sm font-semibold text-slate-700 mb-1.5 block">Deskripsi Singkat</label>
                <textarea rows="2" :class="inputClass(errors.shortDesc) + ' resize-none'" x-model="form.shortDesc" placeholder="Ringkasan untuk kartu daftar paket"></textarea>
            </div>
            <div class="flex flex-wrap gap-4">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" class="w-4 h-4 rounded text-teal-600" x-model="form.isActive" />
                    <span class="text-sm text-slate-700">Aktif</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" class="w-4 h-4 rounded text-teal-600" x-model="form.isFeatured" />
                    <span class="text-sm text-slate-700">Unggulan</span>
                </label>
            </div>
        </div>

        <div class="px-6 pb-6 pt-2 flex gap-3 shrink-0">
            <button type="button" @click="closeModal()" class="flex-1 border border-slate-200 rounded-xl py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50">Batal</button>
            <button type="button" @click="handleSubmit()" :disabled="saving" class="flex-1 bg-teal-700 hover:bg-teal-800 disabled:opacity-60 text-white rounded-xl py-2.5 text-sm font-bold flex items-center justify-center gap-2">
                <svg x-show="saving" class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                <span x-text="saving ? 'Menyimpan…' : 'Simpan'"></span>
            </button>
        </div>
    </div>
</div>

{{-- Konfirmasi hapus --}}
<div x-show="delConfirm" x-cloak class="fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl w-full max-w-sm shadow-2xl p-6">
        <h3 class="font-bold text-slate-800 mb-2">Hapus Paket Wisata?</h3>
        <p class="text-sm text-slate-500 mb-1">Paket <span class="font-semibold text-slate-700" x-text="delConfirm?.name"></span> akan dihapus permanen.</p>
        <p x-show="delConfirm?.bookingsCount > 0" x-cloak class="text-sm text-amber-700 bg-amber-50 border border-amber-100 rounded-lg p-2.5 mt-2">
            Paket memiliki <span x-text="delConfirm?.bookingsCount"></span> pemesanan. Nonaktifkan sebagai alternatif.
        </p>
        <div class="flex gap-3 mt-5">
            <button type="button" @click="delConfirm = null" class="flex-1 border border-slate-200 rounded-xl py-2.5 text-sm font-semibold text-slate-600">Batal</button>
            <button type="button" @click="handleDelete(delConfirm.id)" :disabled="delConfirm?.bookingsCount > 0" class="flex-1 bg-red-600 text-white rounded-xl py-2.5 text-sm font-bold disabled:opacity-50">Hapus</button>
        </div>
    </div>
</div>
