<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ApproverResource\Pages;
use App\Models\Approver;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ApproverResource extends Resource
{
    protected static ?string $model = Approver::class;

    protected static ?string $navigationIcon = 'heroicon-o-check-badge';

    protected static ?string $navigationGroup = 'User Management';

    protected static ?string $navigationLabel = 'Approver';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Pengaturan Approver')
                    ->description('Tentukan siapa yang berhak menyetujui pemesanan kendaraan dan level approvalnya.')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Pilih User')
                            ->options(User::where('role', 'approver')->where('is_active', true)->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->unique(ignoreRecord: true, modifyRuleUsing: function ($rule, $state, $get) {
                                return $rule->where('level', $get('level'));
                            })
                            ->helperText('Hanya user dengan role "Approver" yang dapat dipilih.'),
                        Forms\Components\Select::make('level')
                            ->label('Level Approval')
                            ->options([
                                1 => 'Level 1 - Atasan Langsung',
                                2 => 'Level 2 - Manager/Pimpinan',
                            ])
                            ->required()
                            ->default(1)
                            ->helperText('Level menentukan urutan persetujuan. Level 1 disetujui terlebih dahulu.'),
                        Forms\Components\TextInput::make('position')
                            ->label('Jabatan/Posisi')
                            ->maxLength(255)
                            ->placeholder('e.g., Supervisor, Manager, dll'),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Status Aktif')
                            ->default(true)
                            ->helperText('Approver yang tidak aktif tidak akan menerima permintaan approval.'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Nama Approver')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('level')
                    ->label('Level')
                    ->badge()
                    ->formatStateUsing(fn (int $state): string => "Level {$state}")
                    ->color(fn (int $state): string => match($state) {
                        1 => 'info',
                        2 => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('position')
                    ->label('Jabatan')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                Tables\Columns\TextColumn::make('booking_approvals_count')
                    ->label('Total Approval')
                    ->counts('bookingApprovals')
                    ->badge()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('level')
                    ->label('Level')
                    ->options([
                        1 => 'Level 1',
                        2 => 'Level 2',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
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
            'index' => Pages\ListApprovers::route('/'),
            'create' => Pages\CreateApprover::route('/create'),
            'edit' => Pages\EditApprover::route('/{record}/edit'),
        ];
    }
}