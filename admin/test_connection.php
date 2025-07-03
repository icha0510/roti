<?php
require_once 'config/database.php';

header('Content-Type: application/json');

try {
    $database = new Database();
    $pdo = $database->getConnection();
    
    if (!$pdo) {
        echo json_encode(['error' => 'Database connection failed']);
        exit;
    }
    
    // Test if products table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'products'");
    $tableExists = $stmt->rowCount() > 0;
    
    if (!$tableExists) {
        echo json_encode(['error' => 'Products table does not exist']);
        exit;
    }
    
    // Test if we can fetch products
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM products");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'message' => 'Database connection successful',
        'products_count' => $result['count']
    ]);
    
} catch (Exception $e) {
    echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
}
?> 