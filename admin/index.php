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

// Tambahkan kode PHP untuk menghitung total pendapatan
$database = new Database();
$db = $database->getConnection();
$sql_income = "SELECT SUM(o.total_amount) as total_income FROM orders o WHERE (SELECT status FROM order_tracking WHERE order_id = o.id ORDER BY created_at DESC LIMIT 1) = 'completed'";
$stmt_income = $db->prepare($sql_income);
$stmt_income->execute();
$total_income = $stmt_income->fetch(PDO::FETCH_ASSOC)['total_income'] ?? 0;

// Statistik pesanan hari ini, bulan ini, dibatalkan
$today = date('Y-m-d');
$this_month = date('Y-m');
// Pesanan hari ini
$sql_today = "SELECT COUNT(*) as count_today FROM orders WHERE DATE(created_at) = :today";
$stmt_today = $db->prepare($sql_today);
$stmt_today->bindParam(':today', $today);
$stmt_today->execute();
$count_today = $stmt_today->fetch(PDO::FETCH_ASSOC)['count_today'] ?? 0;
// Pesanan bulan ini
$sql_month = "SELECT COUNT(*) as count_month FROM orders WHERE DATE_FORMAT(created_at, '%Y-%m') = :this_month";
$stmt_month = $db->prepare($sql_month);
$stmt_month->bindParam(':this_month', $this_month);
$stmt_month->execute();
$count_month = $stmt_month->fetch(PDO::FETCH_ASSOC)['count_month'] ?? 0;
// Pesanan dibatalkan
$sql_cancel = "SELECT COUNT(*) as count_cancel FROM orders WHERE (SELECT status FROM order_tracking WHERE order_id = orders.id ORDER BY created_at DESC LIMIT 1) = 'cancelled'";
$stmt_cancel = $db->prepare($sql_cancel);
$stmt_cancel->execute();
$count_cancel = $stmt_cancel->fetch(PDO::FETCH_ASSOC)['count_cancel'] ?? 0;

