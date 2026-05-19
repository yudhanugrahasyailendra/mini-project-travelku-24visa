<?php

// ============================================================
//  TravelKu – Migrations (Modul Paket Wisata Terpisah)
//  Jalankan: php artisan migrate
//  Seed:     php artisan db:seed
// ============================================================

// ── FILE 1: database/migrations/2024_01_01_000001_create_categories_table.php ──

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80)->unique();
            $table->string('slug', 80)->unique();
            $table->text('description')->nullable();
            $table->string('icon', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};

// ── FILE 2: database/migrations/2024_01_01_000002_create_travel_packages_table.php ──

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('travel_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')
                ->constrained('categories')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            // Identitas
            $table->string('code', 20)->unique()->comment('Kode unik, mis. PKG-001');
            $table->string('name', 150)->comment('Nama lengkap paket');
            $table->string('slug', 170)->unique();
            $table->string('destination', 100);
            $table->unsignedTinyInteger('duration_days');
            $table->unsignedTinyInteger('duration_nights');

            // Harga (sumber harga untuk booking)
            $table->decimal('base_price', 12, 2)->comment('Harga dasar per orang (IDR)');
            $table->decimal('price_weekend', 12, 2)->nullable();
            $table->decimal('price_holiday', 12, 2)->nullable();

            // Kapasitas
            $table->unsignedTinyInteger('min_participants')->default(1);
            $table->unsignedTinyInteger('max_participants')->default(100);

            // Konten
            $table->string('short_desc', 300)->nullable();
            $table->longText('description')->nullable();
            $table->text('includes')->nullable()->comment('Yang sudah termasuk');
            $table->text('excludes')->nullable()->comment('Yang tidak termasuk');
            $table->string('cover_image', 255)->nullable();

            // Status & meta
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'deleted_at']);
            $table->index('category_id');
            $table->index(['is_featured', 'is_active']);
            $table->index('sort_order');
            $table->fullText(['name', 'destination', 'short_desc']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('travel_packages');
    }
};

// ── FILE 3: database/migrations/2024_01_01_000003_create_bookings_table.php ──

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('travel_package_id')
                ->constrained('travel_packages')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->string('booking_number', 20)->unique()->comment('mis. BK-20260001');

            // Pemesan
            $table->string('name', 150);
            $table->string('contact', 150);

            // Detail
            $table->date('departure_date');
            $table->unsignedTinyInteger('participants')->default(1);

            // Harga (snapshot dari paket saat booking dibuat)
            $table->decimal('price_per_person', 12, 2)
                ->comment('Snapshot harga dari paket, tidak berubah walau paket diupdate');

            // Status
            $table->enum('status', ['Menunggu', 'Dikonfirmasi', 'Selesai', 'Dibatalkan'])
                ->default('Menunggu');
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('departure_date');
            $table->index(['travel_package_id', 'status'], 'idx_pkg_status');
        });

        // Kolom generated total_price (MySQL 5.7.6+)
        \DB::statement('
            ALTER TABLE bookings
            ADD COLUMN total_price DECIMAL(14,2)
            GENERATED ALWAYS AS (participants * price_per_person) STORED
            COMMENT "Total otomatis = participants × price_per_person"
            AFTER price_per_person
        ');
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};

// ── FILE 4: database/migrations/2024_01_01_000004_create_booking_status_logs_table.php ──

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')
                ->constrained('bookings')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->enum('old_status', ['Menunggu', 'Dikonfirmasi', 'Selesai', 'Dibatalkan'])->nullable();
            $table->enum('new_status', ['Menunggu', 'Dikonfirmasi', 'Selesai', 'Dibatalkan']);
            $table->string('changed_by', 100)->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index('booking_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_status_logs');
    }
};

