<?php

namespace App\Exports;

use App\Models\VehicleBooking;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class VehicleBookingsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $startDate;
    protected $endDate;
    protected $status;

    public function __construct($startDate = null, $endDate = null, $status = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->status = $status;
    }

    public function collection()
    {
        $query = VehicleBooking::with(['user', 'vehicle', 'driver', 'approvals.approver.user']);

        if ($this->startDate) {
            $query->whereDate('start_datetime', '>=', $this->startDate);
        }

        if ($this->endDate) {
            $query->whereDate('start_datetime', '<=', $this->endDate);
        }

        if ($this->status) {
            $query->where('status', $this->status);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'Kode Booking',
            'Pemohon',
            'Kendaraan',
            'Driver',
            'Tujuan/Keperluan',
            'Waktu Mulai',
            'Waktu Selesai',
            'Lokasi Awal',
            'Lokasi Tujuan',
            'Jumlah Penumpang',
            'Status',
            'Approver Level 1',
            'Status Level 1',
            'Approver Level 2',
            'Status Level 2',
            'Catatan',
            'Tanggal Dibuat',
        ];
    }

    public function map($booking): array
    {
        $approval1 = $booking->approvals->firstWhere('level', 1);
        $approval2 = $booking->approvals->firstWhere('level', 2);

        return [
            $booking->booking_code,
            $booking->user->name ?? '-',
            $booking->vehicle ? "{$booking->vehicle->plate_number} - {$booking->vehicle->brand} {$booking->vehicle->model}" : '-',
            $booking->driver->name ?? '-',
            $booking->purpose,
            $booking->start_datetime->format('d/m/Y H:i'),
            $booking->end_datetime->format('d/m/Y H:i'),
            $booking->start_location,
            $booking->end_location,
            $booking->passenger_count,
            $this->getStatusLabel($booking->status),
            $approval1?->approver?->user?->name ?? '-',
            $approval1 ? $this->getStatusLabel($approval1->status) : '-',
            $approval2?->approver?->user?->name ?? '-',
            $approval2 ? $this->getStatusLabel($approval2->status) : '-',
            $booking->notes ?? '-',
            $booking->created_at->format('d/m/Y H:i'),
        ];
    }

    protected function getStatusLabel(string $status): string
    {
        return match($status) {
            'pending' => 'Menunggu',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            'in_progress' => 'Sedang Berjalan',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            default => $status,
        };
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '10B981'],
                ],
            ],
        ];
    }
}