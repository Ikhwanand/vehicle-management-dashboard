<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class VehicleBooking extends Model 
{
    use HasFactory;

    protected $fillable = [
        'booking_code',
        'user_id',
        'vehicle_id',
        'driver_id',
        'purpose',
        'start_datetime',
        'end_datetime',
        'start_location',
        'end_location',
        'passenger_count',
        'status',
        'current_approval_level',
        'notes',
        'rejection_reason',
        'start_odometer',
        'end_odometer',
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
    ];

     /**
     * Boot method to generate booking code
     */
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($booking) {
            if (empty($booking->booking_code)) {
                $booking->booking_code = self::generateBookingCode();
            }
        });
    }
    /**
     * Generate unique booking code
     */
    public static function generateBookingCode(): string
    {
        $prefix = 'BK';
        $date = now()->format('Ymd');
        $random = strtoupper(substr(uniqid(), -4));
        return "{$prefix}{$date}{$random}";
    }
    /**
     * Get the requester (user who made the booking)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    /**
     * Get the vehicle for this booking
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
    /**
     * Get the driver for this booking
     */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }
    /**
     * Get all approvals for this booking
     */
    public function approvals(): HasMany
    {
        return $this->hasMany(BookingApproval::class, 'booking_id');
    }
    /**
     * Get all fuel logs for this booking
     */
    public function fuelLogs(): HasMany
    {
        return $this->hasMany(FuelLog::class, 'booking_id');
    }
    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Menunggu Persetujuan',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            'in_progress' => 'Sedang Berjalan',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            default => $this->status,
        };
    }
    /**
     * Get status color for UI
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            'in_progress' => 'info',
            'completed' => 'success',
            'cancelled' => 'gray',
            default => 'gray',
        };
    }
    /**
     * Check if booking is fully approved
     */
    public function isFullyApproved(): bool
    {
        return $this->status === 'approved';
    }
    /**
     * Get calculated distance
     */
    public function getDistanceAttribute(): ?int
    {
        if ($this->start_odometer && $this->end_odometer) {
            return $this->end_odometer - $this->start_odometer;
        }
        return null;
    }
}