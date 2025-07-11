<?php
// Test file untuk memverifikasi payment method di checkout.php
session_start();

// Simulasi user login
$_SESSION['user_id'] = 1;
$_SESSION['user_name'] = 'Test User';

// Simulasi cart
$_SESSION['cart'] = array(
    1 => array(
        'id' => 1,
        'name' => 'Roti Tawar',
        'price' => 15000,
        'quantity' => 2,
        'image' => 'images/product1.jpg'
    ),
    2 => array(
        'id' => 2,
        'name' => 'Roti Coklat',
        'price' => 20000,
        'quantity' => 1,
        'image' => 'images/product2.jpg'
    )
);

echo "<h2>Test Payment Method di Checkout</h2>";
echo "<p>Status: User sudah login dan cart sudah terisi</p>";

// Test database connection
require_once 'config/database.php';
$database = new Database();
$db = $database->getConnection();

if ($db) {
    echo "<p style='color: green;'>✅ Database connection: OK</p>";
    
    // Test apakah kolom payment_method sudah ada
    try {
        $stmt = $db->prepare("DESCRIBE orders");
        $stmt->execute();
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (in_array('payment_method', $columns)) {
            echo "<p style='color: green;'>✅ Kolom payment_method: OK</p>";
        } else {
            echo "<p style='color: red;'>❌ Kolom payment_method: TIDAK ADA</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error cek kolom: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Database connection: GAGAL</p>";
}

// Test QR code generation
require_once 'generate_qr.php';
try {
    $qr_filename = generatePaymentQR(999, 'TEST-2024-0001', 50000, 'Test Customer');
    echo "<p style='color: green;'>✅ QR Code generation: OK</p>";
    echo "<p>File QR: $qr_filename</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ QR Code generation: " . $e->getMessage() . "</p>";
}

// Test form data
$test_form_data = array(
    'customer_name' => 'Test Customer',
    'customer_email' => 'test@example.com',
    'customer_phone' => '08123456789',
    'nomor_meja' => 'A1',
    'notes' => 'Test order',
    'payment_method' => 'qris'
);

echo "<h3>Test Form Data:</h3>";
echo "<pre style='background: #f8f9fa; padding: 15px; border-radius: 5px;'>";
print_r($test_form_data);
echo "</pre>";

// Test validation
$errors = array();
if (empty($test_form_data['payment_method'])) {
    $errors[] = "Metode pembayaran wajib dipilih";
}

if (empty($errors)) {
    echo "<p style='color: green;'>✅ Form validation: OK</p>";
} else {
    echo "<p style='color: red;'>❌ Form validation errors:</p>";
    echo "<ul>";
    foreach ($errors as $error) {
        echo "<li>$error</li>";
    }
    echo "</ul>";
}

echo "<hr>";
echo "<h3>Link Test:</h3>";
echo "<p><a href='checkout.php' target='_blank'>🔗 Buka Checkout Page</a></p>";
echo "<p><a href='test_qr.php' target='_blank'>🔗 Test QR Code Generation</a></p>";
echo "<p><a href='admin/verify_payment.php' target='_blank'>🔗 Admin Panel - Verifikasi Pembayaran</a></p>";

echo "<hr>";
echo "<h3>Instruksi Test:</h3>";
echo "<ol>";
echo "<li>Klik link 'Buka Checkout Page' di atas</li>";
echo "<li>Isi form checkout dengan data lengkap</li>";
echo "<li>Pilih metode pembayaran 'QRIS'</li>";
echo "<li>Lihat apakah QR code muncul di bawah total harga</li>";
echo "<li>Submit form dan lihat halaman order success</li>";
echo "<li>Cek apakah QR code muncul di halaman success</li>";
echo "</ol>";

// Clear session untuk test yang bersih
// unset($_SESSION['cart']);
?> 