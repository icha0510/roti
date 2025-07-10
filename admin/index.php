<?php
session_start();

// Cek apakah admin sudah login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

require_once 'includes/functions.php';

// Ambil statistik dashboard
$products = getAllProducts();
$categories = getAllCategories();
$posts = getAllPosts();

$total_products = count($products);
$total_categories = count($categories);
$total_posts = count($posts);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../images/logo-rotio.png" rel="icon">
    <title>Dashboard Admin - Roti'O</title>
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
        .card-stats {
            transition: transform 0.2s;
        }
        .card-stats:hover {
            transform: translateY(-5px);
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
                <a href="index.php" class="nav-link active">
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
        <div class="container-fluid p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Beranda</h1>
                <a href="../index.php" target="_blank" class="btn btn-primary">
                    <i class="fas fa-external-link-alt me-2"></i>Lihat Website
                </a>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2 card-stats">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Total Produk</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_products; ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-box fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-success shadow h-100 py-2 card-stats">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Kategori</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_categories; ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-tags fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


            </div>

            <!-- Recent Products -->
            <div class="row">
                <div class="col-lg-8">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary">Produk Terbaru</h6>
                            <a href="products.php" class="btn btn-sm btn-primary">Lihat Semua</a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Gambar</th>
                                            <th>Nama</th>
                                            <th>Kategori</th>
                                            <th>Harga</th>
                                            <th>Stok</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $recent_products = array_slice($products, 0, 5);
                                        foreach ($recent_products as $product): 
                                        ?>
                                        <tr>
                                            <td>
                                                <?php if (!empty($product['image_data'])): ?>
                                                    <?php echo displayImage($product['image_data'], $product['image_mime'], 'img-thumbnail', $product['name']); ?>
                                                <?php else: ?>
                                                    <img src="<?php echo $product['image']; ?>" class="img-thumbnail" alt="<?php echo $product['name']; ?>" style="width: 50px; height: 50px;">
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo $product['name']; ?></td>
                                            <td><?php echo $product['category_name']; ?></td>
                                            <td>Rp<?php echo number_format($product['price'], 3); ?></td>
                                            <td>
                                                <span class="badge <?php echo $product['stock'] > 0 ? 'bg-success' : 'bg-danger'; ?>">
                                                    <?php echo $product['stock']; ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Aksi Cepat</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="products.php?action=add" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Tambah Produk Baru
                                </a>

                                <a href="posts.php?action=add" class="btn btn-warning">
                                    <i class="fas fa-edit me-2"></i>Tulis Artikel Blog
                                </a>
                                <a href="register.php" class="btn btn-success">
                                    <i class="fas fa-user-plus me-2"></i>Tambah Admin Baru
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Informasi Sistem</h6>
                        </div>
                        <div class="card-body">
                            <p><strong>Versi PHP:</strong> <?php echo phpversion(); ?></p>
                            <p><strong>Database:</strong> MySQL</p>
                            <p><strong>Total Artikel:</strong> <?php echo $total_posts; ?></p>
                            <p><strong>Terakhir Diperbarui:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 