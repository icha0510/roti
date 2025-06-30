<?php
session_start();
require_once 'includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $email = $_POST['email'] ?? '';
    
    if (empty($email)) {
        echo json_encode(['success' => false, 'message' => 'Email tidak boleh kosong']);
        exit;
    }
    
    switch ($action) {
        case 'subscribe':
            $result = subscribeNewsletter($email);
            echo json_encode($result);
            break;
            
        case 'unsubscribe':
            $result = unsubscribeNewsletter($email);
            echo json_encode($result);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Action tidak valid']);
            break;
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan']);
}
?> 