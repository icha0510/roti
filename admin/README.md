# Admin Dashboard - Bready Bakery

Panel admin untuk mengelola website Bready Bakery dengan fitur upload gambar langsung ke database.

## Fitur Admin Panel

### ✅ Manajemen Produk
- Tambah, edit, hapus produk
- Upload gambar langsung ke database (base64)
- Set status produk (Featured, New, Sale)
- Manajemen stok dan harga
- Kategori produk

### ✅ Manajemen Banner
- Tambah banner slider homepage
- Upload gambar banner
- Set badge dan link
- Pengaturan urutan tampilan

### ✅ Manajemen Testimonial
- Tambah testimonial pelanggan
- Upload foto profil
- Rating dan review

### ✅ Manajemen Blog Posts
- Tambah artikel blog
- Upload gambar artikel
- Status draft/published

### ✅ Manajemen Awards
- Tambah penghargaan toko
- Upload icon/logo award

## Instalasi Admin Panel

### 1. Update Database
Jalankan file `admin/database_update.sql` untuk menambah kolom penyimpanan gambar:

```sql
-- Import file admin/database_update.sql ke database
```

### 2. Konfigurasi
Edit file `admin/config/database.php` sesuai dengan database Anda:

```php
private $host = 'localhost';
private $db_name = 'bready_db';
private $username = 'root';
private $password = '';
```

### 3. Akses Admin Panel
```
http://localhost/bready/admin/
```

## Struktur File Admin

```
admin/
├── config/
│   └── database.php          # Konfigurasi database
├── includes/
│   └── functions.php         # Fungsi-fungsi admin
├── index.php                 # Dashboard utama
├── products.php              # Manajemen produk
├── categories.php            # Manajemen kategori
├── banners.php               # Manajemen banner
├── testimonials.php          # Manajemen testimonial
├── posts.php                 # Manajemen blog
├── awards.php                # Manajemen awards
├── get_product.php           # API get product
├── database_update.sql       # Update database
└── README.md                 # Dokumentasi ini
```

## Cara Penggunaan

### 1. Dashboard
- Melihat statistik website
- Quick actions untuk menambah konten
- Overview produk, banner, testimonial

### 2. Manajemen Produk
1. Klik "Add New Product"
2. Isi informasi produk
3. Upload gambar (otomatis disimpan ke database)
4. Set status dan kategori
5. Klik "Add Product"

### 3. Manajemen Banner
1. Klik "Add New Banner"
2. Upload gambar banner
3. Set title, subtitle, badge
4. Atur urutan tampilan
5. Klik "Add Banner"

### 4. Manajemen Konten Lainnya
- Testimonial: Tambah review pelanggan
- Blog Posts: Tulis artikel
- Awards: Tambah penghargaan

## Keunggulan Sistem

### 🔒 Keamanan
- Validasi input
- Sanitasi data
- Prepared statements
- File upload validation

### 🖼️ Penyimpanan Gambar
- Gambar disimpan dalam database (base64)
- Tidak perlu folder upload
- Backup lebih mudah
- Tidak ada broken links

### 📱 Responsive Design
- Bootstrap 5
- Mobile-friendly
- Modern UI/UX

### ⚡ Performa
- Optimized queries
- Efficient image handling
- Fast loading

## Troubleshooting

### Error Upload Gambar
- Pastikan file < 5MB
- Format: JPG, PNG, GIF, WebP
- Periksa permission folder

### Error Database
- Periksa konfigurasi database
- Pastikan tabel sudah dibuat
- Jalankan database_update.sql

### Gambar Tidak Muncul
- Periksa kolom image_data di database
- Pastikan base64 encoding berhasil
- Clear browser cache

## Customization

### Menambah Fitur Baru
1. Buat fungsi di `includes/functions.php`
2. Buat halaman admin baru
3. Tambah menu di sidebar
4. Update database jika perlu

### Mengubah Tema
- Edit CSS di file admin
- Modifikasi Bootstrap classes
- Custom styling

## Support

Untuk bantuan atau pertanyaan, silakan buat issue di repository ini.

## License

Admin panel ini dibuat untuk website Bready Bakery. 