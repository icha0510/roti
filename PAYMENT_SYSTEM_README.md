# Sistem Pembayaran Otomatis QRIS - Roti'O

## Overview
Sistem pembayaran otomatis yang memproses pembayaran QRIS secara real-time setelah customer scan QR code. Sistem akan mengecek apakah total pembayaran sesuai dengan total tagihan dan otomatis memproses order jika pembayaran berhasil.

## Fitur Utama

### 1. QR Code Otomatis
- QR code di-generate berdasarkan data pesanan
- Berisi informasi order dan callback URL
- Dapat di-scan untuk pembayaran QRIS

### 2. Callback Pembayaran
- Endpoint `payment_callback.php` untuk menerima data pembayaran
- Validasi otomatis total pembayaran vs total tagihan
- Update status order secara real-time

### 3. Database Tracking
- Tabel `payment_transactions` untuk mencatat semua transaksi
- Tabel `order_tracking` untuk history perubahan status
- Tabel `orders` dengan kolom `payment_method` dan `updated_at`

## File yang Dibuat

### 1. `payment_callback.php`
- Endpoint untuk menerima callback pembayaran
- Validasi dan proses pembayaran otomatis
- Update status order dan tracking

### 2. `create_payment_table.sql`
- Script untuk membuat tabel payment_transactions
- Menambahkan kolom yang diperlukan ke tabel orders

### 3. `test_payment_simulation.php`
- File test untuk simulasi pembayaran
- Menampilkan order pending untuk test
- Simulasi callback pembayaran

### 4. `generate_qr.php` (Updated)
- Menambahkan callback URL ke QR code
- Informasi pembayaran otomatis

## Struktur Database

### Tabel `payment_transactions`
```sql
CREATE TABLE payment_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    order_number VARCHAR(50) NOT NULL,
    amount_paid DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50) NOT NULL DEFAULT 'qris',
    payment_data TEXT,
    status ENUM('pending', 'success', 'failed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### Tabel `orders` (Updated)
- Kolom `payment_method` VARCHAR(50) DEFAULT 'cash'
- Kolom `updated_at` TIMESTAMP

## Alur Kerja Pembayaran

### 1. Customer Checkout
1. Customer memilih metode pembayaran QRIS
2. QR code otomatis muncul di bawah total harga
3. QR code berisi data order dan callback URL

### 2. Scan QR Code
1. Customer scan QR code dengan aplikasi QRIS
2. QR code berisi informasi:
   - Order ID
   - Order Number
   - Total Amount
   - Customer Name
   - Callback URL

### 3. Payment Processing
1. Payment gateway mengirim data ke callback URL
2. Sistem validasi total pembayaran vs total tagihan
3. Jika sesuai: Order status → 'paid' → 'processing'
4. Jika tidak sesuai: Payment record 'failed', order tetap 'pending'

### 4. Database Update
1. Update status order di tabel `orders`
2. Insert payment record di tabel `payment_transactions`
3. Insert tracking record di tabel `order_tracking`

## Status Order

- `pending` - Menunggu pembayaran
- `paid` - Pembayaran berhasil diterima
- `processing` - Order sedang diproses
- `completed` - Order selesai
- `failed` - Pembayaran gagal
- `cancelled` - Order dibatalkan

## API Endpoint

### Payment Callback
```
POST /payment_callback.php
Content-Type: application/json

{
    "order_id": 123,
    "amount_paid": 50000,
    "payment_data": {
        "transaction_id": "TXN123456",
        "payment_method": "qris",
        "timestamp": "2024-01-15 10:30:00"
    }
}
```

### Response Success
```json
{
    "success": true,
    "message": "Pembayaran berhasil diproses",
    "order_number": "ORD-2024-0001",
    "amount_paid": 50000,
    "total_amount": 50000
}
```

### Response Failed
```json
{
    "success": false,
    "message": "Pembayaran tidak cukup. Total tagihan: 50000, Dibayar: 40000"
}
```

## Cara Test

### 1. Test QR Code Generation
```
http://localhost/web/bready/test_qr.php
```

### 2. Test Payment Simulation
```
http://localhost/web/bready/test_payment_simulation.php
```

### 3. Test Callback Endpoint
```
http://localhost/web/bready/payment_callback.php
```

### 4. Test Checkout Flow
1. Login ke aplikasi
2. Tambah produk ke cart
3. Checkout dengan metode QRIS
4. Lihat QR code muncul
5. Test simulasi pembayaran

## Keamanan

### 1. Validasi Input
- Semua input divalidasi sebelum diproses
- Sanitasi data untuk mencegah SQL injection
- Validasi format JSON

### 2. Database Security
- Menggunakan prepared statements
- Transaction untuk konsistensi data
- Rollback jika terjadi error

### 3. Error Handling
- Try-catch untuk semua operasi database
- Log error untuk debugging
- Response error yang informatif

## Monitoring

### 1. Payment Transactions
- Semua transaksi dicatat di tabel `payment_transactions`
- Status transaksi: pending, success, failed, cancelled
- Timestamp untuk tracking waktu

### 2. Order Tracking
- History perubahan status di tabel `order_tracking`
- Deskripsi detail untuk setiap perubahan
- Timestamp untuk audit trail

### 3. Admin Panel
- Halaman verifikasi pembayaran manual
- Dashboard untuk monitoring transaksi
- Filter berdasarkan status dan tanggal

## Troubleshooting

### QR Code Tidak Muncul
1. Cek extension GD sudah aktif
2. Cek folder `qr_codes/` sudah dibuat
3. Cek permission write pada folder

### Callback Tidak Berfungsi
1. Cek URL callback di QR code
2. Cek network connectivity
3. Cek log error di server

### Database Error
1. Cek tabel `payment_transactions` sudah dibuat
2. Cek kolom `payment_method` di tabel `orders`
3. Cek koneksi database

### Payment Tidak Diproses
1. Cek total pembayaran vs total tagihan
2. Cek status order masih 'pending'
3. Cek log error di payment_callback.php

## Pengembangan Selanjutnya

### 1. Integrasi Payment Gateway
- Integrasi dengan payment gateway QRIS yang sebenarnya
- Webhook untuk callback otomatis
- Signature verification untuk keamanan

### 2. Notification System
- Email notification saat pembayaran berhasil
- SMS notification untuk customer
- Push notification untuk admin

### 3. Analytics Dashboard
- Dashboard analytics untuk monitoring pembayaran
- Report transaksi harian/bulanan
- Chart untuk visualisasi data

### 4. Mobile App
- Aplikasi mobile untuk scan QR code
- Push notification untuk status order
- Offline mode untuk transaksi

## Support

Untuk pertanyaan atau masalah teknis, silakan hubungi tim development. 