// ════════════════════════════════════════════════════════════
//  app/Models/Category.php
// ════════════════════════════════════════════════════════════

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'icon', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function travelPackages(): HasMany
    {
        return $this->hasMany(TravelPackage::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}

// ════════════════════════════════════════════════════════════
//  app/Models/TravelPackage.php
// ════════════════════════════════════════════════════════════

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TravelPackage extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_id', 'code', 'name', 'slug', 'destination',
        'duration_days', 'duration_nights',
        'base_price', 'price_weekend', 'price_holiday',
        'min_participants', 'max_participants',
        'short_desc', 'description', 'includes', 'excludes', 'cover_image',
        'is_active', 'is_featured', 'sort_order',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'price_weekend' => 'decimal:2',
        'price_holiday' => 'decimal:2',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    // ── Scopes ──
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true)->where('is_active', true);
    }

    /** Format durasi: "4D3N" */
    public function getDurasiAttribute(): string
    {
        return "{$this->duration_days}D{$this->duration_nights}N";
    }

    /** Harga yang berlaku berdasarkan tanggal keberangkatan */
    public function getPriceForDate(Carbon $date): float
    {
        $isWeekend = $date->isWeekend();
        if ($isWeekend && $this->price_weekend) {
            return (float) $this->price_weekend;
        }

        return (float) $this->base_price;
    }
}

