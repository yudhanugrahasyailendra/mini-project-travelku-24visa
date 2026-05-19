<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Pantai & Bahari', 'slug' => 'pantai-bahari', 'description' => 'Destinasi pantai, selam, snorkeling', 'icon' => 'waves'],
            ['name' => 'Budaya & Sejarah', 'slug' => 'budaya-sejarah', 'description' => 'Candi, museum, seni tradisional', 'icon' => 'landmark'],
            ['name' => 'Petualangan', 'slug' => 'petualangan', 'description' => 'Hiking, arung jeram, off-road', 'icon' => 'mountain'],
            ['name' => 'Honeymoon', 'slug' => 'honeymoon', 'description' => 'Paket romantis untuk pasangan', 'icon' => 'heart'],
            ['name' => 'Family Tour', 'slug' => 'family-tour', 'description' => 'Paket ramah keluarga & anak-anak', 'icon' => 'users'],
            ['name' => 'City Tour', 'slug' => 'city-tour', 'description' => 'Wisata kota, kuliner, belanja', 'icon' => 'map-pin'],
        ];

        foreach ($categories as $cat) {
            Category::firstOrCreate(
                ['slug' => $cat['slug']],
                array_merge($cat, ['is_active' => true])
            );
        }
    }
}
