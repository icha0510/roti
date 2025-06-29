<?php
session_start();
require_once 'includes/functions.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Anda harus login terlebih dahulu untuk melakukan order.";
    header('Location: login.php');
    exit;
}

// Cek apakah form dikirim
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Validasi input
    $errors = array();
    
    // Validasi nama
    if (empty($_POST['customer_name'])) {
        $errors[] = "Nama wajib diisi";
    }
    
    // Validasi email
    if (empty($_POST['customer_email'])) {
        $errors[] = "Email wajib diisi";
    } elseif (!filter_var($_POST['customer_email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid";
    }
    
    // Validasi telepon
    if (empty($_POST['customer_phone'])) {
        $errors[] = "Nomor telepon wajib diisi";
    }
    
    // Validasi alamat
    if (empty($_POST['customer_address'])) {
        $errors[] = "Alamat wajib diisi";
    }
    
    // Validasi produk
    if (empty($_POST['product_id'])) {
        $errors[] = "Produk wajib dipilih";
    }
    
    // Validasi quantity
    if (empty($_POST['product_quantity']) || $_POST['product_quantity'] < 1) {
        $errors[] = "Quantity wajib diisi dan minimal 1";
    }
    
    // Jika tidak ada error, proses order
    if (empty($errors)) {
        
        // Ambil data produk
        $product = getProductById($_POST['product_id']);
        
        if (!$product) {
            $errors[] = "Produk tidak ditemukan";
        } else {
            // Hitung total harga
            $price = $product['is_sale'] && $product['sale_price'] ? $product['sale_price'] : $product['price'];
            $total_amount = $price * $_POST['product_quantity'];
            
            // Siapkan data order
            $order_data = array(
                'user_id' => $_SESSION['user_id'],
                'customer_name' => $_POST['customer_name'],
                'customer_email' => $_POST['customer_email'],
                'customer_phone' => $_POST['customer_phone'],
                'customer_address' => $_POST['customer_address'],
                'product_id' => $_POST['product_id'],
                'product_name' => $product['name'],
                'product_quantity' => $_POST['product_quantity'],
                'price' => $price,
                'notes' => $_POST['notes'] ?? '',
                'total_amount' => $total_amount
            );
            
            // Simpan order ke database
            $result = saveOrder($order_data);
            
            if ($result['success']) {
                // Redirect ke halaman sukses
                $_SESSION['order_success'] = array(
                    'order_number' => $result['order_number'],
                    'order_id' => $result['order_id'],
                    'total_amount' => $total_amount
                );
                header('Location: order-success.php');
                exit;
            } else {
                $errors[] = "Gagal menyimpan order: " . $result['error'];
            }
        }
    }
    
    // Jika ada error, simpan ke session dan redirect kembali
    if (!empty($errors)) {
        $_SESSION['order_errors'] = $errors;
        $_SESSION['order_data'] = $_POST;
        header('Location: order-form.php');
        exit;
    }
    
} else {
    // Jika bukan POST request, redirect ke order form
    header('Location: order-form.php');
    exit;
}
?> 