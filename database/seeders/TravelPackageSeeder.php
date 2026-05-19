<?php

namespace Database\Seeders;

use App\Models\TravelPackage;
use Illuminate\Database\Seeder;

class TravelPackageSeeder extends Seeder
{
    public function run(): void
    {
        $packages = [
            ['name' => 'Bali 4D3N', 'duration' => '4D3N', 'destination' => 'Bali'],
            ['name' => 'Lombok 3D2N', 'duration' => '3D2N', 'destination' => 'Lombok'],
            ['name' => 'Yogyakarta 2D1N', 'duration' => '2D1N', 'destination' => 'Yogyakarta'],
            ['name' => 'Raja Ampat 5D4N', 'duration' => '5D4N', 'destination' => 'Raja Ampat'],
            ['name' => 'Labuan Bajo 4D3N', 'duration' => '4D3N', 'destination' => 'Labuan Bajo'],
            ['name' => 'Komodo 3D2N', 'duration' => '3D2N', 'destination' => 'Komodo'],
            ['name' => 'Manado 4D3N', 'duration' => '4D3N', 'destination' => 'Manado'],
            ['name' => 'Bunaken 3D2N', 'duration' => '3D2N', 'destination' => 'Bunaken'],
            ['name' => 'Wakatobi 4D3N', 'duration' => '4D3N', 'destination' => 'Wakatobi'],
        ];

        foreach ($packages as $pkg) {
            TravelPackage::firstOrCreate(
                ['name' => $pkg['name']],
                array_merge($pkg, ['is_active' => true])
            );
        }
    }
}
