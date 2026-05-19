<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingStatusLog;
use App\Models\TravelPackage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BookingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $bookings = $this->filteredBookingsQuery($request)->get()->map->toFrontendArray();

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
            'packages' => TravelPackage::active()
                ->orderBy('sort_order')
                ->get(['id', 'name', 'code', 'destination']),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validateBookingInput($request);

        $pkg = TravelPackage::where('id', $data['travel_package_id'])
            ->active()
            ->firstOrFail();

        $departure = Carbon::parse($data['departure_date']);

        $booking = Booking::create([
            'travel_package_id' => $pkg->id,
            'booking_number' => Booking::generateNumber(),
            'name' => $data['name'],
            'contact' => $data['contact'],
            'departure_date' => $data['departure_date'],
            'participants' => $data['participants'],
            'price_per_person' => $pkg->getPriceForDate($departure),
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
            'booking' => $booking->load('travelPackage.category')->toFrontendArray(),
        ], 201);
    }

    public function update(Request $request, Booking $booking): JsonResponse
    {
        $data = $this->validateBookingInput($request, $booking);

        $pkg = TravelPackage::where('id', $data['travel_package_id'])
            ->active()
            ->firstOrFail();

        $departure = Carbon::parse($data['departure_date']);

        $booking->update([
            'travel_package_id' => $pkg->id,
            'name' => $data['name'],
            'contact' => $data['contact'],
            'departure_date' => $data['departure_date'],
            'participants' => $data['participants'],
            'price_per_person' => $pkg->getPriceForDate($departure),
            'notes' => $data['notes'] ?? null,
        ]);

        return response()->json([
            'booking' => $booking->fresh('travelPackage.category')->toFrontendArray(),
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
            'booking' => $booking->fresh('travelPackage.category')->toFrontendArray(),
        ]);
    }

    public function destroy(Booking $booking): JsonResponse
    {
        $booking->delete();

        return response()->json(['message' => 'Pemesanan dihapus.']);
    }

    public function export(Request $request): StreamedResponse
    {
        $bookings = $this->filteredBookingsQuery($request)->get();
        $filename = 'pemesanan-travelku-'.now()->format('Y-m-d-His').'.csv';

        $headers = [
            'No. Booking',
            'Nama Pemesan',
            'Kontak',
            'Paket Wisata',
            'Tanggal Berangkat',
            'Jumlah Peserta',
            'Harga per Orang (IDR)',
            'Total Harga (IDR)',
            'Status',
            'Catatan',
            'Dibuat Pada',
        ];

        return response()->streamDownload(function () use ($bookings, $headers) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $headers);
            foreach ($bookings as $booking) {
                fputcsv($handle, $booking->toCsvRow());
            }
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
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

    private function filteredBookingsQuery(Request $request): Builder
    {
        $query = Booking::with(['travelPackage.category'])->latest();

        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        $package = $request->input('package_id') ?? $request->input('package');
        if ($package) {
            $query->byPackage($package);
        }

        $dateFrom = $request->input('date_from') ?? $request->input('dateFrom');
        $dateTo = $request->input('date_to') ?? $request->input('dateTo');

        if ($dateFrom) {
            $query->dateFrom($dateFrom);
        }
        if ($dateTo) {
            $query->dateTo($dateTo);
        }
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        return $query;
    }

    /** @return array<string, mixed> */
    private function validateBookingInput(Request $request, ?Booking $booking = null): array
    {
        $packageId = $request->input('travelPackageId')
            ?? $request->input('travel_package_id');

        $pkg = $packageId ? TravelPackage::active()->find($packageId) : null;

        $validated = validator(
            [
                'travel_package_id' => $packageId,
                'name' => $request->input('name'),
                'contact' => $request->input('contact'),
                'departure_date' => $request->input('departureDate') ?? $request->input('departure_date'),
                'participants' => $request->input('participants'),
                'notes' => $request->input('notes'),
            ],
            [
                'travel_package_id' => ['required', 'integer', Rule::exists('travel_packages', 'id')->where('is_active', true)],
                'name' => ['required', 'min:3', 'regex:/^[\pL\s]+$/u'],
                'contact' => ['required', 'regex:/^(08|\+62)[0-9]{8,13}$|^[\w.+-]+@[\w-]+\.[a-z]{2,}$/i'],
                'departure_date' => ['required', 'date', 'after_or_equal:today'],
                'participants' => [
                    'required',
                    'integer',
                    'min:'.($pkg?->min_participants ?? 1),
                    'max:'.($pkg?->max_participants ?? 100),
                ],
                'notes' => ['nullable', 'string', 'max:1000'],
            ],
            [
                'travel_package_id.required' => 'Paket wisata wajib dipilih.',
                'travel_package_id.exists' => 'Paket wisata tidak valid atau tidak aktif.',
                'participants.min' => 'Jumlah peserta di bawah minimum paket.',
                'participants.max' => 'Jumlah peserta melebihi maksimum paket.',
            ],
        )->validate();

        return $validated;
    }
}
