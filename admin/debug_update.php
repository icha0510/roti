<?php
session_start();
// Pastikan tidak ada output sebelum header JSON
ob_clean();
header('Content-Type: application/json');

require_once '../config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $order_id = $_POST['order_id'] ?? '';
        $status = $_POST['status'] ?? '';
        
        if (empty($order_id) || empty($status)) {
            echo json_encode([
                'success' => false,
                'message' => 'Order ID dan Status harus diisi!'
            ]);
            exit;
        }
        
        // Validasi status yang diizinkan
        $allowed_statuses = ['pending', 'processing', 'shipped', 'completed', 'cancelled'];
        if (!in_array($status, $allowed_statuses)) {
            echo json_encode([
                'success' => false,
                'message' => 'Status tidak valid!'
            ]);
            exit;
        }
        
        // Update status di tabel orders
        $sql = "UPDATE orders SET status = :status WHERE id = :order_id";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':order_id', $order_id);
        
        if ($stmt->execute()) {
            // Tambahkan tracking record
            $tracking_sql = "INSERT INTO order_tracking (order_id, status, created_at) VALUES (:order_id, :status, NOW())";
            $tracking_stmt = $db->prepare($tracking_sql);
            $tracking_stmt->bindParam(':order_id', $order_id);
            $tracking_stmt->bindParam(':status', $status);
            $tracking_stmt->execute();
            
            echo json_encode([
                'success' => true,
                'message' => 'Status order berhasil diupdate!'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Gagal mengupdate status order!'
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Method tidak diizinkan!'
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?> 