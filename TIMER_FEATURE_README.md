# Fitur Timer 5 Menit - Roti'O Bakery

## Overview

Fitur timer 5 menit untuk pembatalan otomatis pesanan yang tidak dibayar dalam waktu yang ditentukan.

## Fitur Utama

### 1. Timer Countdown

- Menampilkan waktu tersisa dalam format MM:SS
- Update real-time setiap detik
- Visual warning ketika < 1 menit tersisa

### 2. Auto Cancellation

- Pesanan dibatalkan otomatis jika tidak dibayar dalam 5 menit
- Status order diupdate menjadi 'cancelled'
- Tracking record dibuat untuk pembatalan

### 3. Visual Feedback

- Timer berubah warna merah ketika < 1 menit tersisa
- Animasi pulse ketika waktu hampir habis
- Halaman timeout yang informatif

## Flow Timer

```
1. Order dibuat → Timer 5 menit dimulai
2. Halaman pembayaran → Timer countdown ditampilkan
3. < 1 menit tersisa → Timer berubah merah dan berkedip
4. Waktu habis → Pesanan dibatalkan otomatis
5. Redirect → User diarahkan ke halaman timeout
```

## File yang Terlibat

### 1. `qr_payment_page.php`

**Fitur yang ditambahkan:**

- Timer countdown display
- Auto cancellation logic
- Visual timer dengan warning
- JavaScript countdown function

**Kode Timer:**

```php
// Cek apakah pesanan sudah expired (5 menit dari created_at)
$order_created = strtotime($order['created_at']);
$current_time = time();
$time_limit = 5 * 60; // 5 menit dalam detik
$time_remaining = $time_limit - ($current_time - $order_created);

// Jika waktu sudah habis dan status masih pending, batalkan pesanan
if ($time_remaining <= 0 && $order['status'] === 'pending') {
    // Update status order menjadi cancelled
    // Insert tracking untuk pembatalan
    // Redirect ke halaman timeout
}
```

### 2. `order-timeout.php` (BARU)

**Fitur:**

- Halaman khusus untuk pesanan yang dibatalkan
- Menampilkan detail pesanan yang expired
- Tombol untuk kembali ke beranda atau pesan lagi
- Animasi dan styling yang menarik

### 3. `cleanup_expired_orders.php` (BARU)

**Fitur:**

- Script untuk membersihkan order yang expired
- Bisa dijalankan manual atau via cron job
- Menampilkan statistik order
- Menampilkan order pending yang masih aktif

## Database Changes

### Tabel `orders`

- Status baru: `cancelled` (untuk pesanan yang timeout)

### Tabel `order_tracking`

- Record baru: `'cancelled', 'Order cancelled due to payment timeout (5 minutes)'`

## JavaScript Timer

```javascript
// Timer countdown
let timeRemaining = <?php echo $time_remaining; ?>;
const countdownElement = document.getElementById('countdown');
const timerSection = document.querySelector('.timer-section');

function updateCountdown() {
    if (timeRemaining <= 0) {
        // Redirect ke halaman timeout
        window.location.href = 'order-timeout.php?order_id=<?php echo $order_id; ?>&order_number=<?php echo $order_number; ?>';
        return;
    }

    const minutes = Math.floor(timeRemaining / 60);
    const seconds = timeRemaining % 60;
    countdownElement.innerHTML = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;

    // Ubah warna timer menjadi merah ketika kurang dari 1 menit
    if (timeRemaining <= 60) {
        timerSection.style.background = 'linear-gradient(135deg, #e74c3c, #c0392b)';
        countdownElement.style.animation = 'pulse 1s infinite';
    }

    timeRemaining--;
}

// Update countdown setiap detik
setInterval(updateCountdown, 1000);
```

## CSS Animations

```css
@keyframes pulse {
  0% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.05);
  }
  100% {
    transform: scale(1);
  }
}

@keyframes shake {
  0%,
  100% {
    transform: translateX(0);
  }
  25% {
    transform: translateX(-5px);
  }
  75% {
    transform: translateX(5px);
  }
}
```

## Testing

### File Test

- `test_timer_feature.php` - Test logika timer dan database

### Cara Test

1. Buat pesanan baru dengan QRIS
2. Buka halaman pembayaran
3. Perhatikan timer countdown
4. Tunggu sampai waktu habis (atau modifikasi waktu di database untuk test)
5. Verifikasi pesanan dibatalkan otomatis

### Test Manual

```sql
-- Modifikasi waktu order untuk test (set ke 5 menit yang lalu)
UPDATE orders
SET created_at = DATE_SUB(NOW(), INTERVAL 5 MINUTE)
WHERE order_number = 'ORD-2025-XXXX';
```

## Automation

### Cron Job

Untuk membersihkan order expired secara otomatis:

```bash
# Jalankan setiap 1 menit
*/1 * * * * php /path/to/cleanup_expired_orders.php

# Jalankan setiap 5 menit
*/5 * * * * php /path/to/cleanup_expired_orders.php
```

### Manual Cleanup

Jalankan script cleanup secara manual:

```bash
php cleanup_expired_orders.php
```

## Keuntungan

1. **Mencegah order pending yang tidak dibayar**
2. **Mengurangi beban sistem**
3. **Memberikan feedback jelas kepada user**
4. **Meningkatkan user experience**
5. **Memastikan inventory management yang akurat**

## Monitoring

### Statistik yang Tersedia

- Jumlah order pending
- Jumlah order paid
- Jumlah order cancelled
- Order yang akan expired dalam 1 menit

### Alert System

- Visual warning ketika timer < 1 menit
- Auto cancellation ketika waktu habis
- Halaman timeout yang informatif

## Troubleshooting

### Timer Tidak Berfungsi

1. Cek JavaScript console untuk error
2. Verifikasi variabel `$time_remaining` di PHP
3. Pastikan order status masih 'pending'

### Order Tidak Dibatal Otomatis

1. Cek log database untuk error
2. Verifikasi query UPDATE dan INSERT
3. Pastikan transaction berhasil di-commit

### Cleanup Script Error

1. Cek permission file
2. Verifikasi database connection
3. Cek log error PHP

## Status

✅ **Fitur timer 5 menit sudah diterapkan dan berfungsi dengan baik!**

- ✅ Timer countdown real-time
- ✅ Auto cancellation
- ✅ Visual warning
- ✅ Halaman timeout
- ✅ Cleanup script
- ✅ Database tracking
