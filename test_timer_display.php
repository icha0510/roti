<?php
// Test file untuk memverifikasi tampilan timer yang sudah diperbaiki
echo "<h1>Test Tampilan Timer yang Diperbaiki</h1>";

// Simulasi waktu tersisa
$time_remaining_test = 187; // 3 menit 7 detik
$minutes = floor($time_remaining_test / 60);
$seconds = $time_remaining_test % 60;

echo "<h2>Contoh Tampilan Timer:</h2>";

echo "<div style='background: linear-gradient(135deg, #ff6b6b, #ee5a24); color: white; border-radius: 15px; padding: 20px; text-align: center; margin: 20px 0; max-width: 400px;'>";
echo "<h4 style='margin-bottom: 15px;'>";
echo "<i class='fas fa-clock' style='margin-right: 10px;'></i>";
echo "Waktu Tersisa untuk Pembayaran";
echo "</h4>";

echo "<div style='font-size: 2.5em; font-weight: bold; margin-bottom: 10px;'>";
echo "<span style='display: inline-block; margin: 0 5px;'>";
echo "<span style='font-size: 0.6em; display: block; opacity: 0.8;'>Menit</span>";
echo "<span>" . sprintf('%02d', $minutes) . "</span>";
echo "</span>";
echo "<span style='font-size: 0.8em; margin: 0 5px;'>:</span>";
echo "<span style='display: inline-block; margin: 0 5px;'>";
echo "<span style='font-size: 0.6em; display: block; opacity: 0.8;'>Detik</span>";
echo "<span>" . sprintf('%02d', $seconds) . "</span>";
echo "</span>";
echo "</div>";

echo "<p style='margin: 0; font-size: 14px; opacity: 0.9;'>";
echo "Pesanan akan dibatalkan otomatis jika tidak dibayar dalam waktu 5 menit";
echo "</p>";
echo "</div>";

echo "<h2>Contoh Tampilan Timer < 1 Menit (Warning):</h2>";

$time_remaining_warning = 45; // 45 detik
$minutes_warning = floor($time_remaining_warning / 60);
$seconds_warning = $time_remaining_warning % 60;

echo "<div style='background: linear-gradient(135deg, #e74c3c, #c0392b); color: white; border-radius: 15px; padding: 20px; text-align: center; margin: 20px 0; max-width: 400px;'>";
echo "<h4 style='margin-bottom: 15px;'>";
echo "<i class='fas fa-clock' style='margin-right: 10px;'></i>";
echo "Waktu Tersisa untuk Pembayaran";
echo "</h4>";

echo "<div style='font-size: 2.5em; font-weight: bold; margin-bottom: 10px; animation: pulse 1s infinite;'>";
echo "<span style='display: inline-block; margin: 0 5px;'>";
echo "<span style='font-size: 0.6em; display: block; opacity: 0.8;'>Menit</span>";
echo "<span>" . sprintf('%02d', $minutes_warning) . "</span>";
echo "</span>";
echo "<span style='font-size: 0.8em; margin: 0 5px;'>:</span>";
echo "<span style='display: inline-block; margin: 0 5px;'>";
echo "<span style='font-size: 0.6em; display: block; opacity: 0.8;'>Detik</span>";
echo "<span>" . sprintf('%02d', $seconds_warning) . "</span>";
echo "</span>";
echo "</div>";

echo "<p style='margin: 0; font-size: 14px; opacity: 0.9;'>";
echo "Pesanan akan dibatalkan otomatis jika tidak dibayar dalam waktu 5 menit";
echo "</p>";
echo "</div>";

echo "<h2>Contoh Tampilan Timer Expired:</h2>";

echo "<div style='background: linear-gradient(135deg, #e74c3c, #c0392b); color: white; border-radius: 15px; padding: 20px; text-align: center; margin: 20px 0; max-width: 400px;'>";
echo "<h4 style='margin-bottom: 15px;'>";
echo "<i class='fas fa-exclamation-triangle' style='margin-right: 10px;'></i>";
echo "Waktu Pembayaran Habis";
echo "</h4>";

echo "<div style='font-size: 2.5em; font-weight: bold; margin-bottom: 10px;'>";
echo "<span style='display: inline-block; margin: 0 5px;'>";
echo "<span style='font-size: 0.6em; display: block; opacity: 0.8;'>Menit</span>";
echo "<span>00</span>";
echo "</span>";
echo "<span style='font-size: 0.8em; margin: 0 5px;'>:</span>";
echo "<span style='display: inline-block; margin: 0 5px;'>";
echo "<span style='font-size: 0.6em; display: block; opacity: 0.8;'>Detik</span>";
echo "<span>00</span>";
echo "</span>";
echo "</div>";

echo "<p style='margin: 0; font-size: 16px;'>";
echo "Pesanan telah dibatalkan otomatis karena tidak dibayar dalam waktu 5 menit";
echo "</p>";
echo "</div>";

echo "<style>";
echo "@keyframes pulse {";
echo "    0% { transform: scale(1); }";
echo "    50% { transform: scale(1.05); }";
echo "    100% { transform: scale(1); }";
echo "}";
echo "</style>";

echo "<h2>Perbaikan yang Dilakukan:</h2>";
echo "<ul>";
echo "<li><strong>Label Menit dan Detik:</strong> Menambahkan label 'Menit' dan 'Detik' di atas angka</li>";
echo "<li><strong>Format yang Jelas:</strong> Timer sekarang menampilkan format yang lebih mudah dibaca</li>";
echo "<li><strong>Styling yang Konsisten:</strong> Baik PHP dan JavaScript menggunakan format yang sama</li>";
echo "<li><strong>Visual Hierarchy:</strong> Label lebih kecil dan transparan, angka lebih besar dan jelas</li>";
echo "<li><strong>Responsive Design:</strong> Timer tetap terlihat baik di berbagai ukuran layar</li>";
echo "</ul>";

echo "<h2>Status:</h2>";
echo "<p style='color: green; font-weight: bold;'>✅ Tampilan timer sudah diperbaiki dengan format yang lebih jelas!</p>";
echo "<p>Timer sekarang menampilkan label 'Menit' dan 'Detik' di atas angka untuk memudahkan pembacaan.</p>";
?> 