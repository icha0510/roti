<?php
require_once 'includes/functions.php';

$action = $_GET['action'] ?? 'list';
$message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'add') {
        $image_result = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            // Cek apakah GD extension tersedia
            if (extension_loaded('gd')) {
                $image_result = uploadImageToDatabase($_FILES['image']);
            } else {
                // Gunakan fungsi sederhana jika GD tidak tersedia
                $image_result = uploadImageToDatabaseSimple($_FILES['image']);
            }
            
            if (isset($image_result['error'])) {
                $message = $image_result['error'];
                $action = 'add';
            }
        }
        
        if (!isset($image_result['error'])) {
            $data = [
                'title' => $_POST['title'],
                'description' => $_POST['description'],
                'year_start' => $_POST['year_start'],
                'year_end' => $_POST['year_end'],
                'image_data' => $image_result['success'] ? $image_result['data'] : null,
                'image_mime' => $image_result['success'] ? $image_result['mime_type'] : null,
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            ];
            
            if (addAward($data)) {
                $message = 'Award added successfully!';
                $action = 'list';
            } else {
                $message = 'Error adding award.';
            }
        }
    }
}

// Handle delete action
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    if (deleteAward($id)) {
        $message = 'Award deleted successfully!';
    } else {
        $message = 'Error deleting award.';
    }
    $action = 'list';
}

$awards = getAllAwards();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Awards - Bready Admin</title>
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
            background: #007bff;
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
            <i class="fas fa-bread-slice me-2"></i>
            <span class="fs-4">Bready Admin</span>
        </a>
        <hr>
        <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item">
                <a href="index.php" class="nav-link">
                    <i class="fas fa-tachometer-alt me-2"></i>
                    Dashboard
                </a>
            </li>
            <li>
                <a href="products.php" class="nav-link text-white">
                    <i class="fas fa-box me-2"></i>
                    Products
                </a>
            </li>
            <li>
                <a href="categories.php" class="nav-link text-white">
                    <i class="fas fa-tags me-2"></i>
                    Categories
                </a>
            </li>
            <li>
                <a href="banners.php" class="nav-link text-white">
                    <i class="fas fa-images me-2"></i>
                    Banners
                </a>
            </li>
            <li>
                <a href="testimonials.php" class="nav-link text-white">
                    <i class="fas fa-comments me-2"></i>
                    Testimonials
                </a>
            </li>
            <li>
                <a href="posts.php" class="nav-link text-white">
                    <i class="fas fa-newspaper me-2"></i>
                    Blog Posts
                </a>
            </li>
            <li>
                <a href="awards.php" class="nav-link active">
                    <i class="fas fa-trophy me-2"></i>
                    Awards
                </a>
            </li>
            <li>
                <a href="orders.php" class="nav-link text-white">
                    <i class="fas fa-clipboard-list me-2"></i>
                    Orders
                </a>
            </li>
        </ul>
        <hr>
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-user-circle me-2"></i>
                <strong>Admin</strong>
            </a>
            <ul class="dropdown-menu dropdown-menu-dark text-small shadow" aria-labelledby="dropdownUser1">
                <li><a class="dropdown-item" href="../index.php" target="_blank">View Website</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="logout.php">Sign out</a></li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Awards</h1>
                <a href="?action=add" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Add New Award
                </a>
            </div>

            <?php if ($message): ?>
                <div class="alert <?php echo strpos($message, 'Error') !== false || strpos($message, 'Gagal') !== false || strpos($message, 'terlalu besar') !== false ? 'alert-danger' : 'alert-success'; ?> alert-dismissible fade show" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (!extension_loaded('gd')): ?>
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <strong>Peringatan:</strong> Extension GD tidak tersedia. Upload gambar dibatasi maksimal 1MB tanpa kompresi. 
                    Untuk kompresi otomatis, aktifkan extension GD di php.ini.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($action === 'list'): ?>
                <!-- Awards List -->
                <div class="card shadow">
                    <div class="card-header">
                        <h5 class="mb-0">All Awards</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Image</th>
                                        <th>Title</th>
                                        <th>Description</th>
                                        <th>Year</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($awards as $award): ?>
                                    <tr>
                                        <td>
                                            <?php if (!empty($award['image_data'])): ?>
                                                <?php echo displayImage($award['image_data'], $award['image_mime'], 'img-thumbnail', $award['title']); ?>
                                            <?php else: ?>
                                                <img src="../images/icons/default-award.jpg" class="img-thumbnail" alt="<?php echo $award['title']; ?>" style="width: 50px; height: 50px;">
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $award['title']; ?></td>
                                        <td><?php echo substr($award['description'], 0, 100) . '...'; ?></td>
                                        <td>
                                            <?php 
                                            if (!empty($award['year_start']) && !empty($award['year_end'])) {
                                                echo $award['year_start'] . ' - ' . $award['year_end'];
                                            } elseif (!empty($award['year_start'])) {
                                                echo $award['year_start'];
                                            } else {
                                                echo 'N/A';
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <span class="badge <?php echo $award['is_active'] ? 'bg-success' : 'bg-secondary'; ?>">
                                                <?php echo $award['is_active'] ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="?delete=<?php echo $award['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
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
                <!-- Add Award Form -->
                <div class="card shadow">
                    <div class="card-header">
                        <h5 class="mb-0">Add New Award</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="title" class="form-label">Award Title</label>
                                        <input type="text" class="form-control" id="title" name="title" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="year_start" class="form-label">Year Start</label>
                                        <input type="number" class="form-control" id="year_start" name="year_start" min="1900" max="2030" value="<?php echo date('Y'); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="year_end" class="form-label">Year End</label>
                                        <input type="number" class="form-control" id="year_end" name="year_end" min="1900" max="2030" value="<?php echo date('Y'); ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="image" class="form-label">Award Image</label>
                                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <div class="form-check mt-4">
                                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
                                            <label class="form-check-label" for="is_active">
                                                Active
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">Add Award</button>
                                <a href="awards.php" class="btn btn-secondary">Cancel</a>
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