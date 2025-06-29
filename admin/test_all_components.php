<?php
session_start();
require_once '../config/database.php';

echo "<h1>Test Komponen FPDF dan Print Order</h1>";

// Test 1: Cek FPDF
echo "<h2>1. Test FPDF Library</h2>";
if (file_exists('../fpdf186/fpdf.php')) {
    echo "✅ File FPDF ditemukan: ../fpdf186/fpdf.php<br>";
    
    try {
        require_once '../fpdf186/fpdf.php';
        $pdf = new FPDF();
        echo "✅ FPDF berhasil di-load<br>";
    } catch (Exception $e) {
        echo "❌ Error loading FPDF: " . $e->getMessage() . "<br>";
    }
} else {
    echo "❌ File FPDF tidak ditemukan<br>";
}

// Test 2: Cek Database
echo "<h2>2. Test Database Connection</h2>";
try {
    $database = new Database();
    $db = $database->getConnection();
    echo "✅ Koneksi database berhasil<br>";
} catch (Exception $e) {
    echo "❌ Error database: " . $e->getMessage() . "<br>";
}

// Test 3: Cek Logo
echo "<h2>3. Test Logo File</h2>";
if (file_exists('../images/logo-dark.png')) {
    echo "✅ Logo ditemukan: ../images/logo-dark.png<br>";
    $logo_size = filesize('../images/logo-dark.png');
    echo "📏 Ukuran logo: " . number_format($logo_size) . " bytes<br>";
} else {
    echo "❌ Logo tidak ditemukan<br>";
}

// Test 4: Cek Session
echo "<h2>4. Test Session</h2>";
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    echo "✅ Admin sudah login<br>";
    echo "👤 Username: " . ($_SESSION['admin_username'] ?? 'Tidak ada') . "<br>";
} else {
    echo "⚠️ Admin belum login (akan di-redirect saat akses print_order.php)<br>";
}

// Test 5: Cek Data Order
echo "<h2>5. Test Data Order</h2>";
if (isset($db)) {
    try {
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM orders");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "📊 Total orders: " . $result['total'] . "<br>";
        
        if ($result['total'] > 0) {
            $stmt = $db->prepare("SELECT id, order_number FROM orders ORDER BY id DESC LIMIT 1");
            $stmt->execute();
            $order = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "🆔 Order terbaru: ID " . $order['id'] . " - " . $order['order_number'] . "<br>";
        }
    } catch (Exception $e) {
        echo "❌ Error query orders: " . $e->getMessage() . "<br>";
    }
}

// Test 6: Cek Order Items
echo "<h2>6. Test Order Items</h2>";
if (isset($db)) {
    try {
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM order_items");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "📦 Total order items: " . $result['total'] . "<br>";
    } catch (Exception $e) {
        echo "❌ Error query order_items: " . $e->getMessage() . "<br>";
    }
}

// Test 7: Cek Order Tracking
echo "<h2>7. Test Order Tracking</h2>";
if (isset($db)) {
    try {
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM order_tracking");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "📋 Total tracking records: " . $result['total'] . "<br>";
    } catch (Exception $e) {
        echo "❌ Error query order_tracking: " . $e->getMessage() . "<br>";
    }
}

// Test 8: Cek File Print Order
echo "<h2>8. Test File Print Order</h2>";
if (file_exists('print_order.php')) {
    echo "✅ File print_order.php ditemukan<br>";
    
    // Cek syntax
    $output = shell_exec('php -l print_order.php 2>&1');
    if (strpos($output, 'No syntax errors') !== false) {
        echo "✅ Syntax print_order.php valid<br>";
    } else {
        echo "❌ Syntax error di print_order.php:<br>";
        echo "<pre>" . htmlspecialchars($output) . "</pre>";
    }
} else {
    echo "❌ File print_order.php tidak ditemukan<br>";
}

// Test 9: Cek PHP Extensions
echo "<h2>9. Test PHP Extensions</h2>";
$required_extensions = ['gd', 'mbstring', 'pdo', 'pdo_mysql'];
foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "✅ Extension $ext tersedia<br>";
    } else {
        echo "❌ Extension $ext tidak tersedia<br>";
    }
}

// Test 10: Cek Permission
echo "<h2>10. Test File Permissions</h2>";
$files_to_check = [
    '../fpdf186/fpdf.php',
    '../images/logo-dark.png',
    'print_order.php',
    'test_pdf.php'
];

foreach ($files_to_check as $file) {
    if (file_exists($file)) {
        if (is_readable($file)) {
            echo "✅ $file dapat dibaca<br>";
        } else {
            echo "❌ $file tidak dapat dibaca<br>";
        }
    } else {
        echo "❌ $file tidak ditemukan<br>";
    }
}

echo "<hr>";
echo "<h2>Langkah Selanjutnya:</h2>";
echo "<ol>";
echo "<li><a href='test_pdf.php' target='_blank'>Test FPDF Sederhana</a></li>";
echo "<li><a href='test_order_data.php'>Test Data Order</a></li>";
echo "<li><a href='print_order.php?order_id=1' target='_blank'>Test Print Order ID 1</a></li>";
echo "<li><a href='orders.php'>Lihat Daftar Order</a></li>";
echo "</ol>";

echo "<h2>URL Test:</h2>";
echo "<ul>";
echo "<li><code>http://localhost/web/bready/admin/test_pdf.php</code> - Test FPDF</li>";
echo "<li><code>http://localhost/web/bready/admin/print_order.php?order_id=1</code> - Print Order</li>";
echo "<li><code>http://localhost/web/bready/admin/test_order_data.php</code> - Test Data</li>";
echo "</ul>";
?> 