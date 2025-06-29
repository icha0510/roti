# Sistem Login Admin - Bready Bakery

## Fitur yang Tersedia

### 1. Login Admin
- ✅ Autentikasi dengan email dan password
- ✅ Session management
- ✅ Redirect otomatis jika sudah login
- ✅ Pesan error yang informatif

### 2. Registrasi Admin
- ✅ Pendaftaran admin baru
- ✅ Validasi input (email, password, konfirmasi)
- ✅ Password strength checker
- ✅ Cek email duplikat
- ✅ Hash password dengan bcrypt

### 3. Logout
- ✅ Logout aman dengan session destroy
- ✅ Update last_login timestamp
- ✅ Redirect ke halaman login

### 4. Proteksi Halaman
- ✅ Semua halaman admin terlindungi
- ✅ Redirect otomatis jika belum login
- ✅ Session timeout handling

## File yang Dibuat

### Core Files:
- `admin/login.php` - Halaman login admin
- `admin/register.php` - Halaman pendaftaran admin
- `admin/logout.php` - Proses logout
- `admin/auth_check.php` - File untuk cek autentikasi

### Database:
- `admin/create_admins_table.sql` - SQL untuk membuat tabel admins

### Functions (di functions.php):
- `authenticateAdmin()` - Autentikasi admin
- `isEmailExists()` - Cek email duplikat
- `registerAdmin()` - Daftar admin baru
- `getAdminById()` - Ambil data admin
- `updateAdminPassword()` - Update password

## Struktur Database

### Tabel: admins
```sql
CREATE TABLE admins (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'super_admin') DEFAULT 'admin',
    is_active BOOLEAN DEFAULT TRUE,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

## Cara Penggunaan

### 1. Setup Database
```bash
# Jalankan SQL untuk membuat tabel
C:\xampp\mysql\bin\mysql.exe -u root -e "source admin/create_admins_table.sql"
```

### 2. Login Default
- **Email**: admin@bready.com
- **Password**: admin123

### 3. Daftar Admin Baru
1. Buka: `http://localhost/web/bready/admin/register.php`
2. Isi form pendaftaran
3. Login dengan akun baru

### 4. Login Admin
1. Buka: `http://localhost/web/bready/admin/login.php`
2. Masukkan email dan password
3. Redirect ke dashboard

## Keamanan

### 1. Password Hashing
- Menggunakan `password_hash()` dengan bcrypt
- Salt otomatis untuk setiap password
- Verifikasi dengan `password_verify()`

### 2. Session Management
- Session timeout handling
- Session destroy saat logout
- Proteksi CSRF (Cross-Site Request Forgery)

### 3. Input Validation
- Validasi email format
- Password strength checker
- Sanitasi input
- Prepared statements untuk SQL

### 4. Access Control
- Cek session di setiap halaman admin
- Redirect otomatis jika belum login
- Role-based access (admin/super_admin)

## Password Strength

### Kriteria Password:
- ✅ Minimal 6 karakter
- ✅ Kombinasi huruf besar/kecil
- ✅ Angka
- ✅ Karakter khusus

### Level Strength:
- 🔴 **Lemah**: < 2 kriteria
- 🟡 **Sedang**: 2-3 kriteria  
- 🟢 **Kuat**: 4-5 kriteria

## Integrasi dengan Halaman Admin

### 1. Tambahkan di Setiap Halaman Admin:
```php
<?php
require_once 'auth_check.php';
require_once 'includes/functions.php';
?>
```

### 2. Update Sidebar:
```php
<strong><?php echo htmlspecialchars(getCurrentAdminName()); ?></strong>
```

### 3. Logout Link:
```php
<a href="logout.php">Sign out</a>
```

## Troubleshooting

### Error: "Call to undefined function authenticateAdmin()"
- Pastikan file `functions.php` sudah di-include
- Cek apakah fungsi sudah ditambahkan

### Error: "Table 'admins' doesn't exist"
- Jalankan SQL untuk membuat tabel
- Cek koneksi database

### Login Gagal
- Cek email dan password
- Pastikan admin aktif (is_active = 1)
- Cek log error PHP

### Session Hilang
- Cek konfigurasi session di php.ini
- Pastikan cookies enabled
- Cek session timeout

## Konfigurasi Tambahan

### Session Timeout (opsional)
```php
// Di auth_check.php
ini_set('session.gc_maxlifetime', 3600); // 1 jam
session_set_cookie_params(3600);
```

### Remember Me (opsional)
```php
// Tambahkan cookie untuk remember me
if (isset($_POST['remember_me'])) {
    setcookie('admin_remember', $token, time() + (30 * 24 * 60 * 60));
}
```

## Kesimpulan

Sistem login admin sudah lengkap dengan:
- ✅ Autentikasi aman
- ✅ Registrasi admin baru
- ✅ Proteksi halaman
- ✅ Session management
- ✅ Password hashing
- ✅ Input validation

Siap digunakan untuk mengamankan admin panel Bready Bakery! 