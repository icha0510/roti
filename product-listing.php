<?php
session_start();
require_once 'includes/functions.php';

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

// Calculate cart totals
$cart_total = 0;
$cart_count = 0;
foreach ($_SESSION['cart'] as $item) {
    $cart_total += $item['price'] * $item['quantity'];
    $cart_count += $item['quantity'];
}

// Ambil parameter dari URL
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : null;
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Ambil data dari database
$categories = getAllCategories();

if ($category_id) {
    $products = getProductsByCategory($category_id);
    $category_name = '';
    foreach ($categories as $cat) {
        if ($cat['id'] == $category_id) {
            $category_name = $cat['name'];
            break;
        }
    }
} else {
    $products = getAllProducts();
    $category_name = 'Semua Produk';
}

$featuredProducts = getAllProducts(6);
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="format-detection" content="telephone=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <link href="apple-touch-icon.png" rel="apple-touch-icon">
    <link href="images/logo-rotio.png" rel="icon">
    <meta name="author" content="">
    <meta name="keywords" content="">
    <meta name="description" content="">
    <title>Daftar Produk - Roti'O</title>
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
      /* Custom Sidebar Styling for Product Categories */
      .ps-sidebar__title {
        position: relative;
        margin-bottom: 25px;
        padding-bottom: 15px;
        font-size: 18px;
        font-weight: 700;
        color: #000;
        text-transform: uppercase;
        border-bottom: 2px solid #cd9b33;
      }

      .ps-sidebar__title:after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        width: 50px;
        height: 2px;
        background-color: #000;
        transition: width 0.3s ease;
      }

      .ps-sidebar__title:hover:after {
        width: 100px;
      }

      .ps-list--arrow li {
        margin-bottom: 8px;
        transition: all 0.3s ease;
      }

      .ps-list--arrow li a {
        position: relative;
        display: block;
        padding: 12px 15px;
        font-family: "Lora", serif;
        font-size: 14px;
        letter-spacing: 0.5px;
        color: #626262;
        background-color: #f8f9fa;
        border-radius: 6px;
        border-left: 3px solid transparent;
        transition: all 0.3s ease;
        text-decoration: none;
      }

      .ps-list--arrow li a:before {
        content: "\f0da";
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        right: 15px;
        font-family: FontAwesome;
        color: #cd9b33;
        opacity: 0;
        transition: all 0.3s ease;
      }

      .ps-list--arrow li a:hover {
        color: #cd9b33;
        background-color: #fff;
        border-left-color: #cd9b33;
        box-shadow: 0 2px 8px rgba(205, 155, 51, 0.15);
        transform: translateX(5px);
      }

      .ps-list--arrow li a:hover:before {
        opacity: 1;
        right: 10px;
      }

      .ps-list--arrow li.current a {
        color: #cd9b33;
        background-color: #fff;
        border-left-color: #cd9b33;
        box-shadow: 0 2px 8px rgba(205, 155, 51, 0.15);
        font-weight: 600;
      }

      .ps-list--arrow li.current a:before {
        opacity: 1;
        right: 10px;
      }

      .ps-list--arrow .badge {
        position: absolute;
        top: 50%;
        right: 35px;
        transform: translateY(-50%);
        background-color: #cd9b33 !important;
        color: white !important;
        font-size: 11px;
        padding: 3px 8px;
        border-radius: 12px;
        font-weight: 600;
      }

      .ps-block--info {
        transition: all 0.3s ease;
      }

      .ps-block--info:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      }

      .ps-block--info h5 {
        transition: color 0.3s ease;
      }

      .ps-block--info:hover h5 {
        color: #000 !important;
      }

      /* Responsive adjustments */
      @media (max-width: 991px) {
        .ps-sidebar {
          margin-bottom: 30px;
        }
        
        .ps-list--arrow li a {
          padding: 15px 20px;
          font-size: 16px;
        }
      }

      @media (max-width: 767px) {
        .ps-sidebar__title {
          font-size: 16px;
        }
        
        .ps-list--arrow li a {
          padding: 12px 15px;
          font-size: 14px;
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
          <li class="menu-item-has-children"><a href="product-listing.php">Produk</a></li>
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
    <li class="menu-item-has-children"><a href="product-listing.php">Produk</a></li>
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

    <!-- Hero Section -->
    <div class="ps-hero bg--cover" data-background="images/hero/product.jpg">
      <div class="ps-hero__content">
        <h1><?php echo $category_name; ?></h1>
        <div class="ps-breadcrumb">
          <ol class="breadcrumb">
            <li><a href="index.php">Beranda</a></li>
            <li><a href="product-listing.php">Produk</a></li>
            <?php if ($category_name != 'Semua Produk'): ?>
            <li class="active"><?php echo $category_name; ?></li>
            <?php else: ?>
            <li class="active">Semua Produk</li>
            <?php endif; ?>
          </ol>
        </div>
      </div>
    </div>

    <!-- Product Listing -->
    <main class="ps-main">
      <div class="ps-container">
        <div class="ps-section__header text-center">
          <h3 class="ps-section__title"><?php echo $category_name; ?></h3>
          <p>Menampilkan <?php echo count($products); ?> produk</p><span><img src="images/icons/floral.png" alt=""></span>
        </div>
        
        <div class="row">
          <!-- Sidebar -->
          <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
            <aside class="ps-sidebar">
              <div class="ps-sidebar__block">
                <h4 class="ps-sidebar__title">
                  <i class="ba-bread-2" style="margin-right: 10px; color: #cd9b33;"></i>
                  Kategori
                </h4>
                <div class="ps-sidebar__content">
                  <ul class="ps-list--arrow">
                    <li class="<?php echo ($category_name == 'Semua Produk') ? 'current' : ''; ?>">
                      <a href="product-listing.php">
                        <i class="fa fa-th-large" style="margin-right: 8px; color: #cd9b33;"></i>
                        Semua Produk
                        <?php if ($category_name == 'Semua Produk'): ?>
                          <span class="badge" style="background-color: #cd9b33; color: white; float: right; margin-top: 2px;"><?php echo count($products); ?></span>
                        <?php endif; ?>
                      </a>
                    </li>
                    <?php foreach ($categories as $category): ?>
                    <li class="<?php echo ($category_name == $category['name']) ? 'current' : ''; ?>">
                      <a href="product-listing.php?category=<?php echo $category['id']; ?>">
                        <i class="fa fa-tag" style="margin-right: 8px; color: #cd9b33;"></i>
                        <?php echo $category['name']; ?>
                        <?php if ($category_name == $category['name']): ?>
                          <span class="badge" style="background-color: #cd9b33; color: white; float: right; margin-top: 2px;"><?php echo count($products); ?></span>
                        <?php endif; ?>
                      </a>
                    </li>
                    <?php endforeach; ?>
                  </ul>
                </div>
              </div>
              
              <!-- Additional Sidebar Block -->
              <div class="ps-sidebar__block" style="margin-top: 30px;">
                <h4 class="ps-sidebar__title">
                  <i class="ba-heart" style="margin-right: 10px; color: #cd9b33;"></i>
                  Informasi Cepat
                </h4>
                <div class="ps-sidebar__content">
                  <div class="ps-block--info" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); padding: 20px; border-radius: 8px; border-left: 4px solid #cd9b33;">
                    <h5 style="color: #cd9b33; margin-bottom: 10px; font-weight: 600;">
                      <i class="fa fa-info-circle" style="margin-right: 5px;"></i>
                      Panduan Produk
                    </h5>
                    <p style="font-size: 14px; line-height: 1.6; color: #666; margin-bottom: 15px;">
                    Jelajahi aroma kopi yang memikat dan kelezatan tiada tara dari Roti'O, sajian yang selalu baru dipanggang untuk Anda. Setiap gigitan menawarkan cita rasa kopi yang unik dan kehangatan yang istimewa.
                    </p>
                    <div style="border-top: 1px solid #dee2e6; padding-top: 15px;">
                      <p style="font-size: 12px; color: #999; margin-bottom: 5px;">
                        <i class="fa fa-clock-o" style="margin-right: 5px;"></i>
                        Diperbarui Setiap Hari
                      </p>
                      <p style="font-size: 12px; color: #999; margin-bottom: 5px;">
                        <i class="fa fa-leaf" style="margin-right: 5px;"></i>
                        Bahan-bahan alami
                      </p>
                      <p style="font-size: 12px; color: #999;">
                        <i class="fa fa-star" style="margin-right: 5px;"></i>
                        Kualitas premium
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </aside>
          </div>

          <!-- Product Grid -->
          <div class="col-lg-9 col-md-9 col-sm-12 col-xs-12">
            <div class="ps-section__content">
              <div class="row">
                <?php if (empty($products)): ?>
                <div class="col-12">
                  <div class="ps-block--empty">
                    <h4>Tidak ada produk ditemukan</h4>
                    <p>Maaf, tidak ada produk yang tersedia dalam kategori ini saat ini.</p>
                  </div>
                </div>
                <?php else: ?>
                  <?php foreach ($products as $product): ?>
                  <div class="col-lg-4 col-md-6 col-sm-6 col-xs-12">
                    <div class="ps-product">
                      <div class="ps-product__thumbnail">
                        <?php echo getProductBadge($product); ?>
                        <?php if (!empty($product['image_data'])): ?>
                          <?php echo displayImage($product['image_data'], $product['image_mime'], 'img-fluid', $product['name']); ?>
                        <?php else: ?>
                          <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>" class="img-fluid">
                        <?php endif; ?>
                        <a class="ps-product__overlay" href="product-detail.php?id=<?php echo $product['id']; ?>"></a>
                        <ul class="ps-product__actions">
                          <!-- <li><a href="#" data-tooltip="Quick View"><i class="ba-magnifying-glass"></i></a></li>
                          <li><a href="#" data-tooltip="Favorite"><i class="ba-heart"></i></a></li> -->
                          <li><a href="cart_actions.php?action=add&id=<?php echo $product['id']; ?>" data-tooltip="Tambah ke Keranjang"><i class="ba-shopping"></i></a></li>
                        </ul>
                      </div>
                      <div class="ps-product__content">
                        <a class="ps-product__title" href="product-detail.php?id=<?php echo $product['id']; ?>"><?php echo $product['name']; ?></a>
                        <p><a href="product-listing.php?category=<?php echo $product['category_id']; ?>"><?php echo $product['category_name']; ?></a></p>
                        <?php echo displayRating($product['rating']); ?>
                        <?php echo displayProductPrice($product); ?>
                      </div>
                    </div>
                  </div>
                  <?php endforeach; ?>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- Site Features -->
    <div class="ps-site-features">
      <div class="ps-container">
        <div class="row">
          <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12 ">
            <div class="ps-block--iconbox"><i class="ba-oven2"></i>
              <h4>Fresh From Oven <span>Baru keluar dari panggangan</span></h4>
              <p>Setiap roti yang kamu nikmati selalu hangat, dan beraroma semerbak.</p>
            </div>
          </div>
          <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12 ">
            <div class="ps-block--iconbox"><i class="ba-biscuit-1"></i>
              <h4>Koki Master<span> DENGAN PASSION</span></h4>
              <p>Koki yang berpengalaman dan berdedikasi.</p>
            </div>
          </div>
          <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12 ">
            <div class="ps-block--iconbox"><i class="ba-flour"></i>
              <h4>Bahan Alami<span> aman untuk keluarga</span></h4>
              <p>Kami selalu memastikan keamanan semua produk toko</p>
            </div>
          </div>
          <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12 ">
            <div class="ps-block--iconbox"><i class="ba-cake-3"></i>
              <h4>Rasa Menarik <span>selalu nikmat</span></h4>
              <p>Kami menawarkan cita rasa yang unik dan menarik.</p>
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

    <div id="back2top"><i class="fa fa-angle-up"></i></div>
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
</body>
</html> 