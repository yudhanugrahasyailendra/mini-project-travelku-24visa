const EMPTY_FORM = {
    name: '',
    contact: '',
    package: '',
    departureDate: '',
    participants: 1,
    pricePerPerson: 0,
    notes: '',
};

const STATUS_STYLE = {
    Menunggu: { pill: 'bg-amber-100 text-amber-800 border border-amber-200', dot: 'bg-amber-400' },
    Dikonfirmasi: { pill: 'bg-sky-100 text-sky-800 border border-sky-200', dot: 'bg-sky-500' },
    Selesai: { pill: 'bg-emerald-100 text-emerald-800 border border-emerald-200', dot: 'bg-emerald-500' },
    Dibatalkan: { pill: 'bg-red-100 text-red-700 border border-red-200', dot: 'bg-red-400' },
};

const NEW_STATUS_BTN = {
    Dikonfirmasi: 'bg-sky-50 hover:bg-sky-100 text-sky-700 border-sky-200',
    Selesai: 'bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border-emerald-200',
    Dibatalkan: 'bg-red-50 hover:bg-red-100 text-red-700 border-red-200',
};

const fmtCurrency = (n) =>
    new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(n);

const fmtDate = (d) =>
    d ? new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : '-';

const inputClass = (hasError) =>
    `w-full border rounded-xl px-3 py-2.5 text-sm bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 transition-all ${
        hasError ? 'border-red-300 bg-red-50 focus:ring-red-400' : 'border-slate-200 focus:ring-teal-500'
    }`;

