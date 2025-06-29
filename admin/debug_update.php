<?php
session_start();
require_once '../config/database.php';

// Debug: Log semua request
error_log("DEBUG: Request received - " . print_r($_REQUEST, true));

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
$status = isset($_POST['status']) ? $_POST['status'] : '';
$description = isset($_POST['description']) ? $_POST['description'] : '';

error_log("DEBUG: Order ID = $order_id, Status = $status, Description = $description");

if (!$order_id || !$status) {
    echo json_encode(['success' => false, 'message' => 'Order ID dan Status harus diisi']);
    exit;
}

// Validasi status yang diizinkan
$allowed_statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
if (!in_array($status, $allowed_statuses)) {
    echo json_encode(['success' => false, 'message' => 'Status tidak valid: ' . $status]);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    error_log("DEBUG: Database connected successfully");
    
    $db->beginTransaction();
    
    // Update status di tabel orders
    $stmt = $db->prepare("UPDATE orders SET status = :status WHERE id = :order_id");
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':order_id', $order_id);
    $result1 = $stmt->execute();
    
    error_log("DEBUG: Orders update result = " . ($result1 ? 'success' : 'failed'));
    
    // Insert ke tabel order_tracking
    $stmt = $db->prepare("INSERT INTO order_tracking (order_id, status, description, created_at) VALUES (:order_id, :status, :description, NOW())");
    $stmt->bindParam(':order_id', $order_id);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':description', $description);
    $result2 = $stmt->execute();
    
    error_log("DEBUG: Order tracking insert result = " . ($result2 ? 'success' : 'failed'));
    
    $db->commit();
    
    error_log("DEBUG: Transaction committed successfully");
    
    // Ambil data order yang sudah diupdate
    $sql = "SELECT o.*, 
            u.name as user_name, u.email as user_email,
            (SELECT status FROM order_tracking WHERE order_id = o.id ORDER BY created_at DESC LIMIT 1) as last_status,
            (SELECT created_at FROM order_tracking WHERE order_id = o.id ORDER BY created_at DESC LIMIT 1) as last_update
            FROM orders o
            LEFT JOIN users u ON o.user_id = u.id
            WHERE o.id = :order_id";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':order_id', $order_id);
    $stmt->execute();
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    error_log("DEBUG: Updated order data = " . print_r($order, true));
    
    echo json_encode([
        'success' => true, 
        'message' => 'Status berhasil diupdate!',
        'order' => $order,
        'debug' => [
            'order_id' => $order_id,
            'status' => $status,
            'description' => $description,
            'orders_updated' => $result1,
            'tracking_inserted' => $result2
        ]
    ]);
    
} catch (PDOException $e) {
    $db->rollback();
    error_log("DEBUG: Database error = " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    error_log("DEBUG: General error = " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?> 