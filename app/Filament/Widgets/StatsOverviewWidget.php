<?php

namespace App\Filament\Widgets;

use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\VehicleBooking;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalVehicles = Vehicle::count();
        $availableVehicles = Vehicle::where('status', 'available')->count();
        $inUseVehicles = Vehicle::where('status', 'in_use')->count();
        
        $totalDrivers = Driver::where('status', 'active')->count();
        
        $pendingBookings = VehicleBooking::where('status', 'pending')->count();
        $approvedBookings = VehicleBooking::where('status', 'approved')->count();
        $completedThisMonth = VehicleBooking::where('status', 'completed')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        return [
            Stat::make('Total Kendaraan', $totalVehicles)
                ->description("{$availableVehicles} tersedia, {$inUseVehicles} digunakan")
                ->descriptionIcon('heroicon-m-truck')
                ->color('primary')
                ->chart([7, 3, 4, 5, 6, 3, 5]),

            Stat::make('Driver Aktif', $totalDrivers)
                ->description('Driver yang tersedia')
                ->descriptionIcon('heroicon-m-user')
                ->color('success'),

            Stat::make('Pemesanan Pending', $pendingBookings)
                ->description('Menunggu persetujuan')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Pemesanan Bulan Ini', $completedThisMonth)
                ->description("{$approvedBookings} disetujui")
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
        ];
    }
}