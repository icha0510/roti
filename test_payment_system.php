<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Test Sistem Pembayaran Roti'O</h1>";
echo "<hr>";

// Test 1: Cek ekstensi GD
echo "<h2>1. Test Ekstensi GD</h2>";
if (extension_loaded('gd')) {
    echo "✅ Ekstensi GD tersedia<br>";
    echo "Versi GD: " . gd_info()['GD Version'] . "<br>";
} else {
    echo "❌ Ekstensi GD tidak tersedia<br>";
    echo "Silakan aktifkan di php.ini<br>";
}
echo "<br>";

// Test 2: Cek library QR Code
echo "<h2>2. Test Library QR Code</h2>";
if (file_exists('vendor/autoload.php')) {
    echo "✅ Library QR Code tersedia<br>";
} else {
    echo "❌ Library QR Code tidak tersedia<br>";
    echo "Silakan install dengan: composer require endroid/qr-code<br>";
}
echo "<br>";

// Test 3: Cek database connection
echo "<h2>3. Test Database Connection</h2>";
try {
    require_once 'config/database.php';
    $database = new Database();
    $db = $database->getConnection();
    echo "✅ Database connection berhasil<br>";
    
    // Cek tabel orders
    $stmt = $db->query("SHOW TABLES LIKE 'orders'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Tabel orders tersedia<br>";
    } else {
        echo "❌ Tabel orders tidak tersedia<br>";
    }
    
    // Cek tabel payment_transactions
    $stmt = $db->query("SHOW TABLES LIKE 'payment_transactions'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Tabel payment_transactions tersedia<br>";
    } else {
        echo "❌ Tabel payment_transactions tidak tersedia<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Database connection gagal: " . $e->getMessage() . "<br>";
}
echo "<br>";

// Test 4: Cek file permissions
echo "<h2>4. Test File Permissions</h2>";
$qr_dir = 'qr_codes';
if (is_dir($qr_dir)) {
    echo "✅ Folder qr_codes tersedia<br>";
    if (is_writable($qr_dir)) {
        echo "✅ Folder qr_codes dapat ditulis<br>";
    } else {
        echo "❌ Folder qr_codes tidak dapat ditulis<br>";
    }
} else {
    echo "⚠️ Folder qr_codes tidak ada (akan dibuat otomatis)<br>";
}
echo "<br>";

// Test 5: Cek file-file penting
echo "<h2>5. Test File-File Penting</h2>";
$important_files = [
    'checkout.php',
    'qr_payment_page.php',
    'generate_qr.php',
    'order-success.php',
    'payment_callback.php',
    'test_qr_payment_flow.php'
];

foreach ($important_files as $file) {
    if (file_exists($file)) {
        echo "✅ $file tersedia<br>";
    } else {
        echo "❌ $file tidak tersedia<br>";
    }
}
echo "<br>";

// Test 6: Cek order terbaru
echo "<h2>6. Test Data Order</h2>";
try {
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM orders");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Total orders: " . $result['total'] . "<br>";
    
    if ($result['total'] > 0) {
        $stmt = $db->prepare("SELECT * FROM orders ORDER BY created_at DESC LIMIT 1");
        $stmt->execute();
        $latest_order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "Order terbaru:<br>";
        echo "- ID: " . $latest_order['id'] . "<br>";
        echo "- Number: " . $latest_order['order_number'] . "<br>";
        echo "- Customer: " . $latest_order['customer_name'] . "<br>";
        echo "- Total: Rp " . number_format($latest_order['total_amount'], 0, ',', '.') . "<br>";
        echo "- Status: " . $latest_order['status'] . "<br>";
        echo "- Payment Method: " . $latest_order['payment_method'] . "<br>";
        
        // Test URL pembayaran
        $payment_url = "qr_payment_page.php?order_id=" . $latest_order['id'] . 
                      "&order_number=" . urlencode($latest_order['order_number']) . 
                      "&total_amount=" . $latest_order['total_amount'] . 
                      "&customer_name=" . urlencode($latest_order['customer_name']);
        
        echo "<br><strong>Test URL Pembayaran:</strong><br>";
        echo "<a href='$payment_url' target='_blank'>$payment_url</a><br>";
        
    } else {
        echo "Tidak ada order untuk testing<br>";
        echo "Silakan buat order terlebih dahulu melalui checkout<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}
echo "<br>";

// Test 7: Cek session
echo "<h2>7. Test Session</h2>";
if (session_status() === PHP_SESSION_ACTIVE) {
    echo "✅ Session aktif<br>";
    if (isset($_SESSION['user_id'])) {
        echo "✅ User logged in (ID: " . $_SESSION['user_id'] . ")<br>";
    } else {
        echo "⚠️ User tidak login<br>";
    }
} else {
    echo "❌ Session tidak aktif<br>";
}
echo "<br>";

// Summary
echo "<h2>📋 Ringkasan Test</h2>";
echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px;'>";
echo "<h3>Langkah Selanjutnya:</h3>";
echo "<ol>";
echo "<li>Jika semua test ✅ berhasil, sistem siap digunakan</li>";
echo "<li>Jika ada ❌, silakan perbaiki sesuai pesan error</li>";
echo "<li>Akses <a href='test_qr_payment_flow.php'>test_qr_payment_flow.php</a> untuk testing lengkap</li>";
echo "<li>Buat order baru melalui <a href='checkout.php'>checkout.php</a></li>";
echo "<li>Test pembayaran dengan scan QR code</li>";
echo "</ol>";
echo "</div>";

echo "<br><hr>";
echo "<p><strong>Test selesai pada:</strong> " . date('Y-m-d H:i:s') . "</p>";
?> 