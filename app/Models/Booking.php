<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'travel_package_id',
        'name',
        'contact',
        'departure_date',
        'participants',
        'price_per_person',
        'status',
        'notes',
    ];

    protected $casts = [
        'departure_date' => 'date',
        'participants' => 'integer',
        'price_per_person' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    const STATUS_MENUNGGU = 'Menunggu';

    const STATUS_DIKONFIRMASI = 'Dikonfirmasi';

    const STATUS_SELESAI = 'Selesai';

    const STATUS_DIBATALKAN = 'Dibatalkan';

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

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByPackage($query, string $packageName)
    {
        return $query->whereHas('travelPackage', fn ($q) => $q->where('name', $packageName));
    }

    public function scopeDateFrom($query, string $date)
    {
        return $query->where('departure_date', '>=', $date);
    }

    public function scopeDateTo($query, string $date)
    {
        return $query->where('departure_date', '<=', $date);
    }

    /** Pencarian berdasarkan nama pemesan atau kontak (HP/email). */
    public function scopeSearch($query, string $keyword)
    {
        $term = '%'.addcslashes(trim($keyword), '%_\\').'%';

        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', $term)
                ->orWhere('contact', 'like', $term);
        });
    }

    public function canTransitionTo(string $newStatus): bool
    {
        return in_array($newStatus, self::STATUS_TRANSITIONS[$this->status] ?? []);
    }

    public function getTotalHarga(): float
    {
        return (float) ($this->total_price ?? ($this->participants * $this->price_per_person));
    }

    /** Baris data untuk export CSV. */
    public function toCsvRow(): array
    {
        $this->loadMissing('travelPackage');

        return [
            $this->id,
            $this->name,
            $this->contact,
            $this->travelPackage?->name ?? '',
            $this->departure_date?->format('Y-m-d') ?? '',
            (int) $this->participants,
            (float) $this->price_per_person,
            $this->getTotalHarga(),
            $this->status,
            $this->notes ?? '',
            $this->created_at?->format('Y-m-d H:i:s') ?? '',
        ];
    }

    /** Format untuk Alpine.js (camelCase) */
    public function toFrontendArray(): array
    {
        $this->loadMissing('travelPackage');

        return [
            'id' => $this->id,
            'name' => $this->name,
            'contact' => $this->contact,
            'package' => $this->travelPackage?->name ?? '',
            'departureDate' => $this->departure_date?->format('Y-m-d') ?? '',
            'participants' => (int) $this->participants,
            'pricePerPerson' => (float) $this->price_per_person,
            'status' => $this->status,
            'notes' => $this->notes ?? '',
            'createdAt' => $this->created_at?->getTimestampMs() ?? 0,
        ];
    }
}
