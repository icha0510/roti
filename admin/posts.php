<?php
session_start();

// Cek apakah admin sudah login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

require_once 'includes/functions.php';

$action = $_GET['action'] ?? 'list';
$message = '';
$message_type = 'success';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'add' || $action === 'edit') {
        $image_result = null;
        $image_data = null;
        $image_mime = null;
        
        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $image_result = uploadImageToDatabase($_FILES['image']);
            if ($image_result['success']) {
                $image_data = $image_result['data'];
                $image_mime = $image_result['mime_type'];
            } else {
                $message = $image_result['error'];
                $message_type = 'danger';
            }
        }
        
        // Validasi input
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $author = trim($_POST['author'] ?? '');
        $excerpt = trim($_POST['excerpt'] ?? '');
        
        if (empty($title)) {
            $message = 'Judul artikel harus diisi!';
            $message_type = 'danger';
        } elseif (empty($content)) {
            $message = 'Konten artikel harus diisi!';
            $message_type = 'danger';
        } elseif (empty($author)) {
            $message = 'Penulis harus diisi!';
            $message_type = 'danger';
        } else {
            $data = [
                'title' => $title,
                'slug' => createSlug($title),
                'content' => $content,
                'excerpt' => $excerpt,
                'author' => $author,
                'status' => isset($_POST['is_published']) ? 'published' : 'draft'
            ];
            
            // Add image data if uploaded
            if ($image_data && $image_mime) {
                $data['image_data'] = $image_data;
                $data['image_mime'] = $image_mime;
            }
            
            if ($action === 'add') {
                if (addPost($data)) {
                    $message = 'Artikel berhasil ditambahkan!';
                    $action = 'list';
                } else {
                    $message = 'Error menambahkan artikel.';
                    $message_type = 'danger';
                }
            } elseif ($action === 'edit') {
                $id = $_GET['id'] ?? 0;
                if (updatePost($id, $data)) {
                    $message = 'Artikel berhasil diperbarui!';
                    $action = 'list';
                } else {
                    $message = 'Error memperbarui artikel.';
                    $message_type = 'danger';
                }
            }
        }
    }
}

// Handle delete action
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    if (deletePost($id)) {
        $message = 'Artikel berhasil dihapus!';
    } else {
        $message = 'Error menghapus artikel.';
        $message_type = 'danger';
    }
    $action = 'list';
}

// Get post for editing
$edit_post = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $edit_post = getPostById($_GET['id']);
    if (!$edit_post) {
        $message = 'Artikel tidak ditemukan.';
        $message_type = 'danger';
        $action = 'list';
    }
}

