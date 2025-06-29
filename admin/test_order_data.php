<?php
session_start();
require_once '../config/database.php';

// Cek apakah user sudah login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo "Silakan login terlebih dahulu";
    exit;
}

$database = new Database();
$db = $database->getConnection();

echo "<h2>Test Data Order untuk Print PDF</h2>";

// Cek koneksi database
try {
    echo "<p>✅ Koneksi database berhasil</p>";
} catch (Exception $e) {
    echo "<p>❌ Error koneksi database: " . $e->getMessage() . "</p>";
    exit;
}

// Ambil semua order
$stmt = $db->prepare("SELECT id, order_number, customer_name, status, total_amount, created_at FROM orders ORDER BY id DESC LIMIT 5");
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h3>Daftar Order Terbaru:</h3>";
if (count($orders) > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f0f0f0;'>";
    echo "<th>ID</th><th>Order Number</th><th>Customer</th><th>Status</th><th>Total</th><th>Tanggal</th><th>Aksi</th>";
    echo "</tr>";
    
    foreach ($orders as $order) {
        echo "<tr>";
        echo "<td>" . $order['id'] . "</td>";
        echo "<td>" . htmlspecialchars($order['order_number']) . "</td>";
        echo "<td>" . htmlspecialchars($order['customer_name']) . "</td>";
        echo "<td>" . htmlspecialchars($order['status']) . "</td>";
        echo "<td>Rp " . number_format($order['total_amount'], 2) . "</td>";
        echo "<td>" . date('d/m/Y H:i', strtotime($order['created_at'])) . "</td>";
        echo "<td><a href='print_order.php?order_id=" . $order['id'] . "' target='_blank'>Print PDF</a></td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h3>Test Print:</h3>";
    echo "<p>Klik link 'Print PDF' di atas untuk test print order.</p>";
    echo "<p>Atau gunakan URL: <code>print_order.php?order_id=1</code> (ganti 1 dengan ID order yang diinginkan)</p>";
    
} else {
    echo "<p>❌ Tidak ada data order</p>";
    echo "<p>Silakan buat order terlebih dahulu melalui frontend website.</p>";
}

// Cek order items
echo "<h3>Test Order Items:</h3>";
$stmt = $db->prepare("SELECT COUNT(*) as total FROM order_items");
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);
echo "<p>Total order items: " . $result['total'] . "</p>";

// Cek order tracking
echo "<h3>Test Order Tracking:</h3>";
$stmt = $db->prepare("SELECT COUNT(*) as total FROM order_tracking");
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);
echo "<p>Total tracking records: " . $result['total'] . "</p>";

echo "<hr>";
echo "<p><a href='orders.php'>← Kembali ke Daftar Order</a></p>";
echo "<p><a href='test_pdf.php' target='_blank'>Test FPDF Sederhana</a></p>";
?> 