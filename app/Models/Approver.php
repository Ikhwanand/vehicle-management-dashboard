<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Approver extends Model 
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'level',
        'position',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Get the user associated with this approver
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Get all booking approvals by this approver
    public function bookingApprovals(): HasMany
    {
        return $this->hasMany(BookingApproval::class);
    }

    // Get level label
    public function getLevelLabelAttribute(): string 
    {
        return match($this->level) {
            1 => 'Approver Level 1',
            2 => 'Approver Level 2',
            default => "Approver Level {$this->level}",
        };
    }
}