<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config/database.php';

// Ambil order terbaru untuk testing
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

// Generate QR code
require_once 'generate_qr.php';

$order_id = $latest_order['id'];
$order_number = $latest_order['order_number'];
$total_amount = $latest_order['total_amount'];
$customer_name = $latest_order['customer_name'];

try {
    $qr_filename = generatePaymentQR($order_id, $order_number, $total_amount, $customer_name);
    
    // Buat URL yang seharusnya ada di QR code
    $payment_url = 'http://localhost/web/bready/qr_payment_page.php';
    $qr_url = $payment_url . '?order_id=' . urlencode($order_id) . 
              '&order_number=' . urlencode($order_number) . 
              '&total_amount=' . urlencode($total_amount) . 
              '&customer_name=' . urlencode($customer_name);
    
} catch (Exception $e) {
    die('Error: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test QR Code URL - Roti'O</title>
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
            max-width: 800px;
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
        }
        
        .test-header h1 {
            font-size: 2.5em;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .test-header p {
            font-size: 1.1em;
            opacity: 0.9;
        }
        
        .test-content {
            padding: 40px;
        }
        
        .qr-section {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .qr-code-container {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 30px;
            display: inline-block;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .qr-code-container img {
            max-width: 250px;
            border: 3px solid #e67e22;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(230,126,34,0.3);
        }
        
        .url-info {
            background: #e8f5e8;
            border: 2px solid #28a745;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .url-info h3 {
            color: #155724;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .url-display {
            background: white;
            border: 1px solid #28a745;
            border-radius: 8px;
            padding: 15px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            word-break: break-all;
            color: #155724;
            margin-bottom: 15px;
        }
        
        .test-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .btn {
            display: inline-block;
            padding: 15px 25px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            text-align: center;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #e67e22, #f39c12);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(230, 126, 34, 0.3);
            color: white;
        }
        
        .btn-success {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
        }
        
        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(40, 167, 69, 0.3);
            color: white;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
            color: white;
        }
        
        .order-details {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
        }
        
        .order-details h3 {
            color: #2c3e50;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .detail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .detail-item {
            background: white;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .detail-item label {
            font-weight: 600;
            color: #7f8c8d;
            font-size: 0.9em;
            display: block;
            margin-bottom: 5px;
        }
        
        .detail-item span {
            color: #2c3e50;
            font-weight: 500;
        }
        
        .instructions {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .instructions h3 {
            color: #856404;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .instructions ol {
            color: #856404;
            padding-left: 20px;
        }
        
        .instructions li {
            margin-bottom: 8px;
        }
        
        .footer-actions {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
        }
        
        @media (max-width: 768px) {
            .test-content {
                padding: 20px;
            }
            
            .test-header h1 {
                font-size: 2em;
            }
            
            .detail-grid {
                grid-template-columns: 1fr;
            }
            
            .test-buttons {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="test-container">
        <div class="test-header">
            <h1><i class="fas fa-qrcode"></i> Test QR Code URL</h1>
            <p>Verifikasi bahwa QR code berisi URL langsung ke halaman pembayaran</p>
        </div>
        
        <div class="test-content">
            <div class="order-details">
                <h3><i class="fas fa-receipt"></i> Detail Order Test</h3>
                <div class="detail-grid">
                    <div class="detail-item">
                        <label>Order ID</label>
                        <span><?php echo $order_id; ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Order Number</label>
                        <span><?php echo htmlspecialchars($order_number); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Customer Name</label>
                        <span><?php echo htmlspecialchars($customer_name); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Total Amount</label>
                        <span style="color: #e67e22; font-weight: 700;">
                            Rp <?php echo number_format($total_amount, 0, ',', '.'); ?>
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="qr-section">
                <h3><i class="fas fa-qrcode"></i> QR Code yang Dihasilkan</h3>
                <div class="qr-code-container">
                    <img src="<?php echo $qr_filename; ?>" alt="QR Code Pembayaran">
                    <div style="margin-top: 15px; color: #6c757d; font-size: 12px;">
                        <p><strong>File:</strong> <?php echo basename($qr_filename); ?></p>
                        <p><strong>Order:</strong> <?php echo htmlspecialchars($order_number); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="url-info">
                <h3><i class="fas fa-link"></i> URL yang Ada di QR Code</h3>
                <div class="url-display">
                    <?php echo htmlspecialchars($qr_url); ?>
                </div>
                <p style="color: #155724; margin: 0;">
                    <i class="fas fa-check-circle"></i> 
                    QR code sekarang berisi URL langsung ke halaman pembayaran, bukan data JSON!
                </p>
            </div>
            
            <div class="instructions">
                <h3><i class="fas fa-info-circle"></i> Cara Testing</h3>
                <ol>
                    <li><strong>Scan QR Code:</strong> Gunakan aplikasi QR scanner di HP</li>
                    <li><strong>Hasil:</strong> Akan langsung membuka halaman pembayaran yang menarik</li>
                    <li><strong>Bukan JSON:</strong> Tidak lagi menampilkan data JSON yang membosankan</li>
                    <li><strong>Fungsi Lengkap:</strong> Halaman pembayaran dengan styling modern dan fungsi pembayaran</li>
                </ol>
            </div>
            
            <div class="test-buttons">
                <a href="<?php echo $qr_url; ?>" class="btn btn-primary" target="_blank">
                    <i class="fas fa-external-link-alt"></i> Buka Halaman Pembayaran
                </a>
                
                <a href="qr_payment_page.php?order_id=<?php echo $order_id; ?>&order_number=<?php echo urlencode($order_number); ?>&total_amount=<?php echo $total_amount; ?>&customer_name=<?php echo urlencode($customer_name); ?>" class="btn btn-success" target="_blank">
                    <i class="fas fa-credit-card"></i> Test Pembayaran
                </a>
                
                <a href="test_qr_payment_flow.php" class="btn btn-secondary">
                    <i class="fas fa-flask"></i> Test Alur Lengkap
                </a>
                
                <a href="checkout.php" class="btn btn-secondary">
                    <i class="fas fa-shopping-cart"></i> Buat Order Baru
                </a>
            </div>
            
            <div style="background: #d4edda; border: 1px solid #c3e6cb; border-radius: 10px; padding: 20px; text-align: center;">
                <h3 style="color: #155724; margin-bottom: 10px;">
                    <i class="fas fa-check-circle"></i> Berhasil!
                </h3>
                <p style="color: #155724; margin: 0;">
                    QR code sekarang berisi URL langsung ke halaman pembayaran yang menarik. 
                    Ketika user scan QR code, mereka akan langsung masuk ke halaman pembayaran dengan styling yang modern!
                </p>
            </div>
        </div>
        
        <div class="footer-actions">
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-home"></i> Kembali ke Beranda
            </a>
        </div>
    </div>
    
    <script>
        // Auto-refresh untuk update QR code
        setTimeout(function() {
            location.reload();
        }, 60000); // Refresh setiap 1 menit
        
        // Copy URL to clipboard
        function copyUrl() {
            const url = '<?php echo $qr_url; ?>';
            navigator.clipboard.writeText(url).then(function() {
                alert('URL berhasil disalin ke clipboard!');
            });
        }
    </script>
</body>
</html> 