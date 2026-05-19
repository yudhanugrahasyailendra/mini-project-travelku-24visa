<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\TravelPackage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TravelKuController extends Controller
{
    public function index(): View
    {
        $bookings = Booking::with('travelPackage')
            ->latest()
            ->get()
            ->map->toFrontendArray()
            ->values()
            ->all();

        return view('travelku.index', [
            'packages' => TravelPackage::active()->orderBy('name')->pluck('name')->values()->all(),
            'statuses' => config('travelku.statuses'),
            'statusTransitions' => config('travelku.status_transitions'),
            'bookings' => $bookings,
        ]);
    }

    public function validateBooking(Request $request): JsonResponse
    {
        $activePackages = TravelPackage::active()->pluck('name')->all();

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'min:3', 'regex:/^[\pL\s]+$/u'],
            'contact' => ['required', function (string $attribute, mixed $value, \Closure $fail): void {
                $phone = preg_match('/^(08|\+62)\d{8,13}$/', preg_replace('/[\s\-]/', '', (string) $value));
                $email = filter_var($value, FILTER_VALIDATE_EMAIL);

                if (! $phone && ! $email) {
                    $fail('Kontak harus berupa nomor HP Indonesia (08/+62) atau email yang valid.');
                }
            }],
            'package' => ['required', 'string', Rule::in($activePackages)],
            'departureDate' => ['required', 'date', 'after_or_equal:today'],
            'participants' => ['required', 'integer', 'min:1', 'max:100'],
            'pricePerPerson' => ['required', 'numeric', 'min:10000'],
        ], [
            'name.required' => 'Nama pemesan wajib diisi.',
            'name.min' => 'Nama pemesan minimal 3 karakter.',
            'name.regex' => 'Nama hanya boleh berisi huruf dan spasi.',
            'contact.required' => 'Kontak wajib diisi.',
            'package.required' => 'Paket wisata wajib dipilih.',
            'package.in' => 'Paket wisata tidak valid.',
            'departureDate.required' => 'Tanggal keberangkatan wajib diisi.',
            'departureDate.after_or_equal' => 'Tanggal keberangkatan tidak boleh di masa lalu.',
            'participants.required' => 'Jumlah peserta wajib diisi.',
            'participants.min' => 'Jumlah peserta minimal 1.',
            'participants.max' => 'Jumlah peserta maksimal 100.',
            'pricePerPerson.required' => 'Harga per orang wajib diisi.',
            'pricePerPerson.min' => 'Harga per orang minimal Rp 10.000.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'valid' => false,
                'errors' => $validator->errors()->toArray(),
            ]);
        }

        return response()->json([
            'valid' => true,
            'errors' => (object) [],
        ]);
    }
}
