<?php
session_start();
require_once 'config/database.php';

// Ambil parameter dari URL
$order_id = isset($_GET['order_id']) ? $_GET['order_id'] : '';
$order_number = isset($_GET['order_number']) ? $_GET['order_number'] : '';

// Validasi parameter
if (empty($order_id) || empty($order_number)) {
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
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Dibatalkan - Roti'O</title>
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
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .timeout-container {
            max-width: 600px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
            overflow: hidden;
            text-align: center;
        }
        
        .timeout-header {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
            padding: 40px;
            position: relative;
        }
        
        .timeout-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="white" opacity="0.1"/><circle cx="10" cy="60" r="0.5" fill="white" opacity="0.1"/><circle cx="90" cy="40" r="0.5" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
        }
        
        .timeout-header h1 {
            font-size: 2.5em;
            font-weight: 700;
            margin-bottom: 10px;
            position: relative;
            z-index: 1;
        }
        
        .timeout-header p {
            font-size: 1.1em;
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }
        
        .timeout-content {
            padding: 40px;
        }
        
        .timeout-icon {
            font-size: 80px;
            color: #e74c3c;
            margin-bottom: 20px;
            animation: shake 0.5s ease-in-out;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
        
        .order-info {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 25px;
            margin: 20px 0;
            border-left: 5px solid #e74c3c;
        }
        
        .order-info h3 {
            color: #2c3e50;
            margin-bottom: 15px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: 600;
            color: #7f8c8d;
        }
        
        .info-value {
            color: #2c3e50;
            font-weight: 500;
        }
        
        .action-buttons {
            margin-top: 30px;
        }
        
        .btn {
            display: inline-block;
            padding: 15px 30px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            margin: 0 10px;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #e67e22, #f39c12);
            color: white;
            border: none;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(230, 126, 34, 0.3);
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
            border: none;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }
        
        .message {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 10px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="timeout-container">
        <div class="timeout-header">
            <h1><i class="fas fa-exclamation-triangle"></i> Pesanan Dibatalkan</h1>
            <p>Waktu pembayaran telah habis</p>
        </div>
        
        <div class="timeout-content">
            <div class="timeout-icon">
                <i class="fas fa-clock"></i>
            </div>
            
            <h2 style="color: #e74c3c; margin-bottom: 20px;">Pesanan Telah Dibatalkan</h2>
            
            <div class="message">
                <i class="fas fa-info-circle"></i>
                <strong>Pesanan Anda telah dibatalkan otomatis</strong> karena tidak dibayar dalam waktu 5 menit setelah dibuat.
            </div>
            
            <div class="order-info">
                <h3><i class="fas fa-receipt"></i> Detail Pesanan</h3>
                <div class="info-row">
                    <span class="info-label">Nomor Pesanan:</span>
                    <span class="info-value"><?php echo htmlspecialchars($order['order_number']); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Nama Pelanggan:</span>
                    <span class="info-value"><?php echo htmlspecialchars($order['customer_name']); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Total Pembayaran:</span>
                    <span class="info-value">Rp <?php echo number_format($order['total_amount'], 3, ',', '.'); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Status:</span>
                    <span class="info-value" style="color: #e74c3c; font-weight: bold;">Dibatalkan</span>
                </div>
            </div>
            
            <div class="action-buttons">
                <a href="index.php" class="btn btn-primary">
                    <i class="fas fa-home"></i> Kembali ke Beranda
                </a>
                <a href="product-listing.php" class="btn btn-secondary">
                    <i class="fas fa-shopping-cart"></i> Pesan Lagi
                </a>
            </div>
            
            <p style="margin-top: 30px; color: #7f8c8d; font-size: 14px;">
                <i class="fas fa-lightbulb"></i>
                <strong>Tips:</strong> Untuk pesanan berikutnya, pastikan untuk menyelesaikan pembayaran dalam waktu 5 menit setelah checkout.
            </p>
        </div>
    </div>
</body>
</html> 