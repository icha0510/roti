<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'includes/functions.php';
require_once 'config/database.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Anda harus login terlebih dahulu untuk melakukan checkout.";
    header('Location: login.php');
    exit;
}

// Ambil data user
$user = getUserById($_SESSION['user_id']);

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Redirect if cart is empty
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    $_SESSION['error'] = "Keranjang belanja Anda kosong.";
    header('Location: cart.php');
    exit;
}

// Calculate totals
$cart_total = 0;
$cart_count = 0;
foreach ($_SESSION['cart'] as $item) {
    $cart_total += $item['price'] * $item['quantity'];
    $cart_count += $item['quantity'];
}

// Ambil daftar meja aktif dan tidak terisi
$meja_stmt = $db->prepare('SELECT t.kode_meja FROM tables t WHERE t.status = "aktif" AND t.kode_meja NOT IN (SELECT nomor_meja FROM orders WHERE status NOT IN ("completed", "cancelled")) ORDER BY t.kode_meja ASC');
$meja_stmt->execute();
$meja_tersedia = $meja_stmt->fetchAll(PDO::FETCH_COLUMN);

// Initialize variables for form data
$order_data = array();
$errors = array();

// Handle form submission
if ($_POST && isset($_POST['place_order'])) {
    $customer_name = trim($_POST['customer_name']);
    $customer_email = trim($_POST['customer_email']);
    $customer_phone = trim($_POST['customer_phone']);
    $nomor_meja = trim($_POST['nomor_meja']);
    $notes = trim($_POST['notes'] ?? '');
    $user_id = $_SESSION['user_id'];
    
    // Store form data for repopulation if there are errors
    $order_data = array(
        'customer_name' => $customer_name,
        'customer_email' => $customer_email,
        'customer_phone' => $customer_phone,
        'nomor_meja' => $nomor_meja,
        'notes' => $notes
    );
    
    // Basic validation
    if (empty($customer_name)) $errors[] = "Nama wajib diisi";
    if (empty($customer_email)) $errors[] = "Email wajib diisi";
    if (!filter_var($customer_email, FILTER_VALIDATE_EMAIL)) $errors[] = "Format email tidak valid";
    if (empty($customer_phone)) $errors[] = "Nomor telepon wajib diisi";
    if (empty($nomor_meja)) $errors[] = "Nomor meja wajib dipilih";
    // Validasi meja tersedia
    $cek_meja = $db->prepare('SELECT COUNT(*) FROM tables t WHERE t.kode_meja = ? AND t.status = "aktif"');
    $cek_meja->execute([$nomor_meja]);
    if ($cek_meja->fetchColumn() == 0) {
        $errors[] = "Nomor meja tidak tersedia.";
    } else {
        $cek_isi = $db->prepare('SELECT COUNT(*) FROM orders WHERE nomor_meja = ? AND status NOT IN ("completed", "cancelled")');
        $cek_isi->execute([$nomor_meja]);
        if ($cek_isi->fetchColumn() > 0) {
            $errors[] = "Meja sudah terisi, silakan pilih meja lain.";
        }
    }
    
    // Validate cart items
    if (empty($_SESSION['cart'])) {
        $errors[] = "Keranjang belanja kosong";
    }
    
    if (empty($errors)) {
        try {
            $db->beginTransaction();
            
            // Generate unique order number
            $order_number = 'ORD-' . date('Y') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
            
            // Insert order
            $stmt = $db->prepare("
                INSERT INTO orders (
                    order_number, user_id, customer_name, customer_email, customer_phone, nomor_meja,
                    notes, total_amount, status, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
            ");
            
            $result = $stmt->execute([
                $order_number,
                $user_id,
                $customer_name,
                $customer_email,
                $customer_phone,
                $nomor_meja,
                $notes,
                $cart_total
            ]);
            
            if (!$result) {
                throw new Exception("Gagal menyimpan data order");
            }
            
            $order_id = $db->lastInsertId();
            
            // Insert order items
            $stmt = $db->prepare("
                INSERT INTO order_items (order_id, product_id, product_name, quantity, price)
                VALUES (?, ?, ?, ?, ?)
            ");
            
            foreach ($_SESSION['cart'] as $product_id => $item) {
                $result = $stmt->execute([
                    $order_id,
                    $product_id,
                    $item['name'],
                    $item['quantity'],
                    $item['price']
                ]);
                
                if (!$result) {
                    throw new Exception("Gagal menyimpan item order");
                }
            }
            
            // Insert order tracking
            $stmt = $db->prepare("INSERT INTO order_tracking (order_id, status, description, created_at) VALUES (?, 'pending', 'Order has been placed successfully', NOW())");
            $stmt->execute([$order_id]);
            
            $db->commit();
            
            // Clear cart and set success message
            $_SESSION['cart'] = array();
            $_SESSION['order_success'] = [
                'order_id' => $order_id,
                'order_number' => $order_number,
                'customer_name' => $customer_name,
                'total_amount' => $cart_total,
                'nomor_meja' => $nomor_meja
            ];
            
            // Redirect to success page
            header('Location: order-success.php');
            exit;
            
        } catch (Exception $e) {
            $db->rollBack();
            $errors[] = "Gagal menyimpan pesanan: " . $e->getMessage();
            error_log("Order error: " . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<!--[if IE 7]><html class="ie ie7"><![endif]-->
<!--[if IE 8]><html class="ie ie8"><![endif]-->
<!--[if IE 9]><html class="ie ie9"><![endif]-->
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="format-detection" content="telephone=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <link href="apple-touch-icon.png" rel="apple-touch-icon">
    <link rel="icon" href="images/logo-rotio.png" type="image/x-icon">
    <meta name="author" content="">
    <meta name="keywords" content="">
    <meta name="description" content="">
    <title>Checkout - Roti'O</title>
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
    <style>
        /* Custom styling untuk checkout order */
        .ps-form__orders {
            position: relative;
            overflow: hidden;
        }
        
        .ps-form__orders::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(230, 126, 34, 0.1), transparent);
            transform: rotate(45deg);
            animation: shimmer 3s infinite;
            pointer-events: none;
        }
        
        @keyframes shimmer {
            0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
            100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
        }
        
        .ps-product--cart:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            border-left-color: #f39c12;
        }
        
        .ps-product--cart {
            position: relative;
            overflow: hidden;
        }
        
        .ps-product--cart::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
            transition: left 0.5s;
        }
        
        .ps-product--cart:hover::after {
            left: 100%;
        }
        
        .ps-btn--yellow:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(230, 126, 34, 0.4);
        }
        
        .ps-link:hover {
            color: #e67e22 !important;
        }
        
        /* Custom scrollbar */
        .ps-block--checkout-orders::-webkit-scrollbar {
            width: 6px;
        }
        
        .ps-block--checkout-orders::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }
        
        .ps-block--checkout-orders::-webkit-scrollbar-thumb {
            background: linear-gradient(180deg, #e67e22, #f39c12);
            border-radius: 3px;
        }
        
        .ps-block--checkout-orders::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(180deg, #d35400, #e67e22);
        }
        
        /* Pulse animation untuk total */
        .ps-block__footer {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(52, 73, 94, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(52, 73, 94, 0); }
            100% { box-shadow: 0 0 0 0 rgba(52, 73, 94, 0); }
        }
    </style>
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
    <!--HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries-->
    <!--WARNING: Respond.js doesn't work if you view the page via file://-->
    <!--[if lt IE 9]><script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script><script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script><![endif]-->
    <!--[if IE 7]><body class="ie7 lt-ie8 lt-ie9 lt-ie10"><![endif]-->
    <!--[if IE 8]><body class="ie8 lt-ie9 lt-ie10"><![endif]-->
    <!--[if IE 9]><body class="ie9 lt-ie10"><![endif]-->
    <style>
        .checkout-item-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #cd9b33;
            box-shadow: 0 2px 8px rgba(205, 155, 51, 0.10);
            background: #fff;
            display: block;
            margin: 0 auto 10px auto;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .ps-product--cart:hover .checkout-item-image {
            transform: scale(1.05);
            box-shadow: 0 4px 16px rgba(205, 155, 51, 0.18);
        }
        .ps-product--cart {
            text-align: center;
            padding-bottom: 10px;
        }
    </style>
</head>
<body>
    
    <!-- Header-->
<header class="header header--3" data-sticky="false">
  <div class="ps-container">
    <nav class="navigation">
      <div class="header-wrapper">
        
        <!-- Logo Section -->
        <div class="header-logo">
          <a class="ps-logo" href="index.php">
            <img src="images/logo-rotio.png" alt="">
          </a>
        </div>

                    <!-- Navigation Menu -->
            <div class="header-nav">
              <ul class="menu">
                <li class="menu-item-has-children">
                  <a href="index.php">Beranda</a>
                </li>
                <li>
                  <a href="about.php">Tentang</a>
                </li>
                <li class="menu-item-has-children">
                  <a href="#">Produk</a>
                  <span class="sub-toggle">
                    <i class="fa fa-angle-down"></i>
                  </span>
                  <ul class="sub-menu">
                    <li><a href="product-listing.php">Daftar Produk</a></li>
                    <li><a href="order-form.php">Formulir Pesanan</a></li>
                  </ul>
                </li>   
                <li class="menu-item-has-children">
                  <a href="#">Lainnya</a>
                  <span class="sub-toggle">
                    <i class="fa fa-angle-down"></i>
                  </span>
                  <ul class="sub-menu">
                    <li><a href="blog-grid.php">Blog</a></li>
                    <li><a href="store.php">Toko Kami</a></li>
                  </ul>
                </li>
                <li>
                  <a href="contact.php">Hubungi Kami</a>
                </li>
              </ul>
            </div>

        <!-- Mobile Menu Toggle -->
        <div class="menu-toggle">
          <span></span>
        </div>
        
        <!-- Header Actions -->
        <div class="header__actions">
          
          <!-- User Profile Dropdown -->
          <div class="header-profile">
            <?php if (isset($_SESSION['user_id'])): ?>
              <div class="ps-dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                  <i class="ba-profile"></i>
                  <span><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                </a>
                                    <ul class="dropdown-menu">
                      <li>
                        <a href="logo-orders.php">Pesanan Saya</a>
                      </li>
                      <li>
                        <a href="profile.php">Profil</a>
                      </li>
                      <li>
                        <hr class="dropdown-divider">
                      </li>
                      <li>
                        <a href="logout.php">Keluar</a>
                      </li>
                    </ul>
              </div>
            <?php else: ?>
              <a href="login.php">
                <i class="ba-profile"></i>
              </a>
            <?php endif; ?>
          </div>
          
          <!-- Shopping Cart -->
          <div class="header-cart">
            <div class="ps-cart">
              <a class="ps-cart__toggle" href="cart.php">
                <span>
                  <i><?php echo $cart_count; ?></i>
                </span>
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
          
        </div>
      </div>
    </nav>
  </div>
</header>

    <!-- Hero Section -->
    <div class="ps-hero bg--cover" data-background="images/hero/product.jpg">
        <div class="ps-hero__content">
            <h1>Checkout</h1>
            <div class="ps-breadcrumb">
                <ol class="breadcrumb">
                    <li><a href="index.php">Beranda</a></li>
                    <li><a href="cart.php">Keranjang</a></li>
                    <li class="active">Checkout</li>
                </ol>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <main class="ps-main">
        <div class="ps-container">
            <!-- Error Messages -->
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <h4>Terjadi kesalahan:</h4>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="ps-section--shopping ps-checkout">
                <div class="ps-section__content">
                    <form method="POST" class="ps-form--menu ps-form--order-form">
                        <div class="row">
                            <div class="col-lg-8 col-md-8 col-sm-12 col-xs-12">
                                <div class="ps-form__content">
                                    <h3 class="ps-section__title">Formulir Pemesanan</h3>
                                    
                                    <div class="row">
                                        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                            <div class="form-group">
                                                <label>Nama Lengkap <sup>*</sup></label>
                                                <input class="form-control" type="text" name="customer_name" placeholder="Masukkan nama lengkap Anda" 
                                                       value="<?php echo isset($order_data['customer_name']) ? htmlspecialchars($order_data['customer_name']) : htmlspecialchars($user['name'] ?? ''); ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                            <div class="form-group">
                                                <label>Nomor Telepon <sup>*</sup></label>
                                                <input class="form-control" type="text" name="customer_phone" placeholder="Masukkan nomor telepon" 
                                                       value="<?php echo isset($order_data['customer_phone']) ? htmlspecialchars($order_data['customer_phone']) : htmlspecialchars($user['phone'] ?? ''); ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                            <div class="form-group">
                                                <label>Nomor Meja <sup>*</sup></label>
                                                <select class="form-control" name="nomor_meja" required>
                                                    <option value="">-- Pilih Nomor Meja --</option>
                                                    <?php foreach ($meja_tersedia as $meja): ?>
                                                        <option value="<?php echo htmlspecialchars($meja); ?>" <?php echo (isset($order_data['nomor_meja']) && $order_data['nomor_meja'] == $meja) ? 'selected' : ''; ?>><?php echo htmlspecialchars($meja); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                            <div class="form-group">
                                                <label>Email <sup>*</sup></label>
                                                <input class="form-control" type="email" name="customer_email" placeholder="Masukkan email Anda" 
                                                       value="<?php echo isset($order_data['customer_email']) ? htmlspecialchars($order_data['customer_email']) : htmlspecialchars($user['email'] ?? ''); ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            <div class="form-group">
                                                <label>Catatan Tambahan</label>
                                                <textarea class="form-control" name="notes" rows="4" placeholder="Catatan khusus untuk pesanan Anda (opsional)"><?php echo isset($order_data['notes']) ? htmlspecialchars($order_data['notes']) : ''; ?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                                <div class="ps-form__orders" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 15px; padding: 25px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); border: 1px solid #dee2e6;">
                                    <h3 style="color: #2c3e50; font-weight: 700; margin-bottom: 25px; text-align: center; position: relative;">
                                        <i class="fa fa-shopping-bag" style="margin-right: 10px; color: #e67e22;"></i>
                                        Pesanan Anda
                                        <div style="position: absolute; bottom: -5px; left: 50%; transform: translateX(-50%); width: 50px; height: 3px; background: linear-gradient(90deg, #e67e22, #f39c12); border-radius: 2px;"></div>
                                    </h3>
                                    <div class="ps-block--checkout-orders" style="max-height: 400px; overflow-y: auto; scrollbar-width: thin; scrollbar-color: #e67e22 #f8f9fa;">
                                        <div class="ps-block__content">
                                            <?php foreach ($_SESSION['cart'] as $item): ?>
                                                <div class="ps-product--cart" style="background: white; border-radius: 10px; padding: 15px; margin-bottom: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); transition: all 0.3s ease; border-left: 4px solid #e67e22;">
                                                    <div class="ps-product__thumbnail" style="float: left; margin-right: 15px;">
                                                        <?php if (!empty($item['image_data'])): ?>
                                                            <?php echo displayImage($item['image_data'], $item['image_mime'], 'checkout-item-image', $item['name']); ?>
                                                        <?php else: ?>
                                                            <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>" class="checkout-item-image" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;">
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="ps-product__content" style="overflow: hidden;">
                                                        <a class="ps-product__title" href="product-detail.php?id=<?php echo $item['id']; ?>" style="color: #2c3e50; font-weight: 600; text-decoration: none; display: block; margin-bottom: 5px; font-size: 14px;">
                                                            <?php echo htmlspecialchars($item['name']); ?>
                                                        </a>
                                                        <p style="margin: 0; color: #6c757d; font-size: 12px;">
                                                            <span>Jumlah: <i style="color: #e67e22; font-weight: 600;"><?php echo $item['quantity']; ?></i></span>
                                                        </p>
                                                    </div>
                                                    <div class="ps-product__price" style="float: right; color: #e67e22; font-weight: 700; font-size: 16px; margin-top: 5px;">
                                                        Rp<?php echo number_format($item['price'] * $item['quantity'], 3); ?>
                                                    </div>
                                                    <div style="clear: both;"></div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <div class="ps-block__footer" style="background: linear-gradient(135deg, #2c3e50, #34495e); border-radius: 10px; padding: 20px; margin-top: 20px; text-align: center;">
                                            <h3 style="color: white; margin: 0; font-size: 18px;">
                                                Total <span style="color: #f39c12; font-weight: 700; font-size: 24px;">Rp <?php echo number_format($cart_total, 3); ?></span>
                                            </h3>
                                        </div>
                                    </div>
                                    <div class="ps-form__footer" style="margin-top: 25px;">
                                        <button type="submit" name="place_order" class="ps-btn ps-btn--yellow ps-btn--fullwidth" style="background: linear-gradient(135deg, #e67e22, #f39c12); border: none; border-radius: 25px; padding: 15px; font-weight: 600; font-size: 16px; text-transform: uppercase; letter-spacing: 1px; box-shadow: 0 5px 15px rgba(230, 126, 34, 0.3); transition: all 0.3s ease;">
                                            <i class="fa fa-check-circle" style="margin-right: 8px;"></i>
                                            Kirim Pesanan
                                        </button>
                                        <a class="ps-link" href="cart.php" style="display: block; text-align: center; margin-top: 15px; color: #6c757d; text-decoration: none; font-weight: 500; transition: color 0.3s ease;">
                                            <i class="fa fa-arrow-left" style="margin-right: 5px;"></i>
                                            Kembali ke Keranjang
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <!-- Site Features -->
    <div class="ps-site-features">
        <div class="ps-container">
            <div class="row">
                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12 ">
                    <div class="ps-block--iconbox">
                        <i class="ba-delivery-truck-2"></i>
                        <h4>Pengiriman Gratis <span> Untuk Pesanan Di Atas Rp199.000</h4>
                        <p>Ingin melacak paket? Temukan informasi pelacakan dan detail pesanan dari Pesanan Saya.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12 ">
                    <div class="ps-block--iconbox">
                        <i class="ba-biscuit-1"></i>
                        <h4>Koki Master<span> DENGAN PASSION</h4>
                        <p>Belanja ribuan temuan, dengan kedatangan baru ditambahkan setiap hari.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12 ">
                    <div class="ps-block--iconbox">
                        <i class="ba-flour"></i>
                        <h4>Bahan Alami<span> melindungi keluarga Anda</h4>
                        <p>Kami selalu memastikan keamanan semua produk toko</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12 ">
                    <div class="ps-block--iconbox">
                        <i class="ba-cake-3"></i>
                        <h4>Rasa Menarik <span>SELALU MENDENGARKAN</span></h4>
                        <p>Kami menawarkan hotline pelanggan 24/7 sehingga Anda tidak pernah sendirian jika memiliki pertanyaan.</p>
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
                newsletterBtn.textContent = 'Subscribing...';
                
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
                    newsletterBtn.textContent = 'Subscribe';
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