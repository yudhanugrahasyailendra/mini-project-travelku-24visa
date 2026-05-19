{{-- Modal: Tambah / Edit --}}
<div x-show="modal.open" x-cloak class="fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-4" @keydown.escape.window="closeModal()">
    <div @click.outside="closeModal()" class="bg-white rounded-2xl w-full max-w-lg shadow-2xl flex flex-col max-h-[90vh]">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between flex-shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center" :class="modal.mode === 'add' ? 'bg-teal-100' : 'bg-amber-100'">
                    <svg x-show="modal.mode === 'add'" class="w-4 h-4 text-teal-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <svg x-show="modal.mode === 'edit'" class="w-4 h-4 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </div>
                <div>
                    <h2 class="font-bold text-slate-800" x-text="modal.mode === 'add' ? 'Tambah Pemesanan Baru' : 'Edit Pemesanan'"></h2>
                    <p class="text-xs text-slate-400" x-text="modal.mode === 'add' ? 'Status awal: Menunggu' : 'ID: ' + (modal.booking?.id || '')"></p>
                </div>
            </div>
            <button type="button" @click="closeModal()" class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-400 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="p-6 overflow-y-auto flex-1 space-y-4">
            <div x-show="errors.general" x-cloak class="bg-red-50 border border-red-200 rounded-xl p-3 flex items-center gap-2 text-red-700 text-sm">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span x-text="errors.general"></span>
            </div>
            <div x-show="validating" x-cloak class="bg-teal-50 border border-teal-200 rounded-xl p-3 flex items-center gap-2 text-teal-700 text-sm">
                <svg class="w-4 h-4 animate-spin flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Memvalidasi data di server……
            </div>

            <div>
                <label class="text-sm font-semibold text-slate-700 mb-1.5 block">Nama Pemesan *</label>
                <input type="text" placeholder="Nama lengkap pemesan" :class="inputClass(errors.name)" x-model="form.name" />
                <p x-show="errors.name" x-cloak class="text-red-500 text-xs mt-1 flex items-center gap-1"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg><span x-text="errors.name"></span></p>
            </div>
            <div>
                <label class="text-sm font-semibold text-slate-700 mb-1.5 block">Kontak (HP / Email) *</label>
                <input type="text" placeholder="08xxxxxxxxxx atau nama@email.com" :class="inputClass(errors.contact)" x-model="form.contact" />
                <p x-show="errors.contact" x-cloak class="text-red-500 text-xs mt-1 flex items-center gap-1"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg><span x-text="errors.contact"></span></p>
            </div>
            <div>
                <label class="text-sm font-semibold text-slate-700 mb-1.5 block">Paket Wisata *</label>
                <select :class="inputClass(errors.package)" x-model="form.package">
                    <option value="">Pilih paket wisata…</option>
                    <template x-for="p in packages" :key="p"><option :value="p" x-text="p"></option></template>
                </select>
                <p x-show="errors.package" x-cloak class="text-red-500 text-xs mt-1 flex items-center gap-1"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg><span x-text="errors.package"></span></p>
            </div>
            <div>
                <label class="text-sm font-semibold text-slate-700 mb-1.5 block">Tanggal Keberangkatan *</label>
                <input type="date" :class="inputClass(errors.departureDate)" x-model="form.departureDate" />
                <p x-show="errors.departureDate" x-cloak class="text-red-500 text-xs mt-1 flex items-center gap-1"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg><span x-text="errors.departureDate"></span></p>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-semibold text-slate-700 mb-1.5 block">Jumlah Peserta *</label>
                    <input type="number" min="1" max="100" placeholder="Min. 1" :class="inputClass(errors.participants)" x-model="form.participants" />
                    <p x-show="errors.participants" x-cloak class="text-red-500 text-xs mt-1" x-text="errors.participants"></p>
                </div>
                <div>
                    <label class="text-sm font-semibold text-slate-700 mb-1.5 block">Harga per Orang (Rp) *</label>
                    <input type="number" min="0" placeholder="mis. 2500000" :class="inputClass(errors.pricePerPerson)" x-model="form.pricePerPerson" />
                    <p x-show="errors.pricePerPerson" x-cloak class="text-red-500 text-xs mt-1" x-text="errors.pricePerPerson"></p>
                </div>
            </div>
            <div x-show="formTotal" x-cloak class="bg-teal-50 border border-teal-100 rounded-xl p-3.5 flex items-center justify-between">
                <span class="text-sm text-teal-600 font-medium">Estimasi Total</span>
                <span class="text-base font-extrabold text-teal-800" x-text="formTotal"></span>
            </div>
            <div>
                <label class="text-sm font-semibold text-slate-700 mb-1.5 block">Catatan (opsional)</label>
                <textarea rows="3" placeholder="Permintaan khusus, alergi, kebutuhan lain…" class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-teal-500 transition-all resize-none" x-model="form.notes"></textarea>
            </div>
        </div>
        <div class="px-6 pb-6 pt-2 flex gap-3 flex-shrink-0">
            <button type="button" @click="closeModal()" class="flex-1 border border-slate-200 rounded-xl py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition-colors">Batal</button>
            <button type="button" @click="handleSubmit()" :disabled="validating" class="flex-1 bg-teal-700 hover:bg-teal-800 disabled:opacity-60 text-white rounded-xl py-2.5 text-sm font-bold flex items-center justify-center gap-2 transition-colors shadow-sm">
                <svg x-show="validating" class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                <span x-text="validating ? 'Memvalidasi…' : (modal.mode === 'add' ? 'Simpan Pemesanan' : 'Simpan Perubahan')"></span>
            </button>
        </div>
    </div>
