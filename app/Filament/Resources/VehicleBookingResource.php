<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VehicleBookingResource\Pages;
use App\Models\Approver;
use App\Models\BookingApproval;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\VehicleBooking;
use App\Models\ActivityLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class VehicleBookingResource extends Resource
{
    protected static ?string $model = VehicleBooking::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Pemesanan';

    protected static ?string $navigationLabel = 'Pemesanan Kendaraan';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pemesanan')
                    ->schema([
                        Forms\Components\TextInput::make('booking_code')
                            ->label('Kode Booking')
                            ->disabled()
                            ->dehydrated(false)
                            ->visible(fn (string $context): bool => $context === 'edit'),
                        Forms\Components\Select::make('vehicle_id')
                            ->label('Kendaraan')
                            ->options(function () {
                                return Vehicle::where('status', 'available')
                                    ->get()
                                    ->mapWithKeys(fn ($vehicle) => [
                                        $vehicle->id => "{$vehicle->plate_number} - {$vehicle->brand} {$vehicle->model} ({$vehicle->vehicle_type_label})"
                                    ]);
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state) {
                                    $vehicle = Vehicle::find($state);
                                    if ($vehicle) {
                                        $set('start_location', $vehicle->location->name ?? '');
                                    }
                                }
                            }),
                        Forms\Components\Select::make('driver_id')
                            ->label('Driver')
                            ->options(function () {
                                return Driver::where('status', 'active')
                                    ->whereDate('license_expiry', '>', now())
                                    ->get()
                                    ->mapWithKeys(fn ($driver) => [
                                        $driver->id => "{$driver->name} - {$driver->license_number}"
                                    ]);
                            })
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Textarea::make('purpose')
                            ->label('Tujuan/Keperluan')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Jadwal Pemesanan')
                    ->schema([
                        Forms\Components\DateTimePicker::make('start_datetime')
                            ->label('Waktu Mulai')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y H:i')
                            ->minDate(now())
                            ->seconds(false),
                        Forms\Components\DateTimePicker::make('end_datetime')
                            ->label('Waktu Selesai')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y H:i')
                            ->minDate(now())
                            ->seconds(false)
                            ->after('start_datetime'),
                        Forms\Components\TextInput::make('start_location')
                            ->label('Lokasi Awal')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('end_location')
                            ->label('Lokasi Tujuan')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('passenger_count')
                            ->label('Jumlah Penumpang')
                            ->numeric()
                            ->default(1)
                            ->minValue(1)
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Pihak Penyetuju')
                    ->description('Pilih approver untuk setiap level persetujuan.')
                    ->schema([
                        Forms\Components\Select::make('approver_level_1')
                            ->label('Approver Level 1 (Atasan Langsung)')
                            ->options(function () {
                                return Approver::where('level', 1)
                                    ->where('is_active', true)
                                    ->with('user')
                                    ->get()
                                    ->mapWithKeys(fn ($approver) => [
                                        $approver->id => "{$approver->user->name} - {$approver->position}"
                                    ]);
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->dehydrated(false),
                        Forms\Components\Select::make('approver_level_2')
                            ->label('Approver Level 2 (Manager/Pimpinan)')
                            ->options(function () {
                                return Approver::where('level', 2)
                                    ->where('is_active', true)
                                    ->with('user')
                                    ->get()
                                    ->mapWithKeys(fn ($approver) => [
                                        $approver->id => "{$approver->user->name} - {$approver->position}"
                                    ]);
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->dehydrated(false),
                    ])->columns(2)
                    ->visible(fn (string $context): bool => $context === 'create'),

                Forms\Components\Section::make('Catatan')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan Tambahan')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('booking_code')
                    ->label('Kode Booking')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable()
                    ->copyMessage('Kode booking disalin!'),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Pemohon')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('vehicle.plate_number')
                    ->label('Kendaraan')
                    ->description(fn (VehicleBooking $record): string => 
                        "{$record->vehicle->brand} {$record->vehicle->model}"
                    )
                    ->searchable(),
                Tables\Columns\TextColumn::make('driver.name')
                    ->label('Driver')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('start_datetime')
                    ->label('Waktu Mulai')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_datetime')
                    ->label('Waktu Selesai')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('status')
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
                Tables\Columns\TextColumn::make('current_approval_level')
                    ->label('Level Approval')
                    ->badge()
                    ->formatStateUsing(fn (int $state, VehicleBooking $record): string => 
                        $record->status === 'pending' ? "Level {$state}" : '-'
                    )
                    ->color('info')
                    ->visible(fn (): bool => auth()->user()->isAdmin()),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Menunggu Approval',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        'in_progress' => 'Sedang Berjalan',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                    ]),
                Tables\Filters\SelectFilter::make('vehicle')
                    ->relationship('vehicle', 'plate_number')
                    ->label('Kendaraan'),
                Tables\Filters\Filter::make('date_range')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('start_datetime', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('start_datetime', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn (VehicleBooking $record): bool => 
                        $record->status === 'pending' && auth()->user()->isAdmin()
                    ),
                Tables\Actions\Action::make('cancel')
                    ->label('Batalkan')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Batalkan Pemesanan')
                    ->modalDescription('Apakah Anda yakin ingin membatalkan pemesanan ini?')
                    ->visible(fn (VehicleBooking $record): bool => 
                        in_array($record->status, ['pending', 'approved']) && auth()->user()->isAdmin()
                    )
                    ->action(function (VehicleBooking $record) {
                        $record->update(['status' => 'cancelled']);
                        
                        // Log activity
                        ActivityLog::create([
                            'user_id' => auth()->id(),
                            'loggable_type' => VehicleBooking::class,
                            'loggable_id' => $record->id,
                            'action' => 'cancelled',
                            'description' => "Pemesanan {$record->booking_code} dibatalkan",
                            'ip_address' => request()->ip(),
                            'user_agent' => request()->userAgent(),
                        ]);

                        // Update vehicle status back to available
                        $record->vehicle->update(['status' => 'available']);

                        Notification::make()
                            ->title('Pemesanan Dibatalkan')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn (): bool => auth()->user()->isAdmin()),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVehicleBookings::route('/'),
            'create' => Pages\CreateVehicleBooking::route('/create'),
            'view' => Pages\ViewVehicleBooking::route('/{record}'),
            'edit' => Pages\EditVehicleBooking::route('/{record}/edit'),
        ];
    }
}