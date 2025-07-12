<?php
require_once 'auth_check.php';
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

// Handle CRUD actions
$action = $_GET['action'] ?? '';
$message = '';

// Tambah meja
if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode_meja = strtoupper(trim($_POST['kode_meja']));
    if ($kode_meja) {
        $stmt = $db->prepare('INSERT INTO tables (kode_meja) VALUES (?)');
        if ($stmt->execute([$kode_meja])) {
            $message = 'Meja berhasil ditambahkan!';
        } else {
            $message = 'Gagal menambah meja (mungkin kode sudah ada).';
        }
    }
}
// Edit meja
if ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $kode_meja = strtoupper(trim($_POST['kode_meja']));
    $status = $_POST['status'] === 'aktif' ? 'aktif' : 'nonaktif';
    $stmt = $db->prepare('UPDATE tables SET kode_meja=?, status=? WHERE id=?');
    if ($stmt->execute([$kode_meja, $status, $id])) {
        $message = 'Meja berhasil diupdate!';
    } else {
        $message = 'Gagal update meja.';
    }
}
// Hapus meja
if ($action === 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $db->prepare('DELETE FROM tables WHERE id=?');
    if ($stmt->execute([$id])) {
        $message = 'Meja berhasil dihapus!';
    } else {
        $message = 'Gagal hapus meja.';
    }
}
// Aktif/nonaktif meja
if ($action === 'toggle' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $db->prepare('UPDATE tables SET status = IF(status="aktif", "nonaktif", "aktif") WHERE id=?');
    $stmt->execute([$id]);
}