// Ambil produk dengan stok menipis
$low_stock_products = array_filter($products, function($p) { return isset($p['stock']) && $p['stock'] < 5; });
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
            --gradient-primary: linear-gradient(135deg, #F2CB05 0%, #E6B800 100%);
            --gradient-success: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            --gradient-warning: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
            --gradient-info: linear-gradient(135deg, #17a2b8 0%, #6f42c1 100%);
            --gradient-danger: linear-gradient(135deg, #dc3545 0%, #e83e8c 100%);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            color: var(--text-dark);
            min-height: 100vh;
        }

        /* Sidebar Styling - Enhanced */
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #343a40 0%, #495057 100%);
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
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            font-weight: 500;
            position: relative;
            overflow: hidden;
        }

        .sidebar .nav-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
            transition: left 0.5s;
        }

        .sidebar .nav-link:hover::before {
            left: 100%;
        }

        .sidebar .nav-link:hover {
            background: rgba(255,255,255,0.1);
            transform: translateX(8px) scale(1.02);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .sidebar .nav-link.active {
            background: var(--gradient-primary);
            color: #343a40;
            font-weight: 600;
            box-shadow: 0 6px 20px rgba(242,203,5,0.4);
            transform: translateX(5px);
        }

        .sidebar .nav-link i {
            width: 20px;
            margin-right: 12px;
            transition: transform 0.3s ease;
        }

        .sidebar .nav-link:hover i {
            transform: scale(1.1);
        }

        /* Main Content - Enhanced */
        .main-content {
            margin-left: 250px;
            padding: 2rem;
            transition: all 0.3s ease;
            min-height: 100vh;
        }

        /* Page Header - Enhanced */
        .page-header {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border-radius: 20px;
            padding: 2.5rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-medium);
            border: 1px solid var(--border-color);
            position: relative;
            overflow: hidden;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-primary);
        }

        .page-title {
            font-size: 2.4rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .page-subtitle {
            color: var(--text-light);
            font-size: 1.1rem;
            font-weight: 400;
        }

        /* Statistics Cards - Enhanced */
        .stats-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: var(--shadow-light);
            border: 1px solid var(--border-color);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
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
            height: 5px;
        }

        .stats-card.primary::before { background: var(--gradient-primary); }
        .stats-card.success::before { background: var(--gradient-success); }
        .stats-card.warning::before { background: var(--gradient-warning); }
        .stats-card.info::before { background: var(--gradient-info); }
        .stats-card.danger::before { background: var(--gradient-danger); }

        .stats-card::after {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            transition: all 0.4s ease;
            opacity: 0;
        }

        .stats-card:hover::after {
            opacity: 1;
            transform: scale(1.5);
        }

        .stats-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: var(--shadow-heavy);
        }

        .stats-icon {
            width: 70px;
            height: 70px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            color: white;
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stats-icon::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: inherit;
            filter: blur(10px);
            opacity: 0.3;
        }

        .stats-icon.primary { background: var(--gradient-primary); }
        .stats-icon.success { background: var(--gradient-success); }
        .stats-icon.warning { background: var(--gradient-warning); }
        .stats-icon.info { background: var(--gradient-info); }
        .stats-icon.danger { background: var(--gradient-danger); }

        .stats-card:hover .stats-icon {
            transform: scale(1.1) rotate(5deg);
        }

        .stats-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
            transition: all 0.3s ease;
        }

        .stats-card:hover .stats-number {
            transform: scale(1.05);
        }

        .stats-label {
            color: var(--text-light);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 0.9rem;
        }

        /* Warning Card - Enhanced */
        .warning-card {
            background: linear-gradient(135deg, #FFF8E1 0%, #FFECB3 100%);
            border: 2px solid #FFB74D;
            border-radius: 20px;
            padding: 2.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(255,183,77,0.3);
            position: relative;
            overflow: hidden;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.02); }
        }

        .warning-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #FF9800, #FF5722, #FF9800);
            background-size: 200% 100%;
            animation: shimmer 3s infinite;
        }

        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }

        .warning-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #FF9800, #FF5722);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 6px 20px rgba(255,152,0,0.4);
        }

        .warning-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: #E65100;
            margin-bottom: 1rem;
        }

        .warning-list {
            list-style: none;
            padding: 0;
        }

        .warning-list li {
            padding: 0.75rem 0;
            border-bottom: 1px solid rgba(255,183,77,0.3);
            color: #BF360C;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .warning-list li:hover {
            background: rgba(255,183,77,0.1);
            padding-left: 10px;
            border-radius: 8px;
        }

        .warning-list li:last-child {
            border-bottom: none;
        }

        /* Content Cards - Enhanced */
        .content-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border-radius: 20px;
            box-shadow: var(--shadow-light);
            border: 1px solid var(--border-color);
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .content-card:hover {
            box-shadow: var(--shadow-heavy);
            transform: translateY(-5px);
        }

        .card-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
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
            background: var(--gradient-primary);
        }

        .card-body {
            padding: 2.5rem;
        }

        /* Table Styling - Enhanced */
        .table {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            background: white;
        }

        .table thead th {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
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
            background: var(--gradient-primary);
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
            background: linear-gradient(135deg, rgba(242,203,5,0.05) 0%, rgba(242,203,5,0.1) 100%);
            transform: scale(1.01);
        }

        /* Buttons - Enhanced */
        .btn {
            border-radius: 12px;
            padding: 0.875rem 1.75rem;
            font-weight: 600;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border: none;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn-primary {
            background: var(--gradient-primary);
            color: #343a40;
            box-shadow: 0 6px 20px rgba(242,203,5,0.4);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #E6B800 0%, #F2CB05 100%);
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(242,203,5,0.5);
            color: #343a40;
        }

        .btn-success {
            background: var(--gradient-success);
            color: white;
            box-shadow: 0 6px 20px rgba(40,167,69,0.4);
        }

        .btn-success:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(40,167,69,0.5);
        }

        .btn-warning {
            background: var(--gradient-warning);
            color: #343a40;
            box-shadow: 0 6px 20px rgba(255,193,7,0.4);
        }

        .btn-warning:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(255,193,7,0.5);
        }

        .btn-info {
            background: var(--gradient-info);
            color: white;
            box-shadow: 0 6px 20px rgba(23,162,184,0.4);
        }

        .btn-info:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(23,162,184,0.5);
        }

        .btn-danger {
            background: var(--gradient-danger);
            color: white;
            box-shadow: 0 6px 20px rgba(220,53,69,0.4);
        }

        .btn-danger:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(220,53,69,0.5);
        }

        /* Badges - Enhanced */
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
            background: var(--gradient-success) !important;
            box-shadow: 0 4px 15px rgba(40,167,69,0.3);
        }

        .bg-danger {
            background: var(--gradient-danger) !important;
            box-shadow: 0 4px 15px rgba(220,53,69,0.3);
        }

        .bg-warning {
            background: var(--gradient-warning) !important;
            color: #343a40 !important;
            box-shadow: 0 4px 15px rgba(255,193,7,0.3);
        }

        .bg-light {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
            color: #343a40 !important;
            box-shadow: 0 4px 15px rgba(108,117,125,0.2);
        }

        /* Responsive - Enhanced */
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
            
            .stats-number {
                font-size: 2rem;
            }

            .stats-card {
                padding: 2rem;
            }

            .page-header {
                padding: 2rem;
            }
        }

        /* Animation - Enhanced */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-40px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .fade-in-up {
            animation: fadeInUp 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .slide-in-left {
            animation: slideInLeft 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Scrollbar - Enhanced */
        ::-webkit-scrollbar {
            width: 10px;
        }

        ::-webkit-scrollbar-track {
            background: linear-gradient(180deg, #f1f1f1 0%, #e9ecef 100%);
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--gradient-primary);
            border-radius: 10px;
            border: 2px solid #f1f1f1;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #E6B800 0%, #F2CB05 100%);
        }

        /* Additional Enhancements */
        .img-thumbnail {
            border-radius: 12px;
            transition: all 0.3s ease;
            border: 2px solid var(--border-color);
        }

        .img-thumbnail:hover {
            transform: scale(1.1);
            box-shadow: var(--shadow-medium);
        }

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
            background: linear-gradient(135deg, rgba(242,203,5,0.1) 0%, rgba(242,203,5,0.2) 100%);
            transform: translateX(5px);
        }

        /* Loading Animation */
        .loading {
            position: relative;
            overflow: hidden;
        }

        .loading::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
            animation: loading 1.5s infinite;
        }

        @keyframes loading {
            0% { left: -100%; }
            100% { left: 100%; }
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
        <!-- Page Header -->
        <div class="page-header fade-in-up">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="page-title">Dashboard</h1>
                    <p class="page-subtitle">Selamat datang kembali, <?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?>!</p>
                </div>
                <a href="../index.php" target="_blank" class="btn btn-primary">
                    <i class="fas fa-external-link-alt"></i>
                    Lihat Website
                </a>
            </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4 fade-in-up" style="animation-delay: 0.1s;">
                <div class="stats-card primary">
                    <div class="stats-icon primary">
                        <i class="fas fa-box"></i>
                                </div>
                    <div class="stats-number"><?php echo $total_products; ?></div>
                    <div class="stats-label">Total Produk</div>
                                </div>
                            </div>

            <div class="col-xl-3 col-md-6 mb-4 fade-in-up" style="animation-delay: 0.2s;">
                <div class="stats-card success">
                    <div class="stats-icon success">
                        <i class="fas fa-tags"></i>
                    </div>
                    <div class="stats-number"><?php echo $total_categories; ?></div>
                    <div class="stats-label">Kategori</div>
                </div>
                                </div>

            <div class="col-xl-3 col-md-6 mb-4 fade-in-up" style="animation-delay: 0.3s;">
                <div class="stats-card warning">
                    <div class="stats-icon warning">
                        <i class="fas fa-money-bill-wave"></i>
                                </div>
                    <div class="stats-number">Rp<?php echo number_format($total_income, 3, ',', '.'); ?></div>
                    <div class="stats-label">Total Pendapatan</div>
                            </div>
                        </div>

            <div class="col-xl-3 col-md-6 mb-4 fade-in-up" style="animation-delay: 0.4s;">
                <div class="stats-card info">
                    <div class="stats-icon info">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <div class="stats-number"><?php echo $count_today; ?></div>
                    <div class="stats-label">Pesanan Hari Ini</div>
                </div>
                                </div>
                            </div>

        <!-- Additional Statistics -->
        <div class="row mb-4">
            <div class="col-xl-4 col-md-6 mb-4 fade-in-up" style="animation-delay: 0.5s;">
                <div class="stats-card primary">
                    <div class="stats-icon primary">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="stats-number"><?php echo $count_month; ?></div>
                    <div class="stats-label">Pesanan Bulan Ini</div>
                </div>
                                    </div>

            <div class="col-xl-4 col-md-6 mb-4 fade-in-up" style="animation-delay: 0.6s;">
                <div class="stats-card danger">
                    <div class="stats-icon danger">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="stats-number"><?php echo $count_cancel; ?></div>
                    <div class="stats-label">Pesanan Dibatalkan</div>
                                    </div>
                                </div>

            <div class="col-xl-4 col-md-6 mb-4 fade-in-up" style="animation-delay: 0.7s;">
                <div class="stats-card success">
                    <div class="stats-icon success">
                        <i class="fas fa-newspaper"></i>
                    </div>
                    <div class="stats-number"><?php echo $total_posts; ?></div>
                    <div class="stats-label">Total Artikel</div>
                                    </div>
                                </div>
                            </div>

        <!-- Low Stock Warning -->
        <?php if (!empty($low_stock_products)): ?>
        <div class="row mb-4 fade-in-up" style="animation-delay: 0.8s;">
            <div class="col-12">
                <div class="warning-card">
                    <div class="d-flex align-items-center mb-3">
                        <div class="warning-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <h5 class="warning-title mb-0">Stok Menipis</h5>
                    </div>
                    <p class="text-muted mb-3">Produk berikut memiliki stok yang menipis dan perlu segera ditambahkan:</p>
                    <ul class="warning-list">
                            <?php foreach ($low_stock_products as $prod): ?>
                            <li>
                                <i class="fas fa-circle me-2" style="font-size: 0.5rem; color: #FF9800;"></i>
                                <?php echo htmlspecialchars($prod['name']); ?> 
                                <span class="badge bg-warning ms-2">Stok: <?php echo $prod['stock']; ?></span>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        <!-- Content Section -->
        <div class="row fade-in-up" style="animation-delay: 0.9s;">
                <div class="col-lg-8">
                <div class="content-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">
                            <i class="fas fa-box me-2"></i>
                            Produk Terbaru
                        </h6>
                        <a href="products.php" class="btn btn-primary btn-sm">
                            <i class="fas fa-eye"></i>
                            Lihat Semua
                        </a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                            <table class="table">
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
                                                <img src="<?php echo $product['image']; ?>" class="img-thumbnail" alt="<?php echo $product['name']; ?>" style="width: 50px; height: 50px; border-radius: 8px;">
                                                <?php endif; ?>
                                            </td>
                                        <td>
                                            <strong><?php echo $product['name']; ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-light"><?php echo $product['category_name']; ?></span>
                                        </td>
                                        <td>
                                            <strong class="text-success">Rp<?php echo number_format($product['price'], 3, ',', '.'); ?></strong>
                                        </td>
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
                <!-- Quick Actions -->
                <div class="content-card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-bolt me-2"></i>
                            Aksi Cepat
                        </h6>
                        </div>
                        <div class="card-body">
                        <div class="d-grid gap-3">
                                <a href="products.php?action=add" class="btn btn-primary">
                                <i class="fas fa-plus"></i>
                                Tambah Produk Baru
                                </a>
                                <a href="posts.php?action=add" class="btn btn-warning">
                                <i class="fas fa-edit"></i>
                                Tulis Artikel Blog
                                </a>
                                <a href="register.php" class="btn btn-success">
                                <i class="fas fa-user-plus"></i>
                                Tambah Admin Baru
                                </a>
                            </div>
                        </div>
                    </div>

                <!-- System Info -->
                <div class="content-card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            Informasi Sistem
                        </h6>
                        </div>
                        <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted">Versi PHP:</span>
                                    <span class="fw-bold"><?php echo phpversion(); ?></span>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted">Database:</span>
                                    <span class="fw-bold">MySQL</span>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted">Total Artikel:</span>
                                    <span class="fw-bold"><?php echo $total_posts; ?></span>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted">Terakhir Update:</span>
                                    <span class="fw-bold"><?php echo date('d/m/Y H:i'); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 