<?php
// Test file untuk simulasi pembayaran QRIS
session_start();
require_once 'config/database.php';

// Simulasi user login
$_SESSION['user_id'] = 1;
$_SESSION['user_name'] = 'Test User';

echo "<h2>Test Simulasi Pembayaran QRIS</h2>";

// Ambil order yang pending untuk test
$database = new Database();
$db = $database->getConnection();

$stmt = $db->prepare("
    SELECT o.*, u.name as user_name 
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.id 
    WHERE o.payment_method = 'qris' AND o.status = 'pending'
    ORDER BY o.created_at DESC 
    LIMIT 5
");
$stmt->execute();
$pending_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($pending_orders)) {
    echo "<p style='color: orange;'>⚠️ Tidak ada order pending untuk test. Silakan buat order baru dengan metode QRIS.</p>";
    echo "<p><a href='checkout.php'>🔗 Buat Order Baru</a></p>";
} else {
    echo "<h3>Order Pending untuk Test:</h3>";
    
    foreach ($pending_orders as $order) {
        echo "<div style='border: 2px solid #e67e22; border-radius: 10px; padding: 15px; margin: 15px 0; background: #f8f9fa;'>";
        echo "<h4>Order #" . htmlspecialchars($order['order_number']) . "</h4>";
        echo "<p><strong>Customer:</strong> " . htmlspecialchars($order['customer_name']) . "</p>";
        echo "<p><strong>Total:</strong> Rp " . number_format($order['total_amount'], 3) . "</p>";
        echo "<p><strong>Status:</strong> " . ucfirst($order['status']) . "</p>";
        echo "<p><strong>Tanggal:</strong> " . date('d/m/Y H:i', strtotime($order['created_at'])) . "</p>";
        
        // Form simulasi pembayaran
        echo "<form method='POST' style='margin-top: 15px;'>";
        echo "<input type='hidden' name='order_id' value='" . $order['id'] . "'>";
        echo "<input type='hidden' name='order_number' value='" . htmlspecialchars($order['order_number']) . "'>";
        echo "<div style='margin-bottom: 10px;'>";
        echo "<label><strong>Jumlah Pembayaran:</strong></label><br>";
        echo "<input type='number' name='amount_paid' value='" . $order['total_amount'] . "' step='1000' style='padding: 8px; border: 1px solid #ddd; border-radius: 5px; width: 200px;'>";
        echo "</div>";
        echo "<button type='submit' name='simulate_payment' style='background: #e67e22; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;'>";
        echo "Simulasi Pembayaran";
        echo "</button>";
        echo "</form>";
        
        echo "</div>";
    }
}

// Handle simulasi pembayaran
if ($_POST && isset($_POST['simulate_payment'])) {
    $order_id = intval($_POST['order_id']);
    $amount_paid = floatval($_POST['amount_paid']);
    
    echo "<h3>Hasil Simulasi Pembayaran:</h3>";
    
    // Kirim data ke payment_callback.php
    $payment_data = array(
        'order_id' => $order_id,
        'amount_paid' => $amount_paid,
        'payment_data' => array(
            'simulation' => true,
            'timestamp' => date('Y-m-d H:i:s'),
            'user_agent' => $_SERVER['HTTP_USER_AGENT']
        )
    );
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/payment_callback.php');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payment_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code == 200) {
        $result = json_decode($response, true);
        
        if ($result['success']) {
            echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
            echo "<h4>✅ Pembayaran Berhasil!</h4>";
            echo "<p><strong>Order:</strong> " . $result['order_number'] . "</p>";
            echo "<p><strong>Total Tagihan:</strong> Rp " . number_format($result['total_amount'], 3) . "</p>";
            echo "<p><strong>Dibayar:</strong> Rp " . number_format($result['amount_paid'], 3) . "</p>";
            echo "<p><strong>Status:</strong> " . $result['message'] . "</p>";
            echo "</div>";
        } else {
            echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
            echo "<h4>❌ Pembayaran Gagal!</h4>";
            echo "<p>" . $result['message'] . "</p>";
            echo "</div>";
        }
    } else {
        echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
        echo "<h4>❌ Error!</h4>";
        echo "<p>HTTP Code: " . $http_code . "</p>";
        echo "<p>Response: " . htmlspecialchars($response) . "</p>";
        echo "</div>";
    }
    
    echo "<p><a href='test_payment_simulation.php'>🔄 Test Lagi</a></p>";
}

echo "<hr>";
echo "<h3>Link Test Lainnya:</h3>";
echo "<p><a href='checkout.php'>🔗 Checkout Page</a></p>";
echo "<p><a href='admin/verify_payment.php'>🔗 Admin Panel - Verifikasi Pembayaran</a></p>";
echo "<p><a href='payment_callback.php'>🔗 Payment Callback Endpoint</a></p>";

echo "<hr>";
echo "<h3>Alur Kerja Pembayaran:</h3>";
echo "<ol>";
echo "<li>Customer scan QR code</li>";
echo "<li>QR code berisi data order dan callback URL</li>";
echo "<li>Payment gateway mengirim data ke callback URL</li>";
echo "<li>Sistem mengecek apakah pembayaran sesuai total tagihan</li>";
echo "<li>Jika sesuai: Order status berubah menjadi 'paid' dan 'processing'</li>";
echo "<li>Jika tidak sesuai: Order tetap 'pending', payment record 'failed'</li>";
echo "<li>Semua transaksi dicatat di tabel payment_transactions</li>";
echo "<li>Tracking history diupdate di order_tracking</li>";
echo "</ol>";
?> 