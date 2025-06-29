<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

// Test koneksi database
try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Test query sederhana
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM orders");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'message' => 'AJAX test berhasil!',
        'data' => [
            'database_connected' => true,
            'total_orders' => $result['total'],
            'timestamp' => date('Y-m-d H:i:s'),
            'method' => $_SERVER['REQUEST_METHOD'],
            'post_data' => $_POST
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage(),
        'data' => [
            'database_connected' => false,
            'timestamp' => date('Y-m-d H:i:s'),
            'method' => $_SERVER['REQUEST_METHOD']
        ]
    ]);
}
?> 