# Sistem QR Code Pembayaran QRIS - Roti'O

## Overview
Sistem QR code pembayaran otomatis untuk aplikasi Roti'O yang memungkinkan customer melakukan pembayaran melalui QRIS dengan QR code yang di-generate secara otomatis berdasarkan data pesanan.

## Fitur Utama

### 1. Generate QR Code Otomatis
- QR code di-generate berdasarkan data pesanan (Order ID, Total, Customer Name)
- QR code berisi informasi JSON yang bisa di-scan untuk pembayaran
- File QR code disimpan di folder `qr_codes/`

### 2. Metode Pembayaran
- **Cash**: Pembayaran tunai langsung
- **QRIS**: Pembayaran melalui QR code QRIS

### 3. Verifikasi Pembayaran
- Admin dapat memverifikasi pembayaran melalui panel admin
- Status order otomatis berubah setelah verifikasi
- Tracking history untuk setiap perubahan status

## File yang Dibuat/Dimodifikasi

### File Baru:
1. `generate_qr.php` - Library untuk generate QR code
2. `admin/verify_payment.php` - Halaman admin untuk verifikasi pembayaran
3. `database_update.sql` - Script untuk update database
4. `QR_SYSTEM_README.md` - Dokumentasi ini

### File yang Dimodifikasi:
1. `checkout.php` - Menambahkan pilihan metode pembayaran dan QR code
2. `order-success.php` - Menampilkan QR code setelah order berhasil

## Instalasi dan Setup

### 1. Install Dependencies
```bash
composer require endroid/qr-code
```

### 2. Update Database
Jalankan script SQL di `database_update.sql`:
```sql
ALTER TABLE orders ADD COLUMN payment_method VARCHAR(50) DEFAULT 'cash' AFTER notes;
```

### 3. Buat Folder QR Codes
```bash
mkdir qr_codes
chmod 777 qr_codes
```

## Cara Kerja

### 1. Proses Checkout
1. Customer memilih metode pembayaran (Cash/QRIS)
2. Jika memilih QRIS, QR code akan muncul di bawah total harga
3. Setelah order berhasil, customer diarahkan ke halaman sukses

### 2. Generate QR Code
1. Data pesanan (Order ID, Total, Customer) di-encode ke JSON
2. JSON di-convert menjadi QR code menggunakan library Endroid QR Code
3. QR code disimpan sebagai file PNG di folder `qr_codes/`

### 3. Verifikasi Pembayaran
1. Admin login ke panel admin
2. Akses halaman "Verifikasi Pembayaran"
3. Pilih status pembayaran (Dibayar/Gagal)
4. Status order otomatis berubah dan tracking record dibuat

## Struktur Data QR Code

QR code berisi JSON dengan format:
```json
{
    "order_id": "123",
    "order_number": "ORD-2024-0001",
    "total_amount": "50000",
    "customer_name": "John Doe",
    "timestamp": "2024-01-15 10:30:00"
}
```

## Status Order

- `pending` - Menunggu pembayaran
- `paid` - Pembayaran berhasil
- `failed` - Pembayaran gagal
- `processing` - Sedang diproses
- `completed` - Pesanan selesai
- `cancelled` - Pesanan dibatalkan

## Keamanan

1. **Validasi Input**: Semua input divalidasi sebelum diproses
2. **SQL Injection Protection**: Menggunakan prepared statements
3. **XSS Protection**: Output di-escape menggunakan htmlspecialchars()
4. **Session Security**: Cek login untuk akses admin

## Troubleshooting

### QR Code Tidak Muncul
1. Pastikan library `endroid/qr-code` sudah terinstall
2. Cek folder `qr_codes/` sudah dibuat dan memiliki permission write
3. Pastikan file `generate_qr.php` bisa diakses

### Error Database
1. Jalankan script `database_update.sql`
2. Pastikan kolom `payment_method` sudah ada di tabel `orders`
3. Cek koneksi database

### QR Code Tidak Terbaca
1. Pastikan QR code berukuran cukup besar (minimal 200px)
2. Cek kontras QR code dengan background
3. Pastikan data JSON valid

## Pengembangan Selanjutnya

1. **Integrasi Payment Gateway**: Integrasi dengan payment gateway QRIS yang sebenarnya
2. **Callback System**: Sistem callback otomatis dari payment gateway
3. **Email Notification**: Notifikasi email saat pembayaran berhasil
4. **Mobile App**: Aplikasi mobile untuk scan QR code
5. **Analytics**: Dashboard analytics untuk monitoring pembayaran

## Support

Untuk pertanyaan atau masalah teknis, silakan hubungi tim development. 