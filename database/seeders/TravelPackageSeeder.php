<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\TravelPackage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TravelPackageSeeder extends Seeder
{
    public function run(): void
    {
        $packages = [
            ['category' => 'pantai-bahari', 'code' => 'PKG-001', 'name' => 'Bali 4D3N – Tropical Paradise', 'destination' => 'Bali', 'days' => 4, 'nights' => 3, 'base' => 2500000, 'weekend' => 2800000, 'holiday' => 3000000, 'min' => 1, 'max' => 20, 'short' => 'Jelajahi keindahan Bali: pantai, pura, dan budaya dalam 4 hari.', 'featured' => true, 'sort' => 1],
            ['category' => 'pantai-bahari', 'code' => 'PKG-002', 'name' => 'Lombok 3D2N – Pearl of the East', 'destination' => 'Lombok', 'days' => 3, 'nights' => 2, 'base' => 1800000, 'weekend' => 2000000, 'holiday' => 2200000, 'min' => 1, 'max' => 30, 'short' => 'Pantai bening, Gili Islands, dan kaki Rinjani dalam satu paket.', 'featured' => true, 'sort' => 2],
            ['category' => 'budaya-sejarah', 'code' => 'PKG-003', 'name' => 'Yogyakarta 2D1N – Warisan Budaya', 'destination' => 'Yogyakarta', 'days' => 2, 'nights' => 1, 'base' => 950000, 'weekend' => 1100000, 'holiday' => 1200000, 'min' => 2, 'max' => 40, 'short' => 'Candi Borobudur, Prambanan, Keraton, dan kuliner khas Jogja.', 'featured' => false, 'sort' => 3],
            ['category' => 'pantai-bahari', 'code' => 'PKG-004', 'name' => 'Raja Ampat 5D4N – Surga Bawah Laut', 'destination' => 'Raja Ampat', 'days' => 5, 'nights' => 4, 'base' => 5800000, 'weekend' => 6500000, 'holiday' => 7000000, 'min' => 2, 'max' => 15, 'short' => 'Menyelam di perairan terkaya biodiversitas laut di dunia.', 'featured' => true, 'sort' => 4],
            ['category' => 'petualangan', 'code' => 'PKG-005', 'name' => 'Labuan Bajo 4D3N – Komodo & Beyond', 'destination' => 'Labuan Bajo', 'days' => 4, 'nights' => 3, 'base' => 3200000, 'weekend' => 3600000, 'holiday' => 3800000, 'min' => 2, 'max' => 20, 'short' => 'Bertemu komodo, snorkeling, dan menikmati sunset terbaik di NTT.', 'featured' => true, 'sort' => 5],
            ['category' => 'petualangan', 'code' => 'PKG-006', 'name' => 'Komodo 3D2N – Naga Terakhir Bumi', 'destination' => 'Komodo', 'days' => 3, 'nights' => 2, 'base' => 2900000, 'weekend' => 3200000, 'holiday' => 3400000, 'min' => 2, 'max' => 18, 'short' => 'Perjalanan eksklusif ke Pulau Komodo dan Pink Beach.', 'featured' => false, 'sort' => 6],
            ['category' => 'pantai-bahari', 'code' => 'PKG-007', 'name' => 'Manado 4D3N – Bunaken Adventure', 'destination' => 'Manado', 'days' => 4, 'nights' => 3, 'base' => 2200000, 'weekend' => 2500000, 'holiday' => 2700000, 'min' => 1, 'max' => 25, 'short' => 'Menyelam di Taman Nasional Bunaken.', 'featured' => false, 'sort' => 7],
            ['category' => 'pantai-bahari', 'code' => 'PKG-008', 'name' => 'Bunaken 3D2N – Wall Dive Paradise', 'destination' => 'Bunaken', 'days' => 3, 'nights' => 2, 'base' => 1900000, 'weekend' => 2100000, 'holiday' => 2300000, 'min' => 2, 'max' => 16, 'short' => 'Paket khusus penyelaman di dinding karang Bunaken.', 'featured' => false, 'sort' => 8],
            ['category' => 'pantai-bahari', 'code' => 'PKG-009', 'name' => 'Wakatobi 4D3N – Coral Triangle', 'destination' => 'Wakatobi', 'days' => 4, 'nights' => 3, 'base' => 3500000, 'weekend' => 3900000, 'holiday' => 4200000, 'min' => 2, 'max' => 12, 'short' => 'Eksplorasi Segitiga Terumbu Karang Dunia.', 'featured' => false, 'sort' => 9],
        ];

        foreach ($packages as $pkg) {
            $category = Category::where('slug', $pkg['category'])->first();
            if (! $category) {
                continue;
            }

            TravelPackage::updateOrCreate(
                ['code' => $pkg['code']],
                [
                    'category_id' => $category->id,
                    'name' => $pkg['name'],
                    'slug' => Str::slug($pkg['name']),
                    'destination' => $pkg['destination'],
                    'duration_days' => $pkg['days'],
                    'duration_nights' => $pkg['nights'],
                    'base_price' => $pkg['base'],
                    'price_weekend' => $pkg['weekend'],
                    'price_holiday' => $pkg['holiday'],
                    'min_participants' => $pkg['min'],
                    'max_participants' => $pkg['max'],
                    'short_desc' => $pkg['short'],
                    'includes' => 'Hotel, makan pagi, transport AC, guide lokal',
                    'excludes' => 'Tiket pesawat, pengeluaran pribadi',
                    'is_active' => true,
                    'is_featured' => $pkg['featured'],
                    'sort_order' => $pkg['sort'],
                ]
            );
        }
    }
}
