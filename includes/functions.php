<?php
require_once 'config/database.php';

// Fungsi untuk mengambil semua produk
function getAllProducts($limit = null) {
    $database = new Database();
    $db = $database->getConnection();
    
    $sql = "SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.stock > 0 
            ORDER BY p.created_at DESC";
    
    if ($limit) {
        $sql .= " LIMIT " . $limit;
    }
    
    $stmt = $db->prepare($sql);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fungsi untuk mengambil produk featured
function getFeaturedProducts($limit = 6) {
    $database = new Database();
    $db = $database->getConnection();
    
    $sql = "SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.is_featured = 1 AND p.stock > 0 
            ORDER BY p.created_at DESC 
            LIMIT " . $limit;
    
    $stmt = $db->prepare($sql);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fungsi untuk mengambil produk berdasarkan kategori
function getProductsByCategory($category_id, $limit = null) {
    $database = new Database();
    $db = $database->getConnection();
    
    $sql = "SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.category_id = :category_id AND p.stock > 0 
            ORDER BY p.created_at DESC";
    
    if ($limit) {
        $sql .= " LIMIT " . $limit;
    }
    
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':category_id', $category_id);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fungsi untuk mengambil semua kategori
function getAllCategories() {
    $database = new Database();
    $db = $database->getConnection();
    
    $sql = "SELECT * FROM categories ORDER BY name";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fungsi untuk mengambil semua banner
function getAllBanners() {
    $database = new Database();
    $db = $database->getConnection();
    
    $sql = "SELECT * FROM banners WHERE is_active = 1 ORDER BY sort_order ASC";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fungsi untuk mengambil semua testimonial
function getAllTestimonials($limit = null) {
    $database = new Database();
    $db = $database->getConnection();
    
    $sql = "SELECT * FROM testimonials WHERE is_active = 1 ORDER BY created_at DESC";
    
    if ($limit) {
        $sql .= " LIMIT " . $limit;
    }
    
    $stmt = $db->prepare($sql);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fungsi untuk mengambil semua posts
function getAllPosts($limit = 3) {
    $database = new Database();
    $db = $database->getConnection();
    
    $sql = "SELECT * FROM posts WHERE status = 'published' ORDER BY created_at DESC LIMIT " . $limit;
    $stmt = $db->prepare($sql);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fungsi untuk mengambil semua awards
function getAllAwards() {
    $database = new Database();
    $db = $database->getConnection();
    
    $sql = "SELECT * FROM awards WHERE is_active = 1 ORDER BY created_at DESC";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fungsi untuk format harga
function formatPrice($price) {
    return '£' . number_format($price, 2);
}

// Fungsi untuk menampilkan badge produk
function getProductBadge($product) {
    if ($product['is_sale'] && $product['sale_price']) {
        $discount = round((($product['price'] - $product['sale_price']) / $product['price']) * 100);
        return '<span class="ps-badge ps-badge--sale"><img src="images/icons/badge-brown.png" alt=""><i>' . $discount . '%</i></span>';
    } elseif ($product['is_new']) {
        return '<span class="ps-badge"><img src="images/icons/badge-red.png" alt=""><i>New</i></span>';
    }
    return '';
}

// Fungsi untuk menampilkan rating bintang
function displayRating($rating) {
    $html = '<select class="ps-rating">';
    for ($i = 1; $i <= 5; $i++) {
        $selected = ($i <= $rating) ? 'selected' : '';
        $html .= '<option value="' . $i . '" ' . $selected . '>' . $i . '</option>';
    }
    $html .= '</select>';
    return $html;
}

// Fungsi untuk menampilkan harga produk
function displayProductPrice($product) {
    if ($product['is_sale'] && $product['sale_price']) {
        return '<p class="ps-product__price"><del>' . formatPrice($product['price']) . '</del> ' . formatPrice($product['sale_price']) . '</p>';
    } else {
        return '<p class="ps-product__price">' . formatPrice($product['price']) . '</p>';
    }
}

// Fungsi untuk menampilkan gambar dari base64
function displayImage($base64_data, $mime_type = 'image/jpeg', $class = '', $alt = '', $style = '') {
    if (!empty($base64_data)) {
        $style_attr = $style ? ' style="' . $style . '"' : '';
        $class_attr = $class ? ' class="' . $class . '"' : '';
        $alt_attr = $alt ? ' alt="' . htmlspecialchars($alt) . '"' : '';
        
        return '<img src="data:' . $mime_type . ';base64,' . $base64_data . '"' . $class_attr . $alt_attr . $style_attr . '>';
    }
    return '';
}

// Fungsi untuk mendapatkan session ID
function getSessionId() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    return session_id();
}

// ===== FUNGSI ORDERS =====

// Fungsi untuk generate order number
function generateOrderNumber() {
    return 'ORD-' . date('Y') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
}

// Fungsi untuk mendapatkan semua orders
function getAllOrders() {
    $database = new Database();
    $db = $database->getConnection();
    
    $sql = "SELECT * FROM orders ORDER BY created_at DESC";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fungsi untuk mendapatkan order by ID
function getOrderById($order_id) {
    $database = new Database();
    $db = $database->getConnection();
    
    $sql = "SELECT * FROM orders WHERE id = :id";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':id', $order_id);
    $stmt->execute();
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Fungsi untuk mendapatkan order items
function getOrderItems($order_id) {
    $database = new Database();
    $db = $database->getConnection();
    
    $sql = "SELECT oi.*, p.name as product_name, p.image_data, p.image_mime 
            FROM order_items oi 
            LEFT JOIN products p ON oi.product_id = p.id 
            WHERE oi.order_id = :order_id";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':order_id', $order_id);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fungsi untuk update status order
function updateOrderStatus($order_id, $status, $description = '') {
    $database = new Database();
    $db = $database->getConnection();
    
    try {
        $db->beginTransaction();
        
        // Update status order
        $sql = "UPDATE orders SET status = :status WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $order_id);
        $stmt->execute();
        
        // Tambah tracking record
        if ($description) {
            $sql = "INSERT INTO order_tracking (order_id, status, description) VALUES (:order_id, :status, :description)";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':order_id', $order_id);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':description', $description);
            $stmt->execute();
        }
        
        $db->commit();
        return true;
        
    } catch (Exception $e) {
        $db->rollback();
        return false;
    }
}

// Fungsi untuk mendapatkan order tracking
function getOrderTracking($order_id) {
    $database = new Database();
    $db = $database->getConnection();
    
    $sql = "SELECT * FROM order_tracking WHERE order_id = :order_id ORDER BY created_at ASC";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':order_id', $order_id);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fungsi untuk mendapatkan label status
function getStatusLabel($status) {
    $labels = [
        'pending' => '<span class="badge bg-warning">Pending</span>',
        'processing' => '<span class="badge bg-info">Processing</span>',
        'shipped' => '<span class="badge bg-primary">Shipped</span>',
        'completed' => '<span class="badge bg-success">Completed</span>',
        'cancelled' => '<span class="badge bg-danger">Cancelled</span>'
    ];
    
    return $labels[$status] ?? '<span class="badge bg-secondary">' . ucfirst($status) . '</span>';
}

// Fungsi untuk menyimpan order baru
function saveOrder($order_data) {
    $database = new Database();
    $db = $database->getConnection();
    
    try {
        $db->beginTransaction();
        
        // Generate order number
        $order_number = generateOrderNumber();
        
        // Insert ke tabel orders
        $sql = "INSERT INTO orders (
            order_number, user_id, customer_name, customer_email, customer_phone, 
            customer_address, order_date, contact_time, product_name, 
            product_quantity, notes, total_amount, status, created_at
        ) VALUES (
            :order_number, :user_id, :customer_name, :customer_email, :customer_phone,
            :customer_address, :order_date, :contact_time, :product_name,
            :product_quantity, :notes, :total_amount, 'pending', NOW()
        )";
        
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':order_number', $order_number);
        $stmt->bindParam(':user_id', $order_data['user_id']);
        $stmt->bindParam(':customer_name', $order_data['customer_name']);
        $stmt->bindParam(':customer_email', $order_data['customer_email']);
        $stmt->bindParam(':customer_phone', $order_data['customer_phone']);
        $stmt->bindParam(':customer_address', $order_data['customer_address']);
        $stmt->bindParam(':order_date', $order_data['order_date']);
        $stmt->bindParam(':contact_time', $order_data['contact_time']);
        $stmt->bindParam(':product_name', $order_data['product_name']);
        $stmt->bindParam(':product_quantity', $order_data['product_quantity']);
        $stmt->bindParam(':notes', $order_data['notes']);
        $stmt->bindParam(':total_amount', $order_data['total_amount']);
        
        $stmt->execute();
        
        $order_id = $db->lastInsertId();
        
        // Insert ke tabel order_tracking
        $sql_tracking = "INSERT INTO order_tracking (
            order_id, status, description, created_at
        ) VALUES (
            :order_id, 'pending', 'Order has been placed successfully', NOW()
        )";
        
        $stmt_tracking = $db->prepare($sql_tracking);
        $stmt_tracking->bindParam(':order_id', $order_id);
        $stmt_tracking->execute();
        
        $db->commit();
        
        return array(
            'success' => true,
            'order_id' => $order_id,
            'order_number' => $order_number
        );
        
    } catch (Exception $e) {
        $db->rollback();
        return array(
            'success' => false,
            'error' => $e->getMessage()
        );
    }
}

// Fungsi untuk mendapatkan produk berdasarkan ID
function getProductById($id) {
    $database = new Database();
    $db = $database->getConnection();
    
    $sql = "SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.id = :id";
    
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Fungsi untuk mendapatkan produk yang tersedia untuk order
function getAvailableProducts() {
    $database = new Database();
    $db = $database->getConnection();
    
    $sql = "SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.stock > 0 
            ORDER BY p.name ASC";
    
    $stmt = $db->prepare($sql);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ===== FUNGSI AUTENTIKASI USER =====

// Fungsi untuk autentikasi user
function authenticateUser($email, $password) {
    $database = new Database();
    $db = $database->getConnection();
    
    $sql = "SELECT * FROM users WHERE email = :email AND is_active = 1";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password'])) {
        return $user;
    }
    
    return false;
}

// Fungsi untuk registrasi user baru
function registerUser($name, $email, $password, $phone = '', $address = '') {
    $database = new Database();
    $db = $database->getConnection();
    
    // Cek apakah email sudah terdaftar
    $sql = "SELECT id FROM users WHERE email = :email";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    if ($stmt->fetch()) {
        return 'Email sudah terdaftar!';
    }
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert user baru
    $sql = "INSERT INTO users (name, email, password, phone, address, is_active, created_at) 
            VALUES (:name, :email, :password, :phone, :address, 1, NOW())";
    
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password', $hashed_password);
    $stmt->bindParam(':phone', $phone);
    $stmt->bindParam(':address', $address);
    
    if ($stmt->execute()) {
        return 'success';
    } else {
        return 'Gagal mendaftar user!';
    }
}

// Fungsi untuk mendapatkan data user berdasarkan ID
function getUserById($user_id) {
    $database = new Database();
    $db = $database->getConnection();
    
    $sql = "SELECT id, name, email, phone, address, created_at FROM users WHERE id = :user_id AND is_active = 1";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Fungsi untuk logout
function logoutUser() {
    session_start();
    session_destroy();
    return true;
}

// Fungsi untuk mendapatkan order berdasarkan user ID
function getOrdersByUserId($user_id) {
    $database = new Database();
    $db = $database->getConnection();
    
    $sql = "SELECT * FROM orders WHERE user_id = :user_id ORDER BY created_at DESC";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fungsi untuk mendapatkan order berdasarkan user ID dengan limit
function getOrdersByUserIdWithLimit($user_id, $limit = 10) {
    $database = new Database();
    $db = $database->getConnection();
    
    $sql = "SELECT * FROM orders WHERE user_id = :user_id ORDER BY created_at DESC LIMIT :limit";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?> 