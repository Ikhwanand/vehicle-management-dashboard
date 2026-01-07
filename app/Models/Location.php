<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'region_id',
        'name',
        'type',
        'address',
        'phone',
    ];

    // Get the region that owns this location
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    // Get all vehicles at this location
    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }

    // Get all drivers at this location
    public function drivers(): HasMany
    {
        return $this->hasMany(Driver::class);
    }

    // Get all users at this location
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    // Get type label
    public function getTypeLabelAttribute(): string 
    {
        return match($this->type) {
            'headquarters' => 'Kantor Pusat',
            'branch' => 'Kantor Cabang',
            'mine' => 'Lokasi Tambang',
            default => $this->type,
        };
    }
}