<?php
session_start();
require_once 'includes/functions.php';

echo "<h1>Test Sistem Sign Up Admin</h1>";

// Test 1: Cek apakah sudah login
echo "<h2>Test 1: Status Login</h2>";
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    echo "✅ Admin sudah login sebagai: " . htmlspecialchars($_SESSION['admin_name'] ?? 'Unknown') . "<br>";
} else {
    echo "❌ Admin belum login<br>";
    echo "<a href='login.php'>Login dulu</a><br>";
}

// Test 2: Cek fungsi isEmailExists
echo "<h2>Test 2: Fungsi isEmailExists</h2>";
$test_email = "test@example.com";
if (function_exists('isEmailExists')) {
    $exists = isEmailExists($test_email);
    echo "Email $test_email " . ($exists ? "sudah ada" : "belum ada") . " di database<br>";
} else {
    echo "❌ Fungsi isEmailExists tidak ditemukan<br>";
}

// Test 3: Cek fungsi registerAdmin
echo "<h2>Test 3: Fungsi registerAdmin</h2>";
if (function_exists('registerAdmin')) {
    echo "✅ Fungsi registerAdmin tersedia<br>";
} else {
    echo "❌ Fungsi registerAdmin tidak ditemukan<br>";
}

// Test 4: Cek koneksi database
echo "<h2>Test 4: Koneksi Database</h2>";
try {
    require_once 'config/database.php';
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Koneksi database berhasil<br>";
    
    // Cek tabel admins
    $stmt = $pdo->query("SHOW TABLES LIKE 'admins'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Tabel admins ada<br>";
        
        // Cek struktur tabel
        $stmt = $pdo->query("DESCRIBE admins");
        echo "Struktur tabel admins:<br>";
        while ($row = $stmt->fetch()) {
            echo "- {$row['Field']} ({$row['Type']})<br>";
        }
    } else {
        echo "❌ Tabel admins tidak ada<br>";
    }
} catch (Exception $e) {
    echo "❌ Error koneksi database: " . $e->getMessage() . "<br>";
}

// Test 5: Cek file register.php
echo "<h2>Test 5: File register.php</h2>";
if (file_exists('register.php')) {
    echo "✅ File register.php ada<br>";
    
    // Cek proteksi session
    $content = file_get_contents('register.php');
    if (strpos($content, 'admin_logged_in') !== false) {
        echo "✅ Proteksi session ada di register.php<br>";
    } else {
        echo "❌ Proteksi session tidak ada di register.php<br>";
    }
} else {
    echo "❌ File register.php tidak ada<br>";
}

// Test 6: Cek .htaccess
echo "<h2>Test 6: File .htaccess</h2>";
if (file_exists('.htaccess')) {
    echo "✅ File .htaccess ada<br>";
    
    $content = file_get_contents('.htaccess');
    if (strpos($content, 'register.php') !== false) {
        echo "✅ Proteksi register.php di .htaccess ada<br>";
    } else {
        echo "❌ Proteksi register.php di .htaccess tidak ada<br>";
    }
} else {
    echo "❌ File .htaccess tidak ada<br>";
}

// Test 7: Cek menu di dashboard
echo "<h2>Test 7: Menu Dashboard</h2>";
if (file_exists('index.php')) {
    $content = file_get_contents('index.php');
    if (strpos($content, 'register.php') !== false) {
        echo "✅ Link register.php ada di dashboard<br>";
    } else {
        echo "❌ Link register.php tidak ada di dashboard<br>";
    }
    
    if (strpos($content, 'Add Admin') !== false) {
        echo "✅ Menu 'Add Admin' ada di dashboard<br>";
    } else {
        echo "❌ Menu 'Add Admin' tidak ada di dashboard<br>";
    }
} else {
    echo "❌ File index.php tidak ada<br>";
}

echo "<h2>Rekomendasi</h2>";
echo "<ul>";
echo "<li>Pastikan sudah login sebagai admin sebelum mengakses register.php</li>";
echo "<li>Gunakan menu 'Add Admin' di sidebar dashboard</li>";
echo "<li>Atau gunakan tombol 'Add New Admin' di Quick Actions</li>";
echo "<li>Jangan akses register.php langsung dari URL</li>";
echo "</ul>";

echo "<h2>Link Penting</h2>";
echo "<a href='login.php'>Login Admin</a><br>";
echo "<a href='index.php'>Dashboard</a><br>";
echo "<a href='register.php'>Add Admin (hanya jika sudah login)</a><br>";
?> 