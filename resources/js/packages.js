const EMPTY_PACKAGE = {
    categoryId: '',
    name: '',
    destination: '',
    durationDays: 4,
    durationNights: 3,
    basePrice: 0,
    priceWeekend: '',
    priceHoliday: '',
    minParticipants: 1,
    maxParticipants: 20,
    shortDesc: '',
    isActive: true,
    isFeatured: false,
    sortOrder: 0,
};

const fmtCurrency = (n) =>
    new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(n);

const inputClass = (hasError) =>
    `w-full border rounded-xl px-3 py-2.5 text-sm bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 transition-all ${
        hasError ? 'border-red-300 bg-red-50 focus:ring-red-400' : 'border-slate-200 focus:ring-teal-500'
    }`;

document.addEventListener('alpine:init', () => {
    Alpine.data('travelPackages', (config) => ({
        packages: config.packages,
        categories: config.categories,
        packagesUrl: config.packagesUrl,
        csrfToken: config.csrfToken,

        search: '',
        statusFilter: '',
        categoryFilter: '',
        mobileNav: false,
        modal: { open: false, mode: 'add', pkg: null },
        form: { ...EMPTY_PACKAGE },
        errors: {},
        saving: false,
        delConfirm: null,
        toast: null,

        fmtCurrency,
        inputClass,

        apiHeaders() {
            return {
                'X-CSRF-TOKEN': this.csrfToken,
                Accept: 'application/json',
            };
        },

        get filtered() {
            let list = [...this.packages].sort((a, b) => a.sortOrder - b.sortOrder || a.name.localeCompare(b.name, 'id'));
            if (this.statusFilter === 'active') list = list.filter((p) => p.isActive);
            if (this.statusFilter === 'inactive') list = list.filter((p) => !p.isActive);
            if (this.categoryFilter) list = list.filter((p) => String(p.categoryId) === String(this.categoryFilter));
            if (this.search.trim()) {
                const q = this.search.trim().toLowerCase();
                list = list.filter(
                    (p) =>
                        p.name.toLowerCase().includes(q) ||
                        p.code.toLowerCase().includes(q) ||
                        p.destination.toLowerCase().includes(q) ||
                        (p.shortDesc && p.shortDesc.toLowerCase().includes(q)),
                );
            }
            return list;
        },

        get summary() {
            const total = this.packages.length;
            const active = this.packages.filter((p) => p.isActive).length;
            const bookings = this.packages.reduce((s, p) => s + p.bookingsCount, 0);
            return { total, active, inactive: total - active, bookings };
        },

        get hasFilter() {
            return Boolean(this.search.trim() || this.statusFilter || this.categoryFilter);
        },

        showToast(msg, type = 'success') {
            this.toast = { msg, type };
            setTimeout(() => {
                this.toast = null;
            }, 3200);
        },

        openAdd() {
            this.form = { ...EMPTY_PACKAGE };
            this.errors = {};
            this.modal = { open: true, mode: 'add', pkg: null };
        },

        openEdit(pkg) {
            this.form = {
                categoryId: pkg.categoryId,
                name: pkg.name,
                destination: pkg.destination,
                durationDays: pkg.durationDays,
                durationNights: pkg.durationNights,
                basePrice: pkg.basePrice,
                priceWeekend: pkg.priceWeekend ?? '',
                priceHoliday: pkg.priceHoliday ?? '',
                minParticipants: pkg.minParticipants,
                maxParticipants: pkg.maxParticipants,
                shortDesc: pkg.shortDesc,
                isActive: pkg.isActive,
                isFeatured: pkg.isFeatured,
                sortOrder: pkg.sortOrder,
            };
            this.errors = {};
            this.modal = { open: true, mode: 'edit', pkg };
        },

        closeModal() {
            this.modal = { open: false, mode: 'add', pkg: null };
        },

        resetFilters() {
            this.search = '';
            this.statusFilter = '';
            this.categoryFilter = '';
        },

        mapErrors(raw) {
            return Object.fromEntries(
                Object.entries(raw).map(([key, value]) => [
                    key.replace(/_([a-z])/g, (_, c) => c.toUpperCase()).replace(/^category_id$/, 'categoryId'),
                    Array.isArray(value) ? value[0] : value,
                ]),
            );
        },

        async handleSubmit() {
            this.saving = true;
            this.errors = {};
            try {
                if (this.modal.mode === 'add') {
                    const res = await window.axios.post(this.packagesUrl, this.form, {
                        headers: this.apiHeaders(),
                    });
                    this.packages = [...this.packages, res.data.package].sort(
                        (a, b) => a.sortOrder - b.sortOrder || a.name.localeCompare(b.name, 'id'),
                    );
                    this.showToast('Paket wisata berhasil ditambahkan!');
                } else {
                    const res = await window.axios.put(
                        `${this.packagesUrl}/${this.modal.pkg.id}`,
                        this.form,
                        { headers: this.apiHeaders() },
                    );
                    this.packages = this.packages
                        .map((p) => (p.id === this.modal.pkg.id ? res.data.package : p))
                        .sort((a, b) => a.sortOrder - b.sortOrder || a.name.localeCompare(b.name, 'id'));
                    this.showToast('Paket wisata berhasil diperbarui!');
                }
                this.closeModal();
            } catch (err) {
                const raw = err.response?.data?.errors || {};
                if (Object.keys(raw).length) {
                    this.errors = this.mapErrors(raw);
                } else {
                    this.showToast(err.response?.data?.message || 'Gagal menyimpan paket.', 'info');
                }
            } finally {
                this.saving = false;
            }
        },

        async toggleActive(pkg) {
            try {
                const res = await window.axios.put(
                    `${this.packagesUrl}/${pkg.id}`,
                    {
                        categoryId: pkg.categoryId,
                        name: pkg.name,
                        destination: pkg.destination,
                        durationDays: pkg.durationDays,
                        durationNights: pkg.durationNights,
                        basePrice: pkg.basePrice,
                        priceWeekend: pkg.priceWeekend,
                        priceHoliday: pkg.priceHoliday,
                        minParticipants: pkg.minParticipants,
                        maxParticipants: pkg.maxParticipants,
                        shortDesc: pkg.shortDesc,
                        isActive: !pkg.isActive,
                        isFeatured: pkg.isFeatured,
                        sortOrder: pkg.sortOrder,
                    },
                    { headers: this.apiHeaders() },
                );
                this.packages = this.packages.map((p) => (p.id === pkg.id ? res.data.package : p));
                this.showToast(res.data.package.isActive ? 'Paket diaktifkan.' : 'Paket dinonaktifkan.');
            } catch {
                this.showToast('Gagal mengubah status paket.', 'info');
            }
        },

        async handleDelete(id) {
            try {
                await window.axios.delete(`${this.packagesUrl}/${id}`, {
                    headers: this.apiHeaders(),
                });
                this.packages = this.packages.filter((p) => p.id !== id);
                this.delConfirm = null;
                this.showToast('Paket wisata dihapus.', 'info');
            } catch (err) {
                this.showToast(err.response?.data?.message || 'Gagal menghapus paket.', 'info');
            }
        },
    }));
});
