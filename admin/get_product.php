<?php
require_once 'includes/functions.php';

header('Content-Type: application/json');

if (isset($_GET['id'])) {
    $product = getProductById((int)$_GET['id']);
    
    if ($product) {
        echo json_encode($product);
    } else {
        echo json_encode(['error' => 'Product not found']);
    }
} else {
    echo json_encode(['error' => 'No ID provided']);
}
?> 