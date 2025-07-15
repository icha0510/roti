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

// Get blog post ID from URL parameter
$post_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get blog post from database
$post = getPostById($post_id);

// If post not found, redirect to blog grid
if (!$post) {
    header('Location: blog-grid.php');
    exit;
}

// Get recent posts for sidebar
$recent_posts = getAllPosts(3);
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
    <meta name="author" content="">
    <meta name="keywords" content="">
    <meta name="description" content="">
    <link href="images/logo-rotio.png" rel="icon">
    <title><?php echo htmlspecialchars($post['title']); ?> - Roti'O</title>
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
    <!--HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries-->
    <!--WARNING: Respond.js doesn't work if you view the page via file://-->
    <!--[if lt IE 9]><script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script><script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script><![endif]-->
    <!--[if IE 7]><body class="ie7 lt-ie8 lt-ie9 lt-ie10"><![endif]-->
    <!--[if IE 8]><body class="ie8 lt-ie9 lt-ie10"><![endif]-->
    <!--[if IE 9]><body class="ie9 lt-ie10"><![endif]-->
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
    
    <div class="ps-hero bg--cover" data-background="images/hero/blog.jpg">
      <div class="ps-hero__content">
        <h1>Detail Blog</h1>
        <div class="ps-breadcrumb">
          <ol class="breadcrumb">
            <li><a href="index.php">Beranda</a></li>
            <li><a href="blog-grid.php">Blog</a></li>
            <li class="active"><?php echo htmlspecialchars($post['title']); ?></li>
          </ol>
        </div>
      </div>
    </div>
    <main class="ps-main">
      <div class="ps-container">
        <div class="row">
          <div class="col-lg-9 col-md-4 col-sm-12 col-xs-12 ">
            <div class="ps-post--detail">
              <div class="ps-post__thumbnail">
                <?php if (!empty($post['image_data'])): ?>
                  <?php echo displayImage($post['image_data'], $post['image_mime'], 'img-fluid', $post['title']); ?>
                <?php else: ?>
                  <img src="<?php echo $post['image']; ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" class="img-fluid">
                <?php endif; ?>
              </div>
              <div class="ps-post__content">
                <div class="ps-post__meta">
                  <div class="ps-post__posted">
                    <span class="date"><?php echo date('j', strtotime($post['created_at'])); ?></span>
                    <span class="month"><?php echo date('M', strtotime($post['created_at'])); ?></span>
                  </div>
                  <!-- <div class="ps-post__actions">
                    <div class="ps-post__action red"><a href="#"><i class="ba-heart"></i></a></div>
                    <div class="ps-post__action cyan"><a href="#"><i class="fa fa-comment-o"></i></a></div>
                    <div class="ps-post__action shared"><a href="#"><i class="fa fa-share-alt"></i> Share</a>
                      <ul class="ps-list--shared">
                        <li class="facebook"><a href="#"><i class="fa fa-facebook"></i></a></li>
                        <li class="twitter"><a href="#"><i class="fa fa-twitter"></i></a></li>
                        <li class="google"><a href="#"><i class="fa fa-google-plus"></i></a></li>
                      </ul>
                    </div>
                  </div> -->
                </div>
                <div class="ps-post__container">
                  <h3 class="ps-post__title"><?php echo htmlspecialchars($post['title']); ?></h3>
                  <p class="ps-post__info">Diposting oleh <a href="blog-grid.php" class="author"><?php echo htmlspecialchars($post['author']); ?></a> - <a href="blog-grid.php">Blog</a></p>
                  <div class="ps-post__content-text">
                    <?php echo nl2br(htmlspecialchars($post['content'])); ?>
                  </div>
                </div>
              </div>
              <div class="ps-post__footer">
                <p class="ps-post__tags"><i class="fa fa-tags"></i><a href="blog-grid.php">Blog</a>, <a href="blog-grid.php">Bakery</a>, <a href="blog-grid.php">Food</a></p>
              </div>
            </div>
          </div>
          <div class="col-lg-3 col-md-4 col-sm-12 col-xs-12 ">
            <div class="ps-blog__sidebar">
              <div class="widget widget_search">
                <form class="ps-form--widget-search" action="do_action" method="post">
                  <input class="form-control" type="text" placeholder="Cari Postingan...">
                  <button><i class="ba-magnifying-glass"></i></button>
                </form>
              </div>
              <div class="widget widget_category">
                <h3 class="widget-title">Postingan Terbaru</h3>
                <ul class="ps-list--arrow">
                  <?php foreach ($recent_posts as $recent_post): ?>
                    <li><a href="blog-detail.php?id=<?php echo $recent_post['id']; ?>"><?php echo htmlspecialchars($recent_post['title']); ?></a></li>
                  <?php endforeach; ?>
                </ul>
              </div>
              <div class="widget widget_ads">
                <h3 class="widget-title">Tersertifikasi Halal</h3><img src="images/halal.jpg" alt="">
              </div>
              <div class="widget widget_recent-posts">
                <h3 class="widget-title">Postingan Terbaru</h3>
                <?php foreach ($recent_posts as $recent_post): ?>
                <div class="ps-post--sidebar">
                  <div class="ps-post__thumbnail">
                    <a class="ps-post__overlay" href="blog-detail.php?id=<?php echo $recent_post['id']; ?>"></a>
                    <?php if (!empty($recent_post['image_data'])): ?>
                      <?php echo displayImage($recent_post['image_data'], $recent_post['image_mime'], '', $recent_post['title']); ?>
                    <?php else: ?>
                      <img src="<?php echo $recent_post['image']; ?>" alt="<?php echo htmlspecialchars($recent_post['title']); ?>">
                    <?php endif; ?>
                  </div>
                  <div class="ps-post__content">
                    <a class="ps-post__title" href="blog-detail.php?id=<?php echo $recent_post['id']; ?>"><?php echo htmlspecialchars($recent_post['title']); ?></a>
                    <p><?php echo date('M j, Y', strtotime($recent_post['created_at'])); ?></p>
                  </div>
                </div>
                <?php endforeach; ?>
              </div>
              <div class="widget widget_tags">
                <h3 class="widget-title">Tag</h3><a href="#">Blog</a><a href="#">Bakery</a><a href="#">Makanan</a><a href="#">Roti</a><a href="#">Kue</a><a href="#">Pastry</a><a href="#">Resep</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
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