// ════════════════════════════════════════════════════════════
//  app/Models/Booking.php
// ════════════════════════════════════════════════════════════

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'travel_package_id', 'booking_number',
        'name', 'contact',
        'departure_date', 'participants', 'price_per_person',
        'status', 'notes',
    ];

    protected $casts = [
        'departure_date' => 'date',
        'participants' => 'integer',
        'price_per_person' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    const STATUS_TRANSITIONS = [
        'Menunggu' => ['Dikonfirmasi', 'Dibatalkan'],
        'Dikonfirmasi' => ['Selesai', 'Dibatalkan'],
        'Selesai' => [],
        'Dibatalkan' => [],
    ];

    public function travelPackage(): BelongsTo
    {
        return $this->belongsTo(TravelPackage::class);
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(BookingStatusLog::class)->latest();
    }

    // ── Scopes ──
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByPackage($query, int $packageId)
    {
        return $query->where('travel_package_id', $packageId);
    }

    public function scopeDateFrom($query, string $date)
    {
        return $query->where('departure_date', '>=', $date);
    }

    public function scopeDateTo($query, string $date)
    {
        return $query->where('departure_date', '<=', $date);
    }

    public function scopeSearch($query, string $keyword)
    {
        return $query->where(function ($q) use ($keyword) {
            $q->where('name', 'like', "%{$keyword}%")
                ->orWhere('contact', 'like', "%{$keyword}%")
                ->orWhere('booking_number', 'like', "%{$keyword}%")
                ->orWhereHas('travelPackage', fn ($q2) => $q2->where('name', 'like', "%{$keyword}%")
                );
        });
    }

    public function canTransitionTo(string $newStatus): bool
    {
        return in_array($newStatus, self::STATUS_TRANSITIONS[$this->status] ?? []);
    }

    /** Generate nomor booking: BK-YYYY + 4 digit urut */
    public static function generateNumber(): string
    {
        $year = now()->year;
        $count = static::whereYear('created_at', $year)->withTrashed()->count() + 1;

        return 'BK-'.$year.str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}

// ════════════════════════════════════════════════════════════
//  app/Http/Controllers/TravelPackageController.php
// ════════════════════════════════════════════════════════════

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\TravelPackage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TravelPackageController extends Controller
{
    /** GET /travel-packages — Daftar paket (untuk admin & dropdown booking) */
    public function index(Request $request)
    {
        $query = TravelPackage::with('category')->orderBy('sort_order');

        if ($request->boolean('active_only', true)) {
            $query->active();
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('search')) {
            $query->whereFullText(['name', 'destination', 'short_desc'], $request->search);
        }

        // Untuk dropdown booking: hanya id, name, base_price, durasi
        if ($request->boolean('dropdown')) {
            return response()->json(
                $query->select('id', 'code', 'name', 'destination', 'duration_days',
                    'duration_nights', 'base_price', 'min_participants', 'max_participants')
                    ->get()
                    ->map(fn ($p) => [...$p->toArray(), 'durasi' => $p->durasi])
            );
        }

        return response()->json([
            'packages' => $query->paginate(20),
            'categories' => Category::active()->get(['id', 'name']),
        ]);
    }

    /** POST /travel-packages — Tambah paket baru */
    public function store(Request $request)
    {
        $data = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:150'],
            'destination' => ['required', 'string', 'max:100'],
            'duration_days' => ['required', 'integer', 'min:1'],
            'duration_nights' => ['required', 'integer', 'min:0'],
            'base_price' => ['required', 'numeric', 'min:10000'],
            'price_weekend' => ['nullable', 'numeric', 'min:10000'],
            'price_holiday' => ['nullable', 'numeric', 'min:10000'],
            'min_participants' => ['required', 'integer', 'min:1'],
            'max_participants' => ['required', 'integer', 'min:1'],
            'short_desc' => ['nullable', 'string', 'max:300'],
            'description' => ['nullable', 'string'],
            'includes' => ['nullable', 'string'],
            'excludes' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'is_featured' => ['boolean'],
        ]);

        $lastCode = TravelPackage::withTrashed()->max('code') ?? 'PKG-000';
        $nextNum = (int) substr($lastCode, 4) + 1;

        $pkg = TravelPackage::create(array_merge($data, [
            'code' => 'PKG-'.str_pad($nextNum, 3, '0', STR_PAD_LEFT),
            'slug' => Str::slug($data['name']),
        ]));

        return response()->json($pkg->load('category'), 201);
    }

    /** PUT /travel-packages/{id} — Update paket */
    public function update(Request $request, TravelPackage $travelPackage)
    {
        $data = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:150'],
            'destination' => ['required', 'string', 'max:100'],
            'duration_days' => ['required', 'integer', 'min:1'],
            'duration_nights' => ['required', 'integer', 'min:0'],
            'base_price' => ['required', 'numeric', 'min:10000'],
            'price_weekend' => ['nullable', 'numeric'],
            'price_holiday' => ['nullable', 'numeric'],
            'min_participants' => ['required', 'integer', 'min:1'],
            'max_participants' => ['required', 'integer', 'min:1'],
            'short_desc' => ['nullable', 'string', 'max:300'],
            'description' => ['nullable', 'string'],
            'includes' => ['nullable', 'string'],
            'excludes' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'is_featured' => ['boolean'],
        ]);

        $travelPackage->update(array_merge($data, [
            'slug' => Str::slug($data['name']),
        ]));

        return response()->json($travelPackage->fresh('category'));
    }

    /** DELETE /travel-packages/{id} — Soft delete */
    public function destroy(TravelPackage $travelPackage)
    {
        // Cegah hapus paket yang masih punya booking aktif
        $aktif = $travelPackage->bookings()
            ->whereIn('status', ['Menunggu', 'Dikonfirmasi'])
            ->count();

        if ($aktif > 0) {
            return response()->json([
                'message' => "Tidak dapat menghapus paket yang masih memiliki {$aktif} booking aktif.",
            ], 422);
        }

        $travelPackage->delete();

        return response()->json(['message' => 'Paket wisata dihapus.']);
    }

    /** GET /travel-packages/{id}/stats — Statistik booking per paket */
    public function stats(TravelPackage $travelPackage)
    {
        $bookings = $travelPackage->bookings()->withoutTrashed();

        return response()->json([
            'package' => $travelPackage->only('id', 'code', 'name'),
            'total_booking' => $bookings->count(),
            'total_peserta' => $bookings->sum('participants'),
            'total_pendapatan' => $bookings->whereIn('status', ['Dikonfirmasi', 'Selesai'])->sum('total_price'),
            'per_status' => $bookings->selectRaw('status, COUNT(*) as jumlah')
                ->groupBy('status')->pluck('jumlah', 'status'),
        ]);
    }
}

