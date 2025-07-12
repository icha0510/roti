<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config/database.php';

// Cek apakah ekstensi GD tersedia
if (!extension_loaded('gd')) {
    die('Error: Ekstensi GD PHP tidak tersedia. Silakan aktifkan di php.ini');
}

// Cek apakah library QR Code tersedia
if (!file_exists('vendor/autoload.php')) {
    die('Error: Library QR Code tidak ditemukan. Silakan install dengan composer require endroid/qr-code');
}

require_once 'vendor/autoload.php';

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;

function generatePaymentQR($order_id, $order_number, $total_amount, $customer_name) {
    // Buat URL untuk halaman pembayaran - sesuaikan dengan path yang benar
    $payment_url = 'http://localhost/Latihan/roti/qr_payment_page.php';
    
    // Buat URL lengkap dengan parameter termasuk auto_pay=true
    $qr_url = $payment_url . '?order_id=' . urlencode($order_id) . 
              '&order_number=' . urlencode($order_number) . 
              '&total_amount=' . urlencode($total_amount) . 
              '&customer_name=' . urlencode($customer_name) . 
              '&auto_pay=true';
    
    // QR code berisi URL langsung, bukan JSON
    $qr_content = $qr_url;
    
    // Buat QR Code
    $qrCode = new QrCode($qr_content);
    $qrCode->setSize(300);
    $qrCode->setMargin(10);
    $qrCode->setErrorCorrectionLevel(new ErrorCorrectionLevelHigh());
    
    // Set warna QR code (orange theme)
    $qrCode->setForegroundColor(new Color(230, 126, 34)); // Orange
    $qrCode->setBackgroundColor(new Color(255, 255, 255)); // White
    
    // Buat writer
    $writer = new PngWriter();
    $result = $writer->write($qrCode);
    
    // Generate nama file
    $qr_filename = 'qr_codes/payment_' . $order_number . '_' . time() . '.png';
    
    // Pastikan direktori ada
    if (!is_dir('qr_codes')) {
        mkdir('qr_codes', 0755, true);
    }
    
    // Simpan QR code
    $result->saveToFile($qr_filename);
    
    return $qr_filename;
}

// Handle AJAX request untuk generate QR code
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'generate_qr') {
    $order_id = $_POST['order_id'];
    $order_number = $_POST['order_number'];
    $total_amount = $_POST['total_amount'];
    $customer_name = $_POST['customer_name'];
    
    try {
        $qr_filename = generatePaymentQR($order_id, $order_number, $total_amount, $customer_name);
        
        echo json_encode([
            'success' => true,
            'qr_filename' => $qr_filename,
            'order_number' => $order_number,
            'total_amount' => $total_amount
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Gagal generate QR code: ' . $e->getMessage()
        ]);
    }
    exit;
}

// Handle request langsung untuk generate QR code
if (isset($_GET['order_id']) && isset($_GET['order_number']) && isset($_GET['total_amount'])) {
    $order_id = $_GET['order_id'];
    $order_number = $_GET['order_number'];
    $total_amount = $_GET['total_amount'];
    $customer_name = $_GET['customer_name'] ?? 'Customer';
    
    try {
        $qr_filename = generatePaymentQR($order_id, $order_number, $total_amount, $customer_name);
        
        // Redirect ke halaman pembayaran
        $payment_url = 'http://localhost/Latihan/roti/qr_payment_page.php?order_id=' . urlencode($order_id) . 
                      '&order_number=' . urlencode($order_number) . 
                      '&total_amount=' . urlencode($total_amount) . 
                      '&customer_name=' . urlencode($customer_name) . 
                      '&auto_pay=true';
        
        header('Location: ' . $payment_url);
        exit;
        
    } catch (Exception $e) {
        die('Error: ' . $e->getMessage());
    }
}
?> 