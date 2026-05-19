<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\TravelPackage;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class BookingSeeder extends Seeder
{
    public function run(): void
    {
        $seeds = [
            ['package' => 'Bali 4D3N', 'name' => 'Budi Santoso', 'contact' => '081234567890', 'departure_date' => '2026-06-15', 'participants' => 4, 'price_per_person' => 2500000, 'status' => 'Dikonfirmasi', 'notes' => 'Minta kamar non-smoking', 'days_ago' => 3],
            ['package' => 'Lombok 3D2N', 'name' => 'Siti Rahayu', 'contact' => 'siti@email.com', 'departure_date' => '2026-07-10', 'participants' => 2, 'price_per_person' => 1800000, 'status' => 'Menunggu', 'notes' => null, 'days_ago' => 1],
            ['package' => 'Yogyakarta 2D1N', 'name' => 'Ahmad Fauzi', 'contact' => '085678901234', 'departure_date' => '2026-05-25', 'participants' => 6, 'price_per_person' => 950000, 'status' => 'Selesai', 'notes' => 'Group tour mahasiswa', 'days_ago' => 5],
            ['package' => 'Raja Ampat 5D4N', 'name' => 'Dewi Kusuma', 'contact' => 'dewi@gmail.com', 'departure_date' => '2026-08-02', 'participants' => 3, 'price_per_person' => 5800000, 'status' => 'Menunggu', 'notes' => 'Honeymoon package', 'days_ago' => 2],
            ['package' => 'Labuan Bajo 4D3N', 'name' => 'Hendra Wijaya', 'contact' => '081999888777', 'departure_date' => '2026-09-14', 'participants' => 8, 'price_per_person' => 3200000, 'status' => 'Dikonfirmasi', 'notes' => 'Grup kantor', 'days_ago' => 4],
            ['package' => 'Komodo 3D2N', 'name' => 'Rina Hastuti', 'contact' => 'rina.h@mail.id', 'departure_date' => '2026-06-01', 'participants' => 2, 'price_per_person' => 2900000, 'status' => 'Dibatalkan', 'notes' => 'Pembatalan karena sakit', 'days_ago' => 6],
        ];

        foreach ($seeds as $seed) {
            $pkg = TravelPackage::where('name', $seed['package'])->first();
            if (! $pkg) {
                continue;
            }

            $ts = Carbon::now()->subDays($seed['days_ago']);
            Booking::create([
                'travel_package_id' => $pkg->id,
                'name' => $seed['name'],
                'contact' => $seed['contact'],
                'departure_date' => $seed['departure_date'],
                'participants' => $seed['participants'],
                'price_per_person' => $seed['price_per_person'],
                'status' => $seed['status'],
                'notes' => $seed['notes'],
                'created_at' => $ts,
                'updated_at' => $ts,
            ]);
        }
    }
}
