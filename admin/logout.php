<?php
session_start();

// Update last_login admin jika ada session
if (isset($_SESSION['admin_id'])) {
    require_once 'includes/functions.php';
    
    $database = new Database();
    $db = $database->getConnection();
    
    $sql = "UPDATE admins SET last_login = NOW() WHERE id = :id";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':id', $_SESSION['admin_id']);
    $stmt->execute();
}

// Hapus semua data session
session_destroy();

// Redirect ke halaman login
header('Location: login.php');
exit();
?> 