<?php
session_start();
require_once '../config/database.php';

$isAjax = isset($_GET['ajax']) && $_GET['ajax'] == 1;

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
if (!$order_id) {
    echo '<div style="color:red;">Order tidak ditemukan.</div>';
    exit;
}

$database = new Database();
$db = $database->getConnection();

// Ambil data order dengan informasi user dan order items
date_default_timezone_set('Asia/Jakarta');
$stmt = $db->prepare("SELECT o.*, u.name as user_name, u.email as user_email 
                      FROM orders o 
                      LEFT JOIN users u ON o.user_id = u.id 
                      WHERE o.id = :id");
$stmt->bindParam(':id', $order_id);
$stmt->execute();
$order = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$order) {
    echo '<div style="color:red;">Order tidak ditemukan.</div>';
    exit;
}

// Ambil data order items
$stmt = $db->prepare("SELECT * FROM order_items WHERE order_id = :order_id");
$stmt->bindParam(':order_id', $order_id);
$stmt->execute();
$order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Proses update status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'])) {
    $status = $_POST['status'];
    $desc = $_POST['description'] ?? '';
    
    // Validasi status yang diizinkan
    $allowed_statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
    if (!in_array($status, $allowed_statuses)) {
        $error = "Status tidak valid. Status yang diizinkan: " . implode(', ', $allowed_statuses);
    } else {
        // Debug: Log data yang diterima
        error_log("POST data received: " . print_r($_POST, true));
        error_log("Status: $status, Description: $desc, Order ID: $order_id");
        
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
            $stmt->bindParam(':description', $desc);
            
            if ($stmt->execute()) {
                $db->commit();
                error_log("Status update successful for order ID: $order_id");
                
                if ($isAjax) {
                    echo '<div style="color:green; padding: 10px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 5px; margin: 10px 0;">Status berhasil diupdate!</div>';
                } else {
                    header("Location: order_tracking_fixed.php?order_id=$order_id&success=1");
                    exit;
                }
            } else {
                $db->rollback();
                $error = "Gagal menyimpan status update";
                error_log("Failed to execute statement for order ID: $order_id");
            }
        } catch (PDOException $e) {
            $db->rollback();
            $error = "Database error: " . $e->getMessage();
            error_log("Database error: " . $e->getMessage());
        }
    }
}

// Ambil riwayat tracking
$stmt = $db->prepare("SELECT * FROM order_tracking WHERE order_id = :order_id ORDER BY created_at DESC");
$stmt->bindParam(':order_id', $order_id);
$stmt->execute();
$trackings = $stmt->fetchAll(PDO::FETCH_ASSOC);

function status_badge($status) {
    $badge = 'badge-pending';
    if ($status == 'delivered') $badge = 'badge-success';
    if ($status == 'cancelled') $badge = 'badge-cancel';
    if ($status == 'processing') $badge = 'badge-process';
    if ($status == 'shipped') $badge = 'badge-shipped';
    return '<span class="badge '.$badge.'">'.ucfirst($status).'</span>';
}

