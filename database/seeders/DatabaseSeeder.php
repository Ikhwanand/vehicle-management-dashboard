<?php

namespace Database\Seeders;

use App\Models\Approver;
use App\Models\Driver;
use App\Models\Location;
use App\Models\Region;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleBooking;
use App\Models\BookingApproval;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create Regions
        $regions = [
            ['name' => 'Sulawesi Tenggara', 'code' => 'SULTRA', 'description' => 'Region Sulawesi Tenggara'],
            ['name' => 'Sulawesi Tengah', 'code' => 'SULTENG', 'description' => 'Region Sulawesi Tengah'],
            ['name' => 'Maluku Utara', 'code' => 'MALUT', 'description' => 'Region Maluku Utara'],
        ];

        foreach ($regions as $region) {
            Region::create($region);
        }

        // Create Locations
        $locations = [
            // Kantor Pusat
            ['region_id' => 1, 'name' => 'Kantor Pusat Jakarta', 'type' => 'headquarters', 'address' => 'Jl. Sudirman No. 1, Jakarta Pusat', 'phone' => '021-5551234'],
            // Kantor Cabang
            ['region_id' => 1, 'name' => 'Kantor Cabang Kendari', 'type' => 'branch', 'address' => 'Jl. Ahmad Yani No. 10, Kendari', 'phone' => '0401-123456'],
            // Tambang
            ['region_id' => 1, 'name' => 'Tambang Kolaka', 'type' => 'mine', 'address' => 'Kolaka, Sulawesi Tenggara', 'phone' => '0405-21234'],
            ['region_id' => 1, 'name' => 'Tambang Konawe', 'type' => 'mine', 'address' => 'Konawe, Sulawesi Tenggara', 'phone' => '0408-21234'],
            ['region_id' => 2, 'name' => 'Tambang Morowali', 'type' => 'mine', 'address' => 'Morowali, Sulawesi Tengah', 'phone' => '0465-21234'],
            ['region_id' => 2, 'name' => 'Tambang Banggai', 'type' => 'mine', 'address' => 'Banggai, Sulawesi Tengah', 'phone' => '0462-21234'],
            ['region_id' => 3, 'name' => 'Tambang Halmahera', 'type' => 'mine', 'address' => 'Halmahera, Maluku Utara', 'phone' => '0924-21234'],
            ['region_id' => 3, 'name' => 'Tambang Obi', 'type' => 'mine', 'address' => 'Pulau Obi, Maluku Utara', 'phone' => '0927-21234'],
        ];

        foreach ($locations as $location) {
            Location::create($location);
        }

        // Create Admin User
        $admin = User::create([
            'name' => 'Administrator',
            'email' => 'admin@nikel.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'location_id' => 1,
            'phone' => '08123456789',
            'position' => 'System Administrator',
            'is_active' => true,
        ]);

        // Create Approver Users
        $approver1 = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi.santoso@nikel.com',
            'password' => Hash::make('password'),
            'role' => 'approver',
            'location_id' => 1,
            'phone' => '08234567890',
            'position' => 'Supervisor Operasional',
            'is_active' => true,
        ]);

        $approver2 = User::create([
            'name' => 'Dewi Lestari',
            'email' => 'dewi.lestari@nikel.com',
            'password' => Hash::make('password'),
            'role' => 'approver',
            'location_id' => 1,
            'phone' => '08345678901',
            'position' => 'Manager Fleet',
            'is_active' => true,
        ]);

        $approver3 = User::create([
            'name' => 'Ahmad Hidayat',
            'email' => 'ahmad.hidayat@nikel.com',
            'password' => Hash::make('password'),
            'role' => 'approver',
            'location_id' => 2,
            'phone' => '08456789012',
            'position' => 'Kepala Cabang',
            'is_active' => true,
        ]);

        // Create Approvers (assign users as approvers with levels)
        Approver::create([
            'user_id' => $approver1->id,
            'level' => 1,
            'position' => 'Supervisor Operasional',
            'is_active' => true,
        ]);

        Approver::create([
            'user_id' => $approver2->id,
            'level' => 2,
            'position' => 'Manager Fleet',
            'is_active' => true,
        ]);

        Approver::create([
            'user_id' => $approver3->id,
            'level' => 1,
            'position' => 'Kepala Cabang',
            'is_active' => true,
        ]);

        // Create Drivers
        $drivers = [
            ['name' => 'Agus Supriyanto', 'phone' => '08567890123', 'license_number' => 'SIM-A-12345678', 'license_expiry' => now()->addYears(2), 'location_id' => 1, 'status' => 'active', 'address' => 'Jl. Merdeka No. 1, Jakarta'],
            ['name' => 'Bambang Wijaya', 'phone' => '08678901234', 'license_number' => 'SIM-A-23456789', 'license_expiry' => now()->addYears(1), 'location_id' => 2, 'status' => 'active', 'address' => 'Jl. Pahlawan No. 2, Kendari'],
            ['name' => 'Candra Kusuma', 'phone' => '08789012345', 'license_number' => 'SIM-B1-34567890', 'license_expiry' => now()->addMonths(6), 'location_id' => 3, 'status' => 'active', 'address' => 'Kolaka, Sulawesi Tenggara'],
            ['name' => 'Dedi Pratama', 'phone' => '08890123456', 'license_number' => 'SIM-B1-45678901', 'license_expiry' => now()->addYears(3), 'location_id' => 4, 'status' => 'active', 'address' => 'Konawe, Sulawesi Tenggara'],
            ['name' => 'Eko Prasetyo', 'phone' => '08901234567', 'license_number' => 'SIM-B2-56789012', 'license_expiry' => now()->addYears(2), 'location_id' => 5, 'status' => 'active', 'address' => 'Morowali, Sulawesi Tengah'],
            ['name' => 'Fajar Nugroho', 'phone' => '08112345678', 'license_number' => 'SIM-B2-67890123', 'license_expiry' => now()->addYears(1), 'location_id' => 6, 'status' => 'inactive', 'address' => 'Banggai, Sulawesi Tengah'],
        ];

        foreach ($drivers as $driver) {
            Driver::create($driver);
        }

        // Create Vehicles
        $vehicles = [
            // Angkutan Orang - Milik Perusahaan
            ['plate_number' => 'B 1234 NKL', 'brand' => 'Toyota', 'model' => 'Fortuner', 'vehicle_type' => 'passenger', 'ownership_type' => 'owned', 'location_id' => 1, 'status' => 'available', 'fuel_consumption' => 10.5, 'current_odometer' => 45000],
            ['plate_number' => 'B 2345 NKL', 'brand' => 'Toyota', 'model' => 'Innova Zenix', 'vehicle_type' => 'passenger', 'ownership_type' => 'owned', 'location_id' => 1, 'status' => 'available', 'fuel_consumption' => 12.0, 'current_odometer' => 32000],
            ['plate_number' => 'DT 3456 AB', 'brand' => 'Mitsubishi', 'model' => 'Pajero Sport', 'vehicle_type' => 'passenger', 'ownership_type' => 'owned', 'location_id' => 2, 'status' => 'available', 'fuel_consumption' => 9.5, 'current_odometer' => 67000],
            ['plate_number' => 'DT 4567 CD', 'brand' => 'Toyota', 'model' => 'Hilux DC', 'vehicle_type' => 'passenger', 'ownership_type' => 'owned', 'location_id' => 3, 'status' => 'in_use', 'fuel_consumption' => 8.0, 'current_odometer' => 89000],
            
            // Angkutan Barang - Milik Perusahaan
            ['plate_number' => 'DT 5678 EF', 'brand' => 'Hino', 'model' => 'Dutro', 'vehicle_type' => 'cargo', 'ownership_type' => 'owned', 'location_id' => 4, 'status' => 'available', 'fuel_consumption' => 6.0, 'current_odometer' => 120000],
            ['plate_number' => 'DT 6789 GH', 'brand' => 'Isuzu', 'model' => 'Elf NMR', 'vehicle_type' => 'cargo', 'ownership_type' => 'owned', 'location_id' => 5, 'status' => 'maintenance', 'fuel_consumption' => 7.5, 'current_odometer' => 95000],
            
            // Kendaraan Sewa
            ['plate_number' => 'B 7890 RNT', 'brand' => 'Toyota', 'model' => 'HiAce', 'vehicle_type' => 'passenger', 'ownership_type' => 'rented', 'rental_company' => 'PT Rental Jaya', 'location_id' => 1, 'status' => 'available', 'fuel_consumption' => 11.0, 'current_odometer' => 28000],
            ['plate_number' => 'B 8901 RNT', 'brand' => 'Mitsubishi', 'model' => 'Fuso FM', 'vehicle_type' => 'cargo', 'ownership_type' => 'rented', 'rental_company' => 'CV Armada Niaga', 'location_id' => 6, 'status' => 'available', 'fuel_consumption' => 5.0, 'current_odometer' => 156000],
        ];

        foreach ($vehicles as $vehicle) {
            Vehicle::create($vehicle);
        }

        // Create Sample Bookings
        $booking1 = VehicleBooking::create([
            'booking_code' => 'BK20260101A1B2',
            'user_id' => $admin->id,
            'vehicle_id' => 1,
            'driver_id' => 1,
            'purpose' => 'Kunjungan ke site tambang Kolaka untuk inspeksi rutin',
            'start_datetime' => now()->addDays(2)->setHour(8)->setMinute(0),
            'end_datetime' => now()->addDays(2)->setHour(17)->setMinute(0),
            'start_location' => 'Kantor Pusat Jakarta',
            'end_location' => 'Tambang Kolaka',
            'passenger_count' => 3,
            'status' => 'pending',
            'current_approval_level' => 1,
            'notes' => 'Mohon dipersiapkan kendaraan dalam kondisi prima',
        ]);

        BookingApproval::create([
            'booking_id' => $booking1->id,
            'approver_id' => 1,
            'level' => 1,
            'status' => 'pending',
        ]);

        BookingApproval::create([
            'booking_id' => $booking1->id,
            'approver_id' => 2,
            'level' => 2,
            'status' => 'pending',
        ]);

        $booking2 = VehicleBooking::create([
            'booking_code' => 'BK20260102C3D4',
            'user_id' => $admin->id,
            'vehicle_id' => 3,
            'driver_id' => 2,
            'purpose' => 'Pengangkutan material dari gudang ke site',
            'start_datetime' => now()->addDays(5)->setHour(6)->setMinute(0),
            'end_datetime' => now()->addDays(5)->setHour(18)->setMinute(0),
            'start_location' => 'Kantor Cabang Kendari',
            'end_location' => 'Tambang Konawe',
            'passenger_count' => 2,
            'status' => 'approved',
            'current_approval_level' => 2,
            'notes' => null,
        ]);

        BookingApproval::create([
            'booking_id' => $booking2->id,
            'approver_id' => 1,
            'level' => 1,
            'status' => 'approved',
            'approved_at' => now()->subDays(1),
            'notes' => 'Disetujui',
        ]);

        BookingApproval::create([
            'booking_id' => $booking2->id,
            'approver_id' => 2,
            'level' => 2,
            'status' => 'approved',
            'approved_at' => now()->subHours(6),
            'notes' => 'OK, disetujui untuk keperluan operasional',
        ]);

        $booking3 = VehicleBooking::create([
            'booking_code' => 'BK20260103E5F6',
            'user_id' => $approver1->id,
            'vehicle_id' => 2,
            'driver_id' => 1,
            'purpose' => 'Meeting dengan vendor di Surabaya',
            'start_datetime' => now()->subDays(3)->setHour(7)->setMinute(0),
            'end_datetime' => now()->subDays(3)->setHour(20)->setMinute(0),
            'start_location' => 'Kantor Pusat Jakarta',
            'end_location' => 'Surabaya',
            'passenger_count' => 4,
            'status' => 'completed',
            'current_approval_level' => 2,
            'start_odometer' => 32000,
            'end_odometer' => 32850,
        ]);

        BookingApproval::create([
            'booking_id' => $booking3->id,
            'approver_id' => 3,
            'level' => 1,
            'status' => 'approved',
            'approved_at' => now()->subDays(5),
        ]);

        BookingApproval::create([
            'booking_id' => $booking3->id,
            'approver_id' => 2,
            'level' => 2,
            'status' => 'approved',
            'approved_at' => now()->subDays(4),
        ]);

        $this->command->info('✅ Demo data berhasil dibuat!');
        $this->command->info('');
        $this->command->table(
            ['Role', 'Email', 'Password'],
            [
                ['Admin', 'admin@nikel.com', 'password'],
                ['Approver Level 1', 'budi.santoso@nikel.com', 'password'],
                ['Approver Level 2', 'dewi.lestari@nikel.com', 'password'],
                ['Approver Level 1', 'ahmad.hidayat@nikel.com', 'password'],
            ]
        );
    }
}