$posts = getAllPosts();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../images/logo-rotio.png" rel="icon">
    <title>Artikel Blog - Admin Roti'O</title>
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
            min-height: 100vh;
        }

        /* Sidebar Styling - Simple */
        .sidebar {
            min-height: 100vh;
            background: #343a40;
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            z-index: 1000;
            box-shadow: var(--shadow-heavy);
            border-right: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar .nav-link {
            color: #fff;
            padding: 12px 20px;
            margin: 4px 10px;
            border-radius: 12px;
            transition: all 0.3s ease;
            font-weight: 500;
            position: relative;
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
            margin-right: 12px;
            transition: transform 0.3s ease;
        }

        .sidebar .nav-link:hover i {
            transform: scale(1.1);
        }

        /* Main Content - Simple */
        .main-content {
            margin-left: 250px;
            padding: 2rem;
            transition: all 0.3s ease;
            min-height: 100vh;
        }

        /* Page Header - Simple */
        .page-header {
            background: white;
            border-radius: 20px;
            padding: 2.5rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-medium);
            border: 1px solid var(--border-color);
            position: relative;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: #F2CB05;
        }

        .page-title {
            font-size: 2.4rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }

        .page-subtitle {
            color: var(--text-light);
            font-size: 1.1rem;
            font-weight: 400;
        }

        /* Content Cards - Simple */
        .content-card {
            background: white;
            border-radius: 20px;
            box-shadow: var(--shadow-light);
            border: 1px solid var(--border-color);
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .content-card:hover {
            box-shadow: var(--shadow-medium);
            transform: translateY(-3px);
        }

        .card-header {
            background: #f8f9fa;
            padding: 1.8rem 2rem;
            border-bottom: 1px solid var(--border-color);
            font-weight: 600;
            color: var(--text-dark);
            position: relative;
        }

        .card-header::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: #F2CB05;
        }

        .card-body {
            padding: 2.5rem;
        }

        /* Form Styling - Simple */
        .form-control, .form-select {
            border-radius: 12px;
            border: 2px solid var(--border-color);
            padding: 0.875rem 1rem;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            background: white;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(242,203,5,0.25);
            transform: translateY(-2px);
        }

        .form-label {
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 0.75rem;
            font-size: 0.95rem;
        }

        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        /* Table Styling - Simple */
        .table {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            background: white;
        }

        .table thead th {
            background: #f8f9fa;
            border: none;
            padding: 1.2rem 1rem;
            font-weight: 700;
            color: var(--text-dark);
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 0.85rem;
            position: relative;
        }

        .table thead th::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: #F2CB05;
        }

        .table tbody td {
            padding: 1.2rem 1rem;
            border: none;
            border-bottom: 1px solid var(--border-color);
            vertical-align: middle;
            transition: all 0.3s ease;
        }

        .table tbody tr {
            transition: all 0.3s ease;
        }

        .table tbody tr:hover {
            background: rgba(242,203,5,0.05);
            transform: scale(1.01);
        }

        /* Buttons - Simple */
        .btn {
            border-radius: 12px;
            padding: 0.875rem 1.75rem;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            position: relative;
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
            box-shadow: 0 4px 15px rgba(40,167,69,0.3);
        }

        .btn-success:hover {
            background: #218838;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(40,167,69,0.4);
        }

        .btn-warning {
            background: #ffc107;
            color: #343a40;
            box-shadow: 0 4px 15px rgba(255,193,7,0.3);
        }

        .btn-warning:hover {
            background: #e0a800;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255,193,7,0.4);
        }

        .btn-info {
            background: #17a2b8;
            color: white;
            box-shadow: 0 4px 15px rgba(23,162,184,0.3);
        }

        .btn-info:hover {
            background: #138496;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(23,162,184,0.4);
        }

        .btn-danger {
            background: #dc3545;
            color: white;
            box-shadow: 0 4px 15px rgba(220,53,69,0.3);
        }

        .btn-danger:hover {
            background: #c82333;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(220,53,69,0.4);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
            box-shadow: 0 4px 15px rgba(108,117,125,0.3);
        }

        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(108,117,125,0.4);
            color: white;
        }

        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
        }

        /* Badges - Simple */
        .badge {
            padding: 0.6rem 1.2rem;
            border-radius: 25px;
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }

        .badge:hover {
            transform: scale(1.05);
        }

        .bg-success {
            background: #28a745 !important;
            box-shadow: 0 4px 15px rgba(40,167,69,0.3);
        }

        .bg-danger {
            background: #dc3545 !important;
            box-shadow: 0 4px 15px rgba(220,53,69,0.3);
        }

        .bg-warning {
            background: #ffc107 !important;
            color: #343a40 !important;
            box-shadow: 0 4px 15px rgba(255,193,7,0.3);
        }

        .bg-info {
            background: #17a2b8 !important;
            box-shadow: 0 4px 15px rgba(23,162,184,0.3);
        }

        .bg-secondary {
            background: #6c757d !important;
            box-shadow: 0 4px 15px rgba(108,117,125,0.3);
        }

        .bg-primary {
            background: #F2CB05 !important;
            color: #343a40 !important;
            box-shadow: 0 4px 15px rgba(242,203,5,0.3);
        }

        .bg-light {
            background: #f8f9fa !important;
            color: #343a40 !important;
            box-shadow: 0 4px 15px rgba(108,117,125,0.2);
        }

        /* Post Image - Simple */
        .post-image {
            max-width: 80px;
            max-height: 80px;
            object-fit: cover;
            border-radius: 12px;
            border: 2px solid var(--border-color);
            transition: all 0.3s ease;
        }

        .post-image:hover {
            transform: scale(1.1);
            box-shadow: var(--shadow-medium);
        }

        /* Alert Styling - Simple */
        .alert {
            border-radius: 15px;
            border: none;
            padding: 1.2rem 1.8rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-light);
            position: relative;
        }

        .alert::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }

        .alert-success::before {
            background: #28a745;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        .alert-danger::before {
            background: #dc3545;
        }

        /* Character Counter */
        .char-counter {
            font-size: 0.8rem;
            color: var(--text-light);
            text-align: right;
            margin-top: 0.5rem;
        }

        .char-counter.warning {
            color: var(--warning-color);
        }

        .char-counter.danger {
            color: var(--danger-color);
        }

        /* Responsive - Simple */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }
            
            .page-title {
                font-size: 2rem;
            }

            .page-header {
                padding: 2rem;
            }

            .card-body {
                padding: 1.5rem;
            }
        }

        /* Animation - Simple */
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

        /* Scrollbar - Simple */
        ::-webkit-scrollbar {
            width: 10px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb {
            background: #F2CB05;
            border-radius: 10px;
            border: 2px solid #f1f1f1;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #E6B800;
        }

        /* Additional Enhancements */
        .dropdown-menu {
            border-radius: 15px;
            box-shadow: var(--shadow-heavy);
            border: 1px solid var(--border-color);
            overflow: hidden;
        }

        .dropdown-item {
            transition: all 0.3s ease;
            padding: 0.75rem 1.5rem;
        }

        .dropdown-item:hover {
            background: rgba(242,203,5,0.1);
            transform: translateX(5px);
        }

        /* Form Validation Styling */
        .form-control.is-invalid {
            border-color: var(--danger-color);
            box-shadow: 0 0 0 0.2rem rgba(220,53,69,0.25);
        }

        .invalid-feedback {
            color: var(--danger-color);
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        /* Loading States */
        .btn.loading {
            position: relative;
            pointer-events: none;
        }

        .btn.loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 20px;
            height: 20px;
            margin: -10px 0 0 -10px;
            border: 2px solid transparent;
            border-top: 2px solid currentColor;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
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
                <a href="categories.php" class="nav-link text-white">
                    <i class="fas fa-tags me-2"></i>
                    Kategori
                </a>
            </li>
            <li>
                <a href="posts.php" class="nav-link active">
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
                    <h1 class="page-title">
                        <?php echo $action === 'add' ? 'Tambah Artikel Baru' : ($action === 'edit' ? 'Edit Artikel' : 'Kelola Artikel Blog'); ?>
                    </h1>
                    <p class="page-subtitle">
                        <?php echo $action === 'add' ? 'Buat artikel baru untuk blog Roti\'O' : ($action === 'edit' ? 'Edit artikel yang sudah ada' : 'Tulis, edit, dan kelola artikel blog Roti\'O'); ?>
                    </p>
                </div>
                <?php if ($action === 'list'): ?>
                <a href="?action=add" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Tambah Artikel
                </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Alert Messages -->
        <?php if ($message): ?>
        <div class="fade-in-up" style="animation-delay: 0.1s;">
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($action === 'list'): ?>
        <!-- Posts List -->
        <div class="content-card fade-in-up" style="animation-delay: 0.2s;">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="fas fa-newspaper me-2"></i>
                    Daftar Artikel
                </h6>
                <span class="badge bg-primary"><?php echo count($posts); ?> Artikel</span>
            </div>
            <div class="card-body">
                <?php if (empty($posts)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-newspaper fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Belum ada artikel</h5>
                    <p class="text-muted">Mulai dengan menambahkan artikel pertama Anda</p>
                    <a href="?action=add" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        Tambah Artikel Pertama
                    </a>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Gambar</th>
                                <th>Judul Artikel</th>
                                <th>Penulis</th>
                                <th>Ringkasan</th>
                                <th>Status</th>
                                <th>Dibuat</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($posts as $post): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($post['image_data'])): ?>
                                        <?php echo displayImage($post['image_data'], $post['image_mime'], 'post-image', $post['title']); ?>
                                    <?php else: ?>
                                        <img src="../images/posts/default.jpg" class="post-image" alt="<?php echo $post['title']; ?>">
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div>
                                        <strong><?php echo htmlspecialchars($post['title']); ?></strong>
                                        <div class="small text-muted"><?php echo $post['slug']; ?></div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark"><?php echo htmlspecialchars($post['author']); ?></span>
                                </td>
                                <td>
                                    <?php if (!empty($post['excerpt'])): ?>
                                        <span class="text-muted"><?php echo htmlspecialchars(substr($post['excerpt'], 0, 60)) . (strlen($post['excerpt']) > 60 ? '...' : ''); ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge <?php echo $post['status'] === 'published' ? 'bg-success' : 'bg-secondary'; ?>">
                                        <?php echo ucfirst($post['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($post['created_at'])); ?></small>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="../blog-detail.php?slug=<?php echo $post['slug']; ?>" target="_blank" class="btn btn-sm btn-info" title="Lihat Artikel">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="?action=edit&id=<?php echo $post['id']; ?>" class="btn btn-sm btn-warning" title="Edit Artikel">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="?delete=<?php echo $post['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus artikel ini?')" title="Hapus Artikel">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($action === 'add' || $action === 'edit'): ?>
        <!-- Add/Edit Post Form -->
        <div class="content-card fade-in-up" style="animation-delay: 0.2s;">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-<?php echo $action === 'add' ? 'plus' : 'edit'; ?> me-2"></i>
                    <?php echo $action === 'add' ? 'Tambah Artikel Baru' : 'Edit Artikel'; ?>
                </h6>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data" id="postForm">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="title" class="form-label">Judul Artikel <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="title" name="title" 
                                       value="<?php echo htmlspecialchars($edit_post['title'] ?? ''); ?>" 
                                       required maxlength="200">
                                <div class="char-counter">
                                    <span id="titleCounter"><?php echo strlen($edit_post['title'] ?? ''); ?></span>/200 karakter
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="author" class="form-label">Penulis <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="author" name="author" 
                                       value="<?php echo htmlspecialchars($edit_post['author'] ?? $_SESSION['admin_name'] ?? 'Admin'); ?>" 
                                       required maxlength="100">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="excerpt" class="form-label">Ringkasan (Opsional)</label>
                        <textarea class="form-control" id="excerpt" name="excerpt" rows="3" 
                                  placeholder="Ringkasan singkat artikel..." maxlength="300"><?php echo htmlspecialchars($edit_post['excerpt'] ?? ''); ?></textarea>
                        <div class="char-counter">
                            <span id="excerptCounter"><?php echo strlen($edit_post['excerpt'] ?? ''); ?></span>/300 karakter
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="content" class="form-label">Konten Artikel <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="content" name="content" rows="15" 
                                  required placeholder="Tulis konten artikel di sini..."><?php echo htmlspecialchars($edit_post['content'] ?? ''); ?></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="image" class="form-label">Gambar Artikel (Opsional)</label>
                                <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                <small class="text-muted">Format: JPG, PNG, GIF. Maksimal 2MB</small>
                                <?php if ($action === 'edit' && !empty($edit_post['image_data'])): ?>
                                <div class="mt-2">
                                    <?php echo displayImage($edit_post['image_data'], $edit_post['image_mime'], 'image-preview', 'Current Image'); ?>
                                    <small class="text-muted d-block">Gambar saat ini</small>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" id="is_published" name="is_published" 
                                           <?php echo ($edit_post['status'] ?? 'draft') === 'published' ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="is_published">
                                        Publikasikan Sekarang
                                    </label>
                                </div>
                                <small class="text-muted">Jika tidak dicentang, artikel akan disimpan sebagai draft</small>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <i class="fas fa-save me-2"></i>
                            <?php echo $action === 'add' ? 'Simpan Artikel' : 'Update Artikel'; ?>
                        </button>
                        <a href="?action=list" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>
                            Kembali
                        </a>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Character counter for title
        document.getElementById('title').addEventListener('input', function() {
            const counter = document.getElementById('titleCounter');
            const length = this.value.length;
            counter.textContent = length;
            
            if (length > 180) {
                counter.parentElement.classList.add('warning');
            } else {
                counter.parentElement.classList.remove('warning');
            }
        });

        // Character counter for excerpt
        document.getElementById('excerpt').addEventListener('input', function() {
            const counter = document.getElementById('excerptCounter');
            const length = this.value.length;
            counter.textContent = length;
            
            if (length > 250) {
                counter.parentElement.classList.add('warning');
            } else {
                counter.parentElement.classList.remove('warning');
            }
        });

        // Simple form validation
        document.getElementById('postForm').addEventListener('submit', function(e) {
            const title = document.getElementById('title').value.trim();
            const content = document.getElementById('content').value.trim();
            
            if (!title) {
                e.preventDefault();
                alert('Judul artikel harus diisi!');
                document.getElementById('title').focus();
                return false;
            }
            
            if (!content) {
                e.preventDefault();
                alert('Konten artikel harus diisi!');
                document.getElementById('content').focus();
                return false;
            }
            
            // Show loading state
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.classList.add('loading');
            submitBtn.disabled = true;
        });
    </script>
</body>
</html> 