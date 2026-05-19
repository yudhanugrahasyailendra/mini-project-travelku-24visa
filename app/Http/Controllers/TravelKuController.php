<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\TravelPackage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TravelKuController extends Controller
{
    public function index(): View
    {
        $bookings = Booking::with(['travelPackage.category'])
            ->latest()
            ->get()
            ->map->toFrontendArray()
            ->values()
            ->all();

        return view('travelku.index', [
            'packages' => TravelPackage::active()
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get()
                ->map->toBookingOptionArray()
                ->values()
                ->all(),
            'statuses' => config('travelku.statuses'),
            'statusTransitions' => config('travelku.status_transitions'),
            'bookings' => $bookings,
        ]);
    }

    public function validateBooking(Request $request): JsonResponse
    {
        $packageId = $request->input('travelPackageId') ?? $request->input('travel_package_id');
        $pkg = $packageId
            ? TravelPackage::active()->find($packageId)
            : null;

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'min:3', 'regex:/^[\pL\s]+$/u'],
            'contact' => ['required', function (string $attribute, mixed $value, \Closure $fail): void {
                $phone = preg_match('/^(08|\+62)\d{8,13}$/', preg_replace('/[\s\-]/', '', (string) $value));
                $email = filter_var($value, FILTER_VALIDATE_EMAIL);

                if (! $phone && ! $email) {
                    $fail('Kontak harus berupa nomor HP Indonesia (08/+62) atau email yang valid.');
                }
            }],
            'travelPackageId' => ['required', 'integer', Rule::exists('travel_packages', 'id')->where('is_active', true)],
            'departureDate' => ['required', 'date', 'after_or_equal:today'],
            'participants' => ['required', 'integer', 'min:1'],
        ], [
            'name.required' => 'Nama pemesan wajib diisi.',
            'name.min' => 'Nama pemesan minimal 3 karakter.',
            'name.regex' => 'Nama hanya boleh berisi huruf dan spasi.',
            'contact.required' => 'Kontak wajib diisi.',
            'travelPackageId.required' => 'Paket wisata wajib dipilih.',
            'travelPackageId.exists' => 'Paket wisata tidak valid atau tidak aktif.',
            'departureDate.required' => 'Tanggal keberangkatan wajib diisi.',
            'departureDate.after_or_equal' => 'Tanggal keberangkatan tidak boleh di masa lalu.',
            'participants.required' => 'Jumlah peserta wajib diisi.',
            'participants.min' => 'Jumlah peserta minimal 1.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'valid' => false,
                'errors' => $validator->errors()->toArray(),
            ]);
        }

        if ($pkg && $request->filled('participants')) {
            $p = (int) $request->participants;
            if ($p < $pkg->min_participants || $p > $pkg->max_participants) {
                return response()->json([
                    'valid' => false,
                    'errors' => [
                        'participants' => ["Peserta harus antara {$pkg->min_participants} – {$pkg->max_participants} orang."],
                    ],
                ]);
            }
        }

        return response()->json([
            'valid' => true,
            'errors' => (object) [],
            'pricePerPerson' => $pkg
                ? $pkg->getPriceForDate(Carbon::parse($request->departureDate))
                : null,
        ]);
    }
}
