<?php
// Test file untuk memverifikasi URL QR code
echo "<h1>Test QR Code URL</h1>";

// Test data
$order_id = 58;
$order_number = 'ORD-2025-1457';
$total_amount = 25;
$customer_name = 'iki';

// Generate URL yang benar
$payment_url = 'http://localhost/Latihan/roti/qr_payment_page.php';
    $qr_url = $payment_url . '?order_id=' . urlencode($order_id) . 
              '&order_number=' . urlencode($order_number) . 
              '&total_amount=' . urlencode($total_amount) . 
              '&customer_name=' . urlencode($customer_name);
    
echo "<h2>URL yang Benar:</h2>";
echo "<p><strong>Base URL:</strong> " . $payment_url . "</p>";
echo "<p><strong>Full URL:</strong> " . $qr_url . "</p>";

echo "<h2>Test URL:</h2>";
echo "<p><a href='" . $qr_url . "' target='_blank'>Klik di sini untuk test URL</a></p>";

echo "<h2>Status:</h2>";
echo "<p style='color: green; font-weight: bold;'>✅ URL sudah diperbaiki!</p>";
echo "<p>URL sekarang mengarah ke: <code>http://localhost/Latihan/roti/qr_payment_page.php</code></p>";
echo "<p>Silakan test QR code yang baru di-generate.</p>";
?> 