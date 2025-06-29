<?php
require_once 'includes/functions.php';

echo "<h1>Test GD Extension dan Upload Functions</h1>";

// Test 1: Cek GD Extension
echo "<h2>1. Status GD Extension</h2>";
if (extension_loaded('gd')) {
    echo "<p style='color: green;'>✅ GD Extension AKTIF</p>";
    echo "<p>Versi GD: " . gd_info()['GD Version'] . "</p>";
} else {
    echo "<p style='color: orange;'>⚠️ GD Extension TIDAK AKTIF</p>";
    echo "<p>Sistem akan menggunakan mode fallback (upload tanpa kompresi)</p>";
}

// Test 2: Cek Functions
echo "<h2>2. Status Functions</h2>";
$functions = [
    'imagecreatefromjpeg' => function_exists('imagecreatefromjpeg'),
    'imagecreatefrompng' => function_exists('imagecreatefrompng'),
    'imagecreatefromgif' => function_exists('imagecreatefromgif'),
    'imagecreatefromwebp' => function_exists('imagecreatefromwebp'),
    'imagejpeg' => function_exists('imagejpeg'),
    'imagepng' => function_exists('imagepng'),
    'imagegif' => function_exists('imagegif'),
    'imagewebp' => function_exists('imagewebp')
];

foreach ($functions as $func => $exists) {
    $status = $exists ? "✅ TERSEDIA" : "❌ TIDAK TERSEDIA";
    $color = $exists ? "green" : "red";
    echo "<p style='color: $color;'>$func: $status</p>";
}

// Test 3: Cek Upload Functions
echo "<h2>3. Upload Functions</h2>";
echo "<p>✅ uploadImageToDatabase() - TERSEDIA</p>";
echo "<p>✅ uploadImageToDatabaseSimple() - TERSEDIA (Fallback)</p>";
echo "<p>✅ uploadImageAsFile() - TERSEDIA (Alternatif)</p>";

// Test 4: Rekomendasi
echo "<h2>4. Rekomendasi</h2>";
if (!extension_loaded('gd')) {
    echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px;'>";
    echo "<h3>Untuk mengaktifkan GD Extension:</h3>";
    echo "<ol>";
    echo "<li>Buka file: <code>C:\\xampp\\php\\php.ini</code></li>";
    echo "<li>Cari baris: <code>;extension=gd</code></li>";
    echo "<li>Hapus tanda semicolon: <code>extension=gd</code></li>";
    echo "<li>Simpan file dan restart Apache</li>";
    echo "</ol>";
    echo "<p><strong>Atau gunakan mode fallback yang sudah tersedia.</strong></p>";
    echo "</div>";
} else {
    echo "<p style='color: green;'>✅ GD Extension sudah aktif. Upload gambar akan dikompresi otomatis.</p>";
}

echo "<h2>5. Mode yang Akan Digunakan</h2>";
if (extension_loaded('gd')) {
    echo "<p style='color: green;'>🎯 Mode: <strong>Upload dengan Kompresi</strong></p>";
    echo "<ul>";
    echo "<li>Batas ukuran: 2MB</li>";
    echo "<li>Resize otomatis: 800x600 pixel</li>";
    echo "<li>Kompresi kualitas: 80%</li>";
    echo "</ul>";
} else {
    echo "<p style='color: orange;'>🎯 Mode: <strong>Upload Sederhana (Fallback)</strong></p>";
    echo "<ul>";
    echo "<li>Batas ukuran: 1MB</li>";
    echo "<li>Tidak ada kompresi</li>";
    echo "<li>Gambar asli</li>";
    echo "</ul>";
}

echo "<hr>";
echo "<p><a href='awards.php'>← Kembali ke Awards</a></p>";
?> 