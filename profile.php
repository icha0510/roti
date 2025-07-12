<?php
session_start();
require_once 'includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=profile.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user = getUserById($user_id);

$error = '';
$success = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($name)) {
        $error = 'Nama tidak boleh kosong!';
    } else {
        // Update profile
        $database = new Database();
        $db = $database->getConnection();
        
        $sql = "UPDATE users SET name = :name, phone = :phone, address = :address WHERE id = :user_id";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':user_id', $user_id);
        
        if ($stmt->execute()) {
            // Update session
            $_SESSION['user_name'] = $name;
            $user['name'] = $name;
            $user['phone'] = $phone;
            $user['address'] = $address;
            
            // Handle password change if provided
            if (!empty($current_password) && !empty($new_password)) {
                if ($new_password !== $confirm_password) {
                    $error = 'Password baru dan konfirmasi password tidak cocok!';
                } elseif (strlen($new_password) < 6) {
                    $error = 'Password minimal 6 karakter!';
                } else {
                    // Verify current password
                    $current_user = authenticateUser($user['email'], $current_password);
                    if ($current_user) {
                        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                        $sql = "UPDATE users SET password = :password WHERE id = :user_id";
                        $stmt = $db->prepare($sql);
                        $stmt->bindParam(':password', $hashed_password);
                        $stmt->bindParam(':user_id', $user_id);
                        
                        if ($stmt->execute()) {
                            $success = 'Profil dan password berhasil diperbarui!';
                        } else {
                            $error = 'Gagal memperbarui password!';
                        }
                    } else {
                        $error = 'Password saat ini salah!';
                    }
                }
            } else {
                $success = 'Profil berhasil diperbarui!';
            }
        } else {
            $error = 'Gagal memperbarui profil!';
        }
    }
}

