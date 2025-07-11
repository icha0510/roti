<?php
// Test file untuk memastikan sistem QR code berfungsi
require_once 'generate_qr.php';

echo "<h2>Test Sistem QR Code</h2>";

try {
    // Test generate QR code
    $order_id = 999;
    $order_number = "TEST-2024-0001";
    $total_amount = 50000;
    $customer_name = "Test Customer";
    
    echo "<p>Menggenerate QR code untuk:</p>";
    echo "<ul>";
    echo "<li>Order ID: $order_id</li>";
    echo "<li>Order Number: $order_number</li>";
    echo "<li>Total: Rp " . number_format($total_amount, 3) . "</li>";
    echo "<li>Customer: $customer_name</li>";
    echo "</ul>";
    
    $qr_filename = generatePaymentQR($order_id, $order_number, $total_amount, $customer_name);
    
    echo "<p><strong>QR Code berhasil di-generate!</strong></p>";
    echo "<p>File: $qr_filename</p>";
    
    // Tampilkan QR code
    echo "<div style='text-align: center; margin: 20px;'>";
    echo "<h3>QR Code Test</h3>";
    echo "<img src='$qr_filename' alt='QR Code Test' style='border: 2px solid #e67e22; border-radius: 10px; max-width: 300px;'>";
    echo "<p style='color: #e67e22; font-weight: 600;'>QR Code berhasil di-generate!</p>";
    echo "</div>";
    
    // Test data JSON
    $payment_data = array(
        'order_id' => $order_id,
        'order_number' => $order_number,
        'total_amount' => $total_amount,
        'customer_name' => $customer_name,
        'timestamp' => date('Y-m-d H:i:s')
    );
    
    echo "<h3>Data QR Code (JSON):</h3>";
    echo "<pre style='background: #f8f9fa; padding: 15px; border-radius: 5px;'>";
    echo json_encode($payment_data, JSON_PRETTY_PRINT);
    echo "</pre>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>Error:</strong> " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='checkout.php'>Kembali ke Checkout</a> | <a href='admin/verify_payment.php'>Admin Panel</a></p>";
?> 