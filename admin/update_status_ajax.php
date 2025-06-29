<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
$status = isset($_POST['status']) ? $_POST['status'] : '';
$description = isset($_POST['description']) ? $_POST['description'] : '';

if (!$order_id || !$status) {
    echo json_encode(['success' => false, 'message' => 'Order ID dan Status harus diisi']);
    exit;
}

// Validasi status yang diizinkan
$allowed_statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
if (!in_array($status, $allowed_statuses)) {
    echo json_encode(['success' => false, 'message' => 'Status tidak valid']);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    $db->beginTransaction();
    
    // Update status di tabel orders
    $stmt = $db->prepare("UPDATE orders SET status = :status WHERE id = :order_id");
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':order_id', $order_id);
    $stmt->execute();
    
    // Insert ke tabel order_tracking
    $stmt = $db->prepare("INSERT INTO order_tracking (order_id, status, description, created_at) VALUES (:order_id, :status, :description, NOW())");
    $stmt->bindParam(':order_id', $order_id);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':description', $description);
    $stmt->execute();
    
    $db->commit();
    
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
    
    echo json_encode([
        'success' => true, 
        'message' => 'Status berhasil diupdate!',
        'order' => $order
    ]);
    
} catch (PDOException $e) {
    $db->rollback();
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?> 