<?php

namespace App\Filament\Pages;

use App\Exports\VehicleBookingsExport;
use App\Models\VehicleBooking;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Carbon;

class BookingReport extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static ?string $navigationGroup = 'Laporan';

    protected static ?string $navigationLabel = 'Laporan Pemesanan';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.booking-report';

    public ?array $filterData = [];

    public function mount(): void
    {
        $this->filterData = [
            'start_date' => now()->startOfMonth()->format('Y-m-d'),
            'end_date' => now()->endOfMonth()->format('Y-m-d'),
            'status' => null,
        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Filter Laporan')
                    ->schema([
                        DatePicker::make('filterData.start_date')
                            ->label('Dari Tanggal')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->default(now()->startOfMonth()),
                        DatePicker::make('filterData.end_date')
                            ->label('Sampai Tanggal')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->default(now()->endOfMonth()),
                        Select::make('filterData.status')
                            ->label('Status')
                            ->options([
                                'pending' => 'Menunggu Approval',
                                'approved' => 'Disetujui',
                                'rejected' => 'Ditolak',
                                'in_progress' => 'Sedang Berjalan',
                                'completed' => 'Selesai',
                                'cancelled' => 'Dibatalkan',
                            ])
                            ->placeholder('Semua Status'),
                    ])->columns(3),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                TextColumn::make('booking_code')
                    ->label('Kode Booking')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Pemohon')
                    ->searchable(),
                TextColumn::make('vehicle.plate_number')
                    ->label('Kendaraan'),
                TextColumn::make('driver.name')
                    ->label('Driver'),
                TextColumn::make('start_datetime')
                    ->label('Waktu Mulai')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('end_datetime')
                    ->label('Waktu Selesai')
                    ->dateTime('d/m/Y H:i'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'pending' => 'Pending',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        'in_progress' => 'Berjalan',
                        'completed' => 'Selesai',
                        'cancelled' => 'Batal',
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
            ])
            ->defaultSort('created_at', 'desc');
    }

    protected function getTableQuery()
    {
        $query = VehicleBooking::with(['user', 'vehicle', 'driver']);

        if (!empty($this->filterData['start_date'])) {
            $query->whereDate('start_datetime', '>=', $this->filterData['start_date']);
        }

        if (!empty($this->filterData['end_date'])) {
            $query->whereDate('start_datetime', '<=', $this->filterData['end_date']);
        }

        if (!empty($this->filterData['status'])) {
            $query->where('status', $this->filterData['status']);
        }

        return $query;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')
                ->label('Export Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(function () {
                    $filename = 'laporan_pemesanan_' . now()->format('Y-m-d_His') . '.xlsx';

                    Notification::make()
                        ->title('Export Berhasil')
                        ->body('File laporan sedang diunduh...')
                        ->success()
                        ->send();

                    return Excel::download(
                        new VehicleBookingsExport(
                            $this->filterData['start_date'] ?? null,
                            $this->filterData['end_date'] ?? null,
                            $this->filterData['status'] ?? null
                        ),
                        $filename
                    );
                }),

            Action::make('filter')
                ->label('Terapkan Filter')
                ->icon('heroicon-o-funnel')
                ->color('primary')
                ->action(function () {
                    $this->resetTable();
                }),
        ];
    }
}