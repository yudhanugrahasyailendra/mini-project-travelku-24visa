<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
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

    protected function durasi(): Attribute
    {
        return Attribute::get(fn () => "{$this->duration_days}D{$this->duration_nights}N");
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true)->where('is_active', true);
    }

    public function scopeSearch($query, string $keyword)
    {
        $term = '%'.addcslashes(trim($keyword), '%_\\').'%';

        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', $term)
                ->orWhere('destination', 'like', $term)
                ->orWhere('code', 'like', $term)
                ->orWhere('short_desc', 'like', $term);
        });
    }

    public function getPriceForDate(Carbon $date): float
    {
        if ($date->isWeekend() && $this->price_weekend) {
            return (float) $this->price_weekend;
        }

        return (float) $this->base_price;
    }

    public static function generateCode(): string
    {
        $lastCode = static::withTrashed()->max('code') ?? 'PKG-000';
        $nextNum = (int) substr($lastCode, 4) + 1;

        return 'PKG-'.str_pad((string) $nextNum, 3, '0', STR_PAD_LEFT);
    }

    /** Format untuk Alpine.js */
    public function toFrontendArray(): array
    {
        $this->loadMissing('category');

        return [
            'id' => $this->id,
            'categoryId' => $this->category_id,
            'categoryName' => $this->category?->name ?? '',
            'code' => $this->code,
            'name' => $this->name,
            'slug' => $this->slug,
            'destination' => $this->destination,
            'durationDays' => (int) $this->duration_days,
            'durationNights' => (int) $this->duration_nights,
            'durasi' => $this->durasi,
            'basePrice' => (float) $this->base_price,
            'priceWeekend' => $this->price_weekend ? (float) $this->price_weekend : null,
            'priceHoliday' => $this->price_holiday ? (float) $this->price_holiday : null,
            'minParticipants' => (int) $this->min_participants,
            'maxParticipants' => (int) $this->max_participants,
            'shortDesc' => $this->short_desc ?? '',
            'description' => $this->description ?? '',
            'includes' => $this->includes ?? '',
            'excludes' => $this->excludes ?? '',
            'isActive' => (bool) $this->is_active,
            'isFeatured' => (bool) $this->is_featured,
            'sortOrder' => (int) $this->sort_order,
            'bookingsCount' => (int) ($this->bookings_count ?? $this->bookings()->count()),
            'createdAt' => $this->created_at?->getTimestampMs() ?? 0,
        ];
    }

    /** Dropdown pemesanan */
    public function toBookingOptionArray(): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'destination' => $this->destination,
            'durasi' => $this->durasi,
            'basePrice' => (float) $this->base_price,
            'minParticipants' => (int) $this->min_participants,
            'maxParticipants' => (int) $this->max_participants,
        ];
    }
}
