<?php
require_once 'config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    echo "<h2>🔧 Running Database Fix Script</h2>";
    
    // Read SQL file
    $sql_file = 'fix_order_tracking.sql';
    if (!file_exists($sql_file)) {
        echo "<p style='color: red;'>❌ File $sql_file tidak ditemukan!</p>";
        exit;
    }
    
    $sql_content = file_get_contents($sql_file);
    $statements = explode(';', $sql_content);
    
    $success_count = 0;
    $error_count = 0;
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (empty($statement) || strpos($statement, '--') === 0) {
            continue;
        }
        
        try {
            $stmt = $db->prepare($statement);
            $result = $stmt->execute();
            
            if ($result) {
                echo "<p style='color: green;'>✅ Berhasil: " . substr($statement, 0, 50) . "...</p>";
                $success_count++;
            } else {
                echo "<p style='color: orange;'>⚠️ Warning: " . substr($statement, 0, 50) . "...</p>";
            }
        } catch (PDOException $e) {
            echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
            echo "<p style='color: gray;'>Statement: " . substr($statement, 0, 100) . "...</p>";
            $error_count++;
        }
    }
    
    echo "<hr>";
    echo "<h3>📊 Summary:</h3>";
    echo "<p>✅ Successful statements: $success_count</p>";
    echo "<p>❌ Failed statements: $error_count</p>";
    
    if ($error_count == 0) {
        echo "<p style='color: green; font-weight: bold;'>🎉 Database fix completed successfully!</p>";
        echo "<p><a href='admin/orders.php' style='background: #667eea; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Orders Dashboard</a></p>";
    } else {
        echo "<p style='color: red; font-weight: bold;'>⚠️ Some errors occurred. Please check the details above.</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database connection error: " . $e->getMessage() . "</p>";
}
?> 