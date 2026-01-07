<?php

namespace App\Filament\Widgets;

use App\Models\VehicleBooking;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class VehicleUsageChart extends ChartWidget
{
    protected static ?string $heading = 'Pemakaian Kendaraan per Bulan';

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $data = [];
        $labels = [];

        // Get data for last 12 months
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $labels[] = $date->translatedFormat('M Y');

            $count = VehicleBooking::whereMonth('start_datetime', $date->month)
                ->whereYear('start_datetime', $date->year)
                ->whereIn('status', ['approved', 'completed', 'in_progress'])
                ->count();

            $data[] = $count;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Pemakaian Kendaraan',
                    'data' => $data,
                    'backgroundColor' => 'rgba(16, 185, 129, 0.2)',
                    'borderColor' => 'rgb(16, 185, 129)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 1,
                    ],
                ],
            ],
        ];
    }
}