document.addEventListener('alpine:init', () => {
    Alpine.data('travelKu', (config) => ({
        packages: config.packages,
        statuses: config.statuses,
        statusTransitions: config.statusTransitions,
        validateUrl: config.validateUrl,
        bookingsUrl: config.bookingsUrl,
        csrfToken: config.csrfToken,

        bookings: config.bookings,
        filters: { status: '', package: '', dateFrom: '', dateTo: '' },
        search: '',
        showFilters: false,
        mobileNav: false,
        modal: { open: false, mode: 'add', booking: null },
        form: { ...EMPTY_FORM },
        errors: {},
        validating: false,
        saving: false,
        statusModal: { open: false, booking: null },
        delConfirm: null,
        toast: null,

        fmtCurrency,
        fmtDate,
        inputClass,
        statusStyle: STATUS_STYLE,
        newStatusBtn: NEW_STATUS_BTN,

        apiHeaders() {
            return {
                'X-CSRF-TOKEN': this.csrfToken,
                Accept: 'application/json',
            };
        },

        get filtered() {
            let list = [...this.bookings].sort((a, b) => b.createdAt - a.createdAt);
            if (this.filters.status) list = list.filter((b) => b.status === this.filters.status);
            if (this.filters.package) list = list.filter((b) => b.package === this.filters.package);
            if (this.filters.dateFrom) list = list.filter((b) => b.departureDate >= this.filters.dateFrom);
            if (this.filters.dateTo) list = list.filter((b) => b.departureDate <= this.filters.dateTo);
            if (this.search) {
                const q = this.search.toLowerCase();
                list = list.filter(
                    (b) =>
                        b.name.toLowerCase().includes(q) ||
                        b.contact.toLowerCase().includes(q) ||
                        b.package.toLowerCase().includes(q),
                );
            }
            return list;
        },

        get summary() {
            const revenue = this.filtered
                .filter((b) => b.status === 'Dikonfirmasi' || b.status === 'Selesai')
                .reduce((s, b) => s + b.participants * b.pricePerPerson, 0);
            const byStatus = Object.fromEntries(
                this.statuses.map((s) => [s, this.filtered.filter((b) => b.status === s).length]),
            );
            return { total: this.filtered.length, revenue, byStatus };
        },

        get hasFilter() {
            return Object.values(this.filters).some((v) => v) || this.search;
        },

        get filterCount() {
            return Object.values(this.filters).filter((v) => v).length + (this.search ? 1 : 0);
        },

        get formTotal() {
            const p = Number(this.form.participants);
            const price = Number(this.form.pricePerPerson);
            return p > 0 && price > 0 ? fmtCurrency(p * price) : null;
        },

        transitionsFor(status) {
            return this.statusTransitions[status] || [];
        },

        showToast(msg, type = 'success') {
            this.toast = { msg, type };
            setTimeout(() => {
                this.toast = null;
            }, 3200);
        },

        openAdd() {
            this.form = { ...EMPTY_FORM };
            this.errors = {};
            this.modal = { open: true, mode: 'add', booking: null };
        },

        openEdit(booking) {
            this.form = { ...booking };
            this.errors = {};
            this.modal = { open: true, mode: 'edit', booking };
        },

        closeModal() {
            this.modal = { open: false, mode: 'add', booking: null };
        },

        resetAll() {
            this.filters = { status: '', package: '', dateFrom: '', dateTo: '' };
            this.search = '';
        },

        async validateServer(data) {
            try {
                const res = await window.axios.post(this.validateUrl, data, {
                    headers: this.apiHeaders(),
                });
                return res.data;
            } catch {
                return { valid: false, errors: { general: 'Validasi server gagal. Periksa koneksi.' } };
            }
        },

        async handleSubmit() {
            this.validating = true;
            this.errors = {};
            const result = await this.validateServer(this.form);
            if (!result.valid) {
                const raw = result.errors || {};
                this.errors = Object.fromEntries(
                    Object.entries(raw).map(([key, value]) => [
                        key,
                        Array.isArray(value) ? value[0] : value,
                    ]),
                );
                this.validating = false;
                return;
            }

            this.saving = true;
            try {
                if (this.modal.mode === 'add') {
                    const res = await window.axios.post(this.bookingsUrl, this.form, {
                        headers: this.apiHeaders(),
                    });
                    this.bookings = [res.data.booking, ...this.bookings];
                    this.showToast('Pemesanan baru berhasil ditambahkan!');
                } else {
                    const res = await window.axios.put(
                        `${this.bookingsUrl}/${this.modal.booking.id}`,
                        this.form,
                        { headers: this.apiHeaders() },
                    );
                    this.bookings = this.bookings.map((b) =>
                        b.id === this.modal.booking.id ? res.data.booking : b,
                    );
                    this.showToast('Pemesanan berhasil diperbarui!');
                }
                this.closeModal();
            } catch (err) {
                const raw = err.response?.data?.errors || {};
                if (Object.keys(raw).length) {
                    this.errors = Object.fromEntries(
                        Object.entries(raw).map(([key, value]) => [
                            key,
                            Array.isArray(value) ? value[0] : value,
                        ]),
                    );
                } else {
                    this.showToast('Gagal menyimpan pemesanan.', 'info');
                }
            } finally {
                this.validating = false;
                this.saving = false;
            }
        },

        async handleStatus(booking, newStatus) {
            try {
                const res = await window.axios.patch(
                    `${this.bookingsUrl}/${booking.id}/status`,
                    { status: newStatus },
                    { headers: this.apiHeaders() },
                );
                this.bookings = this.bookings.map((b) =>
                    b.id === booking.id ? res.data.booking : b,
                );
                this.statusModal = { open: false, booking: null };
                this.showToast(`Status diubah ke "${newStatus}"`);
            } catch {
                this.showToast('Gagal mengubah status.', 'info');
            }
        },

        async handleDelete(id) {
            try {
                await window.axios.delete(`${this.bookingsUrl}/${id}`, {
                    headers: this.apiHeaders(),
                });
                this.bookings = this.bookings.filter((b) => b.id !== id);
                this.delConfirm = null;
                this.showToast('Pemesanan dihapus.', 'info');
            } catch {
                this.showToast('Gagal menghapus pemesanan.', 'info');
            }
        },

        openStatusModal(booking) {
            if (this.transitionsFor(booking.status).length > 0) {
                this.statusModal = { open: true, booking };
            }
        },
    }));
});
