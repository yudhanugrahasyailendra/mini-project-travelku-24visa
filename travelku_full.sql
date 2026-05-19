-- ============================================================
--  TravelKu – Database Lengkap (Modul Paket Wisata Terpisah)
--  MySQL 8.0+ | utf8mb4
--  Import: mysql -u root -p travelku < travelku_full.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS travelku
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
USE travelku;

-- ═══════════════════════════════════════════════════════════
--  TABEL 1: categories  (Kategori Paket Wisata)
--  Contoh: Pantai, Budaya, Petualangan, Honeymoon, dll.
-- ═══════════════════════════════════════════════════════════
CREATE TABLE categories (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(80)  NOT NULL UNIQUE  COMMENT 'Nama kategori, mis. Pantai',
    slug        VARCHAR(80)  NOT NULL UNIQUE  COMMENT 'URL-friendly, mis. pantai',
    description TEXT         NULL,
    icon        VARCHAR(100) NULL             COMMENT 'Nama ikon atau path gambar',
    is_active   TINYINT(1)   NOT NULL DEFAULT 1,
    created_at  TIMESTAMP    NULL,
    updated_at  TIMESTAMP    NULL,

    INDEX idx_cat_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Kategori paket wisata';


-- ═══════════════════════════════════════════════════════════
--  TABEL 2: travel_packages  (Modul Paket Wisata)
--  Paket dikelola admin, booking hanya bisa pilih dari daftar
-- ═══════════════════════════════════════════════════════════
CREATE TABLE travel_packages (
    id               BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id      BIGINT UNSIGNED NOT NULL,

    -- Identitas paket
    code             VARCHAR(20)    NOT NULL UNIQUE COMMENT 'Kode unik, mis. PKG-001',
    name             VARCHAR(150)   NOT NULL        COMMENT 'Nama lengkap, mis. Bali 4D3N – Romantic Escape',
    slug             VARCHAR(170)   NOT NULL UNIQUE COMMENT 'URL-friendly',
    destination      VARCHAR(100)   NOT NULL        COMMENT 'Destinasi utama, mis. Bali',
    duration_days    TINYINT UNSIGNED NOT NULL      COMMENT 'Jumlah hari, mis. 4',
    duration_nights  TINYINT UNSIGNED NOT NULL      COMMENT 'Jumlah malam, mis. 3',

    -- Harga (sumber harga untuk booking)
    base_price       DECIMAL(12,2)  NOT NULL        COMMENT 'Harga dasar per orang (IDR)',
    price_weekend    DECIMAL(12,2)  NULL            COMMENT 'Harga akhir pekan (opsional)',
    price_holiday    DECIMAL(12,2)  NULL            COMMENT 'Harga hari libur nasional (opsional)',

    -- Kapasitas
    min_participants TINYINT UNSIGNED NOT NULL DEFAULT 1  COMMENT 'Min. peserta per booking',
    max_participants TINYINT UNSIGNED NOT NULL DEFAULT 100 COMMENT 'Maks. peserta per booking',

    -- Konten
    short_desc       VARCHAR(300)   NULL            COMMENT 'Deskripsi singkat untuk kartu',
    description      LONGTEXT       NULL            COMMENT 'Deskripsi lengkap / itinerary (HTML/Markdown)',
    includes         TEXT           NULL            COMMENT 'Yang sudah termasuk, mis. hotel, makan',
    excludes         TEXT           NULL            COMMENT 'Yang tidak termasuk, mis. visa, tips',
    cover_image      VARCHAR(255)   NULL            COMMENT 'Path/URL gambar utama',

    -- Status & meta
    is_active        TINYINT(1)     NOT NULL DEFAULT 1,
    is_featured      TINYINT(1)     NOT NULL DEFAULT 0   COMMENT 'Tampilkan di halaman utama',
    sort_order       SMALLINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Urutan tampil di list',
    created_at       TIMESTAMP      NULL,
    updated_at       TIMESTAMP      NULL,
    deleted_at       TIMESTAMP      NULL            COMMENT 'Soft delete',

    CONSTRAINT fk_pkg_category
        FOREIGN KEY (category_id) REFERENCES categories(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,

    INDEX idx_pkg_active     (is_active, deleted_at),
    INDEX idx_pkg_category   (category_id),
    INDEX idx_pkg_featured   (is_featured, is_active),
    INDEX idx_pkg_sort       (sort_order),
    FULLTEXT idx_pkg_search  (name, destination, short_desc)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Modul Paket Wisata – sumber harga & kapasitas untuk booking';


-- ═══════════════════════════════════════════════════════════
--  TABEL 3: bookings  (Pemesanan)
--  price_per_person DISALIN dari paket saat booking dibuat
--  (snapshot harga agar historis tidak berubah jika paket diupdate)
-- ═══════════════════════════════════════════════════════════
CREATE TABLE bookings (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    travel_package_id   BIGINT UNSIGNED NOT NULL,

    -- Nomor booking
    booking_number      VARCHAR(20)    NOT NULL UNIQUE COMMENT 'Nomor booking, mis. BK-20260001',

    -- Data pemesan
    name                VARCHAR(150)   NOT NULL COMMENT 'Nama lengkap pemesan',
    contact             VARCHAR(150)   NOT NULL COMMENT 'No. HP Indonesia atau email',

    -- Detail perjalanan
    departure_date      DATE           NOT NULL COMMENT 'Tanggal keberangkatan',
    participants        TINYINT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Jumlah peserta (1-100)',

    -- Harga (snapshot dari paket saat booking dibuat, tidak berubah walau paket diupdate)
    price_per_person    DECIMAL(12,2)  NOT NULL COMMENT 'Harga per orang saat booking (IDR)',
    total_price         DECIMAL(14,2)  GENERATED ALWAYS AS (participants * price_per_person) STORED
                                       COMMENT 'Total = participants × price_per_person',

    -- Status
    status              ENUM('Menunggu','Dikonfirmasi','Selesai','Dibatalkan')
                        NOT NULL DEFAULT 'Menunggu',
    notes               TEXT           NULL COMMENT 'Catatan / permintaan khusus',

    -- Timestamps
    created_at          TIMESTAMP      NULL,
    updated_at          TIMESTAMP      NULL,
    deleted_at          TIMESTAMP      NULL COMMENT 'Soft delete',

    CONSTRAINT fk_booking_package
        FOREIGN KEY (travel_package_id) REFERENCES travel_packages(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,

    INDEX idx_bk_status        (status),
    INDEX idx_bk_departure     (departure_date),
    INDEX idx_bk_pkg_status    (travel_package_id, status),
    INDEX idx_bk_created       (created_at),
    INDEX idx_bk_number        (booking_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Pemesanan – relasi ke travel_packages via travel_package_id';


-- ═══════════════════════════════════════════════════════════
--  TABEL 4: booking_status_logs  (Riwayat Perubahan Status)
-- ═══════════════════════════════════════════════════════════
CREATE TABLE booking_status_logs (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_id  BIGINT UNSIGNED NOT NULL,
    old_status  ENUM('Menunggu','Dikonfirmasi','Selesai','Dibatalkan') NULL,
    new_status  ENUM('Menunggu','Dikonfirmasi','Selesai','Dibatalkan') NOT NULL,
    changed_by  VARCHAR(100)   NULL COMMENT 'Nama user/admin',
    note        TEXT           NULL COMMENT 'Alasan perubahan (opsional)',
    created_at  TIMESTAMP      NULL,
    updated_at  TIMESTAMP      NULL,

    CONSTRAINT fk_log_booking
        FOREIGN KEY (booking_id) REFERENCES bookings(id)
        ON UPDATE CASCADE ON DELETE CASCADE,

    INDEX idx_log_booking (booking_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Audit trail perubahan status booking';


-- ═══════════════════════════════════════════════════════════
--  SEED: categories
-- ═══════════════════════════════════════════════════════════
INSERT INTO categories (name, slug, description, icon, is_active, created_at, updated_at) VALUES
('Pantai & Bahari',  'pantai-bahari',   'Destinasi pantai, selam, snorkeling',    'waves',     1, NOW(), NOW()),
('Budaya & Sejarah', 'budaya-sejarah',  'Candi, museum, seni tradisional',         'landmark',  1, NOW(), NOW()),
('Petualangan',      'petualangan',     'Hiking, arung jeram, off-road',           'mountain',  1, NOW(), NOW()),
('Honeymoon',        'honeymoon',       'Paket romantis untuk pasangan',           'heart',     1, NOW(), NOW()),
('Family Tour',      'family-tour',     'Paket ramah keluarga & anak-anak',        'users',     1, NOW(), NOW()),
('City Tour',        'city-tour',       'Wisata kota, kuliner, belanja',           'map-pin',   1, NOW(), NOW());


-- ═══════════════════════════════════════════════════════════
--  SEED: travel_packages (9 paket sesuai JSX + detail lengkap)
-- ═══════════════════════════════════════════════════════════
INSERT INTO travel_packages (
    category_id, code, name, slug, destination,
    duration_days, duration_nights,
    base_price, price_weekend, price_holiday,
    min_participants, max_participants,
    short_desc, includes, excludes,
    is_active, is_featured, sort_order, created_at, updated_at
) VALUES
-- 1. Bali 4D3N → Pantai & Bahari
(1, 'PKG-001', 'Bali 4D3N – Tropical Paradise',
 'bali-4d3n-tropical-paradise', 'Bali', 4, 3,
 2500000.00, 2800000.00, 3000000.00, 1, 20,
 'Jelajahi keindahan Bali: pantai, pura, dan budaya dalam 4 hari.',
 'Hotel bintang 3, makan pagi, transport AC, guide lokal, tiket masuk wisata',
 'Tiket pesawat, makan siang & malam, laundry, pengeluaran pribadi',
 1, 1, 1, NOW(), NOW()),

-- 2. Lombok 3D2N → Pantai & Bahari
(1, 'PKG-002', 'Lombok 3D2N – Pearl of the East',
 'lombok-3d2n-pearl-of-the-east', 'Lombok', 3, 2,
 1800000.00, 2000000.00, 2200000.00, 1, 30,
 'Pantai bening, Gili Islands, dan kaki Rinjani dalam satu paket.',
 'Hotel bintang 3, makan pagi, speedboat Gili, guide lokal',
 'Tiket pesawat, snorkeling gear, pengeluaran pribadi',
 1, 1, 2, NOW(), NOW()),

-- 3. Yogyakarta 2D1N → Budaya & Sejarah
(2, 'PKG-003', 'Yogyakarta 2D1N – Warisan Budaya',
 'yogyakarta-2d1n-warisan-budaya', 'Yogyakarta', 2, 1,
 950000.00, 1100000.00, 1200000.00, 2, 40,
 'Candi Borobudur, Prambanan, Keraton, dan kuliner khas Jogja.',
 'Hotel bintang 2, makan pagi, transport AC, guide, tiket Borobudur & Prambanan',
 'Tiket kereta/pesawat, makan siang & malam, oleh-oleh',
 1, 0, 3, NOW(), NOW()),

-- 4. Raja Ampat 5D4N → Pantai & Bahari
(1, 'PKG-004', 'Raja Ampat 5D4N – Surga Bawah Laut',
 'raja-ampat-5d4n-surga-bawah-laut', 'Raja Ampat', 5, 4,
 5800000.00, 6500000.00, 7000000.00, 2, 15,
 'Menyelam di perairan terkaya biodiversitas laut di dunia.',
 'Homestay, 3x makan, speedboat antar pulau, guide selam, snorkeling gear',
 'Tiket pesawat ke Sorong, peralatan scuba diving, visa (jika ada)',
 1, 1, 4, NOW(), NOW()),

-- 5. Labuan Bajo 4D3N → Petualangan
(3, 'PKG-005', 'Labuan Bajo 4D3N – Komodo & Beyond',
 'labuan-bajo-4d3n-komodo-beyond', 'Labuan Bajo', 4, 3,
 3200000.00, 3600000.00, 3800000.00, 2, 20,
 'Bertemu komodo, snorkeling, dan menikmati sunset terbaik di NTT.',
 'Hotel bintang 3, 3x makan, kapal wisata, guide ranger, tiket Taman Nasional',
 'Tiket pesawat, minuman beralkohol, pengeluaran pribadi',
 1, 1, 5, NOW(), NOW()),

-- 6. Komodo 3D2N → Petualangan
(3, 'PKG-006', 'Komodo 3D2N – Naga Terakhir Bumi',
 'komodo-3d2n-naga-terakhir-bumi', 'Komodo', 3, 2,
 2900000.00, 3200000.00, 3400000.00, 2, 18,
 'Perjalanan eksklusif ke Pulau Komodo dan Pink Beach.',
 'Penginapan di kapal/resort, makan, speedboat, ranger bersertifikat',
 'Tiket pesawat ke Labuan Bajo, asuransi perjalanan',
 1, 0, 6, NOW(), NOW()),

-- 7. Manado 4D3N → Pantai & Bahari
(1, 'PKG-007', 'Manado 4D3N – Bunaken Adventure',
 'manado-4d3n-bunaken-adventure', 'Manado', 4, 3,
 2200000.00, 2500000.00, 2700000.00, 1, 25,
 'Menyelam di Taman Nasional Bunaken, salah satu dive site terbaik dunia.',
 'Hotel bintang 3, makan pagi, transport, speedboat Bunaken, guide selam',
 'Tiket pesawat, peralatan scuba, makan siang & malam',
 1, 0, 7, NOW(), NOW()),

-- 8. Bunaken 3D2N → Pantai & Bahari
(1, 'PKG-008', 'Bunaken 3D2N – Wall Dive Paradise',
 'bunaken-3d2n-wall-dive-paradise', 'Bunaken', 3, 2,
 1900000.00, 2100000.00, 2300000.00, 2, 16,
 'Paket khusus penyelaman di dinding karang Bunaken yang legendaris.',
 'Cottage di pulau, 3x makan, 2x dive boat, snorkeling gear',
 'Tiket pesawat + speedboat ke Bunaken, peralatan scuba',
 1, 0, 8, NOW(), NOW()),

-- 9. Wakatobi 4D3N → Pantai & Bahari
(1, 'PKG-009', 'Wakatobi 4D3N – Coral Triangle',
 'wakatobi-4d3n-coral-triangle', 'Wakatobi', 4, 3,
 3500000.00, 3900000.00, 4200000.00, 2, 12,
 'Eksplorasi Segitiga Terumbu Karang Dunia di Sulawesi Tenggara.',
 'Resort tepi pantai, 3x makan, speedboat, guide lokal, snorkeling',
 'Tiket pesawat ke Wangi-Wangi, asuransi, peralatan scuba',
 1, 0, 9, NOW(), NOW());


-- ═══════════════════════════════════════════════════════════
--  SEED: bookings (6 data sample, price_per_person dari paket)
-- ═══════════════════════════════════════════════════════════
INSERT INTO bookings (
    travel_package_id, booking_number, name, contact,
    departure_date, participants, price_per_person,
    status, notes, created_at, updated_at
)
SELECT
    tp.id,
    s.booking_number,
    s.name, s.contact, s.departure_date, s.participants,
    tp.base_price,   -- ← harga diambil dari paket
    s.status, s.notes, s.created_at, s.created_at
FROM (
    SELECT 'BK-20260001' AS booking_number, 'Budi Santoso'  AS name, '081234567890'   AS contact, 'Bali 4D3N – Tropical Paradise'     AS pkg_name, '2026-06-15' AS departure_date, 4 AS participants, 'Dikonfirmasi' AS status, 'Minta kamar non-smoking'  AS notes, DATE_SUB(NOW(), INTERVAL 3 DAY) AS created_at
    UNION ALL
    SELECT 'BK-20260002', 'Siti Rahayu',   'siti@email.com', 'Lombok 3D2N – Pearl of the East',       '2026-07-10', 2, 'Menunggu',     NULL,                       DATE_SUB(NOW(), INTERVAL 1 DAY)
    UNION ALL
    SELECT 'BK-20260003', 'Ahmad Fauzi',   '085678901234',   'Yogyakarta 2D1N – Warisan Budaya',       '2026-05-25', 6, 'Selesai',      'Group tour mahasiswa',     DATE_SUB(NOW(), INTERVAL 5 DAY)
    UNION ALL
    SELECT 'BK-20260004', 'Dewi Kusuma',   'dewi@gmail.com', 'Raja Ampat 5D4N – Surga Bawah Laut',    '2026-08-02', 3, 'Menunggu',     'Honeymoon package',        DATE_SUB(NOW(), INTERVAL 2 DAY)
    UNION ALL
    SELECT 'BK-20260005', 'Hendra Wijaya', '081999888777',   'Labuan Bajo 4D3N – Komodo & Beyond',     '2026-09-14', 8, 'Dikonfirmasi', 'Grup kantor',              DATE_SUB(NOW(), INTERVAL 4 DAY)
    UNION ALL
    SELECT 'BK-20260006', 'Rina Hastuti',  'rina.h@mail.id', 'Komodo 3D2N – Naga Terakhir Bumi',      '2026-06-01', 2, 'Dibatalkan',   'Pembatalan karena sakit',  DATE_SUB(NOW(), INTERVAL 6 DAY)
) s
JOIN travel_packages tp ON tp.name = s.pkg_name;


-- ═══════════════════════════════════════════════════════════
--  QUERY OPERASIONAL – MODUL PAKET WISATA
-- ═══════════════════════════════════════════════════════════

-- P-1. Daftar semua paket aktif (untuk dropdown pilihan di form booking)
SELECT
    tp.id,
    tp.code,
    tp.name,
    tp.destination,
    CONCAT(tp.duration_days, 'D', tp.duration_nights, 'N') AS durasi,
    tp.base_price,
    tp.min_participants,
    tp.max_participants,
    c.name AS kategori
FROM travel_packages tp
JOIN categories c ON c.id = tp.category_id
WHERE tp.is_active = 1
  AND tp.deleted_at IS NULL
ORDER BY tp.sort_order, tp.name;

-- P-2. Detail satu paket (saat user memilih dari dropdown)
SELECT
    tp.id, tp.code, tp.name, tp.slug, tp.destination,
    tp.duration_days, tp.duration_nights,
    tp.base_price, tp.price_weekend, tp.price_holiday,
    tp.min_participants, tp.max_participants,
    tp.short_desc, tp.description,
    tp.includes, tp.excludes, tp.cover_image,
    c.name AS kategori
FROM travel_packages tp
JOIN categories c ON c.id = tp.category_id
WHERE tp.id = 1          -- ganti dengan id yang dipilih
  AND tp.is_active = 1
  AND tp.deleted_at IS NULL;

-- P-3. Tambah paket wisata baru
INSERT INTO travel_packages (
    category_id, code, name, slug, destination,
    duration_days, duration_nights,
    base_price, price_weekend, price_holiday,
    min_participants, max_participants,
    short_desc, includes, excludes,
    is_active, is_featured, sort_order,
    created_at, updated_at
) VALUES (
    1,                           -- category_id
    'PKG-010',                   -- code (unik)
    'Mandalika 3D2N – MotoGP Circuit',
    'mandalika-3d2n-motogp-circuit',
    'Lombok',
    3, 2,
    1750000.00, 1950000.00, 2100000.00,
    2, 30,
    'Kunjungi sirkuit MotoGP Mandalika dan pantai eksotis sekitarnya.',
    'Hotel, makan pagi, transport, tiket sirkuit',
    'Tiket pesawat, pengeluaran pribadi',
    1, 0, 10,
    NOW(), NOW()
);

-- P-4. Update harga paket (tidak mempengaruhi booking yang sudah ada)
UPDATE travel_packages
SET
    base_price   = 2700000.00,
    price_weekend = 3000000.00,
    updated_at   = NOW()
WHERE id = 1;

-- P-5. Nonaktifkan paket (tidak muncul di dropdown booking baru)
UPDATE travel_packages
SET is_active = 0, updated_at = NOW()
WHERE id = 1;

-- P-6. Soft delete paket
UPDATE travel_packages
SET deleted_at = NOW(), updated_at = NOW()
WHERE id = 1;

-- P-7. Statistik paket: berapa kali dipesan & total pendapatan
SELECT
    tp.code,
    tp.name,
    tp.base_price,
    COUNT(b.id)              AS total_booking,
    SUM(b.participants)      AS total_peserta,
    SUM(b.total_price)       AS total_pendapatan,
    SUM(b.status = 'Menunggu')      AS menunggu,
    SUM(b.status = 'Dikonfirmasi')  AS dikonfirmasi,
    SUM(b.status = 'Selesai')       AS selesai,
    SUM(b.status = 'Dibatalkan')    AS dibatalkan
FROM travel_packages tp
LEFT JOIN bookings b ON b.travel_package_id = tp.id AND b.deleted_at IS NULL
WHERE tp.deleted_at IS NULL
GROUP BY tp.id, tp.code, tp.name, tp.base_price
ORDER BY total_pendapatan DESC;


-- ═══════════════════════════════════════════════════════════
--  QUERY OPERASIONAL – MODUL BOOKING
-- ═══════════════════════════════════════════════════════════

-- B-1. Daftar booking + detail paket (terbaru dulu)
SELECT
    b.id,
    b.booking_number,
    b.name                   AS pemesan,
    b.contact,
    tp.id                    AS package_id,
    tp.name                  AS package_name,
    tp.code                  AS package_code,
    tp.destination,
    CONCAT(tp.duration_days,'D',tp.duration_nights,'N') AS durasi,
    c.name                   AS kategori,
    b.departure_date,
    b.participants,
    b.price_per_person,
    b.total_price,
    b.status,
    b.notes,
    b.created_at
FROM bookings b
JOIN travel_packages tp ON tp.id = b.travel_package_id
JOIN categories c        ON c.id  = tp.category_id
WHERE b.deleted_at IS NULL
ORDER BY b.created_at DESC;

-- B-2. Filter booking: status + package_id + rentang tanggal + search
SELECT
    b.id, b.booking_number, b.name AS pemesan, b.contact,
    tp.name AS package_name, tp.destination,
    b.departure_date, b.participants, b.price_per_person, b.total_price,
    b.status, b.notes, b.created_at
FROM bookings b
JOIN travel_packages tp ON tp.id = b.travel_package_id
WHERE b.deleted_at IS NULL
  AND (b.status              = 'Menunggu'   OR '' = '')   -- isi atau kosongkan
  AND (b.travel_package_id   = 1            OR 0  = 0)    -- isi id paket atau 0
  AND (b.departure_date     >= '2026-06-01' OR '' = '')   -- dateFrom
  AND (b.departure_date     <= '2026-12-31' OR '' = '')   -- dateTo
  AND (
      b.name         LIKE '%budi%'
   OR b.contact      LIKE '%budi%'
   OR tp.name        LIKE '%budi%'
   OR b.booking_number LIKE '%budi%'
  )
ORDER BY b.created_at DESC;

-- B-3. Summary cards (total, pendapatan, per-status)
SELECT
    COUNT(*)                                                           AS total_booking,
    SUM(CASE WHEN status IN ('Dikonfirmasi','Selesai')
             THEN total_price ELSE 0 END)                             AS estimasi_pendapatan,
    SUM(status = 'Menunggu')                                          AS jumlah_menunggu,
    SUM(status = 'Dikonfirmasi')                                      AS jumlah_dikonfirmasi,
    SUM(status = 'Selesai')                                           AS jumlah_selesai,
    SUM(status = 'Dibatalkan')                                        AS jumlah_dibatalkan
FROM bookings
WHERE deleted_at IS NULL;

-- B-4. Tambah booking baru (harga otomatis dari paket)
--      Frontend kirim: travel_package_id, nama, kontak, tgl, peserta, catatan
INSERT INTO bookings (
    travel_package_id, booking_number, name, contact,
    departure_date, participants, price_per_person,
    status, notes, created_at, updated_at
)
SELECT
    tp.id,
    -- Generate nomor booking: BK-YYYY + 4 digit urut (contoh via aplikasi)
    CONCAT('BK-', YEAR(NOW()), LPAD((SELECT COUNT(*)+1 FROM bookings), 4, '0')),
    'Nama Pemesan',
    '081234567890',
    '2026-07-20',
    3,
    tp.base_price,   -- ← harga otomatis dari paket, bukan input manual
    'Menunggu',
    'Catatan opsional',
    NOW(), NOW()
FROM travel_packages tp
WHERE tp.id = 1              -- ganti dengan id paket yang dipilih
  AND tp.is_active = 1
  AND tp.deleted_at IS NULL
LIMIT 1;

-- B-5. Edit booking (harga tidak berubah kecuali paket diganti)
UPDATE bookings b
JOIN travel_packages tp ON tp.id = 2 AND tp.is_active = 1 AND tp.deleted_at IS NULL
SET
    b.travel_package_id = tp.id,
    b.price_per_person  = tp.base_price,  -- refresh harga sesuai paket baru
    b.name              = 'Nama Diperbarui',
    b.contact           = 'baru@email.com',
    b.departure_date    = '2026-08-10',
    b.participants      = 4,
    b.notes             = 'Catatan diperbarui',
    b.updated_at        = NOW()
WHERE b.id = 1
  AND b.deleted_at IS NULL;

-- B-6a. Ubah status booking
UPDATE bookings
SET status = 'Dikonfirmasi', updated_at = NOW()
WHERE id = 1 AND deleted_at IS NULL;

-- B-6b. Log perubahan status
INSERT INTO booking_status_logs (booking_id, old_status, new_status, changed_by, created_at, updated_at)
VALUES (1, 'Menunggu', 'Dikonfirmasi', 'Staff Agen', NOW(), NOW());

-- B-7. Soft delete booking
UPDATE bookings SET deleted_at = NOW(), updated_at = NOW() WHERE id = 1;

-- B-8. Riwayat status satu booking
SELECT bsl.old_status, bsl.new_status, bsl.changed_by, bsl.note, bsl.created_at
FROM booking_status_logs bsl
WHERE bsl.booking_id = 1
ORDER BY bsl.created_at DESC;

-- B-9. Booking mendekati keberangkatan (7 hari ke depan)
SELECT b.booking_number, b.name, b.contact, tp.name AS paket, b.departure_date, b.participants
FROM bookings b
JOIN travel_packages tp ON tp.id = b.travel_package_id
WHERE b.deleted_at IS NULL
  AND b.status = 'Dikonfirmasi'
  AND b.departure_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
ORDER BY b.departure_date;