// Calculate cart totals for header
$cart_total = 0;
$cart_count = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_total += $item['price'] * $item['quantity'];
        $cart_count += $item['quantity'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="images/logo-rotio.png" type="image/x-icon">
    <title>Profil - Roti'O</title>
    <link href="https://fonts.googleapis.com/css?family=Kaushan+Script%7CLora:400,700" rel="stylesheet">
    <link rel="stylesheet" href="plugins/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="plugins/bakery-icon/style.css">
    <link rel="stylesheet" href="plugins/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="plugins/owl-carousel/assets/owl.carousel.css">
    <link rel="stylesheet" href="plugins/jquery-bar-rating/dist/themes/fontawesome-stars.css">
    <link rel="stylesheet" href="plugins/bootstrap-select/dist/css/bootstrap-select.min.css">
    <link rel="stylesheet" href="plugins/jquery-ui/jquery-ui.min.css">
    <link rel="stylesheet" href="plugins/slick/slick/slick.css">
    <link rel="stylesheet" href="plugins/lightGallery-master/dist/css/lightgallery.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/header-nav.css">
    <style>
      /* Custom CSS untuk TikTok icon */
      .fa-tiktok:before {
        content: "";
        background-image: url("data:image/svg+xml,%3Csvg id='fi_3046120' enable-background='new 0 0 512 512' height='512' viewBox='0 0 512 512' width='512' xmlns='http://www.w3.org/2000/svg'%3E%3Cg%3E%3Cpath d='m480.32 128.39c-29.22 0-56.18-9.68-77.83-26.01-24.83-18.72-42.67-46.18-48.97-77.83-1.56-7.82-2.4-15.89-2.48-24.16h-83.47v228.08l-.1 124.93c0 33.4-21.75 61.72-51.9 71.68-8.75 2.89-18.2 4.26-28.04 3.72-12.56-.69-24.33-4.48-34.56-10.6-21.77-13.02-36.53-36.64-36.93-63.66-.63-42.23 33.51-76.66 75.71-76.66 8.33 0 16.33 1.36 23.82 3.83v-62.34-22.41c-7.9-1.17-15.94-1.78-24.07-1.78-46.19 0-89.39 19.2-120.27 53.79-23.34 26.14-37.34 59.49-39.5 94.46-2.83 45.94 13.98 89.61 46.58 121.83 4.79 4.73 9.82 9.12 15.08 13.17 27.95 21.51 62.12 33.17 98.11 33.17 8.13 0 16.17-.6 24.07-1.77 33.62-4.98 64.64-20.37 89.12-44.57 30.08-29.73 46.7-69.2 46.88-111.21l-.43-186.56c14.35 11.07 30.04 20.23 46.88 27.34 26.19 11.05 53.96 16.65 82.54 16.64v-60.61-22.49c.02.02-.22.02-.24.02z'/%3E%3C/g%3E%3C/svg%3E");
        background-size: contain;
        background-repeat: no-repeat;
        background-position: center;
        display: inline-block;
        width: 16px;
        height: 16px;
        vertical-align: middle;
      }
      .fa-brands.fa-tiktok {
        font-family: inherit;
      }
    </style>
    <style>
        .profile-container {
            background: linear-gradient(135deg, #F2E205 0%, #F2CB05 100%);
            min-height: 100vh;
            padding: 40px 0;
            margin-top: 120px;
        }
        
        /* Sidebar Styling */
        .profile-sidebar {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(64, 36, 1, 0.15);
            border: 3px solid #402401;
            overflow: hidden;
            height: fit-content;
            position: sticky;
            top: 20px;
        }
        
        .profile-avatar {
            background: linear-gradient(135deg, #402401 0%, #F2CB05 50%, #F2E205 100%);
            padding: 40px 30px;
            text-align: center;
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        .profile-avatar::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="75" cy="75" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="50" cy="10" r="0.5" fill="rgba(255,255,255,0.1)"/><circle cx="10" cy="60" r="0.5" fill="rgba(255,255,255,0.1)"/><circle cx="90" cy="40" r="0.5" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }
        
        .avatar-circle {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            position: relative;
            z-index: 1;
        }
        
        .avatar-circle i {
            font-size: 40px;
            color: white;
        }
        
        .profile-avatar h3 {
            margin: 0 0 10px 0;
            font-size: 1.5rem;
            font-weight: 700;
            position: relative;
            z-index: 1;
        }
        
        .user-email {
            margin: 0 0 25px 0;
            opacity: 0.9;
            font-size: 0.9rem;
            position: relative;
            z-index: 1;
        }
        
        .profile-stats {
            display: flex;
            justify-content: space-around;
            position: relative;
            z-index: 1;
        }
        
        .stat-item {
            text-align: center;
            padding: 15px 10px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            flex: 1;
            margin: 0 5px;
            transition: all 0.3s ease;
        }
        
        .stat-item:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }
        
        .stat-item i {
            font-size: 20px;
            display: block;
            margin-bottom: 5px;
        }
        
        .stat-item span {
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .quick-actions {
            padding: 30px;
        }
        
        .quick-actions h4 {
            color: #402401;
            margin-bottom: 20px;
            font-weight: 700;
            font-size: 1.2rem;
        }
        
        .quick-action-btn {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            margin-bottom: 10px;
            background: linear-gradient(135deg, #F2E205 0%, #F2CB05 100%);
            border: 2px solid #402401;
            border-radius: 12px;
            color: #402401;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .quick-action-btn:hover {
            transform: translateX(5px);
            background: linear-gradient(135deg, #402401 0%, #F2E205 100%);
            color: white;
            text-decoration: none;
            box-shadow: 0 5px 15px rgba(64, 36, 1, 0.2);
        }
        
        .quick-action-btn i {
            margin-right: 12px;
            font-size: 18px;
            width: 20px;
        }
        
        /* Main Content Styling */
        .profile-main {
            margin-bottom: 30px;
        }
        
        .profile-form-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(64, 36, 1, 0.15);
            border: 3px solid #402401;
            overflow: hidden;
        }
        
        .form-header {
            background: linear-gradient(135deg, #402401 0%, #F2CB05 50%, #F2E205 100%);
            color: white;
            padding: 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .form-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="75" cy="75" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="50" cy="10" r="0.5" fill="rgba(255,255,255,0.1)"/><circle cx="10" cy="60" r="0.5" fill="rgba(255,255,255,0.1)"/><circle cx="90" cy="40" r="0.5" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }
        
        .form-header h2 {
            margin: 0 0 10px 0;
            font-size: 2rem;
            font-weight: 700;
            position: relative;
            z-index: 1;
        }
        
        .form-header p {
            margin: 0;
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }
        
        .form-header i {
            margin-right: 10px;
        }
        
        /* Form Sections */
        .form-section {
            padding: 30px;
            border-bottom: 1px solid #e9ecef;
        }
        
        .form-section:last-child {
            border-bottom: none;
        }
        
        .section-header {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 3px solid #F2CB05;
            position: relative;
        }
        
        .section-header::after {
            content: '';
            position: absolute;
            bottom: -3px;
            left: 0;
            width: 50px;
            height: 3px;
            background: linear-gradient(90deg, #402401 0%, #F2E205 100%);
        }
        
        .section-header i {
            font-size: 24px;
            color: #402401;
            margin-right: 15px;
        }
        
        .section-header h3 {
            margin: 0;
            color: #402401;
            font-weight: 700;
            font-size: 1.4rem;
        }
        
        .section-description {
            color: #6c757d;
            font-style: italic;
            margin-bottom: 20px;
        }
        
        /* Form Elements */
        .form-group {
            margin-bottom: 25px;
            animation: fadeInUp 0.6s ease forwards;
            opacity: 0;
            transform: translateY(20px);
        }
        
        .form-group label {
            color: #402401;
            font-weight: 600;
            margin-bottom: 8px;
            display: block;
            font-size: 0.95rem;
        }
        
        .form-group label i {
            margin-right: 8px;
            color: #F2CB05;
        }
        
        .form-control {
            border: 2px solid #F2CB05;
            border-radius: 12px;
            padding: 15px 18px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: #fefefe;
            color: #402401;
        }
        
        .form-control:focus {
            border-color: #402401;
            box-shadow: 0 0 0 0.3rem rgba(242, 203, 5, 0.25);
            background: white;
            outline: none;
        }
        
        .form-control:read-only {
            background: #f8f9fa;
            border-color: #e9ecef;
            color: #6c757d;
        }
        
        /* Action Buttons */
        .form-actions {
            padding: 30px;
            background: linear-gradient(145deg, #f8f9fa 0%, #e9ecef 100%);
            text-align: center;
        }
        
        .btn-update {
            background: linear-gradient(135deg, #402401 0%, #F2CB05 100%);
            border: none;
            border-radius: 12px;
            padding: 15px 35px;
            font-size: 16px;
            font-weight: 700;
            color: white;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 15px rgba(64, 36, 1, 0.2);
            margin-right: 15px;
        }
        
        .btn-update:hover {
            transform: translateY(-3px);
            color: white;
            box-shadow: 0 8px 25px rgba(64, 36, 1, 0.3);
            background: linear-gradient(135deg, #F2CB05 0%, #402401 100%);
        }
        
        .btn-reset {
            background: linear-gradient(135deg, #F2E205 0%, #F2CB05 100%);
            border: 2px solid #402401;
            border-radius: 12px;
            padding: 15px 35px;
            font-size: 16px;
            font-weight: 700;
            color: #402401;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .btn-reset:hover {
            transform: translateY(-3px);
            color: #402401;
            box-shadow: 0 8px 25px rgba(242, 203, 5, 0.3);
            background: linear-gradient(135deg, #402401 0%, #F2E205 100%);
            color: white;
        }
        
        /* Alerts */
        .alert {
            border-radius: 12px;
            margin-bottom: 25px;
            border: none;
            padding: 15px 20px;
            font-weight: 500;
            display: flex;
            align-items: center;
        }
        
        .alert i {
            margin-right: 10px;
            font-size: 18px;
        }
        
        .alert-success {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
            border-left: 4px solid #28a745;
        }
        
        .alert-danger {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        
        .text-muted {
            color: #6c757d !important;
            font-style: italic;
            font-size: 0.9rem;
        }
        
        /* Animation */
        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Responsive Design */
        @media (max-width: 991px) {
            .profile-sidebar {
                position: static;
                margin-bottom: 30px;
            }
        }
        
        @media (max-width: 768px) {
            .profile-container {
                padding: 20px 0;
            }
            
            .form-section {
                padding: 20px;
            }
            
            .form-header {
                padding: 20px;
            }
            
            .form-header h2 {
                font-size: 1.5rem;
            }
            
            .btn-update, .btn-reset {
                width: 100%;
                margin-right: 0;
                margin-bottom: 10px;
            }
            
            .profile-avatar {
                padding: 30px 20px;
            }
            
            .quick-actions {
                padding: 20px;
            }
        }
    </style>
</head>
<body>

    <header class="header" data-sticky="false">
  <div class="ps-container">
    <div class="header-wrapper">
      <!-- Logo Left -->
      <div class="header-logo">
        <a class="ps-logo" href="index.php">
          <img src="images/logo-rotio.png" alt="Roti'O">
        </a>
      </div>
      <!-- Desktop Nav (hidden on mobile) -->
      <nav class="header-nav" id="headerNav">
        <ul class="menu">
          <li class="menu-item-has-children"><a href="index.php">Beranda</a></li>
          <li><a href="about.php">Tentang</a></li>
          <li class="menu-item-has-children">
            <a href="#">Produk</a>
            <span class="sub-toggle"><i class="fa fa-angle-down"></i></span>
            <ul class="sub-menu">
              <li><a href="product-listing.php">Daftar Produk</a></li>
              <li><a href="order-form.php">Formulir Pesanan</a></li>
            </ul>
          </li>
          <li class="menu-item-has-children">
            <a href="#">Lainnya</a>
            <span class="sub-toggle"><i class="fa fa-angle-down"></i></span>
            <ul class="sub-menu">
              <li><a href="blog-grid.php">Blog</a></li>
              <li><a href="store.php">Toko Kami</a></li>
            </ul>
          </li>
          <li><a href="contact.php">Hubungi Kami</a></li>
        </ul>
      </nav>
      <!-- Header Actions (always right) -->
      <div class="header__actions">
        <div class="header-action-item header-profile">
          <?php if (isset($_SESSION['user_id'])): ?>
            <div class="ps-dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                <i class="ba-profile"></i>
                <span class="profile-name"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
              </a>
              <ul class="dropdown-menu">
                <li><a href="logo-orders.php">Pesanan Saya</a></li>
                <li><a href="profile.php">Profil</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a href="logout.php">Keluar</a></li>
              </ul>
            </div>
          <?php else: ?>
            <a href="login.php"><i class="ba-profile"></i></a>
          <?php endif; ?>
        </div>
        <div class="header-action-item header-cart">
          <div class="ps-cart">
            <a class="ps-cart__toggle" href="cart.php">
              <span class="cart-badge"><i><?php echo $cart_count; ?></i></span>
              <i class="ba-shopping"></i>
            </a>
            <div class="ps-cart__listing">
              <div class="ps-cart__content">
                <?php if (!empty($_SESSION['cart'])): ?>
                  <?php $count = 0; foreach ($_SESSION['cart'] as $item): $count++; if ($count <= 6): ?>
                    <div class="ps-cart-item">
                      <a class="ps-cart-item__close" href="cart.php?action=remove&id=<?php echo $item['id']; ?>"></a>
                      <div class="ps-cart-item__thumbnail">
                        <a href="product-detail.php?id=<?php echo $item['id']; ?>"></a>
                        <?php if (!empty($item['image_data'])): ?>
                          <?php echo displayImage($item['image_data'], $item['image_mime'], '', $item['name']); ?>
                        <?php else: ?>
                          <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>">
                        <?php endif; ?>
                      </div>
                      <div class="ps-cart-item__content">
                        <a class="ps-cart-item__title" href="product-detail.php?id=<?php echo $item['id']; ?>">
                          <?php echo $item['name']; ?>
                        </a>
                        <p>
                          <span>Jumlah:<i><?php echo $item['quantity']; ?></i></span>
                          <span>Total:<i>Rp<?php echo number_format($item['price'] * $item['quantity'], 3); ?></i></span>
                        </p>
                      </div>
                    </div>
                  <?php endif; endforeach; ?>
                <?php else: ?>
                  <div class="ps-cart-item">
                    <div class="ps-cart-item__content">
                      <p>Keranjang belanja Anda kosong</p>
                    </div>
                  </div>
                <?php endif; ?>
              </div>
              <div class="ps-cart__total">
                <p>Jumlah item:<span><?php echo $cart_count; ?></span></p>
                <p>Total Item:<span>Rp <?php echo number_format($cart_total, 3); ?></span></p>
              </div>
              <div class="ps-cart__footer">
                <a href="cart.php">Checkout</a>
              </div>
            </div>
          </div>
        </div>
        <!-- Mobile Menu Toggle Button -->
        <div class="header-action-item mobile-menu-toggle">
          <button class="menu-toggle-btn" id="mobileMenuToggle" aria-label="Buka Menu">
            <i class="fa fa-bars" id="menuIcon"></i>
          </button>
        </div>
      </div>
    </div>
  </div>
</header>

<!-- Mobile Menu Overlay -->
<div class="mobile-menu-overlay" id="mobileMenuOverlay"></div>

<!-- Mobile Navigation -->
<nav class="mobile-nav" id="mobileNav">
  <button class="mobile-menu-close" id="mobileMenuClose" aria-label="Tutup Menu">
    <i class="fa fa-times"></i>
  </button>
  <ul class="menu">
    <li class="menu-item-has-children"><a href="index.php">Beranda</a></li>
    <li><a href="about.php">Tentang</a></li>
    <li class="menu-item-has-children">
      <a href="#">Produk</a>
      <span class="sub-toggle"><i class="fa fa-angle-down"></i></span>
      <ul class="sub-menu">
        <li><a href="product-listing.php">Daftar Produk</a></li>
        <li><a href="order-form.php">Formulir Pesanan</a></li>
      </ul>
    </li>
    <li class="menu-item-has-children">
      <a href="#">Lainnya</a>
      <span class="sub-toggle"><i class="fa fa-angle-down"></i></span>
      <ul class="sub-menu">
        <li><a href="blog-grid.php">Blog</a></li>
        <li><a href="store.php">Toko Kami</a></li>
      </ul>
    </li>
    <li><a href="contact.php">Hubungi Kami</a></li>
  </ul>
</nav>

    <!-- Main Content -->
    <div class="profile-container">
        <div class="ps-container">
            <div class="row">
                <!-- Sidebar Profile Info -->
                <div class="col-lg-4 col-md-12 mb-4">
                    <div class="profile-sidebar">
                        <div class="profile-avatar">
                            <div class="avatar-circle">
                                <i class="fa fa-user"></i>
                            </div>
                            <h3><?php echo htmlspecialchars($user['name']); ?></h3>
                            <p class="user-email"><?php echo htmlspecialchars($user['email']); ?></p>
                            <div class="profile-stats">
                                <div class="stat-item">
                                    <i class="fa fa-shopping-bag"></i>
                                    <span>Pesanan</span>
                                </div>
                                <div class="stat-item">
                                    <i class="fa fa-heart"></i>
                                    <span>Favorit</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="quick-actions">
                            <h4>Aksi Cepat</h4>
                            <a href="logo-orders.php" class="quick-action-btn">
                                <i class="fa fa-list"></i>
                                <span>Pesanan Saya</span>
                            </a>
                            <a href="cart.php" class="quick-action-btn">
                                <i class="fa fa-shopping-cart"></i>
                                <span>Keranjang Belanja</span>
                            </a>
                            <a href="index.php" class="quick-action-btn">
                                <i class="fa fa-home"></i>
                                <span>Kembali ke Beranda</span>
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Main Profile Content -->
                <div class="col-lg-8 col-md-12">
                    <div class="profile-main">
                        <!-- Alert Messages -->
                            <?php if ($error): ?>
                                <div class="alert alert-danger">
                                <i class="fa fa-exclamation-triangle"></i>
                                    <?php echo htmlspecialchars($error); ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($success): ?>
                                <div class="alert alert-success">
                                <i class="fa fa-check-circle"></i>
                                    <?php echo htmlspecialchars($success); ?>
                                </div>
                            <?php endif; ?>
                        
                        <!-- Profile Form -->
                        <div class="profile-form-card">
                            <div class="form-header">
                                <h2><i class="fa fa-edit"></i> Edit Profil</h2>
                                <p>Perbarui informasi pribadi dan pengaturan akun Anda</p>
                            </div>
                            
                            <form method="POST" action="">
                                <!-- Personal Information Section -->
                                <div class="form-section">
                                    <div class="section-header">
                                        <i class="fa fa-user-circle"></i>
                                        <h3>Informasi Pribadi</h3>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                <div class="form-group">
                                                <label for="name">
                                                    <i class="fa fa-user"></i> Nama Lengkap
                                                </label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="<?php echo htmlspecialchars($user['name']); ?>" required>
                                            </div>
                                </div>
                                
                                        <div class="col-md-6">
                                <div class="form-group">
                                                <label for="email">
                                                    <i class="fa fa-envelope"></i> Alamat Email
                                                </label>
                                                <input type="email" class="form-control" id="email" 
                                                       value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                                    <small class="text-muted">Email tidak dapat diubah</small>
                                            </div>
                                        </div>
                                </div>
                                
                                    <div class="row">
                                        <div class="col-md-6">
                                <div class="form-group">
                                                <label for="phone">
                                                    <i class="fa fa-phone"></i> Nomor Telepon
                                                </label>
                                    <input type="tel" class="form-control" id="phone" name="phone" 
                                           value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                                            </div>
                                </div>
                                
                                        <div class="col-md-6">
                                <div class="form-group">
                                                <label for="address">
                                                    <i class="fa fa-map-marker"></i> Alamat
                                                </label>
                                    <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Password Change Section -->
                                <div class="form-section">
                                    <div class="section-header">
                                        <i class="fa fa-lock"></i>
                                        <h3>Ubah Password</h3>
                                    </div>
                                    <p class="section-description">Kosongkan jika Anda tidak ingin mengubah password</p>
                                    
                                    <div class="row">
                                        <div class="col-md-4">
                                <div class="form-group">
                                                <label for="current_password">
                                                    <i class="fa fa-key"></i> Password Saat Ini
                                                </label>
                                    <input type="password" class="form-control" id="current_password" name="current_password">
                                            </div>
                                </div>
                                
                                        <div class="col-md-4">
                                <div class="form-group">
                                                <label for="new_password">
                                                    <i class="fa fa-lock"></i> Password Baru
                                                </label>
                                    <input type="password" class="form-control" id="new_password" name="new_password">
                                            </div>
                                </div>
                                
                                        <div class="col-md-4">
                                <div class="form-group">
                                                <label for="confirm_password">
                                                    <i class="fa fa-check-circle"></i> Konfirmasi Password
                                                </label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Action Buttons -->
                                <div class="form-actions">
                                    <button type="submit" class="btn btn-update">
                                        <i class="fa fa-save"></i> Perbarui Profil
                                    </button>
                                    <button type="reset" class="btn btn-reset">
                                        <i class="fa fa-undo"></i> Reset Formulir
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="ps-footer">
      <div class="ps-footer__content">
        <div class="ps-container">
          <div class="row">
            <div class="col-lg-4 col-md-12 col-sm-12 col-xs-12 ">
              <div class="ps-site-info"><a class="ps-logo" href="index.php"><img src="images/logo-rotio.png" alt=""></a>
                <p>Roti'O, sahabat setia perjalanan dengan aroma khas kopi dan tekstur renyah lembut.</p>  
              </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-12 col-xs-12 ">
              <form class="ps-form--subscribe-offer" id="newsletterForm" method="post">
                <h4>Dapatkan berita terbaru</h4>
                <div class="form-group">
                  <input class="form-control" type="email" name="email" id="newsletterEmail" placeholder="Email Anda..." required>
                  <button type="submit" id="newsletterBtn">Ikuti Laman</button>
                </div>
                <p>* Jangan khawatir, kami tidak pernah spam</p>
                <div id="newsletterMessage"></div>
              </form>
              <div class="ps-footer__contact">
                <h4>Hubungi Kami</h4>
                <p>Jl. Raya Cikarang, Kota Bekasi, Jawa Barat</p>
                <P>(+62) 812-3456-7890</P>
              </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-12 col-xs-12 ">
              <div class="ps-footer__open">
                <h4>Jam Buka</h4>
                <p>
                  Senin - Jumat: <br>08:00 am - 08:30 pm <br>
                  Sabtu - Minggu:<br>
                  10:00 am - 16:30 pm
                </p>
                <ul class="ps-list--social">
                  <li><a href="https://www.facebook.com/share/19g2Ds4bML/"><i class="fa fa-facebook"></i></a></li>
                  <li><a href="https://www.tiktok.com/@rotio.indonesia?_t=ZS-8xdJVQ8gKAc&_r=1"><i class="fa-brands fa-tiktok"></i></a></li>
                  <li><a href="https://www.instagram.com/rotio.indonesia?igsh=N2NqdTIwcWFoc2h5"><i class="fa fa-instagram"></i></a></li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </footer>

    <!-- Back to Top -->
    <div id="back2top"><i class="fa fa-angle-up"></i></div>
    
    <!-- Loading -->
    <div class="ps-loading">
        <div class="rectangle-bounce">
            <div class="rect1"></div>
            <div class="rect2"></div>
            <div class="rect3"></div>
            <div class="rect4"></div>
            <div class="rect5"></div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="plugins/jquery/dist/jquery.min.js"></script>
    <script src="plugins/bootstrap/dist/js/bootstrap.min.js"></script>
    <script src="plugins/owl-carousel/owl.carousel.min.js"></script>
    <script src="plugins/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
    <script src="plugins/jquery-bar-rating/dist/jquery.barrating.min.js"></script>
    <script src="plugins/jquery.waypoints.min.js"></script>
    <script src="plugins/jquery.countTo.js"></script>
    <script src="plugins/jquery.matchHeight-min.js"></script>
    <script src="plugins/jquery-ui/jquery-ui.min.js"></script>
    <script src="plugins/gmap3.min.js"></script>
    <script src="plugins/lightGallery-master/dist/js/lightgallery-all.min.js"></script>
    <script src="plugins/slick/slick/slick.min.js"></script>
    <script src="plugins/slick-animation.min.js"></script>
    <script src="plugins/jquery.slimscroll.min.js"></script>
    <script src="js/main.js"></script>
    <script src="js/header.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDsUcTjt43mTheN9ruCsQVgBE-wgN6_AfY&amp;region=GB"></script>
    <!-- Newsletter JavaScript -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const newsletterForm = document.getElementById('newsletterForm');
        const newsletterMessage = document.getElementById('newsletterMessage');
        const newsletterBtn = document.getElementById('newsletterBtn');
        
        if (newsletterForm) {
            newsletterForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const email = document.getElementById('newsletterEmail').value;
                
                if (!email) {
                    showMessage('Email tidak boleh kosong', 'error');
                    return;
                }
                
                // Disable button
                newsletterBtn.disabled = true;
                newsletterBtn.textContent = 'Berlangganan...';
                
                // Send AJAX request
                const formData = new FormData();
                formData.append('action', 'subscribe');
                formData.append('email', email);
                
                fetch('newsletter_handler.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showMessage(data.message, 'success');
                        newsletterForm.reset();
                    } else {
                        showMessage(data.message, 'error');
                    }
                })
                .catch(error => {
                    showMessage('Terjadi kesalahan. Silakan coba lagi.', 'error');
                })
                .finally(() => {
                    // Re-enable button
                    newsletterBtn.disabled = false;
                    newsletterBtn.textContent = 'Berlangganan';
                });
            });
        }
        
        function showMessage(message, type) {
            newsletterMessage.innerHTML = `<div class="alert alert-${type === 'success' ? 'success' : 'danger'}">${message}</div>`;
            
            // Auto hide after 5 seconds
            setTimeout(() => {
                newsletterMessage.innerHTML = '';
            }, 5000);
        }
    });
    </script>
</body>
</html> 