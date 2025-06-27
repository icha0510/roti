<?php
require_once 'config/database.php';

// Fungsi untuk upload gambar dan konversi ke base64
function uploadImageToDatabase($file) {
    if ($file['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        
        if (!in_array($file['type'], $allowed_types)) {
            return ['error' => 'Tipe file tidak didukung. Gunakan JPG, PNG, GIF, atau WebP.'];
        }
        
        if ($file['size'] > 5 * 1024 * 1024) { // 5MB limit
            return ['error' => 'Ukuran file terlalu besar. Maksimal 5MB.'];
        }
        
        // Baca file dan konversi ke base64
        $image_data = file_get_contents($file['tmp_name']);
        $base64 = base64_encode($image_data);
        $mime_type = $file['type'];
        
        return [
            'success' => true,
            'data' => $base64,
            'mime_type' => $mime_type,
            'filename' => $file['name']
        ];
    }
    
    return ['error' => 'Error upload file.'];
}

// Fungsi untuk mengambil semua produk
function getAllProducts() {
    $database = new Database();
    $db = $database->getConnection();
    
    $sql = "SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            ORDER BY p.created_at DESC";
    
    $stmt = $db->prepare($sql);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fungsi untuk mengambil produk berdasarkan ID
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

// Fungsi untuk menambah produk
function addProduct($data) {
    $database = new Database();
    $db = $database->getConnection();
    
    $sql = "INSERT INTO products (name, slug, description, price, sale_price, category_id, image_data, image_mime, stock, is_featured, is_new, is_sale, rating) 
            VALUES (:name, :slug, :description, :price, :sale_price, :category_id, :image_data, :image_mime, :stock, :is_featured, :is_new, :is_sale, :rating)";
    
    $stmt = $db->prepare($sql);
    
    $stmt->bindParam(':name', $data['name']);
    $stmt->bindParam(':slug', $data['slug']);
    $stmt->bindParam(':description', $data['description']);
    $stmt->bindParam(':price', $data['price']);
    $stmt->bindParam(':sale_price', $data['sale_price']);
    $stmt->bindParam(':category_id', $data['category_id']);
    $stmt->bindParam(':image_data', $data['image_data']);
    $stmt->bindParam(':image_mime', $data['image_mime']);
    $stmt->bindParam(':stock', $data['stock']);
    $stmt->bindParam(':is_featured', $data['is_featured']);
    $stmt->bindParam(':is_new', $data['is_new']);
    $stmt->bindParam(':is_sale', $data['is_sale']);
    $stmt->bindParam(':rating', $data['rating']);
    
    return $stmt->execute();
}

// Fungsi untuk update produk
function updateProduct($id, $data) {
    $database = new Database();
    $db = $database->getConnection();
    
    if (isset($data['image_data']) && !empty($data['image_data'])) {
        $sql = "UPDATE products SET 
                name = :name, slug = :slug, description = :description, 
                price = :price, sale_price = :sale_price, category_id = :category_id,
                image_data = :image_data, image_mime = :image_mime,
                stock = :stock, is_featured = :is_featured, is_new = :is_new, 
                is_sale = :is_sale, rating = :rating 
                WHERE id = :id";
    } else {
        $sql = "UPDATE products SET 
                name = :name, slug = :slug, description = :description, 
                price = :price, sale_price = :sale_price, category_id = :category_id,
                stock = :stock, is_featured = :is_featured, is_new = :is_new, 
                is_sale = :is_sale, rating = :rating 
                WHERE id = :id";
    }
    
    $stmt = $db->prepare($sql);
    
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':name', $data['name']);
    $stmt->bindParam(':slug', $data['slug']);
    $stmt->bindParam(':description', $data['description']);
    $stmt->bindParam(':price', $data['price']);
    $stmt->bindParam(':sale_price', $data['sale_price']);
    $stmt->bindParam(':category_id', $data['category_id']);
    $stmt->bindParam(':stock', $data['stock']);
    $stmt->bindParam(':is_featured', $data['is_featured']);
    $stmt->bindParam(':is_new', $data['is_new']);
    $stmt->bindParam(':is_sale', $data['is_sale']);
    $stmt->bindParam(':rating', $data['rating']);
    
    if (isset($data['image_data']) && !empty($data['image_data'])) {
        $stmt->bindParam(':image_data', $data['image_data']);
        $stmt->bindParam(':image_mime', $data['image_mime']);
    }
    
    return $stmt->execute();
}

// Fungsi untuk menghapus produk
function deleteProduct($id) {
    $database = new Database();
    $db = $database->getConnection();
    
    $sql = "DELETE FROM products WHERE id = :id";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':id', $id);
    
    return $stmt->execute();
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

// Fungsi untuk menambah kategori
function addCategory($data) {
    $database = new Database();
    $db = $database->getConnection();
    
    $sql = "INSERT INTO categories (name, slug, description) VALUES (:name, :slug, :description)";
    $stmt = $db->prepare($sql);
    
    $stmt->bindParam(':name', $data['name']);
    $stmt->bindParam(':slug', $data['slug']);
    $stmt->bindParam(':description', $data['description']);
    
    return $stmt->execute();
}

// Fungsi untuk mengambil semua banner
function getAllBanners() {
    $database = new Database();
    $db = $database->getConnection();
    
    $sql = "SELECT * FROM banners ORDER BY sort_order ASC";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fungsi untuk menambah banner
function addBanner($data) {
    $database = new Database();
    $db = $database->getConnection();
    
    $sql = "INSERT INTO banners (title, subtitle, image_data, image_mime, link, badge_text, badge_type, sort_order, is_active) 
            VALUES (:title, :subtitle, :image_data, :image_mime, :link, :badge_text, :badge_type, :sort_order, :is_active)";
    
    $stmt = $db->prepare($sql);
    
    $stmt->bindParam(':title', $data['title']);
    $stmt->bindParam(':subtitle', $data['subtitle']);
    $stmt->bindParam(':image_data', $data['image_data']);
    $stmt->bindParam(':image_mime', $data['image_mime']);
    $stmt->bindParam(':link', $data['link']);
    $stmt->bindParam(':badge_text', $data['badge_text']);
    $stmt->bindParam(':badge_type', $data['badge_type']);
    $stmt->bindParam(':sort_order', $data['sort_order']);
    $stmt->bindParam(':is_active', $data['is_active']);
    
    return $stmt->execute();
}

// Fungsi untuk mengambil semua testimonial
function getAllTestimonials() {
    $database = new Database();
    $db = $database->getConnection();
    
    $sql = "SELECT * FROM testimonials ORDER BY created_at DESC";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fungsi untuk menambah testimonial
function addTestimonial($data) {
    $database = new Database();
    $db = $database->getConnection();
    
    $sql = "INSERT INTO testimonials (name, position, company, content, rating, image_data, image_mime, is_active) 
            VALUES (:name, :position, :company, :content, :rating, :image_data, :image_mime, :is_active)";
    
    $stmt = $db->prepare($sql);
    
    $stmt->bindParam(':name', $data['name']);
    $stmt->bindParam(':position', $data['position']);
    $stmt->bindParam(':company', $data['company']);
    $stmt->bindParam(':content', $data['content']);
    $stmt->bindParam(':rating', $data['rating']);
    $stmt->bindParam(':image_data', $data['image_data']);
    $stmt->bindParam(':image_mime', $data['image_mime']);
    $stmt->bindParam(':is_active', $data['is_active']);
    
    return $stmt->execute();
}

// Fungsi untuk mengambil semua posts
function getAllPosts() {
    $database = new Database();
    $db = $database->getConnection();
    
    $sql = "SELECT * FROM posts ORDER BY created_at DESC";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fungsi untuk menambah post
function addPost($data) {
    $database = new Database();
    $db = $database->getConnection();
    
    $sql = "INSERT INTO posts (title, slug, content, excerpt, image_data, image_mime, author, status) 
            VALUES (:title, :slug, :content, :excerpt, :image_data, :image_mime, :author, :status)";
    
    $stmt = $db->prepare($sql);
    
    $stmt->bindParam(':title', $data['title']);
    $stmt->bindParam(':slug', $data['slug']);
    $stmt->bindParam(':content', $data['content']);
    $stmt->bindParam(':excerpt', $data['excerpt']);
    $stmt->bindParam(':image_data', $data['image_data']);
    $stmt->bindParam(':image_mime', $data['image_mime']);
    $stmt->bindParam(':author', $data['author']);
    $stmt->bindParam(':status', $data['status']);
    
    return $stmt->execute();
}

// Fungsi untuk membuat slug dari string
function createSlug($string) {
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string)));
    return $slug;
}

// Fungsi untuk validasi input
function validateInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Fungsi untuk menampilkan pesan alert
function showAlert($message, $type = 'success') {
    return '<div class="alert alert-' . $type . ' alert-dismissible fade show" role="alert">
                ' . $message . '
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>';
}
?> 