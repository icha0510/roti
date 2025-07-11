<?php
session_start();
require_once 'config/database.php';

// Set header untuk JSON response
header('Content-Type: application/json');

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Function untuk memproses pembayaran
function processPayment($order_id, $paid_amount, $payment_data) {
    global $db;
    
    try {
        // Ambil data order
        $stmt = $db->prepare("SELECT * FROM orders WHERE id = ? AND status = 'pending'");
        $stmt->execute([$order_id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$order) {
            return array(
                'success' => false,
                'message' => 'Order tidak ditemukan atau sudah diproses'
            );
        }
        
        // Cek apakah pembayaran sesuai
        if ($paid_amount >= $order['total_amount']) {
            $db->beginTransaction();
            
            // Update status order menjadi 'paid'
            $update_stmt = $db->prepare("UPDATE orders SET status = 'paid', updated_at = NOW() WHERE id = ?");
            $update_result = $update_stmt->execute([$order_id]);
            
            if (!$update_result) {
                throw new Exception("Gagal update status order");
            }
            
            // Insert payment record
            $payment_stmt = $db->prepare("
                INSERT INTO payment_transactions (
                    order_id, order_number, amount_paid, payment_method, 
                    payment_data, status, created_at
                ) VALUES (?, ?, ?, ?, ?, 'success', NOW())
            ");
            
            $payment_result = $payment_stmt->execute([
                $order_id,
                $order['order_number'],
                $paid_amount,
                'qris',
                json_encode($payment_data)
            ]);
            
            if (!$payment_result) {
                throw new Exception("Gagal menyimpan data pembayaran");
            }
            
            // Insert tracking record
            $tracking_stmt = $db->prepare("
                INSERT INTO order_tracking (
                    order_id, status, description, created_at
                ) VALUES (?, 'paid', 'Payment received successfully via QRIS', NOW())
            ");
            $tracking_stmt->execute([$order_id]);
            
            // Insert tracking untuk processing
            $processing_stmt = $db->prepare("
                INSERT INTO order_tracking (
                    order_id, status, description, created_at
                ) VALUES (?, 'processing', 'Order is being processed', NOW())
            ");
            $processing_stmt->execute([$order_id]);
            
            // Update status order menjadi processing
            $processing_update = $db->prepare("UPDATE orders SET status = 'processing' WHERE id = ?");
            $processing_update->execute([$order_id]);
            
            $db->commit();
            
            return array(
                'success' => true,
                'message' => 'Pembayaran berhasil diproses',
                'order_number' => $order['order_number'],
                'amount_paid' => $paid_amount,
                'total_amount' => $order['total_amount']
            );
            
        } else {
            // Pembayaran tidak cukup
            $db->beginTransaction();
            
            // Insert failed payment record
            $failed_stmt = $db->prepare("
                INSERT INTO payment_transactions (
                    order_id, order_number, amount_paid, payment_method, 
                    payment_data, status, created_at
                ) VALUES (?, ?, ?, ?, ?, 'failed', NOW())
            ");
            
            $failed_stmt->execute([
                $order_id,
                $order['order_number'],
                $paid_amount,
                'qris',
                json_encode($payment_data)
            ]);
            
                         // Insert tracking record untuk failed payment
             $failed_tracking = $db->prepare("
                 INSERT INTO order_tracking (
                     order_id, status, description, created_at
                 ) VALUES (?, 'failed', ?, NOW())
             ");
             $failed_tracking->execute([$order_id, 'Payment amount insufficient. Expected: ' . $order['total_amount'] . ', Received: ' . $paid_amount]);
            $failed_tracking->execute([$order_id]);
            
            $db->commit();
            
            return array(
                'success' => false,
                'message' => 'Pembayaran tidak cukup. Total tagihan: ' . $order['total_amount'] . ', Dibayar: ' . $paid_amount
            );
        }
        
    } catch (Exception $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        
        return array(
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        );
    }
}

// Handle POST request untuk callback pembayaran
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validasi input
    if (!isset($input['order_id']) || !isset($input['amount_paid'])) {
        echo json_encode(array(
            'success' => false,
            'message' => 'Data pembayaran tidak lengkap'
        ));
        exit;
    }
    
    $order_id = intval($input['order_id']);
    $amount_paid = floatval($input['amount_paid']);
    $payment_data = $input['payment_data'] ?? array();
    
    // Proses pembayaran
    $result = processPayment($order_id, $amount_paid, $payment_data);
    
    echo json_encode($result);
    exit;
}

// Handle GET request untuk test
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo json_encode(array(
        'status' => 'Payment callback endpoint is working',
        'usage' => 'Send POST request with order_id and amount_paid'
    ));
    exit;
}
?> 