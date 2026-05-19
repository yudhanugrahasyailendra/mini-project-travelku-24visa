# TravelKu — Sistem Agen Perjalanan

Aplikasi web internal untuk **mengelola pemesanan paket wisata** pada agen perjalanan. Staff dapat mencatat pemesan, memantau status, memfilter data, dan melihat ringkasan pendapatan dari satu dashboard.

---

## Stack & Teknologi

| Teknologi | Peran | Alasan singkat |
|-----------|-------|------------------|
| **Laravel 12** (PHP 8.2+) | Backend, routing, validasi, ORM | Framework matang untuk CRUD, API, migrasi, dan aturan bisnis yang jelas. |
| **SQLite** (default) / MySQL | Database | SQLite memudahkan setup lokal tanpa server DB terpisah; MySQL siap dipakai lewat `.env` untuk produksi. |
| **Blade** | Template HTML | Render halaman awal + injeksi data awal ke frontend dengan ringan. |
| **Alpine.js 3** | Interaktivitas UI | Reaktif di sisi klien tanpa membangun SPA penuh; cocok untuk dashboard admin. |
| **Tailwind CSS 4** | Styling | UI konsisten dan responsif dengan utility class; cepat diiterasi. |
| **Vite 7** | Bundler asset | Build CSS/JS modern dengan HMR saat development. |
| **Axios** | HTTP client | Memanggil API Laravel (CRUD booking, validasi) dari browser. |

---

## Prasyarat

Pastikan terpasang di mesin Anda:

- **PHP** ≥ 8.2 (ekstensi: `pdo`, `sqlite3` atau `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`)
- **Composer**
- **Node.js** ≥ 18 dan **npm**

---

## Cara Menjalankan di Lokal

### 1. Clone & masuk ke folder proyek

```bash
git clone <url-repository> travelku
cd travelku
```

### 2. Instal dependensi PHP

```bash
composer install
```

### 3. Siapkan environment

```bash
cp .env.example .env
php artisan key:generate
```

Secara default proyek memakai **SQLite**. Pastikan file database ada:

```bash
# Windows (PowerShell)
New-Item -ItemType File -Force database/database.sqlite

# Linux / macOS
touch database/database.sqlite
```

> **Opsi MySQL:** ubah di `.env` → `DB_CONNECTION=mysql`, isi `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, lalu buat database kosong di MySQL.

### 4. Migrasi & data contoh

```bash
php artisan migrate
php artisan db:seed
```

Seeder akan mengisi **paket wisata** dan **contoh pemesanan** agar UI langsung bisa dicoba.

### 5. Instal & build asset frontend

```bash
npm install
npm run build
```

Untuk development dengan hot-reload CSS/JS, jalankan di terminal terpisah:

```bash
npm run dev
```

### 6. Jalankan server

```bash
php artisan serve
```

Buka browser: **http://127.0.0.1:8000**

### Ringkas (satu perintah setup)

Jika `composer` script tersedia:

```bash
composer setup
php artisan db:seed
php artisan serve
```

Atau mode development penuh (server + queue + log + Vite):

```bash
composer dev
```

---

## Struktur Singkat

```
app/
  Http/Controllers/   # BookingController, TravelKuController
  Models/             # Booking, TravelPackage, BookingStatusLog
config/travelku.php   # Status & aturan transisi status
database/
  migrations/         # Skema bookings, packages, status logs
  seeders/            # Data awal paket & pemesanan
resources/
  js/travelku.js      # Logika Alpine.js (filter, CRUD, pencarian)
  views/travelku/     # Blade: dashboard, tabel, modal, filter
