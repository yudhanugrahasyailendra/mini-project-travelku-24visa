<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TravelPackage extends Model
{
    protected $fillable = ['name', 'duration', 'destination', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
