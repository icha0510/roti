<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    echo "<h2>Pemeriksaan Database</h2>";
    
    // Cek tabel cart
    echo "<h3>1. Tabel Cart</h3>";
    $sql = "SHOW TABLES LIKE 'cart'";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $cart_exists = $stmt->fetch();
    
    if ($cart_exists) {
        echo "✓ Tabel cart sudah ada<br>";
    } else {
        echo "✗ Tabel cart belum ada - akan dibuat...<br>";
        
        $sql = "CREATE TABLE IF NOT EXISTS `cart` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `session_id` varchar(255) NOT NULL,
            `product_id` int(11) NOT NULL,
            `quantity` int(11) NOT NULL DEFAULT 1,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `session_id` (`session_id`),
            KEY `product_id` (`product_id`),
            FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        $stmt = $db->prepare($sql);
        if ($stmt->execute()) {
            echo "✓ Tabel cart berhasil dibuat<br>";
        } else {
            echo "✗ Gagal membuat tabel cart<br>";
        }
    }
    
    // Cek tabel order_items
    echo "<h3>2. Tabel Order Items</h3>";
    $sql = "SHOW TABLES LIKE 'order_items'";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $order_items_exists = $stmt->fetch();
    
    if ($order_items_exists) {
        echo "✓ Tabel order_items sudah ada<br>";
    } else {
        echo "✗ Tabel order_items belum ada - akan dibuat...<br>";
        
        $sql = "CREATE TABLE IF NOT EXISTS `order_items` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `order_id` int(11) NOT NULL,
            `product_id` int(11) NOT NULL,
            `product_name` varchar(255) NOT NULL,
            `product_price` decimal(10,2) NOT NULL,
            `quantity` int(11) NOT NULL,
            `subtotal` decimal(10,2) NOT NULL,
            PRIMARY KEY (`id`),
            KEY `order_id` (`order_id`),
            KEY `product_id` (`product_id`),
            FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
            FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        $stmt = $db->prepare($sql);
        if ($stmt->execute()) {
            echo "✓ Tabel order_items berhasil dibuat<br>";
        } else {
            echo "✗ Gagal membuat tabel order_items<br>";
        }
    }
    
    // Cek tabel order_tracking
    echo "<h3>3. Tabel Order Tracking</h3>";
    $sql = "SHOW TABLES LIKE 'order_tracking'";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $order_tracking_exists = $stmt->fetch();
    
    if ($order_tracking_exists) {
        echo "✓ Tabel order_tracking sudah ada<br>";
    } else {
        echo "✗ Tabel order_tracking belum ada - akan dibuat...<br>";
        
        $sql = "CREATE TABLE IF NOT EXISTS `order_tracking` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `order_id` int(11) NOT NULL,
            `status` enum('pending','processing','shipped','delivered','cancelled') NOT NULL,
            `description` text NOT NULL,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `order_id` (`order_id`),
            FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        $stmt = $db->prepare($sql);
        if ($stmt->execute()) {
            echo "✓ Tabel order_tracking berhasil dibuat<br>";
        } else {
            echo "✗ Gagal membuat tabel order_tracking<br>";
        }
    }
    
    // Test fungsi cart
    echo "<h3>4. Test Fungsi Cart</h3>";
    
    // Test session
    if (!session_id()) {
        session_start();
    }
    echo "Session ID: " . session_id() . "<br>";
    
    // Test add to cart
    echo "Testing add to cart...<br>";
    $test_product_id = 1;
    if (addToCart($test_product_id, 1)) {
        echo "✓ Berhasil menambah produk ke cart<br>";
    } else {
        echo "✗ Gagal menambah produk ke cart<br>";
    }
    
    // Test get cart items
    $cart_items = getCartItems();
    echo "Jumlah item di cart: " . count($cart_items) . "<br>";
    
    // Test cart total
    $cart_total = getCartTotal();
    echo "Total cart: " . formatPrice($cart_total) . "<br>";
    
    // Test cart count
    $cart_count = getCartItemCount();
    echo "Jumlah item: " . $cart_count . "<br>";
    
    echo "<h3>5. Struktur Tabel yang Ada</h3>";
    $tables = ['cart', 'orders', 'order_items', 'order_tracking'];
    
    foreach ($tables as $table) {
        $sql = "SHOW TABLES LIKE '$table'";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $exists = $stmt->fetch();
        
        if ($exists) {
            echo "✓ Tabel $table ada<br>";
            
            // Tampilkan struktur tabel
            $sql = "DESCRIBE $table";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<table border='1' style='margin-left: 20px; margin-bottom: 10px;'>";
            echo "<tr><th>Column</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
            foreach ($columns as $column) {
                echo "<tr>";
                echo "<td>" . $column['Field'] . "</td>";
                echo "<td>" . $column['Type'] . "</td>";
                echo "<td>" . $column['Null'] . "</td>";
                echo "<td>" . $column['Key'] . "</td>";
                echo "<td>" . $column['Default'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "✗ Tabel $table tidak ada<br>";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?> 