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
$auto_pay = isset($_GET['auto_pay']) ? $_GET['auto_pay'] : '';

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

// Cek apakah pesanan sudah expired (5 menit dari created_at)
$order_created = strtotime($order['created_at']);
$current_time = time();
$time_limit = 5 * 60; // 5 menit dalam detik
$time_remaining = $time_limit - ($current_time - $order_created);

// Pastikan time_remaining tidak negatif dan tidak lebih dari 5 menit
if ($time_remaining < 0) {
    $time_remaining = 0;
} elseif ($time_remaining > $time_limit) {
    $time_remaining = $time_limit;
}

// Jika waktu sudah habis dan status masih pending, batalkan pesanan
if ($time_remaining <= 0 && $order['status'] === 'pending') {
    try {
        $db->beginTransaction();
        
        // Update status order menjadi cancelled
        $stmt = $db->prepare("UPDATE orders SET status = 'cancelled', updated_at = NOW() WHERE id = ?");
        $stmt->execute([$order_id]);
        
        // Insert tracking untuk pembatalan
        $stmt = $db->prepare("
            INSERT INTO order_tracking (order_id, status, description, created_at) 
            VALUES (?, 'cancelled', 'Order cancelled due to payment timeout (5 minutes)', NOW())
        ");
        $stmt->execute([$order_id]);
        
        $db->commit();
        
        // Redirect ke halaman timeout
        header('Location: order-timeout.php?order_id=' . $order_id . '&order_number=' . $order_number);
        exit;
        
    } catch (Exception $e) {
        $db->rollBack();
        $timeout_error = "Gagal membatalkan pesanan: " . $e->getMessage();
    }
}

// Ambil detail order items
$stmt = $db->prepare("SELECT * FROM order_items WHERE order_id = ?");
$stmt->execute([$order_id]);
$order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle auto payment jika user mengakses QR code
if ($auto_pay === 'true' && $order['status'] === 'pending') {
    try {
        $db->beginTransaction();
        
        // Update status order
        $stmt = $db->prepare("UPDATE orders SET status = 'paid', updated_at = NOW() WHERE id = ?");
        $stmt->execute([$order_id]);
        
        // Simpan transaksi pembayaran
        $stmt = $db->prepare("
            INSERT INTO payment_transactions (
                order_id, order_number, amount_paid, payment_method, status, created_at
            ) VALUES (?, ?, ?, 'qris', 'success', NOW())
        ");
        $stmt->execute([$order_id, $order_number, $order['total_amount']]);
        
        // Update tracking
        $stmt = $db->prepare("
            INSERT INTO order_tracking (order_id, status, description, created_at) 
            VALUES (?, 'paid', 'Payment completed via QRIS scan - Auto Payment', NOW())
        ");
        $stmt->execute([$order_id]);
        
        // Update tracking untuk processing
        $stmt = $db->prepare("
            INSERT INTO order_tracking (order_id, status, description, created_at) 
            VALUES (?, 'processing', 'Order is being processed after successful payment', NOW())
        ");
        $stmt->execute([$order_id]);
        
        // Update status order menjadi processing
        $stmt = $db->prepare("UPDATE orders SET status = 'processing' WHERE id = ?");
        $stmt->execute([$order_id]);
        
        $db->commit();
        
        // Set session data untuk order success
        $_SESSION['order_success'] = [
            'order_id' => $order_id,
            'order_number' => $order_number,
            'customer_name' => $customer_name,
            'total_amount' => $total_amount,
            'nomor_meja' => $order['nomor_meja']
        ];
        $_SESSION['payment_method'] = 'qris';
        $_SESSION['auto_payment_success'] = true;
        
        // Redirect ke halaman sukses
        header('Location: order-success.php?from_qr_payment=true&auto_payment=true');
        exit;
        
    } catch (Exception $e) {
        $db->rollBack();
        $auto_payment_error = "Gagal memproses pembayaran otomatis: " . $e->getMessage();
    }
}

// Handle payment submission manual
$payment_success = false;
$payment_error = '';

if ($_POST && isset($_POST['process_payment'])) {
    $payment_amount = floatval($_POST['payment_amount']);
    $payment_method = $_POST['payment_method'];
    
    // Validasi jumlah pembayaran
    if ($payment_amount < $order['total_amount']) {
        $payment_error = "Jumlah pembayaran kurang dari total tagihan (Rp " . number_format($order['total_amount'], 3, ',', '.') . ")";
    } else {
        try {
            $db->beginTransaction();
            
            // Update status order
            $stmt = $db->prepare("UPDATE orders SET status = 'paid', updated_at = NOW() WHERE id = ?");
            $stmt->execute([$order_id]);
            
            // Simpan transaksi pembayaran
            $stmt = $db->prepare("
                INSERT INTO payment_transactions (
                    order_id, order_number, amount_paid, payment_method, status, created_at
                ) VALUES (?, ?, ?, ?, 'success', NOW())
            ");
            $stmt->execute([$order_id, $order_number, $payment_amount, $payment_method]);
            
            // Update tracking
            $stmt = $db->prepare("
                INSERT INTO order_tracking (order_id, status, description, created_at) 
                VALUES (?, 'paid', 'Payment completed successfully', NOW())
            ");
            $stmt->execute([$order_id]);
            
            $db->commit();
            $payment_success = true;
            
            // Set session data untuk order success
            $_SESSION['order_success'] = [
                'order_id' => $order_id,
                'order_number' => $order_number,
                'customer_name' => $customer_name,
                'total_amount' => $total_amount,
                'nomor_meja' => $order['nomor_meja']
            ];
            $_SESSION['payment_method'] = 'qris';
            
            // Redirect ke halaman sukses dengan parameter from_qr_payment
            header("refresh:3;url=order-success.php?from_qr_payment=true");
            
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
    <link href="images/logo-rotio.png" rel="icon">
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
            background: linear-gradient(135deg,rgb(234, 203, 102) 0%,rgb(172, 128, 34) 100%);
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
            color: #e67e22;
            margin-bottom: 10px;
            display: block;
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
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
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
            <h1><i class="fas fa-qrcode"></i> Pembayaran QRIS</h1>
            <p>Scan QR code untuk menyelesaikan pembayaran</p>
        </div>
        
        <div class="payment-content">
            <?php if (isset($auto_payment_error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php echo htmlspecialchars($auto_payment_error); ?>
                </div>
            <?php endif; ?>
            
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
            
            <!-- Info Auto Payment -->
            <?php if ($order['status'] === 'pending'): ?>
                <div class="alert alert-info" style="background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb;">
                    <i class="fas fa-info-circle"></i>
                    <strong>Fitur Auto Payment:</strong> Jika Anda scan QR code ini dan jumlah pembayaran sesuai dengan total tagihan (Rp <?php echo number_format($order['total_amount'], 3, ',', '.'); ?>), pembayaran akan diproses otomatis dan pesanan akan langsung diproses.
                </div>
                
                <!-- Payment Status Checker -->
                <div id="payment-status-checker" style="background: linear-gradient(135deg, #e8f5e8, #d4edda); border: 2px solid #c3e6cb; border-radius: 15px; padding: 20px; margin-bottom: 20px; display: none;">
                    <div style="text-align: center;">
                        <i class="fas fa-spinner fa-spin" style="font-size: 24px; color: #27ae60; margin-bottom: 10px;"></i>
                        <h4 style="color: #155724; margin-bottom: 10px;">Memeriksa Status Pembayaran...</h4>
                        <p style="color: #155724; font-size: 14px; margin: 0;">
                            Sistem sedang memeriksa apakah pembayaran telah diterima
                        </p>
                    </div>
                </div>
                
                <!-- Payment Success Alert -->
                <div id="payment-success-alert" style="background: linear-gradient(135deg, #d4edda, #c3e6cb); border: 2px solid #27ae60; border-radius: 15px; padding: 20px; margin-bottom: 20px; display: none;">
                    <div style="text-align: center;">
                        <i class="fas fa-check-circle" style="font-size: 48px; color: #27ae60; margin-bottom: 15px;"></i>
                        <h3 style="color: #155724; margin-bottom: 10px;">Pembayaran Berhasil!</h3>
                        <p style="color: #155724; font-size: 16px; margin-bottom: 15px;">
                            Pembayaran Anda telah diterima dan pesanan sedang diproses
                        </p>
                        <div style="background: white; border-radius: 10px; padding: 15px; margin-top: 15px;">
                            <p style="margin: 0; color: #6c757d; font-size: 14px;">
                                <i class="fas fa-info-circle" style="color: #27ae60;"></i>
                                Anda akan dialihkan ke halaman sukses dalam beberapa detik
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Timer Countdown -->
                <div class="timer-section" style="background: linear-gradient(135deg, #ff6b6b, #ee5a24); color: white; border-radius: 15px; padding: 20px; text-align: center; margin-bottom: 20px;">
                    <h4 style="margin-bottom: 15px;">
                        <i class="fas fa-clock" style="margin-right: 10px;"></i>
                        Waktu Tersisa untuk Pembayaran
                    </h4>
                    <div id="countdown" style="font-size: 2.5em; font-weight: bold; margin-bottom: 10px;">
                        <?php 
                        if ($time_remaining > 0) {
                            $minutes = floor($time_remaining / 60);
                            $seconds = $time_remaining % 60;
                            echo '<span style="display: inline-block; margin: 0 5px;">';
                            echo '<span style="font-size: 0.6em; display: block; opacity: 0.8;">Menit</span>';
                            echo '<span>' . sprintf('%02d', $minutes) . '</span>';
                            echo '</span>';
                            echo '<span style="font-size: 0.8em; margin: 0 5px;">:</span>';
                            echo '<span style="display: inline-block; margin: 0 5px;">';
                            echo '<span style="font-size: 0.6em; display: block; opacity: 0.8;">Detik</span>';
                            echo '<span>' . sprintf('%02d', $seconds) . '</span>';
                            echo '</span>';
                        } else {
                            echo '<span style="display: inline-block; margin: 0 5px;">';
                            echo '<span style="font-size: 0.6em; display: block; opacity: 0.8;">Menit</span>';
                            echo '<span>00</span>';
                            echo '</span>';
                            echo '<span style="font-size: 0.8em; margin: 0 5px;">:</span>';
                            echo '<span style="display: inline-block; margin: 0 5px;">';
                            echo '<span style="font-size: 0.6em; display: block; opacity: 0.8;">Detik</span>';
                            echo '<span>00</span>';
                            echo '</span>';
                        }
                        ?>
                    </div>
                    <p style="margin: 0; font-size: 14px; opacity: 0.9;">
                        Pesanan akan dibatalkan otomatis jika tidak dibayar dalam waktu 5 menit
                    </p>
                </div>
            <?php elseif ($order['status'] === 'paid'): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <strong>Pesanan Sudah Dibayar:</strong> Pesanan ini sudah dibayar dan sedang diproses.
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
                                Rp <?php echo number_format($item['price'] * $item['quantity'], 3, ',', '.'); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="total-section">
                <h2>Total Pembayaran</h2>
                <div class="total-amount">
                    Rp <?php echo number_format($order['total_amount'], 3, ',', '.'); ?>
                </div>
            </div>
            
            <?php if (!$payment_success): ?>
                <form method="POST" class="payment-form" id="paymentForm">
                    <h3><i class="fas fa-qrcode"></i> Pembayaran QRIS</h3>
                    
                    <!-- QR Code Section untuk QRIS -->
                    <div id="qris_section" style="margin-bottom: 30px;">
                        <div style="background: white; border-radius: 15px; padding: 30px; text-align: center; border: 2px solid #e67e22;">
                            <h4 style="color: #2c3e50; margin-bottom: 20px;">
                                <i class="fas fa-qrcode" style="color: #e67e22; margin-right: 10px;"></i>
                                QR Code Pembayaran QRIS
                            </h4>
                            <div id="qr_code_container">
                                <div class="spinner"></div>
                                <p>Generating QR Code...</p>
                            </div>
                            <div style="margin-top: 20px; background: #f8f9fa; border-radius: 10px; padding: 15px;">
                                <p style="margin: 0; color: #6c757d; font-size: 14px;">
                                    <i class="fas fa-info-circle" style="color: #e67e22;"></i>
                                    Silakan scan QR code di atas menggunakan aplikasi e-wallet atau mobile banking Anda
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Button untuk QRIS -->
                    <div id="qris_button">
                        <button type="button" class="btn-pay" id="qrisPayButton" style="background: linear-gradient(135deg, #e67e22, #f39c12);">
                            <i class="fas fa-qrcode"></i> Bayar dengan QRIS
                        </button>
                    </div>
                    
                    <!-- Hidden inputs untuk form submission -->
                    <input type="hidden" name="payment_method" value="qris">
                    <input type="hidden" name="payment_amount" value="<?php echo $order['total_amount']; ?>">
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
        // Timer countdown
        <?php if ($order['status'] === 'pending' && $time_remaining > 0): ?>
        let timeRemaining = <?php echo $time_remaining; ?>;
        const countdownElement = document.getElementById('countdown');
        const timerSection = document.querySelector('.timer-section');
        
        function updateCountdown() {
            if (timeRemaining <= 0) {
                countdownElement.innerHTML = `
                    <span style="display: inline-block; margin: 0 5px;">
                        <span style="font-size: 0.6em; display: block; opacity: 0.8;">Menit</span>
                        <span>00</span>
                    </span>
                    <span style="font-size: 0.8em; margin: 0 5px;">:</span>
                    <span style="display: inline-block; margin: 0 5px;">
                        <span style="font-size: 0.6em; display: block; opacity: 0.8;">Detik</span>
                        <span>00</span>
                    </span>
                `;
                timerSection.style.background = 'linear-gradient(135deg, #e74c3c, #c0392b)';
                timerSection.innerHTML = `
                    <h4 style="margin-bottom: 15px;">
                        <i class="fas fa-exclamation-triangle" style="margin-right: 10px;"></i>
                        Waktu Pembayaran Habis
                    </h4>
                    <p style="margin: 0; font-size: 16px;">
                        Pesanan telah dibatalkan otomatis karena tidak dibayar dalam waktu 5 menit
                    </p>
                `;
                
                // Redirect ke halaman timeout setelah 3 detik
                setTimeout(() => {
                    window.location.href = 'order-timeout.php?order_id=<?php echo $order_id; ?>&order_number=<?php echo $order_number; ?>';
                }, 3000);
                return;
            }
            
            const minutes = Math.floor(timeRemaining / 60);
            const seconds = timeRemaining % 60;
            countdownElement.innerHTML = `
                <span style="display: inline-block; margin: 0 5px;">
                    <span style="font-size: 0.6em; display: block; opacity: 0.8;">Menit</span>
                    <span>${minutes.toString().padStart(2, '0')}</span>
                </span>
                <span style="font-size: 0.8em; margin: 0 5px;">:</span>
                <span style="display: inline-block; margin: 0 5px;">
                    <span style="font-size: 0.6em; display: block; opacity: 0.8;">Detik</span>
                    <span>${seconds.toString().padStart(2, '0')}</span>
                </span>
            `;
            
            // Ubah warna timer menjadi merah ketika kurang dari 1 menit
            if (timeRemaining <= 60) {
                timerSection.style.background = 'linear-gradient(135deg, #e74c3c, #c0392b)';
                countdownElement.style.animation = 'pulse 1s infinite';
            }
            
            timeRemaining--;
        }
        
        // Update countdown setiap detik
        setInterval(updateCountdown, 1000);
        <?php endif; ?>
        
        // Generate QR Code function
        function generateQRCode() {
            const qrContainer = document.getElementById('qr_code_container');
            qrContainer.innerHTML = '<div class="spinner"></div><p>Generating QR Code...</p>';
            
            // Call AJAX to generate QR code
            fetch('generate_qr.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    'action': 'generate_qr',
                    'order_id': '<?php echo $order_id; ?>',
                    'order_number': '<?php echo $order_number; ?>',
                    'total_amount': '<?php echo $total_amount; ?>',
                    'customer_name': '<?php echo urlencode($customer_name); ?>'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    qrContainer.innerHTML = `
                        <img src="${data.qr_filename}" alt="QR Code Pembayaran" 
                             style="max-width: 200px; border: 2px solid #e67e22; border-radius: 10px; box-shadow: 0 4px 15px rgba(230,126,34,0.2);">
                        <div style="margin-top: 15px; color: #7f8c8d; font-size: 12px;">
                            <p><strong>Order ID:</strong> ${data.order_number}</p>
                        </div>
                    `;
                } else {
                    qrContainer.innerHTML = '<p style="color: red;">Gagal generate QR Code</p>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                qrContainer.innerHTML = '<p style="color: red;">Error generating QR Code</p>';
            });
        }
        
        // QRIS Payment Button
        document.getElementById('qrisPayButton').addEventListener('click', function() {
            // Simulate QRIS payment process
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses Pembayaran QRIS...';
            
            // Simulate payment processing (in real implementation, this would be handled by QRIS provider)
            setTimeout(() => {
                // Submit form with QRIS payment method
                const form = document.getElementById('paymentForm');
                form.submit();
            }, 2000);
        });
        
        // Payment status polling
        let paymentCheckInterval;
        let paymentCheckCount = 0;
        const maxPaymentChecks = 60; // Check for 5 minutes (60 * 5 seconds)
        
        function startPaymentStatusCheck() {
            paymentCheckInterval = setInterval(checkPaymentStatus, 5000); // Check every 5 seconds
        }
        
        function checkPaymentStatus() {
            paymentCheckCount++;
            
            // Show payment status checker
            document.getElementById('payment-status-checker').style.display = 'block';
            
            // Call AJAX to check payment status
            fetch('check_payment_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    'order_id': '<?php echo $order_id; ?>',
                    'order_number': '<?php echo $order_number; ?>'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.status === 'paid') {
                    // Payment successful
                    clearInterval(paymentCheckInterval);
                    document.getElementById('payment-status-checker').style.display = 'none';
                    document.getElementById('payment-success-alert').style.display = 'block';
                    
                    // Hide payment form
                    const paymentForm = document.querySelector('.payment-form');
                    if (paymentForm) {
                        paymentForm.style.display = 'none';
                    }
                    
                    // Redirect to success page after 3 seconds
                    setTimeout(() => {
                        window.location.href = 'order-success.php?from_qr_payment=true&auto_payment=true';
                    }, 3000);
                    
                } else if (paymentCheckCount >= maxPaymentChecks) {
                    // Stop checking after 5 minutes
                    clearInterval(paymentCheckInterval);
                    document.getElementById('payment-status-checker').style.display = 'none';
                    console.log('Payment status check timeout');
                }
            })
            .catch(error => {
                console.error('Error checking payment status:', error);
                if (paymentCheckCount >= maxPaymentChecks) {
                    clearInterval(paymentCheckInterval);
                    document.getElementById('payment-status-checker').style.display = 'none';
                }
            });
        }
        
        // Generate QR code when page loads
        document.addEventListener('DOMContentLoaded', function() {
            generateQRCode();
            
            // Start payment status checking if order is pending
            <?php if ($order['status'] === 'pending'): ?>
            startPaymentStatusCheck();
            <?php endif; ?>
        });
    </script>
</body>
</html> 