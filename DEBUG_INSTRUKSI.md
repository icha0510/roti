# Debug Update Status Order

## Masalah
Tombol update di halaman orders tidak berfungsi dengan benar. Status terupdate di database tapi tabel tidak berubah.

## Langkah Debug

### 1. Buka Developer Tools
1. Buka halaman `admin/orders.php`
2. Tekan F12 untuk membuka Developer Tools
3. Pilih tab "Console"

### 2. Test Update Status
1. Klik tombol "🔄 Update" pada salah satu order
2. Pilih status baru di modal
3. Isi keterangan
4. Klik "Update Status"
5. Perhatikan console log

### 3. Periksa Console Log
Console akan menampilkan:
- "Form submitted"
- Data form yang dikirim
- "Sending request to debug_update.php"
- Response dari server
- Data yang diterima

### 4. Periksa Error Log
Buka file error log PHP (biasanya di `C:\xampp\apache\logs\error.log`) dan cari log yang dimulai dengan "DEBUG:"

### 5. Periksa Database
Setelah update, periksa:
1. Tabel `orders` - kolom `status`
2. Tabel `order_tracking` - record baru

## Kemungkinan Penyebab

### 1. JavaScript Error
- Periksa console untuk error JavaScript
- Pastikan semua fungsi terdefinisi

### 2. AJAX Error
- Periksa network tab di Developer Tools
- Pastikan request dikirim ke URL yang benar

### 3. Database Error
- Periksa error log PHP
- Pastikan koneksi database berhasil

### 4. Permission Error
- Pastikan file `debug_update.php` dapat diakses
- Periksa permission folder admin

## Solusi Sementara

Jika tombol update masih bermasalah, gunakan:
1. Tombol "🔧 Update Status (Fixed)" - untuk update manual
2. File `fix_order_tracking.php` - untuk update dengan interface yang diperbaiki

## File yang Terlibat

1. `admin/orders.php` - Halaman utama dengan tombol update
2. `admin/debug_update.php` - File AJAX untuk update status (dengan debug)
3. `admin/update_status_ajax.php` - File AJAX asli
4. `admin/fix_order_tracking.php` - File perbaikan untuk update manual

## Testing

1. Test dengan order yang berbeda
2. Test dengan status yang berbeda
3. Periksa apakah data tersimpan di database
4. Periksa apakah halaman refresh setelah update

## Expected Output

Jika berhasil, console akan menampilkan:
```
Form submitted
order_id: 1
status: processing
description: Test update
Sending request to debug_update.php
Response received: Response {type: "basic", url: "...", status: 200, ...}
Data received: {success: true, message: "Status berhasil diupdate!", order: {...}, debug: {...}}
Success! Refreshing page in 2 seconds...
```

Jika gagal, akan menampilkan error message di console dan modal. 