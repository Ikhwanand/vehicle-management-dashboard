<?php

namespace App\Filament\Widgets;

use App\Models\VehicleBooking;
use Filament\Widgets\ChartWidget;

class BookingStatusChart extends ChartWidget
{
    protected static ?string $heading = 'Status Pemesanan';

    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 1;

    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $pending = VehicleBooking::where('status', 'pending')->count();
        $approved = VehicleBooking::where('status', 'approved')->count();
        $rejected = VehicleBooking::where('status', 'rejected')->count();
        $completed = VehicleBooking::where('status', 'completed')->count();
        $cancelled = VehicleBooking::where('status', 'cancelled')->count();
        $inProgress = VehicleBooking::where('status', 'in_progress')->count();

        return [
            'datasets' => [
                [
                    'data' => [$pending, $approved, $inProgress, $completed, $rejected, $cancelled],
                    'backgroundColor' => [
                        'rgb(251, 191, 36)',  // warning - pending
                        'rgb(34, 197, 94)',   // success - approved
                        'rgb(56, 189, 248)',  // info - in_progress
                        'rgb(16, 185, 129)',  // emerald - completed
                        'rgb(244, 63, 94)',   // danger - rejected
                        'rgb(148, 163, 184)', // gray - cancelled
                    ],
                ],
            ],
            'labels' => ['Pending', 'Disetujui', 'Berjalan', 'Selesai', 'Ditolak', 'Dibatalkan'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],
            ],
        ];
    }
}