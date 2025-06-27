<?php
require_once 'config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    echo "Mengupdate produk menjadi featured...<br>";
    
    // Update 3 produk pertama menjadi featured
    $sql = "UPDATE products SET is_featured = 1 WHERE id IN (1, 2, 3, 4, 5, 6) LIMIT 6";
    $stmt = $db->prepare($sql);
    $result = $stmt->execute();
    echo "Produk yang diupdate: " . $stmt->rowCount() . "<br>";
    
    // Tampilkan produk yang sudah featured
    $sql = "SELECT id, name, is_featured FROM products WHERE is_featured = 1";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $featured_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Produk Featured:</h3>";
    foreach ($featured_products as $product) {
        echo "ID: " . $product['id'] . " - " . $product['name'] . "<br>";
    }
    
    echo "Selesai!<br>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?> 