<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VehicleResource\Pages;
use App\Filament\Resources\VehicleResource\RelationManagers;
use App\Models\Vehicle;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class VehicleResource extends Resource
{
    protected static ?string $model = Vehicle::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationGroup = 'Master Data';
    
    protected static ?string $navigationLabel = 'Kendaraan';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Kendaraan')
                    ->schema([
                        Forms\Components\TextInput::make('plate_number')
                            ->label('Nomor Plat')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->placeholder('e.g., B 1234 ABC'),
                        Forms\Components\TextInput::make('brand')
                            ->label('Merek')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('model')
                            ->label('Model')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('vehicle_type')
                            ->label('Jenis Kendaraan')
                            ->options([
                                'passenger' => 'Angkutan Orang',
                                'cargo' => 'Angkutan Barang',
                            ])
                            ->required()
                            ->default('passenger'),
                        Forms\Components\Select::make('ownership_type')
                            ->label('Kepemilikan')
                            ->options([
                                'owned' => 'Milik Perusahaan',
                                'rented' => 'Sewa',
                            ])
                            ->required()
                            ->default('owned')
                            ->live(),
                        Forms\Components\TextInput::make('rental_company')
                            ->label('Perusahaan Penyewa')
                            ->maxLength(255)
                            ->visible(fn (Forms\Get $get): bool => $get('ownership_type') === 'rented'),
                        Forms\Components\Select::make('location_id')
                            ->label('Lokasi Penempatan')
                            ->relationship('location', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'available' => 'Tersedia',
                                'in_use' => 'Sedang Digunakan',
                                'maintenance' => 'Dalam Perawatan',
                            ])
                            ->required()
                            ->default('available'),
                    ])->columns(2),
                
                Forms\Components\Section::make('Informasi Teknis')
                    ->schema([
                        Forms\Components\TextInput::make('fuel_consumption')
                            ->label('Konsumsi BBM (km/liter)')
                            ->numeric()
                            ->step(0.01)
                            ->suffix('km/L'),
                        Forms\Components\TextInput::make('current_odometer')
                            ->label('Odometer Saat Ini')
                            ->numeric()
                            ->default(0)
                            ->suffix('km'),
                        Forms\Components\DatePicker::make('last_service_date')
                            ->label('Tanggal Service Terakhir')
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                        Forms\Components\DatePicker::make('next_service_date')
                            ->label('Jadwal Service Berikutnya')
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('plate_number')
                    ->label('Nomor Plat')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('brand')
                    ->label('Merek')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('model')
                    ->label('Model')
                    ->searchable(),
                Tables\Columns\TextColumn::make('vehicle_type')
                    ->label('Jenis')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'passenger' => 'Angkutan Orang',
                        'cargo' => 'Angkutan Barang',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match($state) {
                        'passenger' => 'info',
                        'cargo' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('ownership_type')
                    ->label('Kepemilikan')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'owned' => 'Milik',
                        'rented' => 'Sewa',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match($state) {
                        'owned' => 'success',
                        'rented' => 'primary',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('location.name')
                    ->label('Lokasi')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'available' => 'Tersedia',
                        'in_use' => 'Digunakan',
                        'maintenance' => 'Perawatan',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match($state) {
                        'available' => 'success',
                        'in_use' => 'warning',
                        'maintenance' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('current_odometer')
                    ->label('Odometer')
                    ->numeric()
                    ->suffix(' km')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('location')
                    ->relationship('location', 'name')
                    ->label('Lokasi'),
                Tables\Filters\SelectFilter::make('vehicle_type')
                    ->label('Jenis Kendaraan')
                    ->options([
                        'passenger' => 'Angkutan Orang',
                        'cargo' => 'Angkutan Barang'
                    ]),
                Tables\Filters\SelectFilter::make('ownership_type')
                    ->label('Kepemilikan')
                    ->options([
                        'owned' => 'Milik Perusahaan',
                        'rented' => 'Sewa',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'available' => 'Tersedia',
                        'in_use' => 'Sedang Digunakan',
                        'maintenance' => 'Dalam Perawatan',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListVehicles::route('/'),
            'create' => Pages\CreateVehicle::route('/create'),
            'edit' => Pages\EditVehicle::route('/{record}/edit'),
        ];
    }
}
