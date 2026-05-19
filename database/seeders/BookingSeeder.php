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
            ['package' => 'PKG-001', 'number' => 'BK-20260001', 'name' => 'Budi Santoso', 'contact' => '081234567890', 'departure_date' => '2026-06-15', 'participants' => 4, 'status' => 'Dikonfirmasi', 'notes' => 'Minta kamar non-smoking', 'days_ago' => 3],
            ['package' => 'PKG-002', 'number' => 'BK-20260002', 'name' => 'Siti Rahayu', 'contact' => 'siti@email.com', 'departure_date' => '2026-07-10', 'participants' => 2, 'status' => 'Menunggu', 'notes' => null, 'days_ago' => 1],
            ['package' => 'PKG-003', 'number' => 'BK-20260003', 'name' => 'Ahmad Fauzi', 'contact' => '085678901234', 'departure_date' => '2026-05-25', 'participants' => 6, 'status' => 'Selesai', 'notes' => 'Group tour mahasiswa', 'days_ago' => 5],
            ['package' => 'PKG-004', 'number' => 'BK-20260004', 'name' => 'Dewi Kusuma', 'contact' => 'dewi@gmail.com', 'departure_date' => '2026-08-02', 'participants' => 3, 'status' => 'Menunggu', 'notes' => 'Honeymoon package', 'days_ago' => 2],
            ['package' => 'PKG-005', 'number' => 'BK-20260005', 'name' => 'Hendra Wijaya', 'contact' => '081999888777', 'departure_date' => '2026-09-14', 'participants' => 8, 'status' => 'Dikonfirmasi', 'notes' => 'Grup kantor', 'days_ago' => 4],
            ['package' => 'PKG-006', 'number' => 'BK-20260006', 'name' => 'Rina Hastuti', 'contact' => 'rina.h@mail.id', 'departure_date' => '2026-06-01', 'participants' => 2, 'status' => 'Dibatalkan', 'notes' => 'Pembatalan karena sakit', 'days_ago' => 6],
        ];

        foreach ($seeds as $seed) {
            $pkg = TravelPackage::where('code', $seed['package'])->first();
            if (! $pkg) {
                continue;
            }

            $ts = Carbon::now()->subDays($seed['days_ago']);
            $departure = Carbon::parse($seed['departure_date']);

            Booking::updateOrCreate(
                ['booking_number' => $seed['number']],
                [
                    'travel_package_id' => $pkg->id,
                    'name' => $seed['name'],
                    'contact' => $seed['contact'],
                    'departure_date' => $seed['departure_date'],
                    'participants' => $seed['participants'],
                    'price_per_person' => $pkg->getPriceForDate($departure),
                    'status' => $seed['status'],
                    'notes' => $seed['notes'],
                    'created_at' => $ts,
                    'updated_at' => $ts,
                ]
            );
        }
    }
}
