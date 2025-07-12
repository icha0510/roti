<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'config/database.php';

// Ambil data order terbaru untuk testing
$database = new Database();
$db = $database->getConnection();

$stmt = $db->prepare("SELECT * FROM orders ORDER BY created_at DESC LIMIT 1");
$stmt->execute();
$latest_order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$latest_order) {
    echo "<h2>Tidak ada order untuk testing</h2>";
    echo "<p>Silakan buat order terlebih dahulu melalui checkout</p>";
    echo "<a href='checkout.php'>Buat Order Baru</a>";
    exit;
}

// Ambil detail order items
$stmt = $db->prepare("SELECT * FROM order_items WHERE order_id = ?");
$stmt->execute([$latest_order['id']]);
$order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test QR Payment Flow - Roti'O</title>
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
        
        .test-container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .test-header {
            background: linear-gradient(135deg, #e67e22, #f39c12);
            color: white;
            padding: 30px;
            text-align: center;
            position: relative;
        }
        
        .test-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="white" opacity="0.1"/><circle cx="10" cy="60" r="0.5" fill="white" opacity="0.1"/><circle cx="90" cy="40" r="0.5" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
        }
        
        .test-header h1 {
            font-size: 2.5em;
            font-weight: 700;
            margin-bottom: 10px;
            position: relative;
            z-index: 1;
        }
        
        .test-header p {
            font-size: 1.1em;
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }
        
        .test-content {
            padding: 40px;
        }
        
        .order-info {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            border-left: 5px solid #e67e22;
        }
        
        .order-info h3 {
            color: #2c3e50;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .info-grid {
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
        
        .test-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .test-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }
        
        .test-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
            border-color: #e67e22;
        }
        
        .test-card h4 {
            color: #2c3e50;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .test-card p {
            color: #6c757d;
            margin-bottom: 20px;
            line-height: 1.6;
        }
        
        .btn-test {
            display: inline-block;
            padding: 12px 25px;
            background: linear-gradient(135deg, #e67e22, #f39c12);
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-align: center;
            width: 100%;
        }
        
        .btn-test:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(230, 126, 34, 0.3);
            color: white;
        }
        
        .btn-secondary {
            background: #6c757d;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
            box-shadow: 0 8px 25px rgba(108, 117, 125, 0.3);
        }
        
        .qr-preview {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            margin-bottom: 30px;
        }
        
        .qr-preview h3 {
            color: #2c3e50;
            margin-bottom: 20px;
        }
        
        .qr-code-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            display: inline-block;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .qr-code-container img {
            max-width: 200px;
            border: 2px solid #e67e22;
            border-radius: 10px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: 600;
            margin-top: 10px;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-paid {
            background: #d4edda;
            color: #155724;
        }
        
        .footer-actions {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
        }
        
        .btn-home {
            background: #6c757d;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }
        
        .btn-home:hover {
            background: #5a6268;
            transform: translateY(-1px);
            color: white;
        }
        
        @media (max-width: 768px) {
            .test-content {
                padding: 20px;
            }
            
            .test-header h1 {
                font-size: 2em;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .test-actions {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="test-container">
        <div class="test-header">
            <h1><i class="fas fa-flask"></i> Test QR Payment Flow</h1>
            <p>Halaman testing untuk menguji alur pembayaran QRIS</p>
        </div>
        
        <div class="test-content">
            <div class="order-info">
                <h3><i class="fas fa-receipt"></i> Informasi Order Test</h3>
                
                <div class="info-grid">
                    <div class="info-item">
                        <label>Nomor Pesanan</label>
                        <span><?php echo htmlspecialchars($latest_order['order_number']); ?></span>
                    </div>
                    <div class="info-item">
                        <label>Nama Pelanggan</label>
                        <span><?php echo htmlspecialchars($latest_order['customer_name']); ?></span>
                    </div>
                    <div class="info-item">
                        <label>Nomor Meja</label>
                        <span><?php echo htmlspecialchars($latest_order['nomor_meja']); ?></span>
                    </div>
                    <div class="info-item">
                        <label>Total Tagihan</label>
                        <span style="color: #e67e22; font-weight: 700; font-size: 1.1em;">
                            Rp <?php echo number_format($latest_order['total_amount'], 3, ',', '.'); ?>
                        </span>
                    </div>
                    <div class="info-item">
                        <label>Status</label>
                        <span class="status-badge status-<?php echo $latest_order['status']; ?>">
                            <?php echo ucfirst($latest_order['status']); ?>
                        </span>
                    </div>
                    <div class="info-item">
                        <label>Tanggal Order</label>
                        <span><?php echo date('d/m/Y H:i', strtotime($latest_order['created_at'])); ?></span>
                    </div>
                </div>
                
                <div style="background: white; border-radius: 10px; padding: 15px; margin-top: 20px;">
                    <h4 style="color: #2c3e50; margin-bottom: 10px;">Detail Item:</h4>
                    <?php foreach ($order_items as $item): ?>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid #f1f1f1;">
                            <div>
                                <span style="font-weight: 500;"><?php echo htmlspecialchars($item['product_name']); ?></span>
                                <span style="color: #6c757d; font-size: 0.9em;"> (x<?php echo $item['quantity']; ?>)</span>
                            </div>
                            <span style="color: #e67e22; font-weight: 600;">
                                Rp <?php echo number_format($item['price'] * $item['quantity'], 3, ',', '.'); ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="test-actions">
                <div class="test-card">
                    <h4><i class="fas fa-qrcode"></i> Test QR Code</h4>
                    <p>Generate dan tampilkan QR code untuk order ini. QR code akan mengarah ke halaman pembayaran yang menarik.</p>
                    <a href="generate_qr.php?order_id=<?php echo $latest_order['id']; ?>&order_number=<?php echo urlencode($latest_order['order_number']); ?>&total_amount=<?php echo $latest_order['total_amount']; ?>&customer_name=<?php echo urlencode($latest_order['customer_name']); ?>" class="btn-test">
                        <i class="fas fa-play"></i> Generate QR Code
                    </a>
                </div>
                
                <div class="test-card">
                    <h4><i class="fas fa-credit-card"></i> Test Halaman Pembayaran</h4>
                    <p>Langsung akses halaman pembayaran dengan styling yang menarik dan fungsi pembayaran yang lengkap.</p>
                    <a href="qr_payment_page.php?order_id=<?php echo $latest_order['id']; ?>&order_number=<?php echo urlencode($latest_order['order_number']); ?>&total_amount=<?php echo $latest_order['total_amount']; ?>&customer_name=<?php echo urlencode($latest_order['customer_name']); ?>" class="btn-test">
                        <i class="fas fa-external-link-alt"></i> Buka Halaman Pembayaran
                    </a>
                </div>
                
                <div class="test-card">
                    <h4><i class="fas fa-list"></i> Lihat Semua Order</h4>
                    <p>Akses halaman admin untuk melihat semua order dan status pembayarannya.</p>
                    <a href="logo-orders.php" class="btn-test btn-secondary">
                        <i class="fas fa-table"></i> Lihat Order
                    </a>
                </div>
                
                <div class="test-card">
                    <h4><i class="fas fa-shopping-cart"></i> Buat Order Baru</h4>
                    <p>Buat order baru untuk testing lebih lanjut dengan produk yang berbeda.</p>
                    <a href="checkout.php" class="btn-test btn-secondary">
                        <i class="fas fa-plus"></i> Buat Order Baru
                    </a>
                </div>
            </div>
            
            <div class="qr-preview">
                <h3><i class="fas fa-eye"></i> Preview QR Code</h3>
                <p style="color: #6c757d; margin-bottom: 20px;">
                    QR code akan mengarah ke halaman pembayaran yang menarik dengan styling modern dan fungsi lengkap.
                </p>
                <div class="qr-code-container">
                    <img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgdmlld0JveD0iMCAwIDIwMCAyMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIyMDAiIGhlaWdodD0iMjAwIiBmaWxsPSJ3aGl0ZSIvPgo8cmVjdCB4PSIxMCIgeT0iMTAiIHdpZHRoPSIxODAiIGhlaWdodD0iMTgwIiBmaWxsPSJub25lIiBzdHJva2U9IiNlNjdlMjIiIHN0cm9rZS13aWR0aD0iMiIvPgo8cmVjdCB4PSIzMCIgeT0iMzAiIHdpZHRoPSIxNDAiIGhlaWdodD0iMTQwIiBmaWxsPSJub25lIiBzdHJva2U9IiNlNjdlMjIiIHN0cm9rZS13aWR0aD0iMSIvPgo8Y2lyY2xlIGN4PSIxMDAiIGN5PSIxMDAiIHI9IjEwIiBmaWxsPSIjZTY3ZTIyIi8+Cjx0ZXh0IHg9IjEwMCIgeT0iMTgwIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBmaWxsPSIjZTY3ZTIyIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMTIiPk9yZGVyOiA8L3RleHQ+Cjx0ZXh0IHg9IjEwMCIgeT0iMTkwIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBmaWxsPSIjZTY3ZTIyIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMTIiPj8/PzwvdGV4dD4KPC9zdmc+" alt="QR Code Preview">
                    <div style="margin-top: 15px; color: #6c757d; font-size: 12px;">
                        <p><strong>Order:</strong> <?php echo htmlspecialchars($latest_order['order_number']); ?></p>
                        <p><strong>Total:</strong> Rp <?php echo number_format($latest_order['total_amount'], 3, ',', '.'); ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="footer-actions">
            <a href="index.php" class="btn-home">
                <i class="fas fa-home"></i> Kembali ke Beranda
            </a>
        </div>
    </div>
    
    <script>
        // Auto-refresh untuk update status
        setTimeout(function() {
            location.reload();
        }, 30000); // Refresh setiap 30 detik
        
        // Tambahkan animasi hover
        document.querySelectorAll('.test-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });
    </script>
</body>
</html> 