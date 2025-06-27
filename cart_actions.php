<?php
session_start();
require_once 'includes/functions.php';

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

$action = $_GET['action'] ?? '';
$product_id = $_GET['id'] ?? 0;
$quantity = $_GET['quantity'] ?? 1;

switch ($action) {
    case 'add':
        addToCart($product_id, $quantity);
        break;
    case 'remove':
        removeFromCart($product_id);
        break;
    case 'update':
        updateCartQuantity($product_id, $quantity);
        break;
    case 'clear':
        clearCart();
        break;
    default:
        // Redirect back if no valid action
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
}

// Redirect back to previous page
header('Location: ' . $_SERVER['HTTP_REFERER']);
exit;

function addToCart($product_id, $quantity = 1) {
    $database = new Database();
    $db = $database->getConnection();
    
    // Get product details
    $stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
    
    if ($product) {
        // Check if product already in cart
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id]['quantity'] += $quantity;
        } else {
            $_SESSION['cart'][$product_id] = array(
                'id' => $product['id'],
                'name' => $product['name'],
                'price' => $product['price'],
                'quantity' => $quantity,
                'image' => $product['image'],
                'image_data' => $product['image_data'],
                'image_mime' => $product['image_mime']
            );
        }
        
        $_SESSION['cart_message'] = "Product added to cart successfully!";
    }
}

function removeFromCart($product_id) {
    if (isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
        $_SESSION['cart_message'] = "Product removed from cart!";
    }
}

function updateCartQuantity($product_id, $quantity) {
    if (isset($_SESSION['cart'][$product_id])) {
        if ($quantity > 0) {
            $_SESSION['cart'][$product_id]['quantity'] = $quantity;
        } else {
            removeFromCart($product_id);
        }
        $_SESSION['cart_message'] = "Cart updated successfully!";
    }
}

function clearCart() {
    $_SESSION['cart'] = array();
    $_SESSION['cart_message'] = "Cart cleared successfully!";
}
?> 