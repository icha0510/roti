<?php
session_start();

// Clear order success data
if (isset($_SESSION['order_success'])) {
    unset($_SESSION['order_success']);
}

// Return success response
header('Content-Type: application/json');
echo json_encode(['success' => true]);
?> 