# Sistem Pembayaran Roti'O

## Overview
Sistem pembayaran Roti'O telah diperbarui dengan fitur QRIS dan halaman pembayaran yang menarik. Ketika user scan QR code, mereka akan diarahkan ke halaman pembayaran yang modern dengan styling yang baik.

## Fitur Utama

### 1. Metode Pembayaran
- **Cash**: Pembayaran tunai langsung
- **QRIS**: Pembayaran digital dengan QR code

### 2. Alur Pembayaran

#### A. Pembuatan Order
1. User memilih produk di cart
2. User checkout dan memilih metode pembayaran
3. Jika memilih QRIS, QR code akan di-generate otomatis
4. Order disimpan dengan status 'pending'

#### B. Pembayaran QRIS
1. User scan QR code dari order
2. QR code mengarah ke `qr_payment_page.php`
3. Halaman pembayaran menampilkan:
   - Detail order yang lengkap
   - Pilihan metode pembayaran (Cash/QRIS)
   - Form pembayaran dengan validasi
   - Styling yang menarik dan modern

#### C. Proses Pembayaran
1. User memilih metode pembayaran
2. User memasukkan jumlah pembayaran
3. Sistem validasi jumlah pembayaran
4. Update status order menjadi 'paid'
5. Simpan transaksi pembayaran
6. Update tracking order
7. Redirect ke halaman sukses

## File-File Penting

### 1. `checkout.php`
- Form checkout dengan pilihan metode pembayaran
- Generate QR code otomatis untuk QRIS
- Validasi form dan penyimpanan order

### 2. `qr_payment_page.php` (BARU)
- Halaman pembayaran yang menarik dengan styling modern
- Menampilkan detail order lengkap
- Form pembayaran dengan validasi
- Proses pembayaran langsung
- Responsive design

### 3. `generate_qr.php`
- Generate QR code dengan data order
- QR code mengarah ke halaman pembayaran
- Support AJAX request untuk generate QR code

### 4. `order-success.php`
- Halaman sukses setelah pembayaran
- Menampilkan QR code jika metode QRIS
- Informasi order yang lengkap

### 5. `payment_callback.php`
- Endpoint untuk callback pembayaran otomatis
- Validasi pembayaran
- Update status order

## Database Schema

### Tabel `orders`
```sql
- id (Primary Key)
- order_number (Unique)
- user_id
- customer_name
- customer_email
- customer_phone
- nomor_meja
- notes
- total_amount
- status (pending, paid, completed, cancelled)
- payment_method (cash, qris)
- created_at
- updated_at
```

### Tabel `payment_transactions`
```sql
- id (Primary Key)
- order_id (Foreign Key)
- payment_method
- amount_paid
- payment_date
- status
- created_at
```

### Tabel `order_tracking`
```sql
- id (Primary Key)
- order_id (Foreign Key)
- status
- description
- created_at
```

## Cara Penggunaan

### 1. Testing QR Code
```bash
# Akses file test
http://localhost/web/bready/test_qr_payment_flow.php
```

### 2. Generate QR Code Manual
```bash
# URL untuk generate QR code
http://localhost/web/bready/generate_qr.php?order_id=1&order_number=ORD-2024-0001&total_amount=50000&customer_name=John%20Doe
```

### 3. Akses Halaman Pembayaran
```bash
# URL halaman pembayaran
http://localhost/web/bready/qr_payment_page.php?order_id=1&order_number=ORD-2024-0001&total_amount=50000&customer_name=John%20Doe
```

## Styling dan UI

### Design System
- **Font**: Poppins (Google Fonts)
- **Color Scheme**: Orange (#e67e22, #f39c12)
- **Background**: Gradient purple-blue
- **Cards**: White with shadow and border radius
- **Buttons**: Gradient orange with hover effects

### Responsive Design
- Mobile-first approach
- Grid layout yang adaptif
- Touch-friendly buttons
- Optimized untuk berbagai ukuran layar

### Animations
- Hover effects pada cards dan buttons
- Loading spinner saat proses pembayaran
- Smooth transitions
- Pulse animation untuk total pembayaran

## Security Features

### 1. Validasi Input
- Sanitasi data input
- Validasi jumlah pembayaran
- Validasi metode pembayaran
- CSRF protection

### 2. Database Security
- Prepared statements
- Transaction handling
- Error handling yang proper

### 3. File Security
- Validasi file uploads
- Secure file naming
- Proper file permissions

## Error Handling

### 1. QR Code Generation
- Cek ekstensi GD PHP
- Cek library QR Code
- Error handling untuk file creation

### 2. Payment Processing
- Validasi order existence
- Validasi payment amount
- Database transaction rollback
- User-friendly error messages

## Performance Optimization

### 1. QR Code
- Caching QR code images
- Optimized image size
- Proper file cleanup

### 2. Database
- Indexed queries
- Efficient joins
- Connection pooling

### 3. Frontend
- Minified CSS/JS
- Optimized images
- Lazy loading

## Troubleshooting

### 1. QR Code Tidak Muncul
- Cek ekstensi GD PHP: `php -m | grep gd`
- Cek library QR Code: `composer require endroid/qr-code`
- Cek folder permissions: `chmod 755 qr_codes/`

### 2. Halaman Pembayaran Error
- Cek parameter URL
- Cek database connection
- Cek file permissions

### 3. Pembayaran Gagal
- Cek database transaction
- Cek payment validation
- Cek error logs

## Future Enhancements

### 1. Payment Gateway Integration
- Integrasi dengan payment gateway real
- Webhook handling
- Payment status synchronization

### 2. Mobile App
- Native mobile app
- Push notifications
- Offline support

### 3. Analytics
- Payment analytics
- User behavior tracking
- Performance monitoring

## Support

Untuk bantuan teknis atau pertanyaan, silakan hubungi:
- Email: support@rotio.com
- Phone: (+62) 812-3456-7890
- Documentation: https://docs.rotio.com

---

**Versi**: 2.0  
**Update**: Desember 2024  
**Author**: Roti'O Development Team 