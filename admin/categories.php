<?php
require_once 'includes/functions.php';

$action = $_GET['action'] ?? 'list';
$message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'add') {
        $data = [
            'name' => $_POST['name'],
            'slug' => createSlug($_POST['name']),
            'description' => $_POST['description'] ?? ''
        ];
        
        if (addCategory($data)) {
            $message = 'Kategori berhasil ditambahkan!';
            $action = 'list';
        } else {
            $message = 'Error menambahkan kategori.';
        }
    } elseif ($action === 'edit' && isset($_POST['id'])) {
        $id = $_POST['id'];
        $data = [
            'name' => $_POST['name'],
            'slug' => createSlug($_POST['name']),
            'description' => $_POST['description'] ?? ''
        ];
        
        if (updateCategory($id, $data)) {
            $message = 'Kategori berhasil diperbarui!';
            $action = 'list';
        } else {
            $message = 'Error memperbarui kategori.';
        }
    }
}

// Handle delete action
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    if (deleteCategory($id)) {
        $message = 'Kategori berhasil dihapus!';
    } else {
        $message = 'Error menghapus kategori.';
    }
    $action = 'list';
}

$categories = getAllCategories();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../images/logo-rotio.png" rel="icon">
    <title>Kategori - Admin Roti'O</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background: #343a40;
        }
        .sidebar .nav-link {
            color: #fff;
            padding: 10px 20px;
        }
        .sidebar .nav-link:hover {
            background: #495057;
        }
        .sidebar .nav-link.active {
            background: #F2CB05;
        }
        .main-content {
            margin-left: 250px;
        }
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
            }
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
                <a href="products.php" class="nav-link text-white">
                    <i class="fas fa-box me-2"></i>
                    Produk
                </a>
            </li>
            <li>
                <a href="categories.php" class="nav-link active">
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
        <div class="container-fluid p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Kategori</h1>
                <a href="?action=add" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Tambah Kategori Baru
                </a>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($action === 'list'): ?>
                <!-- Categories List -->
                <div class="card shadow">
                    <div class="card-header">
                        <h5 class="mb-0">Semua Kategori</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nama</th>
                                        <th>Slug</th>
                                        <th>Deskripsi</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($categories as $category): ?>
                                    <tr>
                                        <td><?php echo $category['id']; ?></td>
                                        <td><?php echo $category['name']; ?></td>
                                        <td><?php echo $category['slug']; ?></td>
                                        <td><?php echo $category['description']; ?></td>
                                        <td>
                                            <a href="?action=edit&id=<?php echo $category['id']; ?>" class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="?delete=<?php echo $category['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            <?php elseif ($action === 'add'): ?>
                <!-- Add Category Form -->
                <div class="card shadow">
                    <div class="card-header">
                        <h5 class="mb-0">Add New Category</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label for="name" class="form-label">Category Name</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">Add Category</button>
                                <a href="categories.php" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>

            <?php elseif ($action === 'edit' && isset($_GET['id'])): ?>
                <!-- Edit Category Form -->
                <?php 
                $category_id = $_GET['id'];
                $category = getCategoryById($category_id);
                if (!$category) {
                    echo '<div class="alert alert-danger">Category not found.</div>';
                    exit;
                }
                ?>
                <div class="card shadow">
                    <div class="card-header">
                        <h5 class="mb-0">Edit Category</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="id" value="<?php echo $category['id']; ?>">
                            <div class="mb-3">
                                <label for="name" class="form-label">Category Name</label>
                                <input type="text" class="form-control" id="name" name="name" value="<?php echo $category['name']; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3"><?php echo $category['description']; ?></textarea>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">Update Category</button>
                                <a href="categories.php" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 