routes/web.php        # Route halaman & API booking
```

---

## Fitur

### Fitur utama — selesai

| Fitur | Keterangan |
|-------|------------|
| Daftar pemesanan | Tabel desktop & kartu mobile, urut terbaru |
| Ringkasan dashboard | Total pemesanan, estimasi pendapatan, jumlah per status |
| Tambah pemesanan | Form modal dengan validasi |
| Edit pemesanan | Perbarui data pemesan, paket, tanggal, peserta, harga |
| Hapus pemesanan | Soft delete |
| Ubah status | Aturan transisi: Menunggu → Dikonfirmasi/Dibatalkan; Dikonfirmasi → Selesai/Dibatalkan |
| Riwayat status | Tercatat di `booking_status_logs` |
| Validasi input | Nama (huruf), kontak (HP Indonesia / email), paket aktif, tanggal tidak lampau, peserta & harga minimum |
| Filter lanjutan | Status, paket wisata, rentang tanggal keberangkatan |
| API REST | `GET/POST/PUT/PATCH/DELETE` pada `/bookings` (+ validasi & laporan) |
| UI responsif | Sidebar, layout mobile-friendly |

### Fitur opsional — selesai

| Fitur | Keterangan |
|-------|------------|
| **Pencarian nama pemesan / kontak** | Kotak pencarian di dashboard; filter real-time di browser; scope `search` di API `GET /bookings?search=...` |

### Belum diimplementasi (placeholder di UI / roadmap)

| Fitur | Keterangan |
|-------|------------|
| Modul **Data Pelanggan** | Menu sidebar ada, halaman belum dibuat |
| Modul **Paket Wisata** (CRUD) | Paket hanya di-seed; belum ada halaman kelola paket |
| Halaman **Laporan** | Endpoint `GET /bookings/report` sudah ada; UI laporan belum |
| **Autentikasi** login | Aplikasi diasumsikan internal tanpa login |
| Pagination server-side | Semua data dimuat ke halaman awal |

---

## API Singkat

| Method | Endpoint | Fungsi |
|--------|----------|--------|
| `GET` | `/` | Halaman dashboard |
| `GET` | `/bookings` | Daftar booking (`?status=&package=&date_from=&date_to=&search=`) |
| `POST` | `/bookings` | Buat booking |
| `PUT` | `/bookings/{id}` | Update booking |
| `PATCH` | `/bookings/{id}/status` | Ubah status |
| `DELETE` | `/bookings/{id}` | Hapus booking |
| `POST` | `/bookings/validate` | Validasi form sebelum simpan |
| `GET` | `/bookings/report` | Laporan agregat per paket (JSON) |

---

## Asumsi & Keputusan Teknis

1. **Pengguna tunggal / internal** — Tidak ada autentikasi; semua aksi dicatat sebagai "Staff Agen" di log status.
2. **Arsitektur ringan** — Blade + Alpine.js, bukan SPA React/Vue, agar scope mini-project tetap terkontrol.
3. **Filter & pencarian di klien** — Data booking dimuat sekali saat halaman dibuka; filter status/paket/tanggal dan pencarian nama/kontak dijalankan di browser. Cukup untuk ratusan record; API tetap mendukung query untuk integrasi ke depan.
4. **SQLite sebagai default** — Meminimalkan langkah setup lokal; MySQL siap untuk deployment yang membutuhkan DB bersama.
5. **Aturan status terpusat** — Transisi status didefinisikan di `config/travelku.php` dan `Booking::STATUS_TRANSITIONS` agar tidak bisa loncat status secara sembarangan.
6. **Kontak fleksibel** — Menerima nomor HP format `08` / `+62` atau email valid.
7. **Total harga di database** — Kolom `total_price` generated (`participants × price_per_person`) untuk konsistensi laporan.
8. **Soft delete** — Pemesanan yang dihapus tidak hilang permanen dari database.

---

## Deployment (opsional)

Proyek menyertakan `nixpacks.toml` untuk deploy platform berbasis Nixpacks (mis. Railway). Alur build: `composer install`, `npm install`, `npm run build`, lalu `php artisan serve`.

Pastikan di production:

- `APP_ENV=production`, `APP_DEBUG=false`
- `php artisan migrate --force`
- Database production (MySQL disarankan untuk traffic lebih tinggi)

---

## Hal yang Ingin Diperbaiki (jika ada lebih banyak waktu)

- **Login & otorisasi** — Pembatasan akses per role (admin vs staff).
- **Pagination & filter server-side** — Agar performa tetap baik saat data ribuan booking.
- **Halaman laporan UI** — Visualisasi dari endpoint `/bookings/report` (grafik/tabel export).
- **CRUD paket wisata** — Kelola paket tanpa menyentuh seeder/database manual.
- **Modul pelanggan terpisah** — Profil pelanggan berulang, riwayat per nama/kontak.
- **Automated tests** — Feature test untuk CRUD, transisi status, dan pencarian API.
- **Export PDF/Excel** — Laporan pendapatan untuk kebutuhan administrasi agen.
- **Notifikasi** — Email/WA saat status berubah ke Dikonfirmasi.

---

## Lisensi

Proyek ini menggunakan [Laravel](https://laravel.com) yang dilisensikan di bawah [MIT license](https://opensource.org/licenses/MIT).
