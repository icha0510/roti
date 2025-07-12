<?php
require_once 'includes/functions.php';

$message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add') {
            // Add new product
            $image_result = uploadImageToDatabase($_FILES['image']);
            
            if (isset($image_result['error'])) {
                $message = showAlert($image_result['error'], 'danger');
            } else {
                $data = [
                    'name' => validateInput($_POST['name']),
                    'slug' => createSlug($_POST['name']),
                    'description' => validateInput($_POST['description']),
                    'price' => (float)$_POST['price'],
                    'sale_price' => !empty($_POST['sale_price']) ? (float)$_POST['sale_price'] : null,
                    'category_id' => (int)$_POST['category_id'],
                    'image_data' => $image_result['data'],
                    'image_mime' => $image_result['mime_type'],
                    'stock' => (int)$_POST['stock'],
                    'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
                    'is_new' => isset($_POST['is_new']) ? 1 : 0,
                    'is_sale' => isset($_POST['is_sale']) ? 1 : 0,
                    'rating' => (float)$_POST['rating']
                ];
                
                if (addProduct($data)) {
                    $message = showAlert('Produk berhasil ditambahkan!', 'success');
                } else {
                    $message = showAlert('Error menambahkan produk!', 'danger');
                }
            }
        } elseif ($_POST['action'] == 'edit') {
            // Update product
            $id = (int)$_POST['id'];
            $data = [
                'name' => validateInput($_POST['name']),
                'slug' => createSlug($_POST['name']),
                'description' => validateInput($_POST['description']),
                'price' => (float)$_POST['price'],
                'sale_price' => !empty($_POST['sale_price']) ? (float)$_POST['sale_price'] : null,
                'category_id' => (int)$_POST['category_id'],
                'stock' => (int)$_POST['stock'],
                'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
                'is_new' => isset($_POST['is_new']) ? 1 : 0,
                'is_sale' => isset($_POST['is_sale']) ? 1 : 0,
                'rating' => (float)$_POST['rating']
            ];
            
            // Handle image upload if new image is selected
            if (!empty($_FILES['image']['name'])) {
                $image_result = uploadImageToDatabase($_FILES['image']);
                if (isset($image_result['error'])) {
                    $message = showAlert($image_result['error'], 'danger');
                } else {
                    $data['image_data'] = $image_result['data'];
                    $data['image_mime'] = $image_result['mime_type'];
                }
            }
            
            if (updateProduct($id, $data)) {
                $message = showAlert('Produk berhasil diperbarui!', 'success');
            } else {
                $message = showAlert('Error memperbarui produk!', 'danger');
            }
        }
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if (deleteProduct($id)) {
        $message = showAlert('Produk berhasil dihapus!', 'success');
    } else {
        $message = showAlert('Error menghapus produk!', 'danger');
    }
}

// Get data
$products = getAllProducts();
$categories = getAllCategories();
$edit_product = null;

