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
        .meja-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-top: 30px; }
        .meja-box {
            border-radius: 10px;
            padding: 30px 0;
            text-align: center;
            font-size: 1.5em;
            font-weight: bold;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: 0.2s;
            position: relative;
        }
        .meja-kosong { background: #e9ffe9; border: 2px solid #27ae60; color: #27ae60; }
        .meja-terisi { background: #ffeaea; border: 2px solid #e74c3c; color: #e74c3c; }
        .meja-nonaktif { background: #f4f4f4; border: 2px solid #aaa; color: #888; }
        .meja-box .aksi { margin-top: 10px; }
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
        <div class="container-fluid p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Manajemen Meja</h1>
            </div>
            <?php if ($message): ?>
                <div class="alert alert-info"><?php echo $message; ?></div>
            <?php endif; ?>
            <form method="POST" action="?action=add" class="form-inline mb-4">
                <input type="text" name="kode_meja" class="form-control mr-2" placeholder="Kode Meja (misal: A1)" required maxlength="10">
                <button type="submit" class="btn btn-success">Tambah Meja</button>
            </form>
            <div class="meja-grid">
                <?php foreach ($tables as $meja):
                    $is_terisi = isset($occupied[$meja['kode_meja']]);
                    $class = $meja['status'] === 'nonaktif' ? 'meja-nonaktif' : ($is_terisi ? 'meja-terisi' : 'meja-kosong');
                ?>
                <div class="meja-box <?php echo $class; ?>">
                    <?php echo htmlspecialchars($meja['kode_meja']); ?><br>
                    <small><?php
                        if ($meja['status'] === 'nonaktif') echo 'Nonaktif';
                        else if ($is_terisi) echo 'Terisi';
                        else echo 'Kosong';
                    ?></small>
                    <div class="aksi">
                        <a href="?action=toggle&id=<?php echo $meja['id']; ?>" class="btn btn-sm btn-warning">Aktif/Nonaktif</a>
                        <a href="?action=editform&id=<?php echo $meja['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                        <a href="?action=delete&id=<?php echo $meja['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus meja ini?')">Hapus</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php if ($action === 'editform' && isset($_GET['id'])):
                $id = intval($_GET['id']);
                $stmt = $db->prepare('SELECT * FROM tables WHERE id=?');
                $stmt->execute([$id]);
                $meja = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($meja):
            ?>
            <hr>
            <h3>Edit Meja</h3>
            <form method="POST" action="?action=edit">
                <input type="hidden" name="id" value="<?php echo $meja['id']; ?>">
                <div class="form-group">
                    <label>Kode Meja</label>
                    <input type="text" name="kode_meja" class="form-control" value="<?php echo htmlspecialchars($meja['kode_meja']); ?>" required maxlength="10">
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="aktif" <?php if($meja['status']==='aktif') echo 'selected'; ?>>Aktif</option>
                        <option value="nonaktif" <?php if($meja['status']==='nonaktif') echo 'selected'; ?>>Nonaktif</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="tables.php" class="btn btn-secondary">Batal</a>
            </form>
            <?php endif; endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 