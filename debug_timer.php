<?php
// Debug file untuk melihat nilai time_remaining
require_once 'config/database.php';

echo "<h1>Debug Timer - Nilai Time Remaining</h1>";

// Ambil parameter dari URL
$order_id = isset($_GET['order_id']) ? $_GET['order_id'] : '';
$order_number = isset($_GET['order_number']) ? $_GET['order_number'] : '';

if (empty($order_id) || empty($order_number)) {
    echo "<p style='color: red;'>❌ Parameter order_id dan order_number diperlukan</p>";
    echo "<p>Contoh: debug_timer.php?order_id=1&order_number=ORD-2025-XXXX</p>";
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    $stmt = $db->prepare("SELECT * FROM orders WHERE id = ? AND order_number = ?");
    $stmt->execute([$order_id, $order_number]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        echo "<p style='color: red;'>❌ Order tidak ditemukan</p>";
        exit;
    }
    
    echo "<h2>Detail Order:</h2>";
    echo "<ul>";
    echo "<li><strong>Order ID:</strong> " . $order['id'] . "</li>";
    echo "<li><strong>Order Number:</strong> " . $order['order_number'] . "</li>";
    echo "<li><strong>Status:</strong> " . $order['status'] . "</li>";
    echo "<li><strong>Created At:</strong> " . $order['created_at'] . "</li>";
    echo "</ul>";
    
    // Hitung time remaining
    $order_created = strtotime($order['created_at']);
    $current_time = time();
    $time_limit = 5 * 60; // 5 menit dalam detik
    $time_remaining = $time_limit - ($current_time - $order_created);
    
    echo "<h2>Perhitungan Timer:</h2>";
    echo "<ul>";
    echo "<li><strong>Order Created (timestamp):</strong> " . $order_created . "</li>";
    echo "<li><strong>Current Time (timestamp):</strong> " . $current_time . "</li>";
    echo "<li><strong>Time Limit:</strong> " . $time_limit . " detik (5 menit)</li>";
    echo "<li><strong>Time Remaining:</strong> " . $time_remaining . " detik</li>";
    echo "</ul>";
    
    // Konversi ke menit dan detik
    $minutes = floor($time_remaining / 60);
    $seconds = $time_remaining % 60;
    
    echo "<h2>Format Timer:</h2>";
    echo "<ul>";
    echo "<li><strong>Menit:</strong> " . $minutes . "</li>";
    echo "<li><strong>Detik:</strong> " . $seconds . "</li>";
    echo "<li><strong>Format MM:SS:</strong> " . sprintf('%02d:%02d', $minutes, $seconds) . "</li>";
    echo "</ul>";
    
    // Tampilkan timer seperti di halaman asli
    echo "<h2>Simulasi Tampilan Timer:</h2>";
    echo "<div style='background: linear-gradient(135deg, #ff6b6b, #ee5a24); color: white; border-radius: 15px; padding: 20px; text-align: center; margin: 20px 0; max-width: 400px;'>";
    echo "<h4 style='margin-bottom: 15px;'>";
    echo "<i class='fas fa-clock' style='margin-right: 10px;'></i>";
    echo "Waktu Tersisa untuk Pembayaran";
    echo "</h4>";
    
    echo "<div style='font-size: 2.5em; font-weight: bold; margin-bottom: 10px;'>";
    if ($time_remaining > 0) {
        echo '<span style="display: inline-block; margin: 0 5px;">';
        echo '<span style="font-size: 0.6em; display: block; opacity: 0.8;">Menit</span>';
        echo '<span>' . sprintf('%02d', $minutes) . '</span>';
        echo '</span>';
        echo '<span style="font-size: 0.8em; margin: 0 5px;">:</span>';
        echo '<span style="display: inline-block; margin: 0 5px;">';
        echo '<span style="font-size: 0.6em; display: block; opacity: 0.8;">Detik</span>';
        echo '<span>' . sprintf('%02d', $seconds) . '</span>';
        echo '</span>';
    } else {
        echo '<span style="display: inline-block; margin: 0 5px;">';
        echo '<span style="font-size: 0.6em; display: block; opacity: 0.8;">Menit</span>';
        echo '<span>00</span>';
        echo '</span>';
        echo '<span style="font-size: 0.8em; margin: 0 5px;">:</span>';
        echo '<span style="display: inline-block; margin: 0 5px;">';
        echo '<span style="font-size: 0.6em; display: block; opacity: 0.8;">Detik</span>';
        echo '<span>00</span>';
        echo '</span>';
    }
    echo "</div>";
    
    echo "<p style='margin: 0; font-size: 14px; opacity: 0.9;'>";
    echo "Pesanan akan dibatalkan otomatis jika tidak dibayar dalam waktu 5 menit";
    echo "</p>";
    echo "</div>";
    
    // Debug tambahan
    echo "<h2>Debug Tambahan:</h2>";
    echo "<ul>";
    echo "<li><strong>Order Created (readable):</strong> " . date('Y-m-d H:i:s', $order_created) . "</li>";
    echo "<li><strong>Current Time (readable):</strong> " . date('Y-m-d H:i:s', $current_time) . "</li>";
    echo "<li><strong>Time Difference:</strong> " . ($current_time - $order_created) . " detik</li>";
    echo "<li><strong>Status Timer:</strong> " . ($time_remaining > 0 ? 'AKTIF' : 'EXPIRED') . "</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?> 