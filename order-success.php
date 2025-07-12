<?php
session_start();
require_once 'includes/functions.php';
require_once 'generate_qr.php';

// Cek apakah ada data order success
if (!isset($_SESSION['order_success'])) {
    header('Location: index.php');
    exit;
}

$order_data = $_SESSION['order_success'];
$payment_method = isset($_SESSION['payment_method']) ? $_SESSION['payment_method'] : 'cash';
$from_qr_payment = isset($_GET['from_qr_payment']) && $_GET['from_qr_payment'] === 'true';
$auto_payment = isset($_GET['auto_payment']) && $_GET['auto_payment'] === 'true';

// Tentukan judul, ikon, dan pesan sesuai metode pembayaran
if ($payment_method === 'cash') {
    $success_title = 'Pembayaran Tunai Berhasil!';
    $success_icon = '<i class="fa fa-money" style="color: #27ae60;"></i>';
    $verify_title = 'Pembayaran Tunai Telah Diverifikasi';
    $success_message = 'Terima kasih! Pembayaran Anda secara tunai telah diterima dan pesanan sedang diproses.';
    $info_message = 'Pesanan Anda akan segera diproses oleh tim kami.';
} elseif ($payment_method === 'qris' && $auto_payment) {
    $success_title = 'Pembayaran QRIS Otomatis Berhasil!';
    $success_icon = '<i class="fa fa-bolt" style="color: #f39c12;"></i>';
    $verify_title = 'Pembayaran Otomatis Telah Diverifikasi';
    $success_message = 'Terima kasih! Pembayaran Anda melalui QRIS telah diproses otomatis dan pesanan sedang diproses.';
    $info_message = 'Pesanan Anda telah otomatis diproses dan akan segera disiapkan.';
} elseif ($payment_method === 'qris') {
    $success_title = 'Pembayaran QRIS Berhasil!';
    $success_icon = '<i class="fa fa-credit-card" style="color: #27ae60;"></i>';
    $verify_title = 'Pembayaran Telah Diverifikasi';
    $success_message = 'Terima kasih! Pembayaran Anda melalui QRIS telah berhasil diproses.';
    $info_message = 'Pesanan Anda akan segera diproses oleh tim kami.';
} else {
    $success_title = 'Pesanan Berhasil!';
    $success_icon = '<i class="fa fa-check-circle" style="color: #27ae60;"></i>';
    $verify_title = 'Pesanan Berhasil';
    $success_message = 'Terima kasih! Pesanan Anda telah berhasil.';
    $info_message = 'Pesanan Anda akan segera diproses oleh tim kami.';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Berhasil - Roti'O</title>
    <link href="https://fonts.googleapis.com/css?family=Kaushan+Script%7CLora:400,700" rel="stylesheet">
    <link rel="stylesheet" href="plugins/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="plugins/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            font-family: 'Lora', serif;
            background: #f8f9fa;
        }
        .success-main {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #e0f7fa 0%, #e8f5e9 100%);
        }
        .success-box {
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 8px 32px rgba(44,62,80,0.08);
            padding: 40px 30px 30px 30px;
            max-width: 600px;
            width: 100%;
            margin: 30px auto;
            border: 1.5px solid #e0e0e0;
            position: relative;
        }
        .success-title {
            font-size: 2em;
            font-weight: 700;
            color: #207744;
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            gap: 12px;
            justify-content: center;
        }
        .success-icon {
            font-size: 1.2em;
        }
        .verify-box {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 32px 20px 24px 20px;
            margin-bottom: 18px;
            box-shadow: 0 2px 8px rgba(44,62,80,0.04);
            text-align: center;
        }
        .verify-icon {
            font-size: 3em;
            color: #27ae60;
            margin-bottom: 12px;
        }
        .verify-title {
            font-size: 1.3em;
            font-weight: 600;
            color: #207744;
            margin-bottom: 10px;
        }
        .verify-message {
            color: #444;
            font-size: 1.08em;
            margin-bottom: 0;
        }
        .info-box {
            background: #e8f5e9;
            border-radius: 8px;
            padding: 14px 18px;
            margin-top: 18px;
            color: #207744;
            font-size: 1em;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .order-details {
            margin: 30px 0 18px 0;
            background: #f1f8e9;
            border-radius: 10px;
            padding: 18px 20px 10px 20px;
            border-left: 4px solid #27ae60;
        }
        .order-details h4 {
            color: #207744;
            font-size: 1.1em;
            font-weight: 700;
            margin-bottom: 12px;
        }
        .order-details p {
            margin-bottom: 8px;
            color: #444;
            font-size: 1em;
        }
        .action-buttons {
            text-align: center;
            margin-top: 30px;
        }
        .btn-custom {
            background: linear-gradient(135deg, #e67e22, #f39c12);
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 12px 32px;
            font-size: 1.1em;
            font-weight: 600;
            margin: 0 10px 10px 0;
            transition: all 0.2s;
            box-shadow: 0 4px 16px rgba(230,126,34,0.08);
        }
        .btn-custom:hover {
            background: linear-gradient(135deg, #f39c12, #e67e22);
            color: #fff;
            transform: translateY(-2px) scale(1.03);
        }
        .btn-outline-custom {
            background: #fff;
            color: #e67e22;
            border: 2px solid #e67e22;
        }
        .btn-outline-custom:hover {
            background: #e67e22;
            color: #fff;
        }
        @media (max-width: 600px) {
            .success-box {
                padding: 20px 8px 18px 8px;
            }
            .order-details {
                padding: 12px 8px 6px 8px;
            }
        }
    </style>
</head>
<body>
    <div class="success-main">
        <div class="success-box">
            <div class="success-title">
                <span class="success-icon"><?php echo $success_icon; ?></span>
                <?php echo $success_title; ?>
            </div>
            <div class="verify-box">
                <span class="verify-icon"><i class="fa fa-check-circle"></i></span>
                <div class="verify-title"><?php echo $verify_title; ?></div>
                <div class="verify-message"><?php echo $success_message; ?></div>
                <div class="info-box">
                    <i class="fa fa-info-circle"></i>
                    <?php echo $info_message; ?>
                </div>
            </div>
            <div class="order-details">
                <h4>Detail Pesanan</h4>
                <p><strong>Order ID:</strong> <?php echo htmlspecialchars($order_data['order_number']); ?></p>
                <p><strong>Nama:</strong> <?php echo htmlspecialchars($order_data['customer_name']); ?></p>
                <p><strong>Total:</strong> Rp <?php echo number_format($order_data['total_amount'], 3, ',', '.'); ?></p>
                <p><strong>Meja:</strong> <?php echo htmlspecialchars($order_data['nomor_meja']); ?></p>
            </div>
            <div class="action-buttons">
                <a href="index.php" class="btn btn-custom">
                    <i class="fa fa-home" style="margin-right: 8px;"></i> Kembali ke Beranda
                </a>
                <a href="logo-orders.php" class="btn btn-outline-custom btn-custom">
                    <i class="fa fa-list" style="margin-right: 8px;"></i> Lihat Pesanan Saya
                </a>
            </div>
        </div>
    </div>
</body>
</html>
<?php
unset($_SESSION['order_success']);
unset($_SESSION['payment_method']);
?> 