<?php
// Test file untuk memverifikasi fitur auto payment
echo "<h1>Test Auto Payment Feature</h1>";

// Test data
$order_id = 58;
$order_number = 'ORD-2025-1457';
$total_amount = 25;
$customer_name = 'iki';

// Generate URL dengan auto_pay=true
$payment_url = 'http://localhost/Latihan/roti/qr_payment_page.php';
$qr_url_with_auto = $payment_url . '?order_id=' . urlencode($order_id) . 
                    '&order_number=' . urlencode($order_number) . 
                    '&total_amount=' . urlencode($total_amount) . 
                    '&customer_name=' . urlencode($customer_name) . 
                    '&auto_pay=true';

// Generate URL tanpa auto_pay (untuk perbandingan)
$qr_url_without_auto = $payment_url . '?order_id=' . urlencode($order_id) . 
                       '&order_number=' . urlencode($order_number) . 
                       '&total_amount=' . urlencode($total_amount) . 
                       '&customer_name=' . urlencode($customer_name);

echo "<h2>Fitur Auto Payment:</h2>";
echo "<p>Ketika user scan QR code dan mengakses URL dengan parameter <code>auto_pay=true</code>, sistem akan:</p>";
echo "<ol>";
echo "<li>Memeriksa status pesanan (harus 'pending')</li>";
echo "<li>Memproses pembayaran otomatis</li>";
echo "<li>Update status order menjadi 'paid'</li>";
echo "<li>Simpan transaksi pembayaran</li>";
echo "<li>Redirect ke halaman sukses</li>";
echo "</ol>";

echo "<h2>Test URL:</h2>";
echo "<h3>Dengan Auto Payment (QR Code):</h3>";
echo "<p><a href='" . $qr_url_with_auto . "' target='_blank'>Klik di sini untuk test auto payment</a></p>";
echo "<p><small>URL: " . $qr_url_with_auto . "</small></p>";

echo "<h3>Tanpa Auto Payment (Manual):</h3>";
echo "<p><a href='" . $qr_url_without_auto . "' target='_blank'>Klik di sini untuk test manual payment</a></p>";
echo "<p><small>URL: " . $qr_url_without_auto . "</small></p>";

echo "<h2>Perbedaan:</h2>";
echo "<ul>";
echo "<li><strong>Dengan auto_pay=true:</strong> Pembayaran diproses otomatis jika status 'pending'</li>";
echo "<li><strong>Tanpa auto_pay:</strong> User harus klik button 'Bayar dengan QRIS'</li>";
echo "</ul>";

echo "<h2>Status:</h2>";
echo "<p style='color: green; font-weight: bold;'>✅ Fitur auto payment sudah ditambahkan!</p>";
echo "<p>QR code sekarang akan mengarahkan ke URL dengan auto_pay=true untuk pemrosesan otomatis.</p>";
?> 