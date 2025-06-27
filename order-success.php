<?php
session_start();
require_once 'includes/functions.php';

// Cek apakah ada data order sukses
if (!isset($_SESSION['order_success'])) {
    header('Location: order-form.php');
    exit;
}

$order_data = $_SESSION['order_success'];

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
    <link href="favicon.png" rel="icon">
    <meta name="author" content="">
    <meta name="keywords" content="">
    <meta name="description" content="">
    <title>Order Success - Bready</title>
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
    <!--HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries-->
    <!--WARNING: Respond.js doesn't work if you view the page via file://-->
    <!--[if lt IE 9]><script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script><script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script><![endif]-->
    <!--[if IE 7]><body class="ie7 lt-ie8 lt-ie9 lt-ie10"><![endif]-->
    <!--[if IE 8]><body class="ie8 lt-ie9 lt-ie10"><![endif]-->
    <!--[if IE 9]><body class="ie9 lt-ie10"><![endif]-->
  </head>
  <body>
    <div class="ps-search">
      <div class="ps-search__content"><a class="ps-search__close" href="#"><span></span></a>
        <form class="ps-form--search-2" action="do_action" method="post">
          <h3>Enter your keyword</h3>
          <div class="form-group">
            <input class="form-control" type="text" placeholder="">
            <button class="ps-btn active ps-btn--fullwidth">Search</button>
          </div>
        </form>
      </div>
    </div>
    <!-- Header-->
    <header class="header header--3" data-sticky="false">
      <nav class="navigation">
        <div class="ps-container">
          <a class="ps-logo" href="index.php"><img src="images/logo-light.png" alt=""></a>
          <div class="menu-toggle"><span></span></div>
          <div class="header__actions">
            <a class="ps-search-btn" href="#"><i class="ba-magnifying-glass"></i></a>
            <a href="#"><i class="ba-profile"></i></a>
            <div class="ps-cart">
              <a class="ps-cart__toggle" href="cart.php">
                <span><i><?php echo $cart_count; ?></i></span>
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
                        <a class="ps-cart-item__title" href="product-detail.php?id=<?php echo $item['id']; ?>"><?php echo $item['name']; ?></a>
                        <p><span>Quantity:<i><?php echo $item['quantity']; ?></i></span><span>Total:<i>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></i></span></p>
                      </div>
                    </div>
                    <?php endif; endforeach; ?>
                  <?php else: ?>
                    <div class="ps-cart-item">
                      <div class="ps-cart-item__content">
                        <p>Your cart is empty</p>
                      </div>
                    </div>
                  <?php endif; ?>
                </div>
                <div class="ps-cart__total">
                  <p>Number of items:<span><?php echo $cart_count; ?></span></p>
                  <p>Item Total:<span>$<?php echo number_format($cart_total, 2); ?></span></p>
                </div>
                <div class="ps-cart__footer"><a href="cart.php">Check out</a></div>
              </div>
            </div>
          </div>
          <ul class="menu">
            <li class="menu-item-has-children">
              <a href="index.php">Homepage</a>
            </li>
            <li><a href="about.php">About</a></li>
            <li class="menu-item-has-children">
              <a href="#">product</a>
              <span class="sub-toggle"><i class="fa fa-angle-down"></i></span>
              <ul class="sub-menu">
                <li><a href="product-listing.php">Product List</a></li>
                <li><a href="order-form.php">Order Form</a></li>
              </ul>
            </li>   
            <li class="menu-item-has-children">
              <a href="#">Others</a>
              <span class="sub-toggle"><i class="fa fa-angle-down"></i></span>
              <ul class="sub-menu">
                <li><a href="blog-grid.php">Blog</a></li>
                <li><a href="store.php">Our Stores</a></li>
              </ul>
            </li>
            <li><a href="contact.php">Contact Us</a></li>
          </ul>
        </div>
      </nav>
    </header>
    
    <div class="ps-hero bg--cover" data-background="images/hero/product.jpg">
      <div class="ps-hero__content">
        <h1>Order Success</h1>
        <div class="ps-breadcrumb">
          <ol class="breadcrumb">
            <li><a href="index.php">Home</a></li>
            <li><a href="order-form.php">Order Form</a></li>
            <li class="active">Order Success</li>
          </ol>
        </div>
      </div>
    </div>
    
    <main class="ps-main">
      <div class="ps-container">
        <div class="ps-section--success">
          <div class="row">
            <div class="col-lg-8 col-md-10 col-sm-12 col-xs-12 center-block">
              <div class="ps-success">
                <div class="ps-success__icon">
                  <i class="fa fa-check-circle"></i>
                </div>
                <h1>Order Berhasil!</h1>
                <h3>Terima kasih atas pesanan Anda</h3>
                
                <div class="ps-success__details">
                  <div class="row">
                    <div class="col-md-6">
                      <p><strong>Order Number:</strong><br><?php echo $order_data['order_number']; ?></p>
                      <p><strong>Total Amount:</strong><br>£<?php echo number_format($order_data['total_amount'], 2); ?></p>
                    </div>
                    <div class="col-md-6">
                      <p><strong>Order ID:</strong><br><?php echo $order_data['order_id']; ?></p>
                      <p><strong>Status:</strong><br><span class="badge bg-warning">Pending</span></p>
                    </div>
                  </div>
                </div>
                
                <p>Kami telah menerima pesanan Anda dan akan segera memprosesnya. Tim kami akan menghubungi Anda segera untuk konfirmasi lebih lanjut.</p>
                
                <div class="ps-success__actions">
                  <a class="ps-btn" href="index.php">Kembali ke Homepage</a>
                  <a class="ps-btn ps-btn--outline" href="order-form.php">Order Lagi</a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
    
    <div class="ps-site-features">
      <div class="ps-container">
        <div class="row">
          <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12 ">
            <div class="ps-block--iconbox"><i class="ba-delivery-truck-2"></i>
              <h4>Free Shipping <span> On Order Over$199</h4>
              <p>Want to track a package? Find tracking information and order details from Your Orders.</p>
            </div>
          </div>
          <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12 ">
            <div class="ps-block--iconbox"><i class="ba-biscuit-1"></i>
              <h4>Master Chef<span> WITH PASSION</h4>
              <p>Shop zillions of finds, with new arrivals added daily.</p>
            </div>
          </div>
          <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12 ">
            <div class="ps-block--iconbox"><i class="ba-flour"></i>
              <h4>Natural Materials<span> protect your family</h4>
              <p>We always ensure the safety of all products of store</p>
            </div>
          </div>
          <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12 ">
            <div class="ps-block--iconbox"><i class="ba-cake-3"></i>
              <h4>Attractive Flavor <span>ALWAYS LISTEN</span></h4>
              <p>We offer a 24/7 customer hotline so you're never alone if you have a question.</p>
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
              <div class="ps-site-info"><a class="ps-logo" href="index.html"><img src="images/logo-dark.png" alt=""></a>
                <p>Tart bear claw cake tiramisu chocolate bar gummies dragée lemon drops brownie.</p>
                <ul class="ps-list--social">
                  <li><a href="#"><i class="fa fa-facebook"></i></a></li>
                  <li><a href="#"><i class="fa fa-twitter"></i></a></li>
                  <li><a href="#"><i class="fa fa-dribbble"></i></a></li>
                </ul>
              </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-12 col-xs-12 ">
              <form class="ps-form--subscribe-offer" action="do_action" method="post">
                <h4>Get news & offer</h4>
                <div class="form-group">
                  <input class="form-control" type="text" placeholder="Your Email...">
                  <button>Subscribe</button>
                </div>
                <p>* Don't worry, we never spam</p>
              </form>
              <div class="ps-footer__contact">
                <h4>Contact with me</h4>
                <p>PO Box 16122 Collins Street West,Victoria 8007 Australia</p>
                <P>(+84 ) 7534 9773, (+84 ) 874 548</P>
              </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-12 col-xs-12 ">
              <div class="ps-footer__open">
                <h4>Time to Open</h4>
                <p>
                  Monday - Friday: <br>08:00 am - 08:30 pm <br>
                  Saturday - Sunday:<br>
                  10:00 am - 16:30 pm
                </p>
              </div>
              <ul class="ps-list--payment">
                <li><a href="#"><img src="images/payment-method/visa.png" alt=""></a></li>
                <li><a href="#"><img src="images/payment-method/master-card.png" alt=""></a></li>
                <li><a href="#"><img src="images/payment-method/paypal.png" alt=""></a></li>
              </ul>
            </div>
          </div>
        </div>
      </div>
      <div class="ps-footer__copyright">
        <div class="container">
          <p>
            ©  Copyright by <strong>Bready Store</strong>. Design by<a href="#"> Alena Studio.</a></p>
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
  </body>
</html>

<?php
// Hapus data order sukses dari session setelah ditampilkan
unset($_SESSION['order_success']);
?> 