if (!$isAjax) {
    echo '<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Tracking Order #'.htmlspecialchars($order['order_number']).'</title><link rel="stylesheet" href="../css/style.css"><style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        } 
        .container { 
            max-width: 900px; 
            margin: 0 auto; 
            background: #fff; 
            border-radius: 20px; 
            box-shadow: 0 20px 40px rgba(0,0,0,0.1); 
            padding: 40px;
            position: relative;
            overflow: hidden;
            animation: fadeInUp 0.6s ease-out;
        }
        .container::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #667eea, #764ba2, #f093fb);
        }
        h2 { 
            margin-bottom: 30px; 
            color: #333;
            font-size: 2.2rem;
            font-weight: 700;
            text-align: center;
            position: relative;
        }
        h2::after {
            content: "";
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, #667eea, #764ba2);
            border-radius: 2px;
        }
        h3 {
            color: #333;
            font-size: 1.5rem;
            margin: 25px 0 15px 0;
            font-weight: 600;
        }
        h4 {
            color: #555;
            font-size: 1.2rem;
            margin: 20px 0 10px 0;
            font-weight: 600;
        }
        .badge { 
            padding: 8px 16px; 
            border-radius: 25px; 
            font-size: 12px; 
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-block;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            animation: pulse 2s infinite;
        } 
        .badge-pending { background: linear-gradient(45deg, #ffc107, #ff9800); color: #fff; } 
        .badge-success { background: linear-gradient(45deg, #28a745, #20c997); color: #fff; } 
        .badge-cancel { background: linear-gradient(45deg, #dc3545, #e74c3c); color: #fff; } 
        .badge-process { background: linear-gradient(45deg, #17a2b8, #3498db); color: #fff; } 
        .badge-shipped { background: linear-gradient(45deg, #6f42c1, #8e44ad); color: #fff; } 
        ul.tracking-list { 
            list-style: none; 
            padding: 0; 
        } 
        ul.tracking-list li { 
            margin-bottom: 20px; 
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 15px; 
            padding: 20px;
            border-left: 4px solid #667eea;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        ul.tracking-list li:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
        }
        .form-group { 
            margin-bottom: 20px; 
        } 
        label { 
            font-weight: 600; 
            color: #333;
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
        } 
        textarea { 
            width: 100%; 
            min-height: 80px; 
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 15px;
            font-family: inherit;
            font-size: 14px;
            transition: border-color 0.3s ease;
            resize: vertical;
        }
        textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        button, select { 
            padding: 12px 24px; 
            border: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        button {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }
        select {
            background: white;
            border: 2px solid #e9ecef;
            color: #333;
            min-width: 200px;
        }
        select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .back-link { 
            display: inline-block; 
            margin-top: 30px; 
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
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }
        .user-info { 
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 25px; 
            border-radius: 15px; 
            margin: 20px 0;
            border-left: 4px solid #28a745;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            animation: fadeInUp 0.6s ease-out 0.2s both;
        }
        .user-info h4 {
            color: #28a745;
            margin-bottom: 15px;
        }
        .user-info p {
            margin-bottom: 8px;
            color: #555;
            font-size: 14px;
        }
        .user-info strong {
            color: #333;
            font-weight: 600;
        }
        .product-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            animation: fadeInUp 0.6s ease-out 0.4s both;
        }
        .product-table th {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
        }
        .product-table td {
            padding: 15px;
            border-bottom: 1px solid #e9ecef;
            font-size: 14px;
        }
        .product-table tr:nth-child(even) {
            background: #f8f9fa;
        }
        .product-table tr:hover {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            transform: scale(1.01);
        }
        .order-summary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 15px;
            margin: 20px 0;
            text-align: center;
            animation: fadeInUp 0.6s ease-out 0.6s both;
        }
        .order-summary h4 {
            color: white;
            margin-bottom: 15px;
        }
        .order-summary p {
            margin-bottom: 8px;
            font-size: 16px;
        }
        .order-summary strong {
            font-size: 18px;
        }
        hr {
            border: none;
            height: 2px;
            background: linear-gradient(90deg, transparent, #667eea, transparent);
            margin: 30px 0;
        }
        .status-update-form {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 25px;
            border-radius: 15px;
            margin: 20px 0;
            border-left: 4px solid #17a2b8;
            animation: fadeInUp 0.6s ease-out 0.8s both;
        }
        .status-update-form h3 {
            color: #17a2b8;
            margin-bottom: 20px;
        }
        .tracking-timeline {
            position: relative;
            padding-left: 30px;
            animation: fadeInUp 0.6s ease-out 0.6s both;
        }
        .tracking-timeline::before {
            content: "";
            position: absolute;
            left: 15px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: linear-gradient(180deg, #667eea, #764ba2);
        }
        .tracking-item {
            position: relative;
            margin-bottom: 20px;
            animation: fadeInUp 0.4s ease-out;
        }
        .tracking-item::before {
            content: "";
            position: absolute;
            left: -22px;
            top: 5px;
            width: 12px;
            height: 12px;
            background: #667eea;
            border-radius: 50%;
            border: 3px solid white;
            box-shadow: 0 0 0 2px #667eea;
        }
        .tracking-item:last-child::before {
            background: #28a745;
            box-shadow: 0 0 0 2px #28a745;
        }
        .tracking-content {
            background: white;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        .tracking-content:hover {
            transform: translateX(5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        }
        .tracking-time {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        .empty-tracking {
            text-align: center;
            padding: 40px;
            color: #666;
            font-style: italic;
            animation: fadeInUp 0.6s ease-out 0.6s both;
        }
        .empty-tracking::before {
            content: "📋";
            font-size: 3rem;
            display: block;
            margin-bottom: 15px;
        }
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .tracking-item:nth-child(1) { animation-delay: 0.1s; }
        .tracking-item:nth-child(2) { animation-delay: 0.2s; }
        .tracking-item:nth-child(3) { animation-delay: 0.3s; }
        .tracking-item:nth-child(4) { animation-delay: 0.4s; }
        .tracking-item:nth-child(5) { animation-delay: 0.5s; }
        .product-table tr {
            transition: all 0.3s ease;
        }
        .product-table tr:hover {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            transform: scale(1.01);
        }
        .order-number {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            display: inline-block;
            font-weight: 600;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
            animation: bounce 2s infinite;
        }
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-10px); }
            60% { transform: translateY(-5px); }
        }
        .status-indicator {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 8px;
            animation: blink 1.5s infinite;
        }
        @keyframes blink {
            0%, 50% { opacity: 1; }
            51%, 100% { opacity: 0.3; }
        }
        .status-pending .status-indicator { background: #ffc107; }
        .status-processing .status-indicator { background: #17a2b8; }
        .status-shipped .status-indicator { background: #6f42c1; }
        .status-delivered .status-indicator { background: #28a745; }
        .status-cancelled .status-indicator { background: #dc3545; }
        .floating-action {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            box-shadow: 0 4px 20px rgba(102, 126, 234, 0.4);
            cursor: pointer;
            transition: all 0.3s ease;
            z-index: 1000;
        }
        .floating-action:hover {
            transform: scale(1.1) rotate(360deg);
            box-shadow: 0 8px 30px rgba(102, 126, 234, 0.6);
        }
        .responsive-table {
            overflow-x: auto;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }
        .section-divider {
            height: 3px;
            background: linear-gradient(90deg, transparent, #667eea, transparent);
            margin: 30px 0;
            border-radius: 2px;
            position: relative;
        }
        .section-divider::before {
            content: "";
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 20px;
            height: 20px;
            background: #667eea;
            border-radius: 50%;
            box-shadow: 0 0 0 4px white, 0 0 0 6px #667eea;
        }
        @media (max-width: 768px) {
            .container {
                margin: 10px;
                padding: 20px;
            }
            h2 {
                font-size: 1.8rem;
            }
            .product-table {
                font-size: 12px;
            }
            .product-table th,
            .product-table td {
                padding: 10px 8px;
            }
            .floating-action {
                bottom: 20px;
                right: 20px;
                width: 50px;
                height: 50px;
                font-size: 20px;
            }
        }
        .loading {
            opacity: 0.7;
            pointer-events: none;
        }
        .loading::after {
            content: "";
            position: absolute;
            top: 50%;
            left: 50%;
            width: 20px;
            height: 20px;
            margin: -10px 0 0 -10px;
            border: 2px solid #667eea;
            border-top: 2px solid transparent;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .success-message {
            background: linear-gradient(45deg, #28a745, #20c997);
            color: white;
            padding: 15px 20px;
            border-radius: 10px;
            margin: 20px 0;
            text-align: center;
            animation: slideInDown 0.5s ease-out;
        }
        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .error-message {
            background: linear-gradient(45deg, #dc3545, #e74c3c);
            color: white;
            padding: 15px 20px;
            border-radius: 10px;
            margin: 20px 0;
            text-align: center;
            animation: slideInDown 0.5s ease-out;
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            transform: translateY(-2px);
        }
        .card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin: 20px 0;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        .info-card {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 20px;
            border-radius: 15px;
            border-left: 4px solid #667eea;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
        }
        .info-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
        }
        .info-card h5 {
            color: #667eea;
            margin-bottom: 10px;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .info-card p {
            color: #333;
            font-weight: 600;
            margin: 0;
        }
        .status-badge-large {
            padding: 12px 24px;
            border-radius: 30px;
            font-size: 14px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            display: inline-block;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            margin: 10px 0;
        }
        .print-button {
            background: linear-gradient(45deg, #28a745, #20c997);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-left: 10px;
        }
        .print-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.4);
        }
        @media print {
            .floating-action, .back-link, .status-update-form {
                display: none !important;
            }
            .container {
                box-shadow: none;
                border-radius: 0;
            }
        }
        .gradient-text {
            background: linear-gradient(45deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: 700;
        }
        .pulse-animation {
            animation: pulse 2s infinite;
        }
        .shake-animation {
            animation: shake 0.5s ease-in-out;
        }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
        .fade-in {
            animation: fadeIn 1s ease-in;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .slide-in-left {
            animation: slideInLeft 0.6s ease-out;
        }
        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        .slide-in-right {
            animation: slideInRight 0.6s ease-out;
        }
        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
    </style></head><body>';
    echo '<div class="container">';
}
?>
<h2 class="gradient-text">Tracking Order</h2>
<div class="order-number pulse-animation">#<?php echo htmlspecialchars($order['order_number']); ?></div>

<?php if (isset($error)): ?>
    <div class="error-message">
        <?php echo htmlspecialchars($error); ?>
    </div>
<?php endif; ?>

<?php if (isset($_GET['success'])): ?>
    <div class="success-message">
        Status berhasil diupdate!
    </div>
<?php endif; ?>

<div class="user-info slide-in-left">
    <h4>👤 Informasi Customer</h4>
    <p><strong>Nama:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
    <p><strong>Email:</strong> <?php echo htmlspecialchars($order['customer_email']); ?></p>
    <p><strong>Telepon:</strong> <?php echo htmlspecialchars($order['customer_phone']); ?></p>
    <p><strong>Alamat:</strong> <?php echo htmlspecialchars($order['customer_address']); ?></p>
    
    <?php if ($order['user_name']): ?>
        <h4>🔐 Informasi User Account</h4>
        <p><strong>User:</strong> <?php echo htmlspecialchars($order['user_name']); ?></p>
        <p><strong>Email User:</strong> <?php echo htmlspecialchars($order['user_email']); ?></p>
    <?php else: ?>
        <p><em>Order dari guest user</em></p>
    <?php endif; ?>
</div>

<h4 class="slide-in-right">🛍️ Detail Produk</h4>
<?php if (count($order_items) > 0): ?>
    <div class="responsive-table">
        <table class="product-table">
            <thead>
                <tr>
                    <th>Produk</th>
                    <th style="text-align: center;">Jumlah</th>
                    <th style="text-align: right;">Harga</th>
                    <th style="text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($order_items as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                        <td style="text-align: center;"><?php echo htmlspecialchars($item['quantity']); ?></td>
                        <td style="text-align: right;">Rp<?php echo number_format($item['price'], 2); ?></td>
                        <td style="text-align: right;">Rp<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <p><em>Tidak ada data produk</em></p>
<?php endif; ?>

<div class="order-summary fade-in">
    <h4>📊 Ringkasan Order</h4>
    <div class="info-grid">
        <div class="info-card">
            <h5>Total Order</h5>
            <p>Rp<?php echo number_format($order['total_amount'], 2); ?></p>
        </div>
        <div class="info-card">
            <h5>Tanggal Order</h5>
            <p><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></p>
        </div>
        <?php if ($order['notes']): ?>
        <div class="info-card">
            <h5>Catatan</h5>
            <p><?php echo htmlspecialchars($order['notes']); ?></p>
        </div>
        <?php endif; ?>
    </div>
    <button class="print-button" onclick="printOrder(<?php echo $order_id; ?>)">🖨️ Print Order</button>
</div>

<div class="section-divider"></div>
<h3 class="gradient-text">📋 Riwayat Tracking</h3>
<?php if (count($trackings) == 0): ?>
    <div class="empty-tracking">
        Belum ada riwayat tracking.
    </div>
<?php else: ?>
    <div class="tracking-timeline">
        <?php foreach ($trackings as $t): ?>
            <div class="tracking-item">
                <div class="tracking-content">
                    <div class="status-<?php echo $t['status']; ?>">
                        <span class="status-indicator"></span>
                        <?php echo status_badge($t['status']); ?> - <?php echo htmlspecialchars($t['description']); ?>
                    </div>
                    <div class="tracking-time"><?php echo date('d/m/Y H:i', strtotime($t['created_at'])); ?></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="section-divider"></div>
<div class="status-update-form slide-in-right">
    <h3 class="gradient-text">🔄 Update Status</h3>
    <form method="post">
        <div class="form-group">
            <label>Status</label>
            <select name="status" required>
                <option value="">-- Pilih Status --</option>
                <option value="pending">Pending</option>
                <option value="processing">Processing</option>
                <option value="shipped">Shipped</option>
                <option value="delivered">Delivered</option>
                <option value="cancelled">Cancelled</option>
            </select>
        </div>
        <div class="form-group">
            <label>Keterangan</label>
            <textarea name="description" placeholder="Masukkan keterangan update status..."></textarea>
        </div>
        <button type="submit">Update Status</button>
    </form>
</div>
<a href="orders.php" class="back-link">← Kembali ke Daftar Order</a>

<?php if (!$isAjax): ?>
    <div class="floating-action" onclick="window.scrollTo({top: 0, behavior: 'smooth'})" title="Kembali ke atas">
        ↑
    </div>
    
    <script>
        // Smooth scrolling untuk anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });

        // Loading effect saat submit form
        document.querySelector('form').addEventListener('submit', function() {
            const button = this.querySelector('button[type="submit"]');
            const originalText = button.textContent;
            button.textContent = 'Updating...';
            button.disabled = true;
            button.classList.add('loading');
            
            // Reset setelah 3 detik jika tidak ada redirect
            setTimeout(() => {
                button.textContent = originalText;
                button.disabled = false;
                button.classList.remove('loading');
            }, 3000);
        });

        // Hover effect untuk tracking items
        document.querySelectorAll('.tracking-item').forEach(item => {
            item.addEventListener('mouseenter', function() {
                this.style.transform = 'translateX(10px)';
            });
            
            item.addEventListener('mouseleave', function() {
                this.style.transform = 'translateX(0)';
            });
        });

        // Auto-hide floating action button saat scroll
        let lastScrollTop = 0;
        const floatingAction = document.querySelector('.floating-action');
        
        window.addEventListener('scroll', function() {
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            
            if (scrollTop > lastScrollTop && scrollTop > 300) {
                floatingAction.style.transform = 'translateY(100px)';
            } else {
                floatingAction.style.transform = 'translateY(0)';
            }
            
            lastScrollTop = scrollTop;
        });

        // Parallax effect untuk background
        window.addEventListener('scroll', function() {
            const scrolled = window.pageYOffset;
            const parallax = document.querySelector('.container');
            const speed = scrolled * 0.5;
            parallax.style.transform = `translateY(${speed}px)`;
        });

        // Add success message if URL has success parameter
        if (window.location.search.includes('success=1')) {
            const successDiv = document.createElement('div');
            successDiv.className = 'success-message';
            successDiv.textContent = 'Status berhasil diupdate!';
            document.querySelector('.container').insertBefore(successDiv, document.querySelector('h2'));
            
            setTimeout(() => {
                successDiv.style.opacity = '0';
                setTimeout(() => successDiv.remove(), 500);
            }, 3000);
        }

        // Print functionality
        function printOrder(orderId) {
            window.open('print_order.php?order_id=' + orderId, '_blank');
        }

        // Add keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'p') {
                e.preventDefault();
                printOrder(<?php echo $order_id; ?>);
            }
            if (e.key === 'Escape') {
                window.location.href = 'orders.php';
            }
        });

        // Add tooltips
        const tooltips = document.querySelectorAll('[title]');
        tooltips.forEach(element => {
            element.addEventListener('mouseenter', function() {
                const tooltip = document.createElement('div');
                tooltip.className = 'tooltip';
                tooltip.textContent = this.getAttribute('title');
                tooltip.style.cssText = `
                    position: absolute;
                    background: #333;
                    color: white;
                    padding: 8px 12px;
                    border-radius: 6px;
                    font-size: 12px;
                    z-index: 1000;
                    pointer-events: none;
                    opacity: 0;
                    transition: opacity 0.3s;
                `;
                document.body.appendChild(tooltip);
                
                const rect = this.getBoundingClientRect();
                tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
                tooltip.style.top = rect.top - tooltip.offsetHeight - 10 + 'px';
                
                setTimeout(() => tooltip.style.opacity = '1', 10);
                
                this.addEventListener('mouseleave', function() {
                    tooltip.style.opacity = '0';
                    setTimeout(() => tooltip.remove(), 300);
                }, { once: true });
            });
        });
    </script>
<?php endif; ?>
<?php
if (!$isAjax) {
    echo '</div></body></html>';
}
?> 