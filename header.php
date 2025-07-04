<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

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
    <title><?php echo isset($page_title) ? $page_title . ' - Bready' : 'Bready'; ?></title>
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
    <link rel="stylesheet" href="css/style.css?v=1.4">
    <!--HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries-->
    <!--WARNING: Respond.js doesn't work if you view the page via file://-->
    <!--[if lt IE 9]><script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script><script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script><![endif]-->
    <!--[if IE 7]><body class="ie7 lt-ie8 lt-ie9 lt-ie10"><![endif]-->
    <!--[if IE 8]><body class="ie8 lt-ie9 lt-ie10"><![endif]-->
    <!--[if IE 9]><body class="ie9 lt-ie10"><![endif]-->
  </head>
  <body>
    
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
            <li class="menu-item-has-children <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'current-menu-item' : ''; ?>">
              <a href="index.php">Homepage</a>
            </li>
            <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'about.php') ? 'current-menu-item' : ''; ?>">
              <a href="about.php">About</a>
            </li>
            <li class="menu-item-has-children <?php echo (in_array(basename($_SERVER['PHP_SELF']), ['product-listing.php', 'product-detail.php', 'order-form.php'])) ? 'current-menu-item' : ''; ?>">
              <a href="#">product</a>
              <span class="sub-toggle"><i class="fa fa-angle-down"></i></span>
              <ul class="sub-menu">
                <li><a href="product-listing.php">Product List</a></li>
                <li><a href="order-form.php">Order Form</a></li>
              </ul>
            </li>   
            <li class="menu-item-has-children <?php echo (in_array(basename($_SERVER['PHP_SELF']), ['blog-grid.php', 'store.php'])) ? 'current-menu-item' : ''; ?>">
              <a href="#">Others</a>
              <span class="sub-toggle"><i class="fa fa-angle-down"></i></span>
              <ul class="sub-menu">
                <li><a href="blog-grid.php">Blog</a></li>
                <li><a href="store.php">Our Stores</a></li>
              </ul>
            </li>
            <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'contact.php') ? 'current-menu-item' : ''; ?>">
              <a href="contact.php">Contact Us</a>
            </li>
          </ul>
        </div>
      </nav>
    </header>