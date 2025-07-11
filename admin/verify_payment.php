<?php
session_start();
require_once '../config/database.php';

// Cek apakah admin sudah login
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$database = new Database();
$db = $database->getConnection();

$message = '';
$error = '';

// Handle payment verification
if ($_POST && isset($_POST['verify_payment'])) {
    $order_id = $_POST['order_id'];
    $payment_status = $_POST['payment_status'];
    
    try {
        $stmt = $db->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $result = $stmt->execute([$payment_status, $order_id]);
        
        if ($result) {
            // Insert tracking record
            $tracking_stmt = $db->prepare("INSERT INTO order_tracking (order_id, status, description, created_at) VALUES (?, ?, ?, NOW())");
            $description = ($payment_status == 'paid') ? 'Payment has been verified and order is being processed' : 'Payment verification failed';
            $tracking_stmt->execute([$order_id, $payment_status, $description]);
            
            $message = "Status pembayaran berhasil diperbarui!";
        } else {
            $error = "Gagal memperbarui status pembayaran.";
        }
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Ambil daftar order yang menunggu pembayaran
$stmt = $db->prepare("
    SELECT o.*, u.name as user_name 
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.id 
    WHERE o.payment_method = 'qris' AND o.status IN ('pending', 'processing')
    ORDER BY o.created_at DESC
");
$stmt->execute();
$pending_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Pembayaran - Admin Panel</title>
    <link href="https://fonts.googleapis.com/css?family=Kaushan+Script%7CLora:400,700" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font-family: 'Lora', serif;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        }
        .admin-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin: 30px auto;
            max-width: 1200px;
        }
        .order-card {
            border: 2px solid #e67e22;
            border-radius: 10px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        .order-card:hover {
            box-shadow: 0 8px 25px rgba(230,126,34,0.2);
            transform: translateY(-2px);
        }
        .status-badge {
            font-size: 12px;
            padding: 5px 10px;
            border-radius: 15px;
            font-weight: 600;
        }
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        .status-paid {
            background: #d4edda;
            color: #155724;
        }
        .btn-verify {
            background: linear-gradient(135deg, #e67e22, #f39c12);
            border: none;
            border-radius: 20px;
            padding: 8px 20px;
            color: white;
            font-weight: 600;
        }
        .btn-verify:hover {
            transform: translateY(-1px);
            box-shadow: 0 5px 15px rgba(230,126,34,0.3);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="admin-container p-4">
            <div class="row">
                <div class="col-12">
                    <h2 class="text-center mb-4" style="color: #2c3e50;">
                        <i class="fas fa-qrcode" style="color: #e67e22; margin-right: 10px;"></i>
                        Verifikasi Pembayaran QRIS
                    </h2>
                    
                    <?php if ($message): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle"></i> <?php echo $message; ?>
                            <button type="button" class="close" data-dismiss="alert">
                                <span>&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
                            <button type="button" class="close" data-dismiss="alert">
                                <span>&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (empty($pending_orders)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-check-circle" style="font-size: 60px; color: #27ae60; margin-bottom: 20px;"></i>
                            <h4 style="color: #7f8c8d;">Tidak ada pesanan yang menunggu verifikasi pembayaran</h4>
                            <p style="color: #95a5a6;">Semua pesanan QRIS sudah diverifikasi</p>
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($pending_orders as $order): ?>
                                <div class="col-md-6 col-lg-4">
                                    <div class="order-card p-3">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <h6 class="mb-0" style="color: #2c3e50; font-weight: 600;">
                                                Order #<?php echo htmlspecialchars($order['order_number']); ?>
                                            </h6>
                                            <span class="status-badge status-<?php echo $order['status']; ?>">
                                                <?php echo ucfirst($order['status']); ?>
                                            </span>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <p class="mb-1"><strong>Customer:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
                                            <p class="mb-1"><strong>Email:</strong> <?php echo htmlspecialchars($order['customer_email']); ?></p>
                                            <p class="mb-1"><strong>Phone:</strong> <?php echo htmlspecialchars($order['customer_phone']); ?></p>
                                            <p class="mb-1"><strong>Meja:</strong> <?php echo htmlspecialchars($order['nomor_meja']); ?></p>
                                            <p class="mb-1"><strong>Total:</strong> Rp <?php echo number_format($order['total_amount'], 3); ?></p>
                                            <p class="mb-1"><strong>Tanggal:</strong> <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></p>
                                        </div>
                                        
                                        <?php if (!empty($order['notes'])): ?>
                                            <div class="mb-3">
                                                <strong>Catatan:</strong>
                                                <p class="mb-0" style="font-size: 14px; color: #6c757d;"><?php echo htmlspecialchars($order['notes']); ?></p>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <form method="POST" class="mt-3">
                                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                            <div class="form-group">
                                                <label for="payment_status_<?php echo $order['id']; ?>">Status Pembayaran:</label>
                                                <select class="form-control" name="payment_status" id="payment_status_<?php echo $order['id']; ?>" required>
                                                    <option value="pending" <?php echo ($order['status'] == 'pending') ? 'selected' : ''; ?>>Menunggu Pembayaran</option>
                                                    <option value="paid">Dibayar</option>
                                                    <option value="failed">Gagal</option>
                                                </select>
                                            </div>
                                            <button type="submit" name="verify_payment" class="btn btn-verify btn-block">
                                                <i class="fas fa-check" style="margin-right: 5px;"></i>
                                                Verifikasi Pembayaran
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="text-center mt-4">
                        <a href="dashboard.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left" style="margin-right: 5px;"></i>
                            Kembali ke Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html> 