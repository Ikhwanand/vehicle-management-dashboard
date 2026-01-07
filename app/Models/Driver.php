<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Driver extends Model 
{
    use Hasfactory;

    protected $fillable = [
        'name',
        'phone',
        'license_number',
        'license_expiry',
        'location_id',
        'status',
        'address',
    ];

    protected $casts = [
        'license_expiry' => 'date',
    ];

    // Get the location where this driver is assigned
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    // Get all bookings for this driver
    public function bookings(): HasMany
    {
        return $this->hasMany(VehicleBooking::class);
    }

    // Get all fuel logs for this driver
    public function fuelLogs(): HasMany 
    {
        return $this->hasMany(FuelLog::class);
    }

    // Check if driver is available
    public function isAvailable(): bool 
    {
        return $this->status === 'active';
    }

    // Check if license is expired
    public function isLicenseExpired(): bool 
    {
        return $this->license_expiry->isPast();
    }
}