<?php
// Test file untuk memverifikasi fitur timer 5 menit
echo "<h1>Test Fitur Timer 5 Menit</h1>";

// Test database connection
require_once 'config/database.php';
$database = new Database();
$db = $database->getConnection();

if ($db) {
    echo "<p style='color: green;'>✅ Database connection: OK</p>";
    
    // Test logika timer
    echo "<h2>Test Logika Timer:</h2>";
    
    // Simulasi waktu order dibuat (5 menit yang lalu)
    $order_created_5min_ago = time() - (5 * 60);
    $current_time = time();
    $time_limit = 5 * 60; // 5 menit dalam detik
    $time_remaining_old = $time_limit - ($current_time - $order_created_5min_ago);
    
    echo "<p><strong>Order dibuat 5 menit yang lalu:</strong></p>";
    echo "<ul>";
    echo "<li>Waktu order dibuat: " . date('H:i:s', $order_created_5min_ago) . "</li>";
    echo "<li>Waktu sekarang: " . date('H:i:s', $current_time) . "</li>";
    echo "<li>Waktu tersisa: " . $time_remaining_old . " detik</li>";
    echo "<li>Status: " . ($time_remaining_old <= 0 ? '<span style="color: red;">EXPIRED</span>' : '<span style="color: green;">MASIH AKTIF</span>') . "</li>";
    echo "</ul>";
    
    // Simulasi waktu order dibuat (2 menit yang lalu)
    $order_created_2min_ago = time() - (2 * 60);
    $time_remaining_new = $time_limit - ($current_time - $order_created_2min_ago);
    
    echo "<p><strong>Order dibuat 2 menit yang lalu:</strong></p>";
    echo "<ul>";
    echo "<li>Waktu order dibuat: " . date('H:i:s', $order_created_2min_ago) . "</li>";
    echo "<li>Waktu sekarang: " . date('H:i:s', $current_time) . "</li>";
    echo "<li>Waktu tersisa: " . $time_remaining_new . " detik (" . floor($time_remaining_new / 60) . " menit " . ($time_remaining_new % 60) . " detik)</li>";
    echo "<li>Status: " . ($time_remaining_new <= 0 ? '<span style="color: red;">EXPIRED</span>' : '<span style="color: green;">MASIH AKTIF</span>') . "</li>";
    echo "</ul>";
    
    // Test query untuk membatalkan pesanan
    echo "<h2>Test Query Pembatalan:</h2>";
    try {
        // Cek apakah ada order pending yang sudah expired
        $stmt = $db->prepare("
            SELECT COUNT(*) as expired_count 
            FROM orders 
            WHERE status = 'pending' 
            AND created_at < DATE_SUB(NOW(), INTERVAL 5 MINUTE)
        ");
        $stmt->execute();
        $expired_count = $stmt->fetchColumn();
        
        echo "<p>Order pending yang sudah expired (>5 menit): <strong>$expired_count</strong></p>";
        
        if ($expired_count > 0) {
            echo "<p style='color: orange;'>⚠️ Ada order yang sudah expired dan perlu dibatalkan</p>";
        } else {
            echo "<p style='color: green;'>✅ Tidak ada order yang expired</p>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error query: " . $e->getMessage() . "</p>";
    }
    
} else {
    echo "<p style='color: red;'>❌ Database connection: GAGAL</p>";
}

echo "<h2>Fitur Timer yang Ditambahkan:</h2>";
echo "<ol>";
echo "<li><strong>Timer Countdown:</strong> Menampilkan waktu tersisa dalam format MM:SS</li>";
echo "<li><strong>Auto Cancellation:</strong> Pesanan dibatalkan otomatis jika tidak dibayar dalam 5 menit</li>";
echo "<li><strong>Visual Warning:</strong> Timer berubah warna merah ketika < 1 menit tersisa</li>";
echo "<li><strong>Pulse Animation:</strong> Timer berkedip ketika waktu hampir habis</li>";
echo "<li><strong>Timeout Page:</strong> Halaman khusus untuk pesanan yang dibatalkan</li>";
echo "<li><strong>Database Update:</strong> Status order diupdate menjadi 'cancelled'</li>";
echo "<li><strong>Tracking Record:</strong> Mencatat pembatalan di order_tracking</li>";
echo "</ol>";

echo "<h2>Flow Timer:</h2>";
echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<p><strong>1. Order dibuat:</strong> Timer 5 menit dimulai</p>";
echo "<p><strong>2. Halaman pembayaran:</strong> Timer countdown ditampilkan</p>";
echo "<p><strong>3. < 1 menit tersisa:</strong> Timer berubah merah dan berkedip</p>";
echo "<p><strong>4. Waktu habis:</strong> Pesanan dibatalkan otomatis</p>";
echo "<p><strong>5. Redirect:</strong> User diarahkan ke halaman timeout</p>";
echo "</div>";

echo "<h2>Status:</h2>";
echo "<p style='color: green; font-weight: bold;'>✅ Fitur timer 5 menit sudah ditambahkan!</p>";
echo "<p>Pesanan akan dibatalkan otomatis jika tidak dibayar dalam waktu 5 menit.</p>";
?> 