<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'config/database.php';

// Ambil parameter dari URL
$order_id = isset($_GET['order_id']) ? $_GET['order_id'] : '';
$order_number = isset($_GET['order_number']) ? $_GET['order_number'] : '';
$total_amount = isset($_GET['total_amount']) ? $_GET['total_amount'] : '';
$customer_name = isset($_GET['customer_name']) ? $_GET['customer_name'] : '';

// Validasi parameter
if (empty($order_id) || empty($order_number) || empty($total_amount)) {
    header('Location: index.php');
    exit;
}

// Ambil data order dari database
$database = new Database();
$db = $database->getConnection();

$stmt = $db->prepare("SELECT * FROM orders WHERE id = ? AND order_number = ?");
$stmt->execute([$order_id, $order_number]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    header('Location: index.php');
    exit;
}

// Ambil detail order items
$stmt = $db->prepare("SELECT * FROM order_items WHERE order_id = ?");
$stmt->execute([$order_id]);
$order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle payment submission
$payment_success = false;
$payment_error = '';

if ($_POST && isset($_POST['process_payment'])) {
    $payment_amount = floatval($_POST['payment_amount']);
    $payment_method = $_POST['payment_method'];
    
    // Validasi jumlah pembayaran
    if ($payment_amount < $order['total_amount']) {
        $payment_error = "Jumlah pembayaran kurang dari total tagihan (Rp " . number_format($order['total_amount'], 0, ',', '.') . ")";
    } else {
        try {
            $db->beginTransaction();
            
            // Update status order
            $stmt = $db->prepare("UPDATE orders SET status = 'paid', updated_at = NOW() WHERE id = ?");
            $stmt->execute([$order_id]);
            
            // Simpan transaksi pembayaran
            $stmt = $db->prepare("
                INSERT INTO payment_transactions (
                    order_id, payment_method, amount_paid, payment_date, status, created_at
                ) VALUES (?, ?, ?, NOW(), 'completed', NOW())
            ");
            $stmt->execute([$order_id, $payment_method, $payment_amount]);
            
            // Update tracking
            $stmt = $db->prepare("
                INSERT INTO order_tracking (order_id, status, description, created_at) 
                VALUES (?, 'paid', 'Payment completed successfully', NOW())
            ");
            $stmt->execute([$order_id]);
            
            $db->commit();
            $payment_success = true;
            
            // Redirect ke halaman sukses setelah 3 detik
            header("refresh:3;url=order-success.php?order_id=" . $order_id);
            
        } catch (Exception $e) {
            $db->rollBack();
            $payment_error = "Gagal memproses pembayaran: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran - Roti'O</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .payment-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .payment-header {
            background: linear-gradient(135deg, #e67e22, #f39c12);
            color: white;
            padding: 30px;
            text-align: center;
            position: relative;
        }
        
        .payment-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="white" opacity="0.1"/><circle cx="10" cy="60" r="0.5" fill="white" opacity="0.1"/><circle cx="90" cy="40" r="0.5" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
        }
        
        .payment-header h1 {
            font-size: 2.5em;
            font-weight: 700;
            margin-bottom: 10px;
            position: relative;
            z-index: 1;
        }
        
        .payment-header p {
            font-size: 1.1em;
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }
        
        .payment-content {
            padding: 40px;
        }
        
        .order-summary {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            border-left: 5px solid #e67e22;
        }
        
        .order-summary h3 {
            color: #2c3e50;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .order-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .info-item {
            background: white;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .info-item label {
            font-weight: 600;
            color: #7f8c8d;
            font-size: 0.9em;
            display: block;
            margin-bottom: 5px;
        }
        
        .info-item span {
            color: #2c3e50;
            font-weight: 500;
        }
        
        .order-items {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            border-bottom: 1px solid #ecf0f1;
        }
        
        .item:last-child {
            border-bottom: none;
        }
        
        .item-name {
            font-weight: 500;
            color: #2c3e50;
        }
        
        .item-quantity {
            color: #7f8c8d;
            font-size: 0.9em;
        }
        
        .item-price {
            font-weight: 600;
            color: #e67e22;
        }
        
        .total-section {
            background: linear-gradient(135deg, #2c3e50, #34495e);
            color: white;
            padding: 25px;
            border-radius: 15px;
            text-align: center;
            margin-bottom: 30px;
        }
        
        .total-section h2 {
            font-size: 2em;
            margin-bottom: 10px;
        }
        
        .total-amount {
            font-size: 3em;
            font-weight: 700;
            color: #f39c12;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .payment-form {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
        }
        
        .payment-form h3 {
            color: #2c3e50;
            margin-bottom: 25px;
            text-align: center;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .form-control {
            width: 100%;
            padding: 15px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #e67e22;
            box-shadow: 0 0 0 3px rgba(230, 126, 34, 0.1);
        }
        
        .payment-methods {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .payment-method {
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .payment-method:hover {
            border-color: #e67e22;
            transform: translateY(-2px);
        }
        
        .payment-method.selected {
            border-color: #e67e22;
            background: #fff3e0;
        }
        
        .payment-method i {
            font-size: 2em;
            margin-bottom: 10px;
            display: block;
        }
        
        .payment-method.cash i {
            color: #27ae60;
        }
        
        .payment-method.qris i {
            color: #e74c3c;
        }
        
        .btn-pay {
            width: 100%;
            background: linear-gradient(135deg, #e67e22, #f39c12);
            color: white;
            border: none;
            padding: 18px;
            border-radius: 10px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .btn-pay:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(230, 126, 34, 0.3);
        }
        
        .btn-pay:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .footer-actions {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-1px);
        }
        
        .loading {
            display: none;
            text-align: center;
            padding: 20px;
        }
        
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #e67e22;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 15px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        @media (max-width: 768px) {
            .payment-content {
                padding: 20px;
            }
            
            .payment-header h1 {
                font-size: 2em;
            }
            
            .total-amount {
                font-size: 2.5em;
            }
            
            .order-info {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="payment-container">
        <div class="payment-header">
            <h1><i class="fas fa-credit-card"></i> Pembayaran</h1>
            <p>Lengkapi pembayaran untuk pesanan Anda</p>
        </div>
        
        <div class="payment-content">
            <?php if ($payment_success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    Pembayaran berhasil! Anda akan dialihkan ke halaman sukses dalam beberapa detik.
                </div>
            <?php endif; ?>
            
            <?php if ($payment_error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php echo htmlspecialchars($payment_error); ?>
                </div>
            <?php endif; ?>
            
            <div class="order-summary">
                <h3><i class="fas fa-receipt"></i> Ringkasan Pesanan</h3>
                
                <div class="order-info">
                    <div class="info-item">
                        <label>Nomor Pesanan</label>
                        <span><?php echo htmlspecialchars($order['order_number']); ?></span>
                    </div>
                    <div class="info-item">
                        <label>Nama Pelanggan</label>
                        <span><?php echo htmlspecialchars($order['customer_name']); ?></span>
                    </div>
                    <div class="info-item">
                        <label>Nomor Meja</label>
                        <span><?php echo htmlspecialchars($order['nomor_meja']); ?></span>
                    </div>
                    <div class="info-item">
                        <label>Tanggal Pesanan</label>
                        <span><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></span>
                    </div>
                </div>
                
                <div class="order-items">
                    <?php foreach ($order_items as $item): ?>
                        <div class="item">
                            <div>
                                <div class="item-name"><?php echo htmlspecialchars($item['product_name']); ?></div>
                                <div class="item-quantity">Jumlah: <?php echo $item['quantity']; ?></div>
                            </div>
                            <div class="item-price">
                                Rp <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="total-section">
                <h2>Total Pembayaran</h2>
                <div class="total-amount">
                    Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?>
                </div>
            </div>
            
            <?php if (!$payment_success): ?>
                <form method="POST" class="payment-form" id="paymentForm">
                    <h3><i class="fas fa-credit-card"></i> Pilih Metode Pembayaran</h3>
                    
                    <div class="form-group">
                        <label>Metode Pembayaran</label>
                        <div class="payment-methods">
                            <div class="payment-method" data-method="cash">
                                <i class="fas fa-money-bill-wave"></i>
                                <div>Cash</div>
                            </div>
                            <div class="payment-method" data-method="qris">
                                <i class="fas fa-qrcode"></i>
                                <div>QRIS</div>
                            </div>
                        </div>
                        <input type="hidden" name="payment_method" id="payment_method" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="payment_amount">Jumlah Pembayaran</label>
                        <input type="number" 
                               class="form-control" 
                               id="payment_amount" 
                               name="payment_amount" 
                               min="<?php echo $order['total_amount']; ?>" 
                               step="1000" 
                               value="<?php echo $order['total_amount']; ?>" 
                               required>
                        <small style="color: #7f8c8d; margin-top: 5px; display: block;">
                            Minimal pembayaran: Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?>
                        </small>
                    </div>
                    
                    <button type="submit" name="process_payment" class="btn-pay" id="payButton">
                        <i class="fas fa-lock"></i> Proses Pembayaran
                    </button>
                </form>
                
                <div class="loading" id="loading">
                    <div class="spinner"></div>
                    <p>Memproses pembayaran...</p>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="footer-actions">
            <a href="index.php" class="btn-secondary">
                <i class="fas fa-home"></i> Kembali ke Beranda
            </a>
        </div>
    </div>
    
    <script>
        // Payment method selection
        document.querySelectorAll('.payment-method').forEach(method => {
            method.addEventListener('click', function() {
                // Remove selected class from all methods
                document.querySelectorAll('.payment-method').forEach(m => m.classList.remove('selected'));
                
                // Add selected class to clicked method
                this.classList.add('selected');
                
                // Update hidden input
                document.getElementById('payment_method').value = this.dataset.method;
            });
        });
        
        // Form submission
        document.getElementById('paymentForm').addEventListener('submit', function(e) {
            const paymentMethod = document.getElementById('payment_method').value;
            const paymentAmount = document.getElementById('payment_amount').value;
            
            if (!paymentMethod) {
                e.preventDefault();
                alert('Silakan pilih metode pembayaran');
                return;
            }
            
            if (!paymentAmount || paymentAmount < <?php echo $order['total_amount']; ?>) {
                e.preventDefault();
                alert('Jumlah pembayaran tidak valid');
                return;
            }
            
            // Show loading
            document.getElementById('loading').style.display = 'block';
            document.getElementById('payButton').disabled = true;
        });
        
        // Auto-select first payment method
        document.addEventListener('DOMContentLoaded', function() {
            const firstMethod = document.querySelector('.payment-method');
            if (firstMethod) {
                firstMethod.click();
            }
        });
    </script>
</body>
</html> 