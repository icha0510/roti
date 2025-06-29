# Sistem Sign Up Admin yang Aman

## Overview
Sistem sign up admin telah dibuat dengan keamanan berlapis untuk memastikan hanya admin yang sudah login yang dapat menambahkan admin baru.

## Fitur Keamanan

### 1. Proteksi Session
- Halaman `register.php` hanya bisa diakses oleh admin yang sudah login
- Menggunakan session `admin_logged_in` untuk validasi
- Redirect otomatis ke `login.php` jika belum login

### 2. Proteksi File .htaccess
- Memblokir akses langsung ke `register.php` dari luar
- Hanya mengizinkan akses dari referer admin panel
- Fallback ke pengecekan cookie session

### 3. Validasi Input
- Validasi email format
- Password minimal 6 karakter
- Konfirmasi password harus cocok
- Semua field wajib diisi

### 4. Keamanan Database
- Password di-hash menggunakan `password_hash()`
- Validasi email unik sebelum insert
- Role-based access control

## Cara Menggunakan

### 1. Akses dari Dashboard
1. Login sebagai admin di `admin/login.php`
2. Klik menu "Add Admin" di sidebar
3. Isi form dengan data admin baru
4. Submit untuk menambahkan admin

### 2. Struktur Menu
```
Dashboard
├── Products
├── Categories  
├── Testimonials
├── Blog Posts
├── Awards
├── Orders
└── Add Admin ← Menu baru
```

## File yang Terlibat

### 1. `admin/register.php`
- Form tambah admin baru
- Validasi input
- Integrasi dengan dashboard

### 2. `admin/index.php`
- Menambahkan menu "Add Admin" ke sidebar
- Link ke halaman register

### 3. `admin/.htaccess`
- Proteksi file register.php
- Blokir akses langsung dari luar

### 4. `admin/includes/functions.php`
- Fungsi `registerAdmin()`
- Fungsi `isEmailExists()`
- Validasi dan hashing password

## Keamanan Berlapis

### Layer 1: Session Check
```php
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}
```

### Layer 2: .htaccess Protection
```apache
<Files "register.php">
    Order Allow,Deny
    Deny from all
</Files>
```

### Layer 3: Input Validation
```php
if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
    $error = 'Semua field harus diisi!';
}
```

### Layer 4: Database Security
```php
$admin_data = [
    'password' => password_hash($password, PASSWORD_DEFAULT),
    'is_active' => 1
];
```

## Fitur Tambahan

### 1. Password Strength Indicator
- Menampilkan kekuatan password real-time
- Warna indikator: merah (lemah), kuning (sedang), hijau (kuat)

### 2. Toggle Password Visibility
- Tombol untuk menampilkan/menyembunyikan password
- Icon mata yang berubah sesuai status

### 3. Role Selection
- Pilihan role: Admin atau Super Admin
- Super Admin memiliki akses penuh

### 4. Responsive Design
- Interface yang responsif untuk mobile dan desktop
- Sidebar yang collapse di mobile

## Cara Testing

### 1. Test Akses Langsung
```
http://localhost/web/bready/admin/register.php
```
**Hasil:** Harus redirect ke login.php

### 2. Test dari Dashboard
1. Login sebagai admin
2. Klik menu "Add Admin"
3. **Hasil:** Harus bisa akses form

### 3. Test Validasi
- Coba submit form kosong
- Coba password tidak cocok
- Coba email tidak valid
- **Hasil:** Harus muncul pesan error

### 4. Test Penambahan Admin
1. Isi form dengan data valid
2. Submit form
3. **Hasil:** Admin baru harus terdaftar

## Troubleshooting

### Error: "Access Denied"
- Pastikan sudah login sebagai admin
- Cek session masih aktif
- Coba refresh halaman

### Error: "Email sudah terdaftar"
- Gunakan email yang berbeda
- Atau hapus admin lama dengan email tersebut

### Error: "Gagal menambahkan admin"
- Cek koneksi database
- Cek tabel admins sudah dibuat
- Cek permission database

## Maintenance

### 1. Backup Database
```sql
-- Backup tabel admins
mysqldump -u username -p database_name admins > admins_backup.sql
```

### 2. Reset Password Admin
```sql
UPDATE admins SET password = '$2y$10$...' WHERE email = 'admin@example.com';
```

### 3. Deactivate Admin
```sql
UPDATE admins SET is_active = 0 WHERE id = 1;
```

## Kesimpulan

Sistem sign up admin sudah dibuat dengan keamanan maksimal:
- ✅ Hanya admin yang login yang bisa akses
- ✅ Proteksi file dengan .htaccess
- ✅ Validasi input yang ketat
- ✅ Password hashing yang aman
- ✅ Interface yang user-friendly
- ✅ Dokumentasi lengkap

Sistem ini memastikan bahwa penambahan admin baru hanya bisa dilakukan oleh admin yang sudah berwenang, bukan dari luar sistem. 