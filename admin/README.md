# Bready Admin Panel

Admin panel untuk mengelola website Bready Bakery.

## Fitur yang Tersedia

### 1. Dashboard
- Statistik produk, kategori, banner, dan blog posts
- Tabel produk terbaru
- Quick actions untuk menambah konten baru
- Informasi sistem

### 2. Products Management
- Menambah, mengedit, dan menghapus produk
- Upload gambar produk (disimpan sebagai base64 di database)
- Kategori produk
- Manajemen stok dan harga

### 3. Categories Management
- Menambah, mengedit, dan menghapus kategori
- Slug otomatis dari nama kategori
- Deskripsi kategori

### 4. Banners Management
- Menambah, mengedit, dan menghapus banner
- Upload gambar banner
- Pengaturan link dan badge
- Sort order untuk urutan tampilan



### 6. Blog Posts Management
- Menambah dan menghapus blog posts
- Upload featured image
- Status publish/draft
- Author dan excerpt

### 7. Awards Management
- Menambah dan menghapus awards
- Upload gambar award
- Tahun award
- Status aktif/nonaktif

### 8. Orders Management
- Melihat semua pesanan
- Update status pesanan
- Tracking pesanan
- Detail customer dan produk

## Struktur File

```
admin/
├── index.php              # Dashboard utama
├── products.php           # Manajemen produk
├── categories.php         # Manajemen kategori
├── banners.php           # Manajemen banner

├── posts.php             # Manajemen blog posts
├── awards.php            # Manajemen awards
├── orders.php            # Manajemen pesanan
├── logout.php            # Logout admin
├── includes/
│   └── functions.php     # Fungsi-fungsi helper
└── config/
    └── database.php      # Konfigurasi database
```

## Fungsi-fungsi Utama

### Database Functions
- `getAllProducts()` - Mengambil semua produk
- `getProductById($id)` - Mengambil produk berdasarkan ID
- `addProduct($data)` - Menambah produk baru
- `updateProduct($id, $data)` - Update produk
- `deleteProduct($id)` - Hapus produk

### Image Functions
- `uploadImageToDatabase($file)` - Upload dan konversi gambar ke base64
- `displayImage($image_data, $mime_type, $class, $alt)` - Menampilkan gambar dari base64

### Utility Functions
- `createSlug($string)` - Membuat slug dari string
- `validateInput($data)` - Validasi input
- `showAlert($message, $type)` - Menampilkan alert

## Cara Penggunaan

1. **Akses Admin Panel**
   - Buka browser dan akses: `http://localhost/web/bready/admin/`
   - Login dengan kredensial admin

2. **Menambah Produk**
   - Klik menu "Products"
   - Klik "Add New Product"
   - Isi form dan upload gambar
   - Klik "Add Product"

3. **Menambah Banner**
   - Klik menu "Banners"
   - Klik "Add New Banner"
   - Upload gambar dan isi form
   - Klik "Add Banner"

4. **Mengelola Pesanan**
   - Klik menu "Orders"
   - Lihat daftar pesanan
   - Update status pesanan sesuai kebutuhan

## Database Tables

### products
- id, name, slug, description, price, sale_price
- category_id, image_data, image_mime, stock
- is_featured, is_new, is_sale, rating, created_at

### categories
- id, name, slug, description

### banners
- id, title, subtitle, image_data, image_mime
- link, badge_text, badge_type, sort_order, is_active



### posts
- id, title, slug, content, excerpt, author
- image_data, image_mime, is_published, created_at

### awards
- id, title, description, year
- image_data, image_mime, is_active

### orders
- id, user_id, total_amount, status, created_at
- shipping_address, payment_method

## Keamanan

- Validasi input pada semua form
- Sanitasi data sebelum disimpan ke database
- Pembatasan tipe file upload (hanya gambar)
- Pembatasan ukuran file (maksimal 5MB)
- Session management untuk login

## Troubleshooting

### Error: Call to undefined function displayImage()
- Pastikan fungsi `displayImage()` sudah ada di `includes/functions.php`
- Fungsi ini digunakan untuk menampilkan gambar dari data base64

### Error: Database connection failed
- Periksa konfigurasi database di `config/database.php`
- Pastikan MySQL server berjalan
- Periksa username, password, dan nama database

### Error: Upload file failed
- Periksa permission folder upload
- Pastikan ukuran file tidak melebihi 5MB
- Pastikan tipe file adalah gambar (JPG, PNG, GIF, WebP)

## Dependencies

- PHP 7.4+
- MySQL 5.7+
- Bootstrap 5.1.3
- Font Awesome 6.0.0
- jQuery (untuk beberapa fitur)

## Support

Untuk bantuan teknis atau pertanyaan, silakan hubungi tim development. 