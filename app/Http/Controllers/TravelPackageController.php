<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\TravelPackage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TravelPackageController extends Controller
{
    public function index(): View
    {
        $packages = TravelPackage::with('category')
            ->withCount('bookings')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map->toFrontendArray()
            ->values()
            ->all();

        return view('travelku.packages.index', [
            'packages' => $packages,
            'categories' => Category::active()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validatePackage($request);

        $package = TravelPackage::create(array_merge($data, [
            'code' => TravelPackage::generateCode(),
            'slug' => Str::slug($data['name']),
        ]));

        return response()->json([
            'package' => $package->load('category')->loadCount('bookings')->toFrontendArray(),
        ], 201);
    }

    public function update(Request $request, TravelPackage $travelPackage): JsonResponse
    {
        $data = $this->validatePackage($request, $travelPackage);

        $travelPackage->update(array_merge($data, [
            'slug' => Str::slug($data['name']),
        ]));

        return response()->json([
            'package' => $travelPackage->fresh('category')->loadCount('bookings')->toFrontendArray(),
        ]);
    }

    public function destroy(TravelPackage $travelPackage): JsonResponse
    {
        $aktif = $travelPackage->bookings()
            ->whereIn('status', ['Menunggu', 'Dikonfirmasi'])
            ->count();

        if ($aktif > 0) {
            return response()->json([
                'message' => "Tidak dapat menghapus paket yang masih memiliki {$aktif} booking aktif. Nonaktifkan paket sebagai alternatif.",
            ], 422);
        }

        if ($travelPackage->bookings()->exists()) {
            return response()->json([
                'message' => 'Paket tidak dapat dihapus karena masih memiliki riwayat pemesanan. Nonaktifkan paket sebagai alternatif.',
            ], 422);
        }

        $travelPackage->delete();

        return response()->json(['message' => 'Paket wisata dihapus.']);
    }

    /** @return array<string, mixed> */
    private function validatePackage(Request $request, ?TravelPackage $travelPackage = null): array
    {
        $isActive = $request->has('isActive')
            ? filter_var($request->input('isActive'), FILTER_VALIDATE_BOOLEAN)
            : ($request->has('is_active')
                ? filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN)
                : true);

        $isFeatured = $request->has('isFeatured')
            ? filter_var($request->input('isFeatured'), FILTER_VALIDATE_BOOLEAN)
            : ($request->has('is_featured')
                ? filter_var($request->input('is_featured'), FILTER_VALIDATE_BOOLEAN)
                : false);

        $validated = validator(
            [
                'category_id' => $request->input('categoryId') ?? $request->input('category_id'),
                'name' => $request->input('name'),
                'destination' => $request->input('destination'),
                'duration_days' => $request->input('durationDays') ?? $request->input('duration_days'),
                'duration_nights' => $request->input('durationNights') ?? $request->input('duration_nights'),
                'base_price' => $request->input('basePrice') ?? $request->input('base_price'),
                'price_weekend' => $request->input('priceWeekend') ?? $request->input('price_weekend'),
                'price_holiday' => $request->input('priceHoliday') ?? $request->input('price_holiday'),
                'min_participants' => $request->input('minParticipants') ?? $request->input('min_participants'),
                'max_participants' => $request->input('maxParticipants') ?? $request->input('max_participants'),
                'short_desc' => $request->input('shortDesc') ?? $request->input('short_desc'),
                'description' => $request->input('description'),
                'includes' => $request->input('includes'),
                'excludes' => $request->input('excludes'),
                'is_active' => $isActive,
                'is_featured' => $isFeatured,
                'sort_order' => $request->input('sortOrder') ?? $request->input('sort_order') ?? 0,
            ],
            [
                'category_id' => ['required', 'exists:categories,id'],
                'name' => [
                    'required',
                    'string',
                    'min:3',
                    'max:150',
                    Rule::unique('travel_packages', 'name')->ignore($travelPackage?->id),
                ],
                'destination' => ['required', 'string', 'max:100'],
                'duration_days' => ['required', 'integer', 'min:1', 'max:30'],
                'duration_nights' => ['required', 'integer', 'min:0', 'max:29'],
                'base_price' => ['required', 'numeric', 'min:10000'],
                'price_weekend' => ['nullable', 'numeric', 'min:10000'],
                'price_holiday' => ['nullable', 'numeric', 'min:10000'],
                'min_participants' => ['required', 'integer', 'min:1'],
                'max_participants' => ['required', 'integer', 'min:1', 'gte:min_participants'],
                'short_desc' => ['nullable', 'string', 'max:300'],
                'description' => ['nullable', 'string'],
                'includes' => ['nullable', 'string'],
                'excludes' => ['nullable', 'string'],
                'is_active' => ['boolean'],
                'is_featured' => ['boolean'],
                'sort_order' => ['integer', 'min:0'],
            ],
            [
                'category_id.required' => 'Kategori wajib dipilih.',
                'name.required' => 'Nama paket wajib diisi.',
                'name.unique' => 'Nama paket sudah digunakan.',
                'destination.required' => 'Destinasi wajib diisi.',
                'base_price.min' => 'Harga dasar minimal Rp 10.000.',
                'max_participants.gte' => 'Maks. peserta harus ≥ min. peserta.',
            ],
        )->validate();

        return $validated;
    }
}
