<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model 
{
    use HasFactory;
    
    protected $fillable = [
        'plate_number',
        'brand',
        'model',
        'vehicle_type',
        'ownership_type',
        'rental_company',
        'location_id',
        'status',
        'fuel_consumption',
        'current_odometer',
        'last_service_date',
        'next_service_date',
        'notes',
    ];

    protected $casts = [
        'fuel_consumption' => 'decimal:2',
        'last_service_date' => 'date',
        'next_service_date' => 'date',
    ];

    /**
     * Get the location where this vehicle is assigned
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
    /**
     * Get all bookings for this vehicle
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(VehicleBooking::class);
    }
    /**
     * Get all fuel logs for this vehicle
     */
    public function fuelLogs(): HasMany
    {
        return $this->hasMany(FuelLog::class);
    }
    /**
     * Get all service schedules for this vehicle
     */
    public function serviceSchedules(): HasMany
    {
        return $this->hasMany(ServiceSchedule::class);
    }
    /**
     * Get full vehicle name (Brand + Model)
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->brand} {$this->model}";
    }
    /**
     * Get vehicle type label
     */
    public function getVehicleTypeLabelAttribute(): string
    {
        return match($this->vehicle_type) {
            'passenger' => 'Angkutan Orang',
            'cargo' => 'Angkutan Barang',
            default => $this->vehicle_type,
        };
    }
    /**
     * Get ownership type label
     */
    public function getOwnershipTypeLabelAttribute(): string
    {
        return match($this->ownership_type) {
            'owned' => 'Milik Perusahaan',
            'rented' => 'Sewa',
            default => $this->ownership_type,
        };
    }
    /**
     * Check if vehicle is available for booking
     */
    public function isAvailable(): bool
    {
        return $this->status === 'available';
    }
}