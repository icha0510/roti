<?php
// Prevent any output before JSON response
ob_start();

require_once 'auth_check.php';
require_once 'config/database.php';

// Clear any output buffer
ob_clean();

// Set content type to JSON
header('Content-Type: application/json');

// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid product ID']);
    exit;
}

$product_id = (int)$_GET['id'];

try {
    // Use the Database class to get connection
    $database = new Database();
    $pdo = $database->getConnection();
    
    if (!$pdo) {
        throw new Exception('Database connection failed');
    }
    
    // Fetch product data
    $stmt = $pdo->prepare("SELECT id, name, description, price, sale_price, category_id, stock, rating, is_featured, is_new, is_sale, image FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        http_response_code(404);
        echo json_encode(['error' => 'Product not found']);
        exit;
    }
    
    // Return product data as JSON
    echo json_encode($product);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?> 