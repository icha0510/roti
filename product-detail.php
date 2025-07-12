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

// Ambil ID produk dari URL dengan validasi
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Validasi ID produk
if ($product_id <= 0) {
    // Redirect ke halaman produk jika ID tidak valid
    header('Location: product-listing.php');
    exit();
}

// Ambil detail produk dari database
$product = getProductById($product_id);

// Cek apakah produk ditemukan
if (!$product) {
    // Redirect ke halaman produk jika produk tidak ditemukan
    header('Location: product-listing.php');
    exit();
}

// Fungsi untuk mengambil produk terkait
function getRelatedProducts($category_id, $current_product_id, $limit = 4) {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        $sql = "SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.category_id = :category_id AND p.id != :current_id AND p.stock > 0 
                ORDER BY p.created_at DESC 
                LIMIT " . (int)$limit;
        
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
        $stmt->bindParam(':current_id', $current_product_id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        // Return array kosong jika terjadi error
        return array();
    }
}

// Ambil produk terkait dengan error handling
$related_products = array();
if (isset($product['category_id']) && $product['category_id']) {
    $related_products = getRelatedProducts($product['category_id'], $product_id);
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
    <link href="images/logo-rotio.png" rel="icon">
    <title><?php echo htmlspecialchars($product['name']); ?> - Roti'O</title>
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
      /* Tambahan agar konten tidak tertutup header */
      .ps-product-detail {
        margin-top: 120px;
      }
      @media (max-width: 991px) {
        .ps-product-detail {
          margin-top: 80px;
        }
      }
      /* Styling untuk stock status */
      .in-stock {
        color: #28a745;
        font-weight: bold;
      }
      .out-of-stock {
        color: #dc3545;
        font-weight: bold;
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

    <!-- Breadcrumb -->
    <div class="ps-breadcrumb">
        <div class="ps-container">
            <ul class="breadcrumb">
                <li><a href="index.php">Beranda</a></li>
                <li><a href="product-listing.php">Produk</a></li>
                <?php if (isset($product['category_name']) && $product['category_name']): ?>
                <li><a href="product-listing.php?category=<?php echo $product['category_id']; ?>"><?php echo htmlspecialchars($product['category_name']); ?></a></li>
                <?php endif; ?>
                <li><?php echo htmlspecialchars($product['name']); ?></li>
            </ul>
        </div>
    </div>

    <!-- Product Detail -->
    <div class="ps-product-detail">
        <div class="ps-container">
            <div class="row">
                <!-- Product Images -->
                <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                    <div class="ps-product__gallery">
                        <div class="ps-product__image">
                            <?php if (!empty($product['image_data'])): ?>
                                <?php echo displayImage($product['image_data'], $product['image_mime'], 'img-fluid', $product['name']); ?>
                            <?php elseif (!empty($product['image'])): ?>
                                <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="img-fluid">
                            <?php else: ?>
                                <img src="images/products/default.jpg" alt="<?php echo htmlspecialchars($product['name']); ?>" class="img-fluid">
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Product Info -->
                <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                    <div class="ps-product__info">
                        <h1 class="ps-product__name"><?php echo htmlspecialchars($product['name']); ?></h1>
                        
                        <div class="ps-product__meta">
                            <?php if (isset($product['category_name']) && $product['category_name']): ?>
                            <p>Kategori: <a href="product-listing.php?category=<?php echo $product['category_id']; ?>"><?php echo htmlspecialchars($product['category_name']); ?></a></p>
                            <?php endif; ?>
                        </div>

                        <div class="ps-product__rating">
                            <?php echo displayRating($product['rating'] ?? 0); ?>
                            <span class="ps-product__review">(<?php echo $product['rating'] ?? 0; ?> stars)</span>
                        </div>

                        <div class="ps-product__price">
                            <?php echo displayProductPrice($product); ?>
                        </div>

                        <div class="ps-product__description">
                            <h4>Deskripsi</h4>
                            <p><?php echo htmlspecialchars($product['description'] ?? ''); ?></p>
                        </div>

                        <div class="ps-product__stock">
                            <p>Stok: <span class="<?php echo ($product['stock'] ?? 0) > 0 ? 'in-stock' : 'out-of-stock'; ?>"><?php echo ($product['stock'] ?? 0) > 0 ? ($product['stock'] . ' tersedia') : 'Habis'; ?></span></p>
                        </div>

                        <?php if (($product['stock'] ?? 0) > 0): ?>
                        <div class="ps-product__actions">
                            <div class="ps-product__quantity">
                                <label>Jumlah:</label>
                                <input type="number" min="1" max="<?php echo $product['stock']; ?>" value="1" class="form-control" id="product-quantity">
                            </div>
                            <a href="#" class="ps-btn ps-btn--fullwidth" id="add-to-cart-btn" data-product-id="<?php echo $product['id']; ?>">Tambah ke Keranjang</a>
                        </div>
                        <?php endif; ?>

                        <div class="ps-product__badges">
                            <?php echo getProductBadge($product); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Products -->
    <?php if (!empty($related_products)): ?>
    <div class="ps-related-products">
        <div class="ps-container">
            <div class="ps-section__header">
                <h3 class="ps-section__title">Produk Terkait</h3>
                <p>Anda mungkin juga menyukai</p>
            </div>
            <div class="ps-section__content">
                <div class="row">
                    <?php foreach ($related_products as $related_product): ?>
                    <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
                        <div class="ps-product">
                            <div class="ps-product__thumbnail">
                                <?php echo getProductBadge($related_product); ?>
                                <?php if (!empty($related_product['image_data'])): ?>
                                    <?php echo displayImage($related_product['image_data'], $related_product['image_mime'], '', $related_product['name']); ?>
                                <?php elseif (!empty($related_product['image'])): ?>
                                    <img src="<?php echo htmlspecialchars($related_product['image']); ?>" alt="<?php echo htmlspecialchars($related_product['name']); ?>">
                                <?php else: ?>
                                    <img src="images/products/default.jpg" alt="<?php echo htmlspecialchars($related_product['name']); ?>">
                                <?php endif; ?>
                                <a class="ps-product__overlay" href="product-detail.php?id=<?php echo $related_product['id']; ?>"></a>
                                <ul class="ps-product__actions">
                                    <li><a href="cart_actions.php?action=add&id=<?php echo $related_product['id']; ?>" data-tooltip="Tambah ke Keranjang"><i class="ba-shopping"></i></a></li>
                                </ul>
                            </div>
                            <div class="ps-product__content">
                                <a class="ps-product__title" href="product-detail.php?id=<?php echo $related_product['id']; ?>"><?php echo htmlspecialchars($related_product['name']); ?></a>
                                <?php if (isset($related_product['category_name']) && $related_product['category_name']): ?>
                                <p><a href="product-listing.php?category=<?php echo $related_product['category_id']; ?>"><?php echo htmlspecialchars($related_product['category_name']); ?></a></p>
                                <?php endif; ?>
                                <?php echo displayRating($related_product['rating'] ?? 0); ?>
                                <?php echo displayProductPrice($related_product); ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

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
    
    <script>
    $(document).ready(function() {
        // Add to Cart functionality
        $('#add-to-cart-btn').on('click', function(e) {
            e.preventDefault();
            var productId = $(this).data('product-id');
            var quantity = $('#product-quantity').val();
            
            // Validasi quantity
            if (quantity < 1) {
                alert('Jumlah harus minimal 1');
                return;
            }
            
            // Redirect to cart_actions.php with parameters
            window.location.href = 'cart_actions.php?action=add&id=' + productId + '&quantity=' + quantity;
        });
        
        // Newsletter form handling
        $('#newsletterForm').on('submit', function(e) {
            e.preventDefault();
            var email = $('#newsletterEmail').val();
            
            $.ajax({
                url: 'newsletter_handler.php',
                type: 'POST',
                data: {email: email},
                dataType: 'json',
                success: function(response) {
                    $('#newsletterMessage').html('<div class="alert alert-' + (response.success ? 'success' : 'danger') + '">' + response.message + '</div>');
                    if (response.success) {
                        $('#newsletterEmail').val('');
                    }
                },
                error: function() {
                    $('#newsletterMessage').html('<div class="alert alert-danger">Terjadi kesalahan. Silakan coba lagi.</div>');
                }
            });
        });
    });
    </script>
</body>
</html> 