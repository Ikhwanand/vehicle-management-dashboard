# Nikel Fleet Management System

Aplikasi web untuk monitoring dan pemesanan kendaraan pada perusahaan tambang nikel. Dikembangkan menggunakan Laravel + Filament.

## 📋 Deskripsi

Sistem ini memungkinkan perusahaan tambang nikel untuk:

-   Mengelola data kendaraan (milik perusahaan dan sewa)
-   Mengelola data driver
-   Memproses pemesanan kendaraan dengan approval berjenjang (minimal 2 level)
-   Memonitor konsumsi BBM dan jadwal service
-   Menghasilkan laporan periodik yang dapat di-export ke Excel
-   Melihat dashboard dengan grafik pemakaian kendaraan

## 🔧 Spesifikasi Teknis

| Komponen      | Versi        |
| ------------- | ------------ |
| PHP           | ^8.1         |
| Laravel       | ^10.10       |
| Filament      | ^3.2         |
| MySQL/MariaDB | 8.0+ / 10.4+ |
| Node.js       | 18+          |

## 📦 Instalasi

### 1. Clone Repository

```bash
git clone https://github.com/Ikhwanand/vehicle-management-dashboard
cd vehicle-management-dashboard
```

### 2. Install Dependencies

```bash
composer install
npm install
```

### 3. Konfigurasi Environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit file `.env` dan sesuaikan konfigurasi database:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nikel_fleet
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Migrasi Database & Seeder

```bash
php artisan migrate
php artisan db:seed
```

### 5. Build Assets

```bash
npm run build
```

### 6. Jalankan Server

```bash
php artisan serve
```

Akses aplikasi di: `http://localhost:8000/admin`

## 👤 Akun Demo

| Role             | Email                   | Password |
| ---------------- | ----------------------- | -------- |
| Administrator    | admin@nikel.com         | password |
| Approver Level 1 | budi.santoso@nikel.com  | password |
| Approver Level 2 | dewi.lestari@nikel.com  | password |
| Approver Level 1 | ahmad.hidayat@nikel.com | password |

## 📖 Panduan Penggunaan

### Untuk Administrator

1. **Login** menggunakan akun admin
2. **Master Data**: Kelola Region, Lokasi, Driver, dan Kendaraan melalui menu "Master Data"
3. **User Management**: Kelola pengguna dan approver melalui menu "User Management"
4. **Pemesanan Kendaraan**:
    - Klik menu "Pemesanan" > "Pemesanan Kendaraan"
    - Klik tombol "Buat Pemesanan Baru"
    - Isi form pemesanan, pilih kendaraan, driver, dan approver
    - Submit pemesanan
5. **Laporan**: Akses menu "Laporan" untuk melihat dan export data pemesanan

### Untuk Approver

1. **Login** menggunakan akun approver
2. Lihat daftar pemesanan yang menunggu persetujuan
3. Klik pemesanan untuk melihat detail
4. Pilih "Setujui" atau "Tolak" dengan memberikan catatan
5. Persetujuan berjenjang: Level 1 harus approve terlebih dahulu sebelum Level 2

## 🔄 Alur Pemesanan Kendaraan

```
┌─────────────────┐
│  Admin membuat  │
│    pemesanan    │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Status: PENDING │
│ Level: 1        │
└────────┬────────┘
         │
         ▼
┌─────────────────┐     ┌──────────────┐
│ Approver Lv.1   │────►│   REJECTED   │
│    Review       │ NO  │              │
└────────┬────────┘     └──────────────┘
         │ YES
         ▼
┌─────────────────┐
│ Status: PENDING │
│ Level: 2        │
└────────┬────────┘
         │
         ▼
┌─────────────────┐     ┌──────────────┐
│ Approver Lv.2   │────►│   REJECTED   │
│    Review       │ NO  │              │
└────────┬────────┘     └──────────────┘
         │ YES
         ▼
┌─────────────────┐
│    APPROVED     │
│                 │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│   IN_PROGRESS   │
│  (Penggunaan)   │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│   COMPLETED     │
└─────────────────┘
```

## 📊 Fitur Dashboard

-   **Stats Overview**: Total kendaraan, driver aktif, pemesanan pending
-   **Vehicle Usage Chart**: Grafik pemakaian kendaraan per bulan (12 bulan terakhir)
-   **Booking Status Chart**: Distribusi status pemesanan (Doughnut chart)
-   **Latest Bookings**: Tabel pemesanan terbaru

## 📁 Struktur Database

### Tabel Utama

-   `regions` - Data region/wilayah
-   `locations` - Data lokasi (kantor pusat, cabang, tambang)
-   `users` - Data pengguna (admin & approver)
-   `approvers` - Data approver dengan level
-   `drivers` - Data driver
-   `vehicles` - Data kendaraan
-   `vehicle_bookings` - Data pemesanan kendaraan
-   `booking_approvals` - Data approval per level
-   `fuel_logs` - Log konsumsi BBM
-   `service_schedules` - Jadwal service kendaraan
-   `activity_logs` - Log aktivitas sistem

## 🛠️ Pengembangan

### Menjalankan Development Server

```bash
php artisan serve
npm run dev
```

### Membuat User Baru

```bash
php artisan make:filament-user
```

### Clear Cache

```bash
php artisan optimize:clear
```

## 📝 Catatan Penting

1. Pastikan driver yang dipilih memiliki SIM yang masih berlaku
2. Kendaraan dengan status "in_use" atau "maintenance" tidak dapat dipesan
3. Approval berjenjang wajib minimal 2 level
4. Export Excel membutuhkan package `maatwebsite/excel`

## 📄 Lisensi

---

**Developed with ❤️ using Laravel + Filament**
