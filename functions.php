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
    return 'Rp' . number_format($price, 3);
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
?>