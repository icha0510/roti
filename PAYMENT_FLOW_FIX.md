# Payment Flow Fix - Roti'O Bakery

## Overview

Perbaikan alur pembayaran QRIS dengan penambahan fitur timer 5 menit untuk pembatalan otomatis pesanan.

## Masalah Sebelumnya

1. User langsung diarahkan ke halaman sukses setelah checkout dengan QRIS
2. Tidak ada halaman khusus untuk menampilkan QR code
3. QR code tidak di-generate otomatis
4. URL QR code tidak sesuai dengan struktur folder
5. Tidak ada fitur auto payment saat scan QR code
6. Error database karena kolom payment_date tidak ada
7. **Tidak ada timer untuk pembatalan otomatis pesanan**

## Solusi yang Diterapkan

### 1. Perbaikan Alur Pembayaran

- **Checkout → QR Payment Page → Order Success**
- User diarahkan ke `qr_payment_page.php` setelah checkout dengan QRIS
- QR code di-generate otomatis saat halaman dimuat
- Setelah pembayaran, user diarahkan ke `order-success.php`

### 2. Perbaikan QR Code Generation

- URL QR code disesuaikan: `http://localhost/Latihan/roti/qr_payment_page.php`
- Parameter yang ditambahkan: `auto_pay=true`
- QR code di-generate dengan library Endroid QR Code

### 3. Fitur Auto Payment

- Saat QR code di-scan dengan parameter `auto_pay=true`
- Sistem mengecek apakah jumlah pembayaran sesuai
- Jika sesuai, pembayaran diproses otomatis
- Status order diupdate menjadi 'paid'

### 4. Perbaikan Database

- Menghapus kolom `payment_date` dari query insert
- Menggunakan kolom yang benar: `order_id`, `order_number`, `amount_paid`, `payment_method`, `status`, `created_at`

### 5. **Fitur Timer 5 Menit (BARU)**

- Timer countdown ditampilkan di halaman pembayaran
- Format waktu: MM:SS (menit:detik)
- **Auto cancellation**: Pesanan dibatalkan otomatis jika tidak dibayar dalam 5 menit
- **Visual warning**: Timer berubah merah ketika < 1 menit tersisa
- **Pulse animation**: Timer berkedip ketika waktu hampir habis
- **Timeout page**: Halaman khusus untuk pesanan yang dibatalkan
- **Database update**: Status order diupdate menjadi 'cancelled'
- **Tracking record**: Mencatat pembatalan di order_tracking

## File yang Dimodifikasi

### 1. `checkout.php`

- Redirect QRIS payment ke `qr_payment_page.php`
- Menambahkan parameter yang diperlukan

### 2. `qr_payment_page.php`

- Halaman khusus untuk pembayaran QRIS
- Auto-generate QR code
- Fitur auto payment saat scan QR
- **Timer countdown 5 menit**
- **Auto cancellation logic**
- **Visual timer dengan warning**

### 3. `generate_qr.php`

- Perbaikan URL QR code
- Menambahkan parameter `auto_pay=true`

### 4. `order-success.php`

- Menampilkan pesan sukses untuk QRIS payment
- Menggunakan parameter `from_qr_payment=true`

### 5. **`order-timeout.php` (BARU)**

- Halaman khusus untuk pesanan yang dibatalkan
- Menampilkan detail pesanan yang expired
- Tombol untuk kembali ke beranda atau pesan lagi

## Flow Timer 5 Menit

```
1. Order dibuat → Timer 5 menit dimulai
2. Halaman pembayaran → Timer countdown ditampilkan
3. < 1 menit tersisa → Timer berubah merah dan berkedip
4. Waktu habis → Pesanan dibatalkan otomatis
5. Redirect → User diarahkan ke halaman timeout
```

## Database Changes

### Tabel `orders`

- Status baru: `cancelled` (untuk pesanan yang timeout)

### Tabel `order_tracking`

- Record baru: `'cancelled', 'Order cancelled due to payment timeout (5 minutes)'`

## Testing

### File Test yang Dibuat

1. `test_payment_flow.php` - Test alur pembayaran
2. `test_qr_generation.php` - Test generate QR code
3. `test_auto_payment.php` - Test fitur auto payment
4. `test_qr_url.php` - Test URL QR code
5. `test_database_fix.php` - Test perbaikan database
6. **`test_timer_feature.php` (BARU)** - Test fitur timer 5 menit

### Cara Test Timer

1. Buat pesanan baru dengan QRIS
2. Buka halaman pembayaran
3. Perhatikan timer countdown
4. Tunggu sampai waktu habis (atau modifikasi waktu di database untuk test)
5. Verifikasi pesanan dibatalkan otomatis

## Keuntungan Fitur Timer

1. **Mencegah order pending yang tidak dibayar**
2. **Mengurangi beban sistem**
3. **Memberikan feedback jelas kepada user**
4. **Meningkatkan user experience**
5. **Memastikan inventory management yang akurat**

## Status

✅ **SEMUA FITUR SUDAH DITERAPKAN DAN BERFUNGSI**

- ✅ Alur pembayaran QRIS diperbaiki
- ✅ QR code generation otomatis
- ✅ Auto payment saat scan QR
- ✅ Perbaikan database
- ✅ **Timer 5 menit dengan auto cancellation**
- ✅ **Halaman timeout yang informatif**
