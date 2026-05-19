<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingStatusLog;
use App\Models\TravelPackage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class BookingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Booking::with('travelPackage')->latest();

        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }
        if ($request->filled('package')) {
            $query->byPackage($request->package);
        }
        if ($request->filled('date_from')) {
            $query->dateFrom($request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->dateTo($request->date_to);
        }
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        $bookings = $query->get()->map->toFrontendArray();

        $summaryQuery = Booking::query();
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            $summary = $summaryQuery->selectRaw('
                COUNT(*) AS total,
                SUM(CASE WHEN status IN (\'Dikonfirmasi\',\'Selesai\') THEN total_price ELSE 0 END) AS revenue,
                SUM(CASE WHEN status = \'Menunggu\' THEN 1 ELSE 0 END) AS menunggu,
                SUM(CASE WHEN status = \'Dikonfirmasi\' THEN 1 ELSE 0 END) AS dikonfirmasi,
                SUM(CASE WHEN status = \'Selesai\' THEN 1 ELSE 0 END) AS selesai,
                SUM(CASE WHEN status = \'Dibatalkan\' THEN 1 ELSE 0 END) AS dibatalkan
            ')->first();
        } else {
            $summary = $summaryQuery->selectRaw('
                COUNT(*) AS total,
                SUM(CASE WHEN status IN ("Dikonfirmasi","Selesai") THEN total_price ELSE 0 END) AS revenue,
                SUM(status = "Menunggu") AS menunggu,
                SUM(status = "Dikonfirmasi") AS dikonfirmasi,
                SUM(status = "Selesai") AS selesai,
                SUM(status = "Dibatalkan") AS dibatalkan
            ')->first();
        }

        return response()->json([
            'bookings' => $bookings,
            'summary' => $summary,
            'packages' => TravelPackage::active()->pluck('name'),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validateBookingInput($request);

        $pkg = TravelPackage::where('name', $data['package'])->firstOrFail();

        $booking = Booking::create([
            'travel_package_id' => $pkg->id,
            'name' => $data['name'],
            'contact' => $data['contact'],
            'departure_date' => $data['departure_date'],
            'participants' => $data['participants'],
            'price_per_person' => $data['price_per_person'],
            'status' => Booking::STATUS_MENUNGGU,
            'notes' => $data['notes'] ?? null,
        ]);

        BookingStatusLog::create([
            'booking_id' => $booking->id,
            'old_status' => null,
            'new_status' => Booking::STATUS_MENUNGGU,
            'changed_by' => auth()->user()?->name ?? 'Staff Agen',
        ]);

        return response()->json([
            'booking' => $booking->load('travelPackage')->toFrontendArray(),
        ], 201);
    }

    public function update(Request $request, Booking $booking): JsonResponse
    {
        $data = $this->validateBookingInput($request);

        $pkg = TravelPackage::where('name', $data['package'])->firstOrFail();

        $booking->update([
            'travel_package_id' => $pkg->id,
            'name' => $data['name'],
            'contact' => $data['contact'],
            'departure_date' => $data['departure_date'],
            'participants' => $data['participants'],
            'price_per_person' => $data['price_per_person'],
            'notes' => $data['notes'] ?? null,
        ]);

        return response()->json([
            'booking' => $booking->fresh('travelPackage')->toFrontendArray(),
        ]);
    }

    public function updateStatus(Request $request, Booking $booking): JsonResponse
    {
        $request->validate([
            'status' => ['required', Rule::in(
                Booking::STATUS_TRANSITIONS[$booking->status] ?? []
            )],
        ]);

        $oldStatus = $booking->status;
        $newStatus = $request->status;

        if (! $booking->canTransitionTo($newStatus)) {
            return response()->json([
                'message' => "Tidak dapat mengubah status dari {$oldStatus} ke {$newStatus}.",
            ], 422);
        }

        DB::transaction(function () use ($booking, $oldStatus, $newStatus, $request) {
            $booking->update(['status' => $newStatus]);

            BookingStatusLog::create([
                'booking_id' => $booking->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'changed_by' => auth()->user()?->name ?? 'Staff Agen',
                'note' => $request->note,
            ]);
        });

        return response()->json([
            'message' => "Status diubah ke \"{$newStatus}\".",
            'booking' => $booking->fresh('travelPackage')->toFrontendArray(),
        ]);
    }

    public function destroy(Booking $booking): JsonResponse
    {
        $booking->delete();

        return response()->json(['message' => 'Pemesanan dihapus.']);
    }

    public function report(): JsonResponse
    {
        $report = DB::table('bookings AS b')
            ->join('travel_packages AS tp', 'tp.id', '=', 'b.travel_package_id')
            ->whereNull('b.deleted_at')
            ->whereIn('b.status', ['Dikonfirmasi', 'Selesai'])
            ->selectRaw('
                tp.name AS paket,
                COUNT(b.id) AS total_booking,
                SUM(b.participants) AS total_peserta,
                SUM(b.total_price) AS total_pendapatan,
                AVG(b.price_per_person) AS rata_harga
            ')
            ->groupBy('tp.id', 'tp.name')
            ->orderByDesc('total_pendapatan')
            ->get();

        return response()->json($report);
    }

    /** @return array<string, mixed> */
    private function validateBookingInput(Request $request): array
    {
        $input = [
            'name' => $request->input('name'),
            'contact' => $request->input('contact'),
            'package' => $request->input('package'),
            'departure_date' => $request->input('departureDate') ?? $request->input('departure_date'),
            'participants' => $request->input('participants'),
            'price_per_person' => $request->input('pricePerPerson') ?? $request->input('price_per_person'),
            'notes' => $request->input('notes'),
        ];

        return validator($input, [
            'name' => ['required', 'min:3', 'regex:/^[\pL\s]+$/u'],
            'contact' => ['required', 'regex:/^(08|\+62)[0-9]{8,13}$|^[\w.+-]+@[\w-]+\.[a-z]{2,}$/i'],
            'package' => ['required', Rule::exists('travel_packages', 'name')->where('is_active', true)],
            'departure_date' => ['required', 'date', 'after_or_equal:today'],
            'participants' => ['required', 'integer', 'min:1', 'max:100'],
            'price_per_person' => ['required', 'numeric', 'min:10000'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ])->validate();
    }
}
