<?php
echo "<h1>Test Database Connection</h1>";

// Test 1: Cek apakah bisa konek ke MySQL
echo "<h2>Test 1: Koneksi MySQL</h2>";
try {
    $pdo = new PDO("mysql:host=localhost", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Koneksi MySQL berhasil<br>";
} catch (Exception $e) {
    echo "❌ Error koneksi MySQL: " . $e->getMessage() . "<br>";
    exit;
}

// Test 2: Cek database bready_db
echo "<h2>Test 2: Database bready_db</h2>";
try {
    $pdo = new PDO("mysql:host=localhost;dbname=bready_db", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Database bready_db ada dan bisa diakses<br>";
} catch (Exception $e) {
    echo "❌ Error database bready_db: " . $e->getMessage() . "<br>";
    
    // Coba database bready
    echo "<h2>Test 2b: Database bready</h2>";
    try {
        $pdo = new PDO("mysql:host=localhost;dbname=bready", "root", "");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "✅ Database bready ada dan bisa diakses<br>";
    } catch (Exception $e2) {
        echo "❌ Error database bready: " . $e2->getMessage() . "<br>";
    }
}

// Test 3: List semua database
echo "<h2>Test 3: List Database</h2>";
try {
    $pdo = new PDO("mysql:host=localhost", "root", "");
    $stmt = $pdo->query("SHOW DATABASES");
    echo "Database yang tersedia:<br>";
    while ($row = $stmt->fetch()) {
        echo "- " . $row[0] . "<br>";
    }
} catch (Exception $e) {
    echo "❌ Error list database: " . $e->getMessage() . "<br>";
}
?> 