// Get product for editing
if (isset($_GET['edit'])) {
    $edit_product = getProductById((int)$_GET['edit']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../images/logo-rotio.png" rel="icon">
    <title>Kelola Produk - Admin Roti'O</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #F2CB05;
            --primary-dark: #E6B800;
            --secondary-color: #343a40;
            --accent-color: #E74C3C;
            --success-color: #28a745;
            --info-color: #17a2b8;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --light-bg: #F8F9FA;
            --dark-bg: #343a40;
            --text-dark: #2C3E50;
            --text-light: #6C757D;
            --border-color: #E9ECEF;
            --shadow-light: 0 2px 10px rgba(0,0,0,0.08);
            --shadow-medium: 0 4px 20px rgba(0,0,0,0.12);
            --shadow-heavy: 0 8px 30px rgba(0,0,0,0.15);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f8f9fa;
            color: var(--text-dark);
        }

        /* Sidebar Styling - Mempertahankan desain asli */
        .sidebar {
            min-height: 100vh;
            background: #343a40;
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            z-index: 1000;
            box-shadow: var(--shadow-heavy);
        }

        .sidebar .nav-link {
            color: #fff;
            padding: 10px 20px;
            margin: 2px 10px;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .sidebar .nav-link:hover {
            background: #495057;
            transform: translateX(5px);
        }

        .sidebar .nav-link.active {
            background: #F2CB05;
            color: #343a40;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(242,203,5,0.3);
        }

        .sidebar .nav-link i {
            width: 20px;
            margin-right: 10px;
        }

        /* Main Content */
        .main-content {
            margin-left: 250px;
            padding: 2rem;
            transition: all 0.3s ease;
        }

        /* Page Header */
        .page-header {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-light);
            border: 1px solid var(--border-color);
        }

        .page-title {
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }

        .page-subtitle {
            color: var(--text-light);
            font-size: 1.1rem;
            font-weight: 400;
        }

        /* Content Cards */
        .content-card {
            background: white;
            border-radius: 15px;
            box-shadow: var(--shadow-light);
            border: 1px solid var(--border-color);
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .content-card:hover {
            box-shadow: var(--shadow-medium);
        }

        .card-header {
            background: #f8f9fa;
            padding: 1.5rem 2rem;
            border-bottom: 1px solid var(--border-color);
            font-weight: 600;
            color: var(--text-dark);
        }

        .card-body {
            padding: 2rem;
        }

        /* Form Styling */
        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid var(--border-color);
            padding: 0.75rem 1rem;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(242,203,5,0.25);
        }

        .form-label {
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }

        /* Table Styling */
        .table {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .table thead th {
            background: #f8f9fa;
            border: none;
            padding: 1rem;
            font-weight: 600;
            color: var(--text-dark);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.85rem;
        }

        .table tbody td {
            padding: 1rem;
            border: none;
            border-bottom: 1px solid var(--border-color);
            vertical-align: middle;
        }

        .table tbody tr:hover {
            background: rgba(242,203,5,0.05);
        }

        /* Buttons */
        .btn {
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: #F2CB05;
            color: #343a40;
            box-shadow: 0 4px 15px rgba(242,203,5,0.3);
        }

        .btn-primary:hover {
            background: #E6B800;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(242,203,5,0.4);
            color: #343a40;
        }

        .btn-success {
            background: #28a745;
            color: white;
        }

        .btn-warning {
            background: #ffc107;
            color: #343a40;
        }

        .btn-info {
            background: #17a2b8;
            color: white;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
        }

        /* Badges */
        .badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.8rem;
        }

        .bg-success {
            background: #28a745 !important;
        }

        .bg-danger {
            background: #dc3545 !important;
        }

        .bg-warning {
            background: #ffc107 !important;
            color: #343a40 !important;
        }

        .bg-info {
            background: #17a2b8 !important;
        }

        /* Product Image */
        .product-image {
            max-width: 80px;
            max-height: 80px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid var(--border-color);
        }

        /* Modal Styling */
        .modal-content {
            border-radius: 15px;
            border: none;
            box-shadow: var(--shadow-heavy);
        }

        .modal-header {
            background: #f8f9fa;
            border-bottom: 1px solid var(--border-color);
            border-radius: 15px 15px 0 0;
        }

        .modal-footer {
            background: #f8f9fa;
            border-top: 1px solid var(--border-color);
            border-radius: 0 0 15px 15px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }
            
            .page-title {
                font-size: 1.8rem;
            }
        }

        /* Animation */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in-up {
            animation: fadeInUp 0.6s ease-out;
        }

        /* Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb {
            background: #F2CB05;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #E6B800;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar position-fixed top-0 start-0 d-flex flex-column flex-shrink-0 p-3 text-white" style="width: 250px;">
        <a href="index.php" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
            <img src="../images/logo-rotio.png" alt="Roti'O" class="me-2" style="width: 30px; height: 30px;">
            <span class="fs-4">Admin Roti'O</span>
        </a>
        <hr>
        <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item">
                <a href="index.php" class="nav-link text-white">
                    <i class="fas fa-tachometer-alt me-2"></i>
                    Beranda
                </a>
            </li>
            <li>
                <a href="products.php" class="nav-link active">
                    <i class="fas fa-box me-2"></i>
                    Produk
                </a>
            </li>
            <li>
                <a href="categories.php" class="nav-link text-white">
                    <i class="fas fa-tags me-2"></i>
                    Kategori
                </a>
            </li>
            <li>
                <a href="posts.php" class="nav-link text-white">
                    <i class="fas fa-newspaper me-2"></i>
                    Artikel Blog
                </a>
            </li>
            <li>
                <a href="newsletter.php" class="nav-link text-white">
                    <i class="fas fa-envelope me-2"></i>
                    Buletin
                </a>
            </li>
            <li>
                <a href="orders.php" class="nav-link text-white">
                    <i class="fas fa-clipboard-list me-2"></i>
                    Pesanan
                </a>
            </li>
            <li>
                <a href="tables.php" class="nav-link text-white">
                    <i class="fas fa-chair me-2"></i>
                    Manajemen Meja
                </a>
            </li>
            <li>
                <a href="register.php" class="nav-link text-white">
                    <i class="fas fa-user-plus me-2"></i>
                    Tambah Admin
                </a>
            </li>
        </ul>
        <hr>
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-user-circle me-2"></i>
                <strong><?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?></strong>
            </a>
            <ul class="dropdown-menu dropdown-menu-dark text-small shadow" aria-labelledby="dropdownUser1">
                <li><a class="dropdown-item" href="../index.php" target="_blank">Lihat Website</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="logout.php">Keluar</a></li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Page Header -->
        <div class="page-header fade-in-up">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="page-title">Kelola Produk</h1>
                    <p class="page-subtitle">Tambah, edit, dan kelola produk Roti'O</p>
                </div>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
                    <i class="fas fa-plus"></i>
                    Tambah Produk
                </button>
            </div>
        </div>

        <!-- Alert Messages -->
        <?php if ($message): ?>
        <div class="fade-in-up" style="animation-delay: 0.1s;">
            <?php echo $message; ?>
        </div>
        <?php endif; ?>

        <!-- Products Table -->
        <div class="content-card fade-in-up" style="animation-delay: 0.2s;">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="fas fa-box me-2"></i>
                    Daftar Produk
                </h6>
                <span class="badge bg-primary"><?php echo count($products); ?> Produk</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Gambar</th>
                                <th>Nama Produk</th>
                                <th>Kategori</th>
                                <th>Harga</th>
                                <th>Stok</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($product['image_data'])): ?>
                                        <?php echo displayImage($product['image_data'], $product['image_mime'], 'product-image', $product['name']); ?>
                                    <?php else: ?>
                                        <img src="<?php echo $product['image']; ?>" class="product-image" alt="<?php echo $product['name']; ?>">
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div>
                                        <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                                        <div class="small text-muted"><?php echo htmlspecialchars(substr($product['description'], 0, 50)) . '...'; ?></div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark"><?php echo $product['category_name']; ?></span>
                                </td>
                                <td>
                                    <div>
                                        <strong class="text-success">Rp<?php echo number_format($product['price'], 0, ',', '.'); ?></strong>
                                        <?php if ($product['sale_price']): ?>
                                            <div class="small text-danger">Sale: Rp<?php echo number_format($product['sale_price'], 0, ',', '.'); ?></div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge <?php echo $product['stock'] > 0 ? 'bg-success' : 'bg-danger'; ?>">
                                        <?php echo $product['stock']; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex flex-column gap-1">
                                        <?php if ($product['is_featured']): ?>
                                            <span class="badge bg-warning">Featured</span>
                                        <?php endif; ?>
                                        <?php if ($product['is_new']): ?>
                                            <span class="badge bg-info">New</span>
                                        <?php endif; ?>
                                        <?php if ($product['is_sale']): ?>
                                            <span class="badge bg-danger">Sale</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button class="btn btn-sm btn-info" onclick="editProduct(<?php echo $product['id']; ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <a href="?delete=<?php echo $product['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus produk ini?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Product Modal -->
    <div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addProductModalLabel">
                        <i class="fas fa-plus me-2"></i>
                        Tambah Produk Baru
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="add">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Nama Produk</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="category_id" class="form-label">Kategori</label>
                                    <select class="form-select" id="category_id" name="category_id" required>
                                        <option value="">Pilih Kategori</option>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Deskripsi</label>
                            <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="price" class="form-label">Harga</label>
                                    <input type="number" class="form-control" id="price" name="price" step="0.01" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="sale_price" class="form-label">Harga Diskon</label>
                                    <input type="number" class="form-control" id="sale_price" name="sale_price" step="0.01">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="stock" class="form-label">Stok</label>
                                    <input type="number" class="form-control" id="stock" name="stock" required>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="image" class="form-label">Gambar Produk</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*" required>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured">
                                    <label class="form-check-label" for="is_featured">
                                        Produk Unggulan
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_new" name="is_new">
                                    <label class="form-check-label" for="is_new">
                                        Produk Baru
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_sale" name="is_sale">
                                    <label class="form-check-label" for="is_sale">
                                        Produk Diskon
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="rating" class="form-label">Rating</label>
                            <input type="number" class="form-control" id="rating" name="rating" step="0.1" min="0" max="5" value="0">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>
                            Simpan Produk
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Product Modal -->
    <div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editProductModalLabel">
                        <i class="fas fa-edit me-2"></i>
                        Edit Produk
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" enctype="multipart/form-data" id="editProductForm">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_name" class="form-label">Nama Produk</label>
                                    <input type="text" class="form-control" id="edit_name" name="name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_category_id" class="form-label">Kategori</label>
                                    <select class="form-select" id="edit_category_id" name="category_id" required>
                                        <option value="">Pilih Kategori</option>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_description" class="form-label">Deskripsi</label>
                            <textarea class="form-control" id="edit_description" name="description" rows="3" required></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="edit_price" class="form-label">Harga</label>
                                    <input type="number" class="form-control" id="edit_price" name="price" step="0.01" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="edit_sale_price" class="form-label">Harga Diskon</label>
                                    <input type="number" class="form-control" id="edit_sale_price" name="sale_price" step="0.01">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="edit_stock" class="form-label">Stok</label>
                                    <input type="number" class="form-control" id="edit_stock" name="stock" required>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_image" class="form-label">Gambar Produk (Opsional)</label>
                            <input type="file" class="form-control" id="edit_image" name="image" accept="image/*">
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="edit_is_featured" name="is_featured">
                                    <label class="form-check-label" for="edit_is_featured">
                                        Produk Unggulan
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="edit_is_new" name="is_new">
                                    <label class="form-check-label" for="edit_is_new">
                                        Produk Baru
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="edit_is_sale" name="is_sale">
                                    <label class="form-check-label" for="edit_is_sale">
                                        Produk Diskon
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_rating" class="form-label">Rating</label>
                            <input type="number" class="form-control" id="edit_rating" name="rating" step="0.1" min="0" max="5">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>
                            Update Produk
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editProduct(id) {
            // Fetch product data and populate modal
            fetch(`get_product.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('edit_id').value = data.id;
                    document.getElementById('edit_name').value = data.name;
                    document.getElementById('edit_category_id').value = data.category_id;
                    document.getElementById('edit_description').value = data.description;
                    document.getElementById('edit_price').value = data.price;
                    document.getElementById('edit_sale_price').value = data.sale_price || '';
                    document.getElementById('edit_stock').value = data.stock;
                    document.getElementById('edit_rating').value = data.rating;
                    document.getElementById('edit_is_featured').checked = data.is_featured == 1;
                    document.getElementById('edit_is_new').checked = data.is_new == 1;
                    document.getElementById('edit_is_sale').checked = data.is_sale == 1;
                    
                    new bootstrap.Modal(document.getElementById('editProductModal')).show();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading product data');
                });
        }
    </script>
</body>
</html> 