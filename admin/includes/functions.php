<?php
require_once 'config/database.php';

// Inisialisasi koneksi database
$database = new Database();
$pdo = $database->getConnection();

// Fungsi untuk mendapatkan koneksi database
function getConnection() {
    global $pdo;
    return $pdo;
}

// Fungsi untuk upload gambar dan konversi ke base64
function uploadImageToDatabase($file) {
    if ($file['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        
        if (!in_array($file['type'], $allowed_types)) {
            return ['error' => 'Tipe file tidak didukung. Gunakan JPG, PNG, GIF, atau WebP.'];
        }
        
        if ($file['size'] > 2 * 1024 * 1024) { // 2MB limit (dikurangi dari 5MB)
            return ['error' => 'Ukuran file terlalu besar. Maksimal 2MB.'];
        }
        
        // Baca file dan kompres gambar
        $image_data = file_get_contents($file['tmp_name']);
        $mime_type = $file['type'];
        
        // Kompres gambar jika ukurannya terlalu besar
        if (strlen($image_data) > 500 * 1024) { // Jika lebih dari 500KB
            $compressed_data = compressImage($file['tmp_name'], $mime_type);
            if ($compressed_data !== false) {
                $image_data = $compressed_data;
            }
            // Jika kompresi gagal, gunakan data asli
        }
        
        $base64 = base64_encode($image_data);
        
        // Cek ukuran base64, jika masih terlalu besar, kompres lagi
        if (strlen($base64) > 1 * 1024 * 1024) { // Jika base64 lebih dari 1MB
            return ['error' => 'Gambar terlalu besar setelah kompresi. Gunakan gambar yang lebih kecil.'];
        }
        
        return [
            'success' => true,
            'data' => $base64,
            'mime_type' => $mime_type,
            'filename' => $file['name']
        ];
    }
    
    return ['error' => 'Error upload file.'];
}

// Fungsi untuk mengompres gambar
function compressImage($source_path, $mime_type) {
    // Cek apakah extension GD tersedia
    if (!extension_loaded('gd')) {
        // Jika GD tidak tersedia, return file asli
        return file_get_contents($source_path);
    }
    
    $max_width = 800;
    $max_height = 600;
    $quality = 80;
    
    // Baca gambar
    switch ($mime_type) {
        case 'image/jpeg':
            if (!function_exists('imagecreatefromjpeg')) {
                return file_get_contents($source_path);
            }
            $image = imagecreatefromjpeg($source_path);
            break;
        case 'image/png':
            if (!function_exists('imagecreatefrompng')) {
                return file_get_contents($source_path);
            }
            $image = imagecreatefrompng($source_path);
            break;
        case 'image/gif':
            if (!function_exists('imagecreatefromgif')) {
                return file_get_contents($source_path);
            }
            $image = imagecreatefromgif($source_path);
            break;
        case 'image/webp':
            if (!function_exists('imagecreatefromwebp')) {
                return file_get_contents($source_path);
            }
            $image = imagecreatefromwebp($source_path);
            break;
        default:
            return file_get_contents($source_path);
    }
    
    if (!$image) {
        return file_get_contents($source_path);
    }
    
    // Dapatkan dimensi asli
    $width = imagesx($image);
    $height = imagesy($image);
    
    // Hitung dimensi baru
    if ($width > $max_width || $height > $max_height) {
        $ratio = min($max_width / $width, $max_height / $height);
        $new_width = round($width * $ratio);
        $new_height = round($height * $ratio);
        
        // Buat gambar baru
        $new_image = imagecreatetruecolor($new_width, $new_height);
        
        // Preserve transparency untuk PNG dan GIF
        if ($mime_type === 'image/png' || $mime_type === 'image/gif') {
            imagealphablending($new_image, false);
            imagesavealpha($new_image, true);
            $transparent = imagecolorallocatealpha($new_image, 255, 255, 255, 127);
            imagefilledrectangle($new_image, 0, 0, $new_width, $new_height, $transparent);
        }
        
        // Resize gambar
        imagecopyresampled($new_image, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
        
        // Output ke buffer
        ob_start();
        switch ($mime_type) {
            case 'image/jpeg':
                if (function_exists('imagejpeg')) {
                    imagejpeg($new_image, null, $quality);
                }
                break;
            case 'image/png':
                if (function_exists('imagepng')) {
                    imagepng($new_image, null, 9);
                }
                break;
            case 'image/gif':
                if (function_exists('imagegif')) {
                    imagegif($new_image);
                }
                break;
            case 'image/webp':
                if (function_exists('imagewebp')) {
                    imagewebp($new_image, null, $quality);
                }
                break;
        }
        $compressed_data = ob_get_contents();
        ob_end_clean();
        
        // Bersihkan memory
        imagedestroy($image);
        imagedestroy($new_image);
        
        return $compressed_data;
    }
    
    // Jika ukuran sudah kecil, return asli
    imagedestroy($image);
    return file_get_contents($source_path);
}

// Fungsi alternatif untuk upload gambar sebagai file (jika database tidak mendukung gambar besar)
function uploadImageAsFile($file) {
    if ($file['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        
        if (!in_array($file['type'], $allowed_types)) {
            return ['error' => 'Tipe file tidak didukung. Gunakan JPG, PNG, GIF, atau WebP.'];
        }
        
        if ($file['size'] > 2 * 1024 * 1024) { // 2MB limit
            return ['error' => 'Ukuran file terlalu besar. Maksimal 2MB.'];
        }
        
        // Buat direktori upload jika belum ada
        $upload_dir = '../images/uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        // Generate nama file unik
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '_' . time() . '.' . $extension;
        $filepath = $upload_dir . $filename;
        
        // Kompres dan simpan gambar
        $image_data = file_get_contents($file['tmp_name']);
        if (strlen($image_data) > 500 * 1024) { // Jika lebih dari 500KB
            $compressed_data = compressImage($file['tmp_name'], $file['type']);
            if ($compressed_data !== false) {
                $image_data = $compressed_data;
            }
        }
        
        // Simpan file
        if (file_put_contents($filepath, $image_data)) {
            return [
                'success' => true,
                'filepath' => 'images/uploads/' . $filename,
                'mime_type' => $file['type'],
                'filename' => $filename
            ];
        } else {
            return ['error' => 'Gagal menyimpan file.'];
        }
    }
    
    return ['error' => 'Error upload file.'];
}

// Fungsi untuk mengambil semua produk
function getAllProducts() {
    global $pdo;
    
    $sql = "SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            ORDER BY p.created_at DESC";
    
    $stmt = $pdo->prepare($sql);
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
    global $pdo;
    
    $sql = "SELECT * FROM categories ORDER BY name";
    $stmt = $pdo->prepare($sql);
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

// Fungsi untuk mengambil kategori berdasarkan ID
function getCategoryById($id) {
    $database = new Database();
    $db = $database->getConnection();
    
    $sql = "SELECT * FROM categories WHERE id = :id";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Fungsi untuk update kategori
function updateCategory($id, $data) {
    $database = new Database();
    $db = $database->getConnection();
    
    $sql = "UPDATE categories SET name = :name, slug = :slug, description = :description WHERE id = :id";
    $stmt = $db->prepare($sql);
    
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':name', $data['name']);
    $stmt->bindParam(':slug', $data['slug']);
    $stmt->bindParam(':description', $data['description']);
    
    return $stmt->execute();
}

// Fungsi untuk menghapus kategori
function deleteCategory($id) {
    $database = new Database();
    $db = $database->getConnection();
    
    $sql = "DELETE FROM categories WHERE id = :id";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':id', $id);
    
    return $stmt->execute();
}

// Fungsi untuk mengambil semua testimonial
function getAllTestimonials() {
    global $pdo;
    
    $sql = "SELECT * FROM testimonials ORDER BY created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fungsi untuk menambah testimonial
function addTestimonial($data) {
    $database = new Database();
    $db = $database->getConnection();
    
    $sql = "INSERT INTO testimonials (name, position, content, rating, image_data, image_mime) 
            VALUES (:name, :position, :content, :rating, :image_data, :image_mime)";
    
    $stmt = $db->prepare($sql);
    
    $stmt->bindParam(':name', $data['name']);
    $stmt->bindParam(':position', $data['position']);
    $stmt->bindParam(':content', $data['content']);
    $stmt->bindParam(':rating', $data['rating']);
    $stmt->bindParam(':image_data', $data['image_data']);
    $stmt->bindParam(':image_mime', $data['image_mime']);
    
    return $stmt->execute();
}

// Fungsi untuk menghapus testimonial
function deleteTestimonial($id) {
    $database = new Database();
    $db = $database->getConnection();
    
    $sql = "DELETE FROM testimonials WHERE id = :id";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':id', $id);
    
    return $stmt->execute();
}

// Fungsi untuk mengambil semua posts
function getAllPosts() {
    global $pdo;
    
    $sql = "SELECT * FROM posts ORDER BY created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fungsi untuk menambah post
function addPost($data) {
    $database = new Database();
    $db = $database->getConnection();
    
    $sql = "INSERT INTO posts (title, slug, content, excerpt, author, image_data, image_mime, status) 
            VALUES (:title, :slug, :content, :excerpt, :author, :image_data, :image_mime, :status)";
    
    $stmt = $db->prepare($sql);
    
    $stmt->bindParam(':title', $data['title']);
    $stmt->bindParam(':slug', $data['slug']);
    $stmt->bindParam(':content', $data['content']);
    $stmt->bindParam(':excerpt', $data['excerpt']);
    $stmt->bindParam(':author', $data['author']);
    $stmt->bindParam(':image_data', $data['image_data']);
    $stmt->bindParam(':image_mime', $data['image_mime']);
    $stmt->bindParam(':status', $data['status']);
    
    return $stmt->execute();
}

// Fungsi untuk menghapus post
function deletePost($id) {
    $database = new Database();
    $db = $database->getConnection();
    
    $sql = "DELETE FROM posts WHERE id = :id";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':id', $id);
    
    return $stmt->execute();
}

// Fungsi untuk mengambil semua awards
function getAllAwards() {
    $database = new Database();
    $db = $database->getConnection();
    
    $sql = "SELECT * FROM awards ORDER BY year_start DESC";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fungsi untuk menambah award
function addAward($data) {
    $database = new Database();
    $db = $database->getConnection();
    
    $sql = "INSERT INTO awards (title, description, year_start, year_end, image_data, image_mime, is_active) 
            VALUES (:title, :description, :year_start, :year_end, :image_data, :image_mime, :is_active)";
    
    $stmt = $db->prepare($sql);
    
    $stmt->bindParam(':title', $data['title']);
    $stmt->bindParam(':description', $data['description']);
    $stmt->bindParam(':year_start', $data['year_start']);
    $stmt->bindParam(':year_end', $data['year_end']);
    $stmt->bindParam(':image_data', $data['image_data']);
    $stmt->bindParam(':image_mime', $data['image_mime']);
    $stmt->bindParam(':is_active', $data['is_active']);
    
    return $stmt->execute();
}

// Fungsi untuk menghapus award
function deleteAward($id) {
    $database = new Database();
    $db = $database->getConnection();
    
    $sql = "DELETE FROM awards WHERE id = :id";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':id', $id);
    
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

// Fungsi untuk menampilkan gambar dari data base64
function displayImage($image_data, $mime_type, $class = '', $alt = '') {
    if (!empty($image_data)) {
        // Jika data sudah dalam format base64, gunakan langsung
        if (is_string($image_data) && base64_decode($image_data, true) !== false) {
            return '<img src="data:' . $mime_type . ';base64,' . $image_data . '" class="' . $class . '" alt="' . $alt . '" style="max-width: 100px; height: auto;">';
        }
        // Jika data dalam format binary, konversi ke base64
        else {
            $base64 = base64_encode($image_data);
            return '<img src="data:' . $mime_type . ';base64,' . $base64 . '" class="' . $class . '" alt="' . $alt . '" style="max-width: 100px; height: auto;">';
        }
    }
    return '<img src="../images/products/default.jpg" class="' . $class . '" alt="' . $alt . '" style="max-width: 100px; height: auto;">';
}

// Fungsi upload gambar sederhana tanpa kompresi (fallback jika GD tidak tersedia)
function uploadImageToDatabaseSimple($file) {
    if ($file['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        
        if (!in_array($file['type'], $allowed_types)) {
            return ['error' => 'Tipe file tidak didukung. Gunakan JPG, PNG, GIF, atau WebP.'];
        }
        
        if ($file['size'] > 1 * 1024 * 1024) { // 1MB limit untuk versi sederhana
            return ['error' => 'Ukuran file terlalu besar. Maksimal 1MB untuk versi tanpa kompresi.'];
        }
        
        // Baca file langsung tanpa kompresi
        $image_data = file_get_contents($file['tmp_name']);
        $mime_type = $file['type'];
        
        $base64 = base64_encode($image_data);
        
        // Cek ukuran base64
        if (strlen($base64) > 1 * 1024 * 1024) { // Jika base64 lebih dari 1MB
            return ['error' => 'Gambar terlalu besar. Gunakan gambar yang lebih kecil atau aktifkan extension GD untuk kompresi.'];
        }
        
        return [
            'success' => true,
            'data' => $base64,
            'mime_type' => $mime_type,
            'filename' => $file['name']
        ];
    }
    
    return ['error' => 'Error upload file.'];
}

// Fungsi untuk autentikasi admin
function authenticateAdmin($email, $password) {
    $database = new Database();
    $db = $database->getConnection();
    
    $sql = "SELECT * FROM admins WHERE email = :email AND is_active = 1";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin && password_verify($password, $admin['password'])) {
        return $admin;
    }
    
    return false;
}

// Fungsi untuk mengecek apakah email sudah terdaftar
function isEmailExists($email) {
    $database = new Database();
    $db = $database->getConnection();
    
    $sql = "SELECT COUNT(*) FROM admins WHERE email = :email";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    return $stmt->fetchColumn() > 0;
}

// Fungsi untuk mendaftarkan admin baru
function registerAdmin($data) {
    $database = new Database();
    $db = $database->getConnection();
    
    $sql = "INSERT INTO admins (name, email, password, role, is_active, created_at) 
            VALUES (:name, :email, :password, :role, :is_active, NOW())";
    
    $stmt = $db->prepare($sql);
    
    $stmt->bindParam(':name', $data['name']);
    $stmt->bindParam(':email', $data['email']);
    $stmt->bindParam(':password', $data['password']);
    $stmt->bindParam(':role', $data['role']);
    $stmt->bindParam(':is_active', $data['is_active']);
    
    return $stmt->execute();
}

// Fungsi untuk mengambil data admin berdasarkan ID
function getAdminById($id) {
    $database = new Database();
    $db = $database->getConnection();
    
    $sql = "SELECT id, name, email, role, is_active, created_at FROM admins WHERE id = :id";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Fungsi untuk update password admin
function updateAdminPassword($id, $new_password) {
    $database = new Database();
    $db = $database->getConnection();
    
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    
    $sql = "UPDATE admins SET password = :password WHERE id = :id";
    $stmt = $db->prepare($sql);
    
    $stmt->bindParam(':password', $hashed_password);
    $stmt->bindParam(':id', $id);
    
    return $stmt->execute();
}
?> 