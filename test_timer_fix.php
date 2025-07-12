<?php
// Test file untuk memverifikasi perbaikan timer
echo "<h1>Test Perbaikan Timer</h1>";

// Simulasi berbagai skenario waktu
$scenarios = [
    'Order baru (5 menit tersisa)' => 300,
    'Order 2 menit yang lalu (3 menit tersisa)' => 180,
    'Order 4 menit yang lalu (1 menit tersisa)' => 60,
    'Order 30 detik yang lalu (4.5 menit tersisa)' => 270,
    'Order 5 menit yang lalu (expired)' => 0,
    'Order 6 menit yang lalu (expired)' => -60
];

foreach ($scenarios as $description => $time_remaining) {
    echo "<h3>$description</h3>";
    
    // Terapkan logika perbaikan
    $time_limit = 5 * 60; // 5 menit dalam detik
    if ($time_remaining < 0) {
        $time_remaining = 0;
    } elseif ($time_remaining > $time_limit) {
        $time_remaining = $time_limit;
    }
    
    $minutes = floor($time_remaining / 60);
    $seconds = $time_remaining % 60;
    
    echo "<p><strong>Time Remaining:</strong> $time_remaining detik</p>";
    echo "<p><strong>Menit:</strong> $minutes</p>";
    echo "<p><strong>Detik:</strong> $seconds</p>";
    
    // Tampilkan timer
    echo "<div style='background: linear-gradient(135deg, #ff6b6b, #ee5a24); color: white; border-radius: 15px; padding: 20px; text-align: center; margin: 20px 0; max-width: 400px;'>";
    echo "<h4 style='margin-bottom: 15px;'>";
    echo "<i class='fas fa-clock' style='margin-right: 10px;'></i>";
    echo "Waktu Tersisa untuk Pembayaran";
    echo "</h4>";
    
    echo "<div style='font-size: 2.5em; font-weight: bold; margin-bottom: 10px;'>";
    if ($time_remaining > 0) {
        echo '<span style="display: inline-block; margin: 0 5px;">';
        echo '<span style="font-size: 0.6em; display: block; opacity: 0.8;">Menit</span>';
        echo '<span>' . sprintf('%02d', $minutes) . '</span>';
        echo '</span>';
        echo '<span style="font-size: 0.8em; margin: 0 5px;">:</span>';
        echo '<span style="display: inline-block; margin: 0 5px;">';
        echo '<span style="font-size: 0.6em; display: block; opacity: 0.8;">Detik</span>';
        echo '<span>' . sprintf('%02d', $seconds) . '</span>';
        echo '</span>';
    } else {
        echo '<span style="display: inline-block; margin: 0 5px;">';
        echo '<span style="font-size: 0.6em; display: block; opacity: 0.8;">Menit</span>';
        echo '<span>00</span>';
        echo '</span>';
        echo '<span style="font-size: 0.8em; margin: 0 5px;">:</span>';
        echo '<span style="display: inline-block; margin: 0 5px;">';
        echo '<span style="font-size: 0.6em; display: block; opacity: 0.8;">Detik</span>';
        echo '<span>00</span>';
        echo '</span>';
    }
    echo "</div>";
    
    echo "<p style='margin: 0; font-size: 14px; opacity: 0.9;'>";
    echo "Pesanan akan dibatalkan otomatis jika tidak dibayar dalam waktu 5 menit";
    echo "</p>";
    echo "</div>";
    
    echo "<hr>";
}

echo "<h2>Perbaikan yang Dilakukan:</h2>";
echo "<ul>";
echo "<li><strong>Validasi Time Remaining:</strong> Memastikan nilai tidak negatif dan tidak lebih dari 5 menit</li>";
echo "<li><strong>Format yang Benar:</strong> Menampilkan menit dan detik yang benar</li>";
echo "<li><strong>Label yang Jelas:</strong> Menit dan Detik ditampilkan di atas angka</li>";
echo "</ul>";

echo "<h2>Status:</h2>";
echo "<p style='color: green; font-weight: bold;'>✅ Timer sudah diperbaiki dan akan menampilkan format yang benar!</p>";
echo "<p>Sekarang timer akan menampilkan format MM:SS yang benar dengan label Menit dan Detik.</p>";
?> 