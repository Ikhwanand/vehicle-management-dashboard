<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FuelLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'booking_id',
        'driver_id',
        'liters',
        'cost',
        'price_per_liter',
        'odometer',
        'fuel_date',
        'fuel_type',
        'gas_station',
        'notes',
    ];

    protected $casts = [
        'liters' => 'decimal:2',
        'cost' => 'decimal:2',
        'price_per_liter' => 'decimal:2',
        'fuel_date' => 'date',
    ];

    /**
     * Get the vehicle for this fuel log
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Get the booking associated with this fuel log
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(VehicleBooking::class, 'booking_id');
    }

    /**
     * Get the driver who fueled
     */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }
}