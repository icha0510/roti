# Sistem Login, Signup, dan Logo Pesanan - Bready Bakery

## 📋 Daftar File yang Dibuat

### 1. **Sistem Autentikasi**
- `login.php` - Halaman login user
- `signup.php` - Halaman registrasi user baru
- `logout.php` - Proses logout user
- `profile.php` - Halaman profil user

### 2. **Logo Pesanan**
- `logo-orders.php` - Halaman daftar pesanan dengan logo menarik

### 3. **Database**
- `users_table.sql` - SQL untuk membuat tabel users

### 4. **Fungsi Tambahan**
- `includes/functions.php` - Ditambahkan fungsi autentikasi

## 🚀 Cara Menggunakan

### 1. **Setup Database**
```sql
-- Jalankan file users_table.sql di database MySQL
-- File ini akan membuat tabel users dan sample data
```

### 2. **Fitur Login/Signup**
- **Login**: Akses `login.php`
  - Email: `john@example.com` atau `jane@example.com`
  - Password: `password`
- **Signup**: Akses `signup.php` untuk membuat akun baru
- **Profile**: Akses `profile.php` untuk mengubah data profil

### 3. **Logo Pesanan**
- Akses `logo-orders.php` untuk melihat daftar pesanan
- Halaman ini hanya bisa diakses setelah login
- Menampilkan pesanan dengan desain card yang menarik

## 🎨 Fitur Desain

### **Halaman Login/Signup**
- ✅ Gradient background yang menarik
- ✅ Form yang modern dan responsif
- ✅ Validasi input yang lengkap
- ✅ Pesan error/success yang informatif

### **Logo Pesanan**
- ✅ Hero section dengan gradient dan pattern
- ✅ Card design untuk setiap pesanan
- ✅ Status badge dengan warna yang berbeda
- ✅ Informasi pesanan yang lengkap
- ✅ Empty state yang menarik

### **Header Navigation**
- ✅ Dropdown menu untuk user yang sudah login
- ✅ Link ke My Orders, Profile, dan Logout
- ✅ Icon profil yang menampilkan nama user

## 🔧 Fungsi yang Ditambahkan

### **Di includes/functions.php:**
```php
// Autentikasi user
authenticateUser($email, $password)

// Registrasi user baru
registerUser($name, $email, $password, $phone, $address)

// Ambil data user
getUserById($user_id)

// Logout user
logoutUser()
```

## 📱 Responsive Design
- ✅ Mobile-friendly
- ✅ Bootstrap 5 compatible
- ✅ Custom CSS untuk tampilan yang menarik

## 🔒 Keamanan
- ✅ Password hashing dengan `password_hash()`
- ✅ Session management
- ✅ Input validation dan sanitization
- ✅ SQL injection protection dengan prepared statements

## 🎯 Cara Testing

1. **Buat akun baru:**
   - Buka `signup.php`
   - Isi form dengan data lengkap
   - Klik "Create Account"

2. **Login dengan akun yang ada:**
   - Buka `login.php`
   - Email: `john@example.com`
   - Password: `password`

3. **Lihat pesanan:**
   - Setelah login, klik nama user di header
   - Pilih "My Orders"
   - Atau akses langsung `logo-orders.php`

4. **Update profil:**
   - Klik nama user di header
   - Pilih "Profile"
   - Update informasi atau password

## 🎨 Customization

### **Mengubah Warna Theme:**
Edit CSS di file masing-masing:
```css
/* Ganti warna gradient */
background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);

/* Ganti warna button */
.btn-login, .btn-signup, .btn-update {
    background: linear-gradient(135deg, #YOUR_COLOR1 0%, #YOUR_COLOR2 100%);
}
```

### **Mengubah Logo:**
- Ganti file `images/logo-light.png` dan `images/logo-dark.png`
- Sesuaikan ukuran di CSS jika diperlukan

## 📞 Support
Jika ada pertanyaan atau masalah, silakan hubungi developer.

---
**Dibuat dengan ❤️ untuk Bready Bakery** 