// Ambil semua meja
$stmt = $db->query('SELECT * FROM tables ORDER BY kode_meja ASC');
$tables = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ambil status terisi dari tabel orders
$order_stmt = $db->prepare('SELECT nomor_meja, status FROM orders WHERE status NOT IN ("completed", "cancelled")');
$order_stmt->execute();
$occupied = [];
foreach ($order_stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $occupied[$row['nomor_meja']] = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../images/logo-rotio.png" rel="icon">
    <title>Manajemen Meja - Admin Roti'O</title>
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

        /* Alert Styling */
        .alert {
            border-radius: 12px;
            border: none;
            padding: 1rem 1.5rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-light);
        }

        .alert-info {
            background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
            color: #0c5460;
            border-left: 4px solid #17a2b8;
        }

        /* Table Grid */
        .meja-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .meja-box {
            background: white;
            border-radius: 15px;
            padding: 2rem 1rem;
            text-align: center;
            font-size: 1.2rem;
            font-weight: 600;
            box-shadow: var(--shadow-light);
            transition: all 0.3s ease;
            position: relative;
            border: 2px solid transparent;
            overflow: hidden;
        }

        .meja-box::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--primary-color);
        }

        .meja-box:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-medium);
        }

        .meja-kosong {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            border-color: #28a745;
            color: #155724;
        }

        .meja-kosong::before {
            background: #28a745;
        }

        .meja-terisi {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            border-color: #dc3545;
            color: #721c24;
        }

        .meja-terisi::before {
            background: #dc3545;
        }

        .meja-nonaktif {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-color: #6c757d;
            color: #6c757d;
        }

        .meja-nonaktif::before {
            background: #6c757d;
        }

        .meja-box .kode {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .meja-box .status {
            font-size: 0.9rem;
            font-weight: 500;
            margin-bottom: 1rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .meja-box .aksi {
            display: flex;
            gap: 0.5rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        /* Stats Cards */
        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: var(--shadow-light);
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            height: 100%;
        }

        .stats-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--primary-color);
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-medium);
        }

        .stats-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            margin-bottom: 1rem;
            background: var(--primary-color);
        }

        .stats-number {
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }

        .stats-label {
            color: var(--text-light);
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.85rem;
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

            .meja-grid {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
                gap: 1rem;
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
                <a href="tables.php" class="nav-link active">
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
                    <h1 class="page-title">Manajemen Meja</h1>
                    <p class="page-subtitle">Kelola status dan ketersediaan meja</p>
                </div>
            </div>
        </div>

        <!-- Alert Messages -->
        <?php if ($message): ?>
        <div class="fade-in-up" style="animation-delay: 0.1s;">
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="fas fa-info-circle me-2"></i>
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3 fade-in-up" style="animation-delay: 0.2s;">
                <div class="stats-card">
                    <div class="stats-icon">
                        <i class="fas fa-chair"></i>
                    </div>
                    <div class="stats-number"><?php echo count($tables); ?></div>
                    <div class="stats-label">Total Meja</div>
                </div>
            </div>
            <div class="col-md-3 fade-in-up" style="animation-delay: 0.3s;">
                <div class="stats-card">
                    <div class="stats-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stats-number"><?php echo count(array_filter($tables, function($t) { return $t['status'] === 'aktif'; })); ?></div>
                    <div class="stats-label">Meja Aktif</div>
                </div>
            </div>
            <div class="col-md-3 fade-in-up" style="animation-delay: 0.4s;">
                <div class="stats-card">
                    <div class="stats-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stats-number"><?php echo count($occupied); ?></div>
                    <div class="stats-label">Meja Terisi</div>
                </div>
            </div>
            <div class="col-md-3 fade-in-up" style="animation-delay: 0.5s;">
                <div class="stats-card">
                    <div class="stats-icon">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="stats-number"><?php echo count(array_filter($tables, function($t) { return $t['status'] === 'nonaktif'; })); ?></div>
                    <div class="stats-label">Meja Nonaktif</div>
                </div>
            </div>
        </div>

        <!-- Add Table Form -->
        <div class="content-card fade-in-up" style="animation-delay: 0.6s;">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-plus me-2"></i>
                    Tambah Meja Baru
                </h6>
            </div>
            <div class="card-body">
                <form method="POST" action="?action=add" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label for="kode_meja" class="form-label">Kode Meja</label>
                        <input type="text" id="kode_meja" name="kode_meja" class="form-control" placeholder="Contoh: A1, B2, C3" required maxlength="10">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-success w-100">
                            <i class="fas fa-plus"></i>
                            Tambah Meja
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tables Grid -->
        <div class="content-card fade-in-up" style="animation-delay: 0.7s;">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-chair me-2"></i>
                    Daftar Meja
                </h6>
            </div>
            <div class="card-body">
                <div class="meja-grid">
                    <?php foreach ($tables as $meja):
                        $is_occupied = isset($occupied[$meja['kode_meja']]);
                        $status_class = $meja['status'] === 'nonaktif' ? 'meja-nonaktif' : ($is_occupied ? 'meja-terisi' : 'meja-kosong');
                        $status_text = $meja['status'] === 'nonaktif' ? 'Nonaktif' : ($is_occupied ? 'Terisi' : 'Kosong');
                    ?>
                    <div class="meja-box <?php echo $status_class; ?>">
                        <div class="kode"><?php echo htmlspecialchars($meja['kode_meja']); ?></div>
                        <div class="status"><?php echo $status_text; ?></div>
                        <div class="aksi">
                            <button class="btn btn-sm btn-info" onclick="editMeja(<?php echo $meja['id']; ?>, '<?php echo htmlspecialchars($meja['kode_meja']); ?>', '<?php echo $meja['status']; ?>')">
                                <i class="fas fa-edit"></i>
                            </button>
                            <a href="?action=toggle&id=<?php echo $meja['id']; ?>" class="btn btn-sm btn-warning">
                                <i class="fas fa-power-off"></i>
                            </a>
                            <a href="?action=delete&id=<?php echo $meja['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus meja ini?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Table Modal -->
    <div class="modal fade" id="editMejaModal" tabindex="-1" aria-labelledby="editMejaModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editMejaModalLabel">
                        <i class="fas fa-edit me-2"></i>
                        Edit Meja
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="?action=edit">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_kode_meja" class="form-label">Kode Meja</label>
                            <input type="text" class="form-control" id="edit_kode_meja" name="kode_meja" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_status" class="form-label">Status</label>
                            <select class="form-select" id="edit_status" name="status">
                                <option value="aktif">Aktif</option>
                                <option value="nonaktif">Nonaktif</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>
                            Update Meja
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editMeja(id, kode, status) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_kode_meja').value = kode;
            document.getElementById('edit_status').value = status;
            
            new bootstrap.Modal(document.getElementById('editMejaModal')).show();
        }
    </script>
</body>
</html> 