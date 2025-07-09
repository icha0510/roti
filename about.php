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
    <title>Tentang - Roti'O</title>
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
                <li class="menu-item-has-children current-menu-item">
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
    
    <div class="ps-hero bg--cover" data-background="images/hero/about.jpg">
      <div class="ps-hero__content">
        <h1> Tentang Kami</h1>
        <div class="ps-breadcrumb">
          <ol class="breadcrumb">
            <li><a href="index.php">Beranda</a></li>
            <li class="active">Tentang Kami</li>
          </ol>
        </div>
      </div>
    </div>

    <!-- About Intro-->
    <div class="ps-about-intro">
      <div class="ps-container">
        <div class="ps-section__header text-center">
          <h3 class="ps-section__title">Selamat Datang</h3>
          <p>DI TOKO ROTI'O KAMI</p><span><img src="images/icons/floral.png" alt=""></span>
        </div>
      </div>
      <div class="ps-block--signature">
        <div class="ps-block__thumbnail"><img src="images/signature.png" alt=""></div>
        <div class="ps-block__content">
          <p>"Di Roti'O, kami percaya kebahagiaan itu ada di hal-hal kecil. Setiap roti yang baru keluar dari oven kami adalah janji kehangatan yang jujur dan aroma yang pasti bikin kamu tersenyum."</p><small>~ Owner Roti'O ~</small><img src="images/signature-2.png" alt="">
        </div>
      </div>

    <!-- Tentang Kami Section -->
    <section class="ps-section--about-tentang" style="background:#fff; border-radius:0; box-shadow:none; padding:80px 0; margin:0 auto; max-width:1170px; position:relative;">
      <style>
        .ps-section--about-tentang {
          background:#fff !important;
        }
        .ps-section--about-tentang .ps-section__header {
          text-align:center;
          margin-bottom:60px;
        }
        .ps-section--about-tentang .ps-section__title {
          font-family:"Kaushan Script",cursive;
          font-size:48px;
          color:#000;
          font-weight:400;
          margin-bottom:15px;
        }
        .ps-section--about-tentang .ps-section__subtitle {
          font-size:20px;
          font-weight:400;
          color:#000;
          text-transform:uppercase;
          margin-bottom:20px;
        }
        .ps-section--about-tentang .ps-section__floral {
          display:block;
          margin:0 auto;
        }
        .ps-section--about-tentang .ps-section__floral img {
          max-width:75px;
        }
        .ps-section--about-tentang .ps-section__content {
          max-width:900px;
          margin:0 auto;
          padding:0 20px;
        }
        .ps-section--about-tentang .ps-section__content p {
          font-size:16px;
          color:#4e3939;
          margin-bottom:20px;
          line-height:1.8em;
          text-align:justify;
        }
        .ps-section--about-tentang .ps-section__content p:first-of-type {
          font-size:18px;
          color:#000;
          font-style:italic;
          text-align:center;
          margin-bottom:30px;
        }
        @media (max-width: 767px) {
          .ps-section--about-tentang .ps-section__title { font-size:36px; }
          .ps-section--about-tentang .ps-section__subtitle { font-size:16px; }
          .ps-section--about-tentang { padding:60px 0; }
        }
      </style>
      <div class="ps-container">
        <div class="ps-section__header">
          <h3 class="ps-section__title">Tentang Kami</h3>
          <p class="ps-section__subtitle">ROTI'O, AROMA KOPI YANG MEMIKAT HATI</p>
          <span class="ps-section__floral"><img src="images/icons/floral.png" alt=""></span>
        </div>
        <div class="ps-section__content">
          <p>Selamat datang di dunia Roti'O, di mana setiap gigitan adalah sebuah cerita tentang kehangatan dan kelezatan yang tak terlupakan. Sejak 23 Mei 2012, kami berdedikasi untuk menyajikan roti kopi yang ikonik, dikenal dengan aroma khasnya yang semerbak dan teksturnya yang sempurna: renyah di luar, lembut di dalam.</p>
          <p>Filosofi kami sederhana namun kuat: <b>"Fresh From The Oven"</b>. Ini bukan sekadar slogan, melainkan janji. Setiap Roti'O yang Anda nikmati selalu dipanggang langsung di gerai kami. Begitu Anda mendekat, aroma perpaduan kopi dan karamel yang hangat akan langsung menyapa, memberikan isyarat bahwa kelezatan yang akan Anda santap baru saja keluar dari pemanggang. Kehangatan ini menciptakan pengalaman yang tak hanya memanjakan lidah, tetapi juga menghangatkan hati.</p>
          <p>Berawal dari gerai pertama kami di Stasiun Kota, Jakarta, kami melihat bagaimana Roti'O dengan cepat menjadi teman setia bagi para pelancong dan penikmat kuliner. Keberhasilan ini mendorong kami untuk memperluas jangkauan. Kini, Roti'O hadir di berbagai lokasi strategis di seluruh Indonesia, mulai dari bandara, stasiun, pusat perbelanjaan, hingga rest area. Kami bangga dapat menemani setiap perjalanan dan mengisi momen berharga Anda dengan cita rasa yang konsisten dan tak tertandingi.</p>
          <p>Roti'O adalah tentang menciptakan momen kebahagiaan sederhana. Baik sebagai pengganjal lapar di tengah kesibukan, teman minum kopi di pagi hari, atau sekadar camilan untuk memanjakan diri, Roti'O selalu hadir dengan kualitas terbaik. Kami berkomitmen untuk terus menyajikan kelezatan yang konsisten, menjaga setiap detail proses, agar setiap Roti'O yang sampai di tangan Anda adalah yang terbaik.</p>
          <p style="margin-bottom:0;">Terima kasih telah menjadikan Roti'O bagian dari kisah Anda. Kami akan terus berinovasi dan menyebarkan aroma kebahagiaan di setiap sudut kota, fresh from our oven to your hands.</p>
        </div>
      </div>
    </section>
    
      <div class="ps-about-number">
        <div class="ps-container">
          <div class="row">
            <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12 ">
              <div class="ps-block--countdown"><i class="ba-biscuit-1"></i><span class="number ps-block__number" data-from="0" data-to="165"> 165</span>
                <h4>Koki</h4>
              </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12 ">
              <div class="ps-block--countdown"><i class="ba-mixer"></i><span class="number ps-block__number" data-from="0" data-to="2130"> 2130</span>
                <h4>Resep</h4>
              </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12 ">
              <div class="ps-block--countdown"><i class="ba-bread-2"></i><span class="number ps-block__number" data-from="0" data-to="3450"> 3450</span>
                <h4>Roti per hari</h4>
              </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12 ">
              <div class="ps-block--countdown"><i class="ba-flour"></i><span class="number ps-block__number" data-from="0" data-to="345"> 345</span>
                <h4>Tepung</h4>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    
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