<?php

namespace App\Filament\Resources\VehicleBookingResource\Pages;

use App\Filament\Resources\VehicleBookingResource;
use App\Models\ActivityLog;
use App\Models\BookingApproval;
use App\Models\VehicleBooking;
use Filament\Actions;
use Filament\Infolists\Components;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Filament\Notifications\Notification;

class ViewVehicleBooking extends ViewRecord
{
    protected static string $resource = VehicleBookingResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Components\Section::make('Informasi Pemesanan')
                    ->schema([
                        Components\TextEntry::make('booking_code')
                            ->label('Kode Booking')
                            ->weight('bold')
                            ->copyable(),
                        Components\TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => match($state) {
                                'pending' => 'Menunggu Approval',
                                'approved' => 'Disetujui',
                                'rejected' => 'Ditolak',
                                'in_progress' => 'Sedang Berjalan',
                                'completed' => 'Selesai',
                                'cancelled' => 'Dibatalkan',
                                default => $state,
                            })
                            ->color(fn (string $state): string => match($state) {
                                'pending' => 'warning',
                                'approved' => 'success',
                                'rejected' => 'danger',
                                'in_progress' => 'info',
                                'completed' => 'success',
                                'cancelled' => 'gray',
                                default => 'gray',
                            }),
                        Components\TextEntry::make('user.name')
                            ->label('Pemohon'),
                        Components\TextEntry::make('purpose')
                            ->label('Tujuan/Keperluan')
                            ->columnSpanFull(),
                    ])->columns(3),

                Components\Section::make('Detail Kendaraan & Driver')
                    ->schema([
                        Components\TextEntry::make('vehicle.plate_number')
                            ->label('Nomor Plat'),
                        Components\TextEntry::make('vehicle.full_name')
                            ->label('Kendaraan'),
                        Components\TextEntry::make('vehicle.vehicle_type_label')
                            ->label('Jenis'),
                        Components\TextEntry::make('driver.name')
                            ->label('Driver'),
                        Components\TextEntry::make('driver.phone')
                            ->label('Telepon Driver'),
                        Components\TextEntry::make('driver.license_number')
                            ->label('No. SIM'),
                    ])->columns(3),

                Components\Section::make('Jadwal & Lokasi')
                    ->schema([
                        Components\TextEntry::make('start_datetime')
                            ->label('Waktu Mulai')
                            ->dateTime('d M Y H:i'),
                        Components\TextEntry::make('end_datetime')
                            ->label('Waktu Selesai')
                            ->dateTime('d M Y H:i'),
                        Components\TextEntry::make('passenger_count')
                            ->label('Jumlah Penumpang'),
                        Components\TextEntry::make('start_location')
                            ->label('Lokasi Awal'),
                        Components\TextEntry::make('end_location')
                            ->label('Lokasi Tujuan'),
                    ])->columns(3),

                Components\Section::make('Status Persetujuan')
                    ->schema([
                        Components\RepeatableEntry::make('approvals')
                            ->label('')
                            ->schema([
                                Components\TextEntry::make('level')
                                    ->label('Level')
                                    ->formatStateUsing(fn (int $state): string => "Level {$state}"),
                                Components\TextEntry::make('approver.user.name')
                                    ->label('Approver'),
                                Components\TextEntry::make('approver.position')
                                    ->label('Jabatan'),
                                Components\TextEntry::make('status')
                                    ->label('Status')
                                    ->badge()
                                    ->formatStateUsing(fn (string $state): string => match($state) {
                                        'pending' => 'Menunggu',
                                        'approved' => 'Disetujui',
                                        'rejected' => 'Ditolak',
                                        default => $state,
                                    })
                                    ->color(fn (string $state): string => match($state) {
                                        'pending' => 'warning',
                                        'approved' => 'success',
                                        'rejected' => 'danger',
                                        default => 'gray',
                                    }),
                                Components\TextEntry::make('approved_at')
                                    ->label('Waktu')
                                    ->dateTime('d M Y H:i')
                                    ->placeholder('-'),
                                Components\TextEntry::make('notes')
                                    ->label('Catatan')
                                    ->placeholder('-'),
                            ])->columns(6),
                    ]),

                Components\Section::make('Catatan')
                    ->schema([
                        Components\TextEntry::make('notes')
                            ->label('')
                            ->placeholder('Tidak ada catatan'),
                        Components\TextEntry::make('rejection_reason')
                            ->label('Alasan Penolakan')
                            ->visible(fn (VehicleBooking $record): bool => $record->status === 'rejected'),
                    ])
                    ->collapsed(),
            ]);
    }

    protected function getHeaderActions(): array
    {
        $user = auth()->user();
        $booking = $this->record;

        $actions = [];

        // Approval actions for approvers
        if ($user->isApprover() && $booking->status === 'pending') {
            $approver = $user->approver;

            if ($approver) {
                $pendingApproval = BookingApproval::where('booking_id', $booking->id)
                    ->where('approver_id', $approver->id)
                    ->where('level', $booking->current_approval_level)
                    ->where('status', 'pending')
                    ->first();

                if ($pendingApproval) {
                    $actions[] = Actions\Action::make('approve')
                        ->label('Setujui')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Setujui Pemesanan')
                        ->modalDescription('Apakah Anda yakin ingin menyetujui pemesanan ini?')
                        ->form([
                            \Filament\Forms\Components\Textarea::make('notes')
                                ->label('Catatan (Opsional)')
                                ->rows(3),
                        ])
                        ->action(function (array $data) use ($booking, $pendingApproval, $approver) {
                            // Update approval record
                            $pendingApproval->update([
                                'status' => 'approved',
                                'notes' => $data['notes'] ?? null,
                                'approved_at' => now(),
                            ]);

                            // Check if there's next level approval
                            $nextApproval = BookingApproval::where('booking_id', $booking->id)
                                ->where('level', $booking->current_approval_level + 1)
                                ->where('status', 'pending')
                                ->first();

                            if ($nextApproval) {
                                // Move to next approval level
                                $booking->update([
                                    'current_approval_level' => $booking->current_approval_level + 1,
                                ]);
                            } else {
                                // All approvals done, mark as approved
                                $booking->update([
                                    'status' => 'approved',
                                ]);
                            }

                            // Log activity
                            ActivityLog::create([
                                'user_id' => auth()->id(),
                                'loggable_type' => VehicleBooking::class,
                                'loggable_id' => $booking->id,
                                'action' => 'approved',
                                'description' => "Pemesanan {$booking->booking_code} disetujui oleh {$approver->user->name} (Level {$approver->level})",
                                'ip_address' => request()->ip(),
                                'user_agent' => request()->userAgent(),
                            ]);

                            Notification::make()
                                ->title('Pemesanan Disetujui')
                                ->success()
                                ->send();

                            $this->redirect($this->getResource()::getUrl('view', ['record' => $booking]));
                        });

                    $actions[] = Actions\Action::make('reject')
                        ->label('Tolak')
                        ->icon('heroicon-o-x-mark')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Tolak Pemesanan')
                        ->form([
                            \Filament\Forms\Components\Textarea::make('rejection_reason')
                                ->label('Alasan Penolakan')
                                ->required()
                                ->rows(3),
                        ])
                        ->action(function (array $data) use ($booking, $pendingApproval, $approver) {
                            // Update approval record
                            $pendingApproval->update([
                                'status' => 'rejected',
                                'notes' => $data['rejection_reason'],
                                'approved_at' => now(),
                            ]);

                            // Mark booking as rejected
                            $booking->update([
                                'status' => 'rejected',
                                'rejection_reason' => $data['rejection_reason'],
                            ]);

                            // Update vehicle status back to available
                            $booking->vehicle->update(['status' => 'available']);

                            // Log activity
                            ActivityLog::create([
                                'user_id' => auth()->id(),
                                'loggable_type' => VehicleBooking::class,
                                'loggable_id' => $booking->id,
                                'action' => 'rejected',
                                'description' => "Pemesanan {$booking->booking_code} ditolak oleh {$approver->user->name}",
                                'ip_address' => request()->ip(),
                                'user_agent' => request()->userAgent(),
                            ]);

                            Notification::make()
                                ->title('Pemesanan Ditolak')
                                ->success()
                                ->send();

                            $this->redirect($this->getResource()::getUrl('view', ['record' => $booking]));
                        });
                }
            }
        }

        // Edit action for admin
        if ($user->isAdmin() && $booking->status === 'pending') {
            $actions[] = Actions\EditAction::make();
        }

        return $actions;
    }
}