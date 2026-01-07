# Activity Diagram - Pemesanan Kendaraan

## Diagram dalam Format Text (PlantUML Compatible)

```plantuml
@startuml
title Activity Diagram - Sistem Pemesanan Kendaraan

|Admin|
start
:Login ke sistem;
:Akses menu Pemesanan Kendaraan;
:Klik "Buat Pemesanan Baru";
:Pilih kendaraan yang tersedia;
:Pilih driver yang aktif;
:Isi detail pemesanan;
note right
  - Tujuan/Keperluan
  - Waktu mulai & selesai
  - Lokasi awal & tujuan
  - Jumlah penumpang
end note
:Pilih Approver Level 1;
:Pilih Approver Level 2;
:Submit pemesanan;

|Sistem|
:Generate kode booking;
:Set status = PENDING;
:Set current_approval_level = 1;
:Update status kendaraan = IN_USE;
:Buat record booking_approvals (Level 1 & 2);
:Catat activity log;
:Kirim notifikasi ke Approver Level 1;

|Approver Level 1|
:Login ke sistem;
:Lihat daftar pemesanan pending;
:Klik untuk melihat detail pemesanan;

if (Setujui pemesanan?) then (Ya)
  :Klik tombol "Setujui";
  :Isi catatan (opsional);
  :Konfirmasi persetujuan;

  |Sistem|
  :Update booking_approval Level 1;
  note right
    - status = APPROVED
    - approved_at = now()
  end note
  :Set current_approval_level = 2;
  :Catat activity log;
  :Kirim notifikasi ke Approver Level 2;

  |Approver Level 2|
  :Login ke sistem;
  :Lihat daftar pemesanan pending;
  :Klik untuk melihat detail pemesanan;

  if (Setujui pemesanan?) then (Ya)
    :Klik tombol "Setujui";
    :Isi catatan (opsional);
    :Konfirmasi persetujuan;

    |Sistem|
    :Update booking_approval Level 2;
    :Set booking status = APPROVED;
    :Catat activity log;
    :Kirim notifikasi ke Admin;

    |Admin|
    :Terima notifikasi pemesanan disetujui;
    :Koordinasi dengan driver;

    |Driver|
    :Terima informasi pemesanan;
    :Siapkan kendaraan;
    :Jalankan pemesanan;

    |Sistem|
    :Update status = IN_PROGRESS;

    |Driver|
    :Selesaikan perjalanan;
    :Laporkan odometer akhir;

    |Admin|
    :Update data perjalanan;
    :Set status = COMPLETED;

    |Sistem|
    :Update status kendaraan = AVAILABLE;
    :Catat activity log;
    :Selesai;

  else (Tidak)
    :Klik tombol "Tolak";
    :Isi alasan penolakan (wajib);
    :Konfirmasi penolakan;

    |Sistem|
    :Update booking_approval Level 2 = REJECTED;
    :Set booking status = REJECTED;
    :Update status kendaraan = AVAILABLE;
    :Catat activity log;
    :Kirim notifikasi penolakan;

    |Admin|
    :Terima notifikasi penolakan;
    :Buat pemesanan baru atau cancel;
    stop
  endif

else (Tidak)
  :Klik tombol "Tolak";
  :Isi alasan penolakan (wajib);
  :Konfirmasi penolakan;

  |Sistem|
  :Update booking_approval Level 1 = REJECTED;
  :Set booking status = REJECTED;
  :Update status kendaraan = AVAILABLE;
  :Catat activity log;
  :Kirim notifikasi penolakan;

  |Admin|
  :Terima notifikasi penolakan;
  :Revisi atau buat pemesanan baru;
  stop
endif

stop
@enduml
```

## Visual Representation (ASCII)

```
┌────────────────────────────────────────────────────────────────────┐
│                    ACTIVITY DIAGRAM                                 │
│              Pemesanan Kendaraan dengan Approval Berjenjang        │
└────────────────────────────────────────────────────────────────────┘

    ┌───────┐
    │ START │
    └───┬───┘
        │
        ▼
┌───────────────────┐
│ Admin login &     │
│ buat pemesanan    │
└─────────┬─────────┘
          │
          ▼
┌───────────────────┐
│ Sistem:           │
│ - Generate code   │
│ - Status=PENDING  │
│ - Level=1         │
│ - Log activity    │
└─────────┬─────────┘
          │
          ▼
┌───────────────────┐
│ Approver Level 1  │
│ review pemesanan  │
└─────────┬─────────┘
          │
    ┌─────┴─────┐
    │           │
    ▼           ▼
┌───────┐   ┌───────┐
│APPROVE│   │REJECT │
└───┬───┘   └───┬───┘
    │           │
    ▼           ▼
┌─────────┐ ┌─────────────────┐
│ Level=2 │ │ Status=REJECTED │
│ Notify  │ │ Vehicle=AVAIL   │
│ Lv.2    │ │ Notify Admin    │
└────┬────┘ └────────┬────────┘
     │               │
     ▼               ▼
┌───────────────────┐   ┌───────┐
│ Approver Level 2  │   │  END  │
│ review pemesanan  │   └───────┘
└─────────┬─────────┘
          │
    ┌─────┴─────┐
    │           │
    ▼           ▼
┌───────┐   ┌───────┐
│APPROVE│   │REJECT │
└───┬───┘   └───┬───┘
    │           │
    ▼           ▼
┌─────────────────┐  ┌─────────────────┐
│ Status=APPROVED │  │ Status=REJECTED │
│ Notify Admin    │  │ Vehicle=AVAIL   │
└────────┬────────┘  └────────┬────────┘
         │                    │
         ▼                    ▼
┌─────────────────┐       ┌───────┐
│ Driver jalankan │       │  END  │
│ pemesanan       │       └───────┘
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Status=         │
│ IN_PROGRESS     │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Perjalanan      │
│ selesai         │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Status=         │
│ COMPLETED       │
│ Vehicle=AVAIL   │
└────────┬────────┘
         │
         ▼
    ┌────────┐
    │  END   │
    └────────┘
```

## Keterangan Status

| Status      | Deskripsi                        |
| ----------- | -------------------------------- |
| PENDING     | Menunggu persetujuan             |
| APPROVED    | Disetujui semua level            |
| REJECTED    | Ditolak oleh salah satu approver |
| IN_PROGRESS | Sedang berjalan                  |
| COMPLETED   | Selesai                          |
| CANCELLED   | Dibatalkan oleh admin            |

## Aktor dalam Sistem

| Aktor            | Deskripsi                                                 |
| ---------------- | --------------------------------------------------------- |
| Admin            | Membuat pemesanan, mengelola master data, melihat laporan |
| Approver Level 1 | Atasan langsung, menyetujui/menolak tahap pertama         |
| Approver Level 2 | Manager/Pimpinan, menyetujui/menolak tahap akhir          |
| Driver           | Menjalankan pemesanan yang sudah disetujui                |
| Sistem           | Otomatis mencatat log, update status, kirim notifikasi    |
