<?php

namespace App\Filament\Resources\VehicleBookingResource\Pages;

use App\Filament\Resources\VehicleBookingResource;
use App\Models\ActivityLog;
use App\Models\BookingApproval;
use App\Models\VehicleBooking;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateVehicleBooking extends CreateRecord
{
    protected static string $resource = VehicleBookingResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        $data['status'] = 'pending';
        $data['current_approval_level'] = 1;
        $data['booking_code'] = VehicleBooking::generateBookingCode();

        return $data;
    }

    protected function afterCreate(): void
    {
        $booking = $this->record;
        $formData = $this->form->getRawState();

        // Create approval records for both levels
        if (!empty($formData['approver_level_1'])) {
            BookingApproval::create([
                'booking_id' => $booking->id,
                'approver_id' => $formData['approver_level_1'],
                'level' => 1,
                'status' => 'pending',
            ]);
        }

        if (!empty($formData['approver_level_2'])) {
            BookingApproval::create([
                'booking_id' => $booking->id,
                'approver_id' => $formData['approver_level_2'],
                'level' => 2,
                'status' => 'pending',
            ]);
        }

        // Update vehicle status to in_use (reserved)
        $booking->vehicle->update(['status' => 'in_use']);

        // Log activity
        ActivityLog::create([
            'user_id' => auth()->id(),
            'loggable_type' => VehicleBooking::class,
            'loggable_id' => $booking->id,
            'action' => 'created',
            'description' => "Pemesanan baru {$booking->booking_code} dibuat",
            'new_values' => $booking->toArray(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        Notification::make()
            ->title('Pemesanan Berhasil Dibuat')
            ->body("Kode Booking: {$booking->booking_code}")
            ->success()
            ->send();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}