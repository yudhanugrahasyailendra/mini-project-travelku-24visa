<?php

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