</div>

{{-- Modal: Ubah Status --}}
<div x-show="statusModal.open && statusModal.booking" x-cloak class="fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-4" @keydown.escape.window="statusModal = { open: false, booking: null }">
    <div @click.outside="statusModal = { open: false, booking: null }" class="bg-white rounded-2xl w-full max-w-xs shadow-2xl p-6">
        <h2 class="font-bold text-slate-800 mb-0.5">Ubah Status Pemesanan</h2>
        <p class="text-sm text-slate-500 mb-1" x-text="statusModal.booking?.name"></p>
        <p class="text-xs text-slate-400 mb-5" x-text="statusModal.booking ? statusModal.booking.package + ' · ' + fmtDate(statusModal.booking.departureDate) : ''"></p>
        <div class="flex items-center gap-2 mb-5 pb-4 border-b border-slate-100">
            <span class="text-xs text-slate-500">Status saat ini:</span>
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold border" :class="statusModal.booking ? statusStyle[statusModal.booking.status].pill : ''">
                <span class="w-1.5 h-1.5 rounded-full" :class="statusModal.booking ? statusStyle[statusModal.booking.status].dot : ''"></span>
                <span x-text="statusModal.booking?.status"></span>
            </span>
        </div>
        <div class="space-y-2">
            <template x-for="ns in (statusModal.booking ? transitionsFor(statusModal.booking.status) : [])" :key="ns">
                <button type="button" @click="handleStatus(statusModal.booking, ns)" class="w-full flex items-center justify-between px-4 py-3 rounded-xl text-sm font-semibold border transition-colors" :class="newStatusBtn[ns]">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        <span x-text="'Ubah ke &quot;' + ns + '&quot;'"></span>
                    </span>
                    <span class="w-2 h-2 rounded-full" :class="statusStyle[ns].dot"></span>
                </button>
            </template>
        </div>
        <button type="button" @click="statusModal = { open: false, booking: null }" class="w-full mt-3 py-2.5 rounded-xl text-sm text-slate-500 hover:bg-slate-50 border border-slate-200 transition-colors">Batal</button>
    </div>
</div>

{{-- Modal: Konfirmasi Hapus --}}
<div x-show="delConfirm" x-cloak class="fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-4" @keydown.escape.window="delConfirm = null">
    <div @click.outside="delConfirm = null" class="bg-white rounded-2xl w-full max-w-sm shadow-2xl p-6 text-center">
        <div class="w-14 h-14 bg-red-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <svg class="w-7 h-7 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
        </div>
        <h2 class="font-bold text-slate-800 text-lg mb-1">Hapus Pemesanan?</h2>
        <p class="text-sm text-slate-500 mb-1">Pemesanan atas nama <span class="font-semibold text-slate-700" x-text="delConfirm?.name"></span></p>
        <p class="text-xs text-slate-400 mb-6" x-text="delConfirm ? delConfirm.package + ' · ' + fmtDate(delConfirm.departureDate) : ''"></p>
        <div class="flex gap-3">
            <button type="button" @click="delConfirm = null" class="flex-1 border border-slate-200 rounded-xl py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50">Batal</button>
            <button type="button" @click="handleDelete(delConfirm.id)" class="flex-1 bg-red-600 hover:bg-red-700 text-white rounded-xl py-2.5 text-sm font-bold transition-colors">Ya, Hapus</button>
        </div>
    </div>
</div>


