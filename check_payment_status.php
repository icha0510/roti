<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'config/database.php';

// Set header untuk JSON response
header('Content-Type: application/json');

// Validasi request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit;
}

// Validasi parameter
$order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
$order_number = isset($_POST['order_number']) ? $_POST['order_number'] : '';

if (empty($order_id) || empty($order_number)) {
    echo json_encode([
        'success' => false,
        'message' => 'Order ID dan Order Number diperlukan'
    ]);
    exit;
}

try {
    // Initialize database connection
    $database = new Database();
    $db = $database->getConnection();
    
    // Ambil status order terbaru
    $stmt = $db->prepare("SELECT status, updated_at FROM orders WHERE id = ? AND order_number = ?");
    $stmt->execute([$order_id, $order_number]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        echo json_encode([
            'success' => false,
            'message' => 'Order tidak ditemukan'
        ]);
        exit;
    }
    
    // Cek apakah ada transaksi pembayaran yang berhasil
    $stmt = $db->prepare("
        SELECT status, created_at 
        FROM payment_transactions 
        WHERE order_id = ? AND status = 'success' 
        ORDER BY created_at DESC 
        LIMIT 1
    ");
    $stmt->execute([$order_id]);
    $payment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Cek tracking terbaru
    $stmt = $db->prepare("
        SELECT status, description, created_at 
        FROM order_tracking 
        WHERE order_id = ? 
        ORDER BY created_at DESC 
        LIMIT 1
    ");
    $stmt->execute([$order_id]);
    $tracking = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $response = [
        'success' => true,
        'order_status' => $order['status'],
        'payment_status' => $payment ? $payment['status'] : null,
        'tracking_status' => $tracking ? $tracking['status'] : null,
        'tracking_description' => $tracking ? $tracking['description'] : null,
        'last_updated' => $order['updated_at']
    ];
    
    // Tentukan status pembayaran berdasarkan kondisi
    if ($order['status'] === 'paid' || $order['status'] === 'processing') {
        $response['status'] = 'paid';
        $response['message'] = 'Pembayaran berhasil diproses';
    } elseif ($order['status'] === 'cancelled') {
        $response['status'] = 'cancelled';
        $response['message'] = 'Pesanan telah dibatalkan';
    } else {
        $response['status'] = 'pending';
        $response['message'] = 'Menunggu pembayaran';
    }
    
    echo json_encode($response);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?> 