<?php
require_once 'config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    echo "<h2>🔧 Menjalankan Script Perbaikan Database</h2>";
    
    // SQL untuk memperbaiki tabel order_tracking
    $sql_commands = [
        "DROP TABLE IF EXISTS `order_tracking`",
        
        "CREATE TABLE `order_tracking` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `order_id` int(11) NOT NULL,
            `status` enum('pending','processing','shipped','delivered','cancelled') NOT NULL,
            `description` text NOT NULL,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `order_id` (`order_id`),
            FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        "INSERT INTO `order_tracking` (`order_id`, `status`, `description`, `created_at`) VALUES
        (1, 'pending', 'Order has been placed successfully', NOW()),
        (2, 'processing', 'Order is being processed', NOW()),
        (3, 'delivered', 'Order has been delivered', NOW())",
        
        "UPDATE orders SET status = 'pending' WHERE status NOT IN ('pending', 'processing', 'shipped', 'delivered', 'cancelled')",
        
        "CREATE INDEX idx_order_tracking_order_id ON order_tracking(order_id)",
        "CREATE INDEX idx_order_tracking_status ON order_tracking(status)",
        "CREATE INDEX idx_order_tracking_created_at ON order_tracking(created_at)"
    ];
    
    foreach ($sql_commands as $index => $sql) {
        echo "<p><strong>Executing command " . ($index + 1) . ":</strong></p>";
        echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 5px; margin: 5px 0;'>" . htmlspecialchars($sql) . "</pre>";
        
        try {
            $stmt = $db->prepare($sql);
            $result = $stmt->execute();
            
            if ($result) {
                echo "<p style='color: green;'>✅ Berhasil dijalankan</p>";
            } else {
                echo "<p style='color: orange;'>⚠️ Command berhasil tapi tidak ada perubahan</p>";
            }
        } catch (PDOException $e) {
            echo "<p style='color: red;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
        
        echo "<hr>";
    }
    
    // Verifikasi hasil
    echo "<h3>🔍 Verifikasi Hasil</h3>";
    
    // Cek tabel order_tracking
    $sql = "SHOW TABLES LIKE 'order_tracking'";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $table_exists = $stmt->fetch();
    
    if ($table_exists) {
        echo "<p style='color: green;'>✅ Tabel order_tracking berhasil dibuat</p>";
        
        // Cek struktur tabel
        $sql = "DESCRIBE order_tracking";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h4>Struktur Tabel order_tracking:</h4>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        foreach ($columns as $column) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($column['Field']) . "</td>";
            echo "<td>" . htmlspecialchars($column['Type']) . "</td>";
            echo "<td>" . htmlspecialchars($column['Null']) . "</td>";
            echo "<td>" . htmlspecialchars($column['Key']) . "</td>";
            echo "<td>" . htmlspecialchars($column['Default']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Cek data sample
        $sql = "SELECT COUNT(*) as count FROM order_tracking";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $count = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "<p><strong>Jumlah data di order_tracking:</strong> " . $count['count'] . "</p>";
        
    } else {
        echo "<p style='color: red;'>❌ Tabel order_tracking gagal dibuat</p>";
    }
    
    // Cek orders
    $sql = "SELECT COUNT(*) as count FROM orders";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $count = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<p><strong>Jumlah data di orders:</strong> " . $count['count'] . "</p>";
    
    echo "<h3>🎉 Selesai!</h3>";
    echo "<p>Database sudah diperbaiki. Sekarang Anda bisa:</p>";
    echo "<ul>";
    echo "<li><a href='admin/fix_order_tracking.php' target='_blank'>Menggunakan file perbaikan untuk update status</a></li>";
    echo "<li><a href='admin/orders.php' target='_blank'>Melihat daftar orders</a></li>";
    echo "<li><a href='admin/order_tracking.php?order_id=1' target='_blank'>Melihat tracking order</a></li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<h2>❌ Error</h2>";
    echo "<p style='color: red;'>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Pastikan:</p>";
    echo "<ul>";
    echo "<li>Database connection berfungsi</li>";
    echo "<li>File config/database.php ada dan benar</li>";
    echo "<li>MySQL server berjalan</li>";
    echo "</ul>";
}
?>

<style>
body {
    font-family: Arial, sans-serif;
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
    background: #f5f5f5;
}
h2, h3, h4 {
    color: #333;
}
pre {
    font-size: 12px;
    overflow-x: auto;
}
table {
    margin: 10px 0;
}
th, td {
    padding: 8px;
    text-align: left;
}
th {
    background: #667eea;
    color: white;
}
tr:nth-child(even) {
    background: #f8f9fa;
}
a {
    color: #667eea;
    text-decoration: none;
}
a:hover {
    text-decoration: underline;
}
</style> 