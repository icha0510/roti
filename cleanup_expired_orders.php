<?php
/**
 * Script untuk membersihkan order yang sudah expired (lebih dari 5 menit)
 * Script ini bisa dijalankan secara manual atau via cron job
 */

require_once 'config/database.php';

echo "<h1>Cleanup Expired Orders</h1>";
echo "<p>Menjalankan cleanup untuk order yang sudah expired (>5 menit)...</p>";

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Cari order yang sudah expired
    $stmt = $db->prepare("
        SELECT id, order_number, customer_name, total_amount, created_at 
        FROM orders 
        WHERE status = 'pending' 
        AND created_at < DATE_SUB(NOW(), INTERVAL 5 MINUTE)
    ");
    $stmt->execute();
    $expired_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $expired_count = count($expired_orders);
    
    if ($expired_count > 0) {
        echo "<p style='color: orange;'>⚠️ Ditemukan <strong>$expired_count</strong> order yang sudah expired</p>";
        
        // Mulai transaction
        $db->beginTransaction();
        
        $cancelled_count = 0;
        
        foreach ($expired_orders as $order) {
            try {
                // Update status order menjadi cancelled
                $stmt = $db->prepare("UPDATE orders SET status = 'cancelled', updated_at = NOW() WHERE id = ?");
                $stmt->execute([$order['id']]);
                
                // Insert tracking untuk pembatalan
                $stmt = $db->prepare("
                    INSERT INTO order_tracking (order_id, status, description, created_at) 
                    VALUES (?, 'cancelled', 'Order cancelled due to payment timeout (5 minutes) - Auto cleanup', NOW())
                ");
                $stmt->execute([$order['id']]);
                
                $cancelled_count++;
                
                echo "<p style='color: red;'>❌ Order <strong>{$order['order_number']}</strong> dibatalkan (Expired: " . date('H:i:s', strtotime($order['created_at'])) . ")</p>";
                
            } catch (Exception $e) {
                echo "<p style='color: red;'>❌ Error membatalkan order {$order['order_number']}: " . $e->getMessage() . "</p>";
            }
        }
        
        // Commit transaction
        $db->commit();
        
        echo "<p style='color: green;'>✅ Berhasil membatalkan <strong>$cancelled_count</strong> order yang expired</p>";
        
    } else {
        echo "<p style='color: green;'>✅ Tidak ada order yang expired</p>";
    }
    
    // Tampilkan statistik
    echo "<h2>Statistik Order:</h2>";
    
    $stmt = $db->prepare("SELECT status, COUNT(*) as count FROM orders GROUP BY status");
    $stmt->execute();
    $stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 20px 0;'>";
    echo "<tr style='background: #f8f9fa;'><th>Status</th><th>Jumlah</th></tr>";
    
    foreach ($stats as $stat) {
        $color = '';
        switch ($stat['status']) {
            case 'pending':
                $color = 'orange';
                break;
            case 'paid':
                $color = 'green';
                break;
            case 'cancelled':
                $color = 'red';
                break;
            default:
                $color = 'black';
        }
        
        echo "<tr>";
        echo "<td style='color: $color; font-weight: bold;'>" . ucfirst($stat['status']) . "</td>";
        echo "<td style='text-align: center;'>" . $stat['count'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Tampilkan order pending yang masih aktif
    $stmt = $db->prepare("
        SELECT order_number, customer_name, total_amount, created_at,
               TIMESTAMPDIFF(SECOND, created_at, NOW()) as seconds_ago
        FROM orders 
        WHERE status = 'pending' 
        ORDER BY created_at DESC
    ");
    $stmt->execute();
    $pending_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($pending_orders) > 0) {
        echo "<h2>Order Pending yang Masih Aktif:</h2>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 20px 0;'>";
        echo "<tr style='background: #f8f9fa;'><th>Order Number</th><th>Customer</th><th>Total</th><th>Created</th><th>Time Ago</th><th>Time Left</th></tr>";
        
        foreach ($pending_orders as $order) {
            $time_left = 300 - $order['seconds_ago']; // 5 menit = 300 detik
            $minutes_left = floor($time_left / 60);
            $seconds_left = $time_left % 60;
            
            $time_left_str = $time_left > 0 ? sprintf('%02d:%02d', $minutes_left, $seconds_left) : 'EXPIRED';
            $row_color = $time_left <= 60 ? 'background: #ffe6e6;' : '';
            
            echo "<tr style='$row_color'>";
            echo "<td>" . $order['order_number'] . "</td>";
            echo "<td>" . $order['customer_name'] . "</td>";
            echo "<td>Rp " . number_format($order['total_amount'], 0, ',', '.') . "</td>";
            echo "<td>" . date('H:i:s', strtotime($order['created_at'])) . "</td>";
            echo "<td>" . floor($order['seconds_ago'] / 60) . "m " . ($order['seconds_ago'] % 60) . "s ago</td>";
            echo "<td style='font-weight: bold; color: " . ($time_left <= 60 ? 'red' : 'green') . ";'>" . $time_left_str . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
        echo "<p style='color: red;'>❌ Transaction rolled back</p>";
    }
}

echo "<hr>";
echo "<p><strong>Script selesai dijalankan pada:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><strong>Tips:</strong> Jalankan script ini secara berkala (misal setiap 1 menit) untuk membersihkan order yang expired</p>";
echo "<p><strong>Cron job example:</strong> */1 * * * * php /path/to/cleanup_expired_orders.php</p>";
?> 