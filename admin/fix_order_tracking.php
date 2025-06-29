<?php
session_start();
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Fungsi untuk update status order
function updateOrderStatus($db, $order_id, $status, $description) {
    try {
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
        return true;
        
    } catch (PDOException $e) {
        $db->rollback();
        error_log("Database error: " . $e->getMessage());
        return false;
    }
}

// Proses update status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'])) {
    $order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
    $status = $_POST['status'];
    $description = $_POST['description'] ?? '';
    
    // Validasi status yang diizinkan
    $allowed_statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
    if (!in_array($status, $allowed_statuses)) {
        $error = "Status tidak valid. Status yang diizinkan: " . implode(', ', $allowed_statuses);
    } else {
        if (updateOrderStatus($db, $order_id, $status, $description)) {
            $success = "Status berhasil diupdate!";
        } else {
            $error = "Gagal mengupdate status";
        }
    }
}

// Ambil semua orders untuk ditampilkan
$sql = "SELECT o.*, 
        u.name as user_name, u.email as user_email,
        (SELECT status FROM order_tracking WHERE order_id = o.id ORDER BY created_at DESC LIMIT 1) as last_status,
        (SELECT created_at FROM order_tracking WHERE order_id = o.id ORDER BY created_at DESC LIMIT 1) as last_update
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        ORDER BY o.created_at DESC";
$stmt = $db->prepare($sql);
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fix Order Tracking - Admin</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        } 
        .container { 
            max-width: 1200px; 
            margin: 0 auto; 
            background: #fff; 
            border-radius: 20px; 
            box-shadow: 0 20px 40px rgba(0,0,0,0.1); 
            padding: 40px;
        }
        h1 { 
            text-align: center; 
            color: #333; 
            margin-bottom: 30px;
            font-size: 2.5rem;
        }
        .success-message {
            background: linear-gradient(45deg, #28a745, #20c997);
            color: white;
            padding: 15px 20px;
            border-radius: 10px;
            margin: 20px 0;
            text-align: center;
        }
        .error-message {
            background: linear-gradient(45deg, #dc3545, #e74c3c);
            color: white;
            padding: 15px 20px;
            border-radius: 10px;
            margin: 20px 0;
            text-align: center;
        }
        .order-card {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #667eea;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        .order-number {
            font-size: 1.2rem;
            font-weight: bold;
            color: #667eea;
        }
        .status-badge {
            padding: 8px 16px;
            border-radius: 25px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            color: white;
        }
        .status-pending { background: linear-gradient(45deg, #ffc107, #ff9800); }
        .status-processing { background: linear-gradient(45deg, #17a2b8, #3498db); }
        .status-shipped { background: linear-gradient(45deg, #6f42c1, #8e44ad); }
        .status-delivered { background: linear-gradient(45deg, #28a745, #20c997); }
        .status-cancelled { background: linear-gradient(45deg, #dc3545, #e74c3c); }
        .order-details {
            margin-bottom: 15px;
        }
        .order-details p {
            margin: 5px 0;
            color: #555;
        }
        .update-form {
            background: white;
            padding: 20px;
            border-radius: 10px;
            border: 2px solid #e9ecef;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }
        select, textarea {
            width: 100%;
            padding: 10px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 14px;
        }
        select:focus, textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        button {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            padding: 10px 20px;
            border: 2px solid #667eea;
            border-radius: 25px;
            transition: all 0.3s ease;
        }
        .back-link:hover {
            background: #667eea;
            color: white;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Fix Order Tracking</h1>
        
        <?php if (isset($success)): ?>
            <div class="success-message">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <div style="text-align: center; margin: 20px 0;">
            <p><strong>Total Orders:</strong> <?php echo count($orders); ?></p>
        </div>
        
        <?php foreach ($orders as $order): ?>
            <div class="order-card">
                <div class="order-header">
                    <div class="order-number">#<?php echo htmlspecialchars($order['order_number']); ?></div>
                    <?php
                        $status = $order['last_status'] ?? $order['status'] ?? 'pending';
                        $status_class = 'status-' . $status;
                    ?>
                    <span class="status-badge <?php echo $status_class; ?>"><?php echo ucfirst($status); ?></span>
                </div>
                
                <div class="order-details">
                    <p><strong>Customer:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($order['customer_email']); ?></p>
                    <p><strong>Total:</strong> Rp<?php echo number_format($order['total_amount'], 2); ?></p>
                    <p><strong>Tanggal:</strong> <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></p>
                </div>
                
                <div class="update-form">
                    <form method="post">
                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                        
                        <div class="form-group">
                            <label>Update Status:</label>
                            <select name="status" required>
                                <option value="">-- Pilih Status --</option>
                                <option value="pending" <?php echo ($status == 'pending') ? 'selected' : ''; ?>>Pending</option>
                                <option value="processing" <?php echo ($status == 'processing') ? 'selected' : ''; ?>>Processing</option>
                                <option value="shipped" <?php echo ($status == 'shipped') ? 'selected' : ''; ?>>Shipped</option>
                                <option value="delivered" <?php echo ($status == 'delivered') ? 'selected' : ''; ?>>Delivered</option>
                                <option value="cancelled" <?php echo ($status == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Keterangan:</label>
                            <textarea name="description" rows="3" placeholder="Masukkan keterangan update status..."></textarea>
                        </div>
                        
                        <button type="submit">Update Status</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
        
        <a href="orders.php" class="back-link">← Kembali ke Orders</a>
    </div>
</body>
</html> 