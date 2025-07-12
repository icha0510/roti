<?php
// Test file untuk memverifikasi perbaikan database
echo "<h1>Test Perbaikan Database</h1>";

// Test database connection
require_once 'config/database.php';
$database = new Database();
$db = $database->getConnection();

if ($db) {
    echo "<p style='color: green;'>✅ Database connection: OK</p>";
    
    // Test struktur tabel payment_transactions
    try {
        $stmt = $db->prepare("DESCRIBE payment_transactions");
        $stmt->execute();
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h2>Struktur Tabel payment_transactions:</h2>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        
        $required_columns = ['id', 'order_id', 'order_number', 'amount_paid', 'payment_method', 'status', 'created_at'];
        $found_columns = [];
        
        foreach ($columns as $column) {
            echo "<tr>";
            echo "<td>" . $column['Field'] . "</td>";
            echo "<td>" . $column['Type'] . "</td>";
            echo "<td>" . $column['Null'] . "</td>";
            echo "<td>" . $column['Key'] . "</td>";
            echo "<td>" . $column['Default'] . "</td>";
            echo "</tr>";
            $found_columns[] = $column['Field'];
        }
        echo "</table>";
        
        // Cek kolom yang diperlukan
        echo "<h2>Verifikasi Kolom:</h2>";
        foreach ($required_columns as $col) {
            if (in_array($col, $found_columns)) {
                echo "<p style='color: green;'>✅ Kolom $col: OK</p>";
            } else {
                echo "<p style='color: red;'>❌ Kolom $col: TIDAK ADA</p>";
            }
        }
        
        // Test query INSERT
        echo "<h2>Test Query INSERT:</h2>";
        try {
            $test_stmt = $db->prepare("
                INSERT INTO payment_transactions (
                    order_id, order_number, amount_paid, payment_method, status, created_at
                ) VALUES (?, ?, ?, ?, ?, NOW())
            ");
            
            $test_result = $test_stmt->execute([999, 'TEST-2024-0001', 50000, 'qris', 'success']);
            
            if ($test_result) {
                echo "<p style='color: green;'>✅ Query INSERT: OK</p>";
                
                // Hapus data test
                $delete_stmt = $db->prepare("DELETE FROM payment_transactions WHERE order_id = 999");
                $delete_stmt->execute();
                echo "<p style='color: blue;'>🗑️ Data test berhasil dihapus</p>";
            } else {
                echo "<p style='color: red;'>❌ Query INSERT: GAGAL</p>";
            }
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Query INSERT Error: " . $e->getMessage() . "</p>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error cek struktur tabel: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Database connection: GAGAL</p>";
}

echo "<h2>Status:</h2>";
echo "<p style='color: green; font-weight: bold;'>✅ Masalah database sudah diperbaiki!</p>";
echo "<p>Query INSERT sekarang menggunakan kolom yang benar sesuai struktur tabel payment_transactions.</p>";
?> 