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

// Cek apakah user datang dari proses pembayaran QRIS yang sudah selesai
$from_qr_payment = isset($_GET['from_qr_payment']) && $_GET['from_qr_payment'] === 'true';
?>
<!DOCTYPE html>
<html lang="en">
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
        .success-container {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            margin: 50px auto;
            max-width: 800px;
        }
        .success-icon {
            font-size: 80px;
            color: #27ae60;
            margin-bottom: 20px;
        }
        .qr-section {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-top: 30px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            border: 2px solid #e67e22;
        }
        .order-details {
            background: linear-gradient(135deg, #2c3e50, #34495e);
            color: white;
            border-radius: 15px;
            padding: 25px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header header--3">
        <div class="ps-container">
            <nav class="navigation">
                <div class="header-wrapper">
                    <div class="header-logo">
                        <a class="ps-logo" href="index.php">
                            <img src="images/logo-rotio.png" alt="">
                        </a>
                    </div>
                </div>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="ps-main">
        <div class="ps-container">
            <div class="success-container">
                <div class="text-center">
                    <i class="fa fa-check-circle success-icon"></i>
                    <h1 style="color: #27ae60; margin-bottom: 10px;">Pesanan Berhasil!</h1>
                    <p style="color: #7f8c8d; font-size: 18px;">Terima kasih telah berbelanja di Roti'O</p>
                </div>

                <div class="order-details">
                    <h3 style="margin-bottom: 20px; text-align: center;">
                        <i class="fa fa-shopping-bag" style="margin-right: 10px;"></i>
                        Detail Pesanan
                    </h3>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Order ID:</strong> <?php echo htmlspecialchars($order_data['order_number']); ?></p>
                            <p><strong>Nama:</strong> <?php echo htmlspecialchars($order_data['customer_name']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Total:</strong> Rp <?php echo number_format($order_data['total_amount'], 3); ?></p>
                            <p><strong>Meja:</strong> <?php echo htmlspecialchars($order_data['nomor_meja']); ?></p>
                        </div>
                    </div>
                </div>

                <?php if ($from_qr_payment): ?>
                <div class="qr-section">
                    <h3 style="text-align: center; color: #27ae60; margin-bottom: 25px;">
                        <i class="fa fa-check-circle" style="margin-right: 10px; color: #27ae60;"></i>
                        <?php if (isset($_GET['auto_payment']) && $_GET['auto_payment'] === 'true'): ?>
                            Pembayaran QRIS Otomatis Berhasil!
                        <?php else: ?>
                            Pembayaran QRIS Berhasil!
                        <?php endif; ?>
                    </h3>
                    <div style="text-align: center; background: #d4edda; border-radius: 15px; padding: 30px; border: 2px solid #c3e6cb;">
                        <i class="fa fa-credit-card" style="font-size: 48px; color: #27ae60; margin-bottom: 20px;"></i>
                        <h4 style="color: #155724; margin-bottom: 15px;">
                            <?php if (isset($_GET['auto_payment']) && $_GET['auto_payment'] === 'true'): ?>
                                Pembayaran Otomatis Telah Diverifikasi
                            <?php else: ?>
                                Pembayaran Telah Diverifikasi
                            <?php endif; ?>
                        </h4>
                        <p style="color: #155724; font-size: 16px; margin-bottom: 20px;">
                            <?php if (isset($_GET['auto_payment']) && $_GET['auto_payment'] === 'true'): ?>
                                Terima kasih! Pembayaran Anda melalui QRIS telah diproses otomatis dan pesanan sedang diproses.
                            <?php else: ?>
                                Terima kasih! Pembayaran Anda melalui QRIS telah berhasil diproses.
                            <?php endif; ?>
                        </p>
                        <div style="background: white; border-radius: 10px; padding: 20px; margin-top: 20px;">
                            <p style="margin: 0; color: #6c757d; font-size: 14px;">
                                <i class="fa fa-info-circle" style="color: #27ae60;"></i>
                                <?php if (isset($_GET['auto_payment']) && $_GET['auto_payment'] === 'true'): ?>
                                    Pesanan Anda telah otomatis diproses dan akan segera disiapkan
                                <?php else: ?>
                                    Pesanan Anda akan segera diproses oleh tim kami
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <div class="text-center" style="margin-top: 30px;">
                    <a href="index.php" class="btn btn-primary" style="background: linear-gradient(135deg, #e67e22, #f39c12); border: none; border-radius: 25px; padding: 12px 30px; font-weight: 600; margin-right: 15px;">
                        <i class="fa fa-home" style="margin-right: 8px;"></i>
                        Kembali ke Beranda
                    </a>
                    <a href="logo-orders.php" class="btn btn-outline-primary" style="border: 2px solid #e67e22; color: #e67e22; border-radius: 25px; padding: 12px 30px; font-weight: 600;">
                        <i class="fa fa-list" style="margin-right: 8px;"></i>
                        Lihat Pesanan Saya
                    </a>
                </div>
            </div>
        </div>
    </main>

    <!-- Scripts -->
    <script src="plugins/jquery/dist/jquery.min.js"></script>
    <script src="plugins/bootstrap/dist/js/bootstrap.min.js"></script>
</body>
</html>

<?php
// Clear order success data setelah ditampilkan
unset($_SESSION['order_success']);
unset($_SESSION['payment_method']);
?> 