// ════════════════════════════════════════════════════════════
//  app/Http/Controllers/BookingController.php (ringkas)
// ════════════════════════════════════════════════════════════

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingStatusLog;
use App\Models\TravelPackage;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $query = Booking::with(['travelPackage.category'])->latest();

        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }
        if ($request->filled('package_id')) {
            $query->byPackage($request->package_id);
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

        $summary = Booking::selectRaw('
            COUNT(*) AS total,
            SUM(CASE WHEN status IN ("Dikonfirmasi","Selesai") THEN total_price ELSE 0 END) AS revenue,
            SUM(status = "Menunggu")      AS menunggu,
            SUM(status = "Dikonfirmasi")  AS dikonfirmasi,
            SUM(status = "Selesai")       AS selesai,
            SUM(status = "Dibatalkan")    AS dibatalkan
        ')->first();

        return response()->json([
            'bookings' => $query->paginate(20),
            'summary' => $summary,
            'packages' => TravelPackage::active()->get(['id', 'name', 'code', 'destination']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'travel_package_id' => ['required', 'exists:travel_packages,id'],
            'name' => ['required', 'min:3', 'regex:/^[\pL\s]+$/u'],
            'contact' => ['required', 'regex:/^(08|\+62)[0-9]{8,13}$|^[\w.+-]+@[\w-]+\.[a-z]{2,}$/i'],
            'departure_date' => ['required', 'date', 'after_or_equal:today'],
            'participants' => ['required', 'integer', 'min:1', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        // Ambil harga dari paket (tidak dari input user)
        $pkg = TravelPackage::where('id', $data['travel_package_id'])
            ->active()->firstOrFail();

        // Validasi kapasitas
        if ($data['participants'] < $pkg->min_participants || $data['participants'] > $pkg->max_participants) {
            return response()->json([
                'message' => "Peserta harus antara {$pkg->min_participants} – {$pkg->max_participants} orang.",
            ], 422);
        }

        $booking = Booking::create([
            'travel_package_id' => $pkg->id,
            'booking_number' => Booking::generateNumber(),
            'name' => $data['name'],
            'contact' => $data['contact'],
            'departure_date' => $data['departure_date'],
            'participants' => $data['participants'],
            'price_per_person' => $pkg->getPriceForDate(Carbon::parse($data['departure_date'])),
            'status' => 'Menunggu',
            'notes' => $data['notes'] ?? null,
        ]);

        BookingStatusLog::create([
            'booking_id' => $booking->id,
            'old_status' => null,
            'new_status' => 'Menunggu',
            'changed_by' => auth()->user()?->name ?? 'Staff Agen',
        ]);

        return response()->json($booking->load('travelPackage.category'), 201);
    }

    public function updateStatus(Request $request, Booking $booking)
    {
        $request->validate([
            'status' => ['required', Rule::in(Booking::STATUS_TRANSITIONS[$booking->status] ?? [])],
        ]);

        $old = $booking->status;
        DB::transaction(function () use ($booking, $old, $request) {
            $booking->update(['status' => $request->status]);
            BookingStatusLog::create([
                'booking_id' => $booking->id,
                'old_status' => $old,
                'new_status' => $request->status,
                'changed_by' => auth()->user()?->name ?? 'Staff Agen',
                'note' => $request->note,
            ]);
        });

        return response()->json(['message' => "Status diubah ke \"{$request->status}\".", 'booking' => $booking->fresh('travelPackage')]);
    }

    public function destroy(Booking $booking)
    {
        $booking->delete();

        return response()->json(['message' => 'Pemesanan dihapus.']);
    }
}

// ════════════════════════════════════════════════════════════
//  routes/api.php  (tambahkan route berikut)
// ════════════════════════════════════════════════════════════

// use App\Http\Controllers\{TravelPackageController, BookingController};

// Route::apiResource('categories', CategoryController::class);
// Route::apiResource('travel-packages', TravelPackageController::class);
// Route::get('travel-packages/{travelPackage}/stats', [TravelPackageController::class, 'stats']);

// Route::get('bookings',           [BookingController::class, 'index']);
// Route::post('bookings',          [BookingController::class, 'store']);
// Route::patch('bookings/{booking}/status', [BookingController::class, 'updateStatus']);
// Route::delete('bookings/{booking}',       [BookingController::class, 'destroy']);
