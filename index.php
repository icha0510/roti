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

// Ambil data dari database
$featuredProducts = getAllProducts(6);
$banners = getAllBanners();
$posts = getAllPosts(3);
$categories = getAllCategories();
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
    <title>Roti'O</title>
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
  </head>
  
    <!-- Header-->
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
          <li class="menu-item-has-children current-menu-item"><a href="index.php">Beranda</a></li>
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
    <li class="menu-item-has-children current-menu-item"><a href="index.php">Beranda</a></li>
    <li><a href="about.php">Tentang</a></li>
    <li class="menu-item-has-children">
      <a href="#">Produk</a>
      <span class="sub-toggle"><i class="fa fa-angle-down"></i></span>
      <ul class="sub-menu">
        <li><a href="product-listing.php">Daftar Produk</a></li>
        <li><a href="order-form.php"></a></li>
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

    <!-- Home banner-->
    <div class="pb-80" id="slider">
      <div class="ps-carousel--animate ps-carousel--1st">
        <?php foreach ($banners as $banner): ?>
        <div class="item">
          <div class="ps-product--banner">
            <?php if ($banner['badge_text']): ?>
              <span class="ps-badge ps-badge--<?php echo $banner['badge_type']; ?>">
                <img src="images/icons/badge-<?php echo ($banner['badge_type'] == 'sale') ? 'brown' : 'red'; ?>.png" alt="">
                <i><?php echo $banner['badge_text']; ?></i>
              </span>
            <?php endif; ?>
            <?php if (!empty($banner['image_data'])): ?>
              <?php echo displayImage($banner['image_data'], $banner['image_mime'], '', $banner['title']); ?>
            <?php else: ?>
              <img src="<?php echo $banner['image']; ?>" alt="<?php echo $banner['title']; ?>">
            <?php endif; ?>
            <div class="ps-product__footer">
              <a class="ps-btn" href="order-form.php">Pesan Sekarang</a>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <!-- Home 1 products-->
    <div class="ps-home-product bg--cover" data-background="images/bg/home-product.jpg">
      <div class="ps-container">
        <div class="ps-section__header">
          <h3 class="ps-section__title">Menu Hari Ini</h3>
          <p>Roti'o setiap hari</p><span><img src="images/icons/floral.png" alt=""></span>
        </div>
        <div class="ps-section__content">
          <div class="row">
            <?php foreach ($featuredProducts as $product): ?>
            <div class="col-lg-4 col-md-6 col-sm-6 col-xs-12 ">
              <div class="ps-product ps-product--horizontal">
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
                    <li><a href="cart_actions.php?action=add&id=<?php echo $product['id']; ?>" data-tooltip="Add to Cart"><i class="ba-shopping"></i></a></li>
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
          </div>
        </div>
      </div>
    </div>
    
    <!-- home-blog-->
    <div class="ps-home-blog">
      <div class="ps-container">
        <div class="ps-section__header">
          <h3 class="ps-section__title">Blog Kami</h3>
          <!-- <p>Live with passion</p>
          <span><img src="images/icons/floral.png" alt=""></span>   -->
        </div>
        
        <div class="ps-section__content">
          <div class="row">
            <?php foreach ($posts as $post): ?>
            <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12 ">
              <div class="ps-post">
                <div class="ps-post__thumbnail">
                  <?php if (!empty($post['image_data'])): ?>
                    <?php echo displayImage($post['image_data'], $post['image_mime'], '', $post['title']); ?>
                  <?php else: ?>
                    <img src="<?php echo $post['image']; ?>" alt="<?php echo $post['title']; ?>">
                  <?php endif; ?>
                  <a class="ps-post__overlay" href="blog-detail.php?id=<?php echo $post['id']; ?>"></a>
                </div>
                <div class="ps-post__content">
                  <span class="ps-post__posted"><?php echo date('F j, Y', strtotime($post['created_at'])); ?></span>
                  <a class="ps-post__title" href="blog-detail.php?id=<?php echo $post['id']; ?>"><?php echo $post['title']; ?></a>
                  <span class="ps-post__byline">Oleh<a href="#"> <?php echo $post['author']; ?></a></span>
                  <p><?php echo $post['excerpt']; ?></p>
                  <a class="ps-post__morelink" href="blog-detail.php?id=<?php echo $post['id']; ?>">Baca selengkapnya</a>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>


    <!-- footer-->
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
    <!-- Plugins-->
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
    <!-- Custom scripts-->
    <script src="js/main.js"></script>
    <script src="js/header.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDsUcTjt43mTheN9ruCsQVgBE-wgN6_AfY&amp;region=GB"></script>
    <script>
    $(document).ready(function() {

        // Newsletter functionality (unchanged)
        $('#newsletterForm').on('submit', function(e) {
            e.preventDefault();
            var email = $('#newsletterEmail').val();
            $('#newsletterBtn').prop('disabled', true).text('Memproses...');
            $('#newsletterMessage').html('');
            $.ajax({
                url: 'newsletter_handler.php',
                type: 'POST',
                data: { email: email, action: 'subscribe' },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#newsletterMessage').html('<span style="color:green;">'+response.message+'</span>');
                        $('#newsletterForm')[0].reset();
                    } else {
                        $('#newsletterMessage').html('<span style="color:red;">'+response.message+'</span>');
                    }
                    $('#newsletterBtn').prop('disabled', false).text('Ikuti Laman');
                },
                error: function() {
                    $('#newsletterMessage').html('<span style="color:red;">Terjadi kesalahan. Coba lagi.</span>');
                    $('#newsletterBtn').prop('disabled', false).text('Ikuti Laman');
                }
            });
        });
    });
    </script>
  </body>
</html> 