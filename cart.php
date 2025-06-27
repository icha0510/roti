<?php
session_start();
require_once 'includes/functions.php';

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

// Handle cart actions
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $product_id = $_GET['id'] ?? 0;
    
    switch ($action) {
        case 'remove':
            if (isset($_SESSION['cart'][$product_id])) {
                unset($_SESSION['cart'][$product_id]);
                $_SESSION['cart_message'] = "Product removed from cart!";
            }
            break;
        case 'update':
            $quantity = $_GET['quantity'] ?? 1;
            if (isset($_SESSION['cart'][$product_id])) {
                if ($quantity > 0) {
                    $_SESSION['cart'][$product_id]['quantity'] = $quantity;
                } else {
                    unset($_SESSION['cart'][$product_id]);
                }
                $_SESSION['cart_message'] = "Cart updated successfully!";
            }
            break;
        case 'clear':
            $_SESSION['cart'] = array();
            $_SESSION['cart_message'] = "Cart cleared successfully!";
            break;
    }
    
    // Redirect to remove query parameters
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Bready</title>
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
      <div class="ps-container">
        <nav class="navigation">
          <div class="header-wrapper">
            
            <!-- Logo Section -->
            <div class="header-logo">
              <a class="ps-logo" href="index.php">
                <img src="images/logo-light.png" alt="">
              </a>
            </div>

            <!-- Navigation Menu -->
            <div class="header-nav">
              <ul class="menu">
                <li class="menu-item-has-children">
                  <a href="index.php">Homepage</a>
                </li>
                <li>
                  <a href="about.php">About</a>
                </li>
                <li class="menu-item-has-children">
                  <a href="#">Product</a>
                  <span class="sub-toggle">
                    <i class="fa fa-angle-down"></i>
                  </span>
                  <ul class="sub-menu">
                    <li><a href="product-listing.php">Product List</a></li>
                    <li><a href="order-form.php">Order Form</a></li>
                  </ul>
                </li>   
                <li class="menu-item-has-children">
                  <a href="#">Others</a>
                  <span class="sub-toggle">
                    <i class="fa fa-angle-down"></i>
                  </span>
                  <ul class="sub-menu">
                    <li><a href="blog-grid.php">Blog</a></li>
                    <li><a href="store.php">Our Stores</a></li>
                  </ul>
                </li>
                <li>
                  <a href="contact.php">Contact Us</a>
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
                        <a href="logo-orders.php">My Orders</a>
                      </li>
                      <li>
                        <a href="profile.php">Profile</a>
                      </li>
                      <li>
                        <hr class="dropdown-divider">
                      </li>
                      <li>
                        <a href="logout.php">Logout</a>
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
                                <span>Quantity:<i><?php echo $item['quantity']; ?></i></span>
                                <span>Total:<i>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></i></span>
                              </p>
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
                    <div class="ps-cart__footer">
                      <a href="cart.php">Check out</a>
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
    <div class="ps-hero bg--cover" data-background="images/hero/about.jpg">
        <div class="ps-hero__content">
            <h1>Shopping Cart</h1>
            <div class="ps-breadcrumb">
                <ol class="breadcrumb">
                    <li><a href="index.php">Home</a></li>
                    <li class="active">Shopping Cart</li>
                </ol>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <main class="ps-main">
        <div class="ps-container">
            <?php if (isset($_SESSION['cart_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['cart_message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['cart_message']); ?>
            <?php endif; ?>

            <?php if (empty($_SESSION['cart'])): ?>
                <div class="ps-cart-listing">
                    <div class="text-center py-5">
                        <i class="fa fa-shopping-cart fa-3x text-muted mb-3"></i>
                        <h3>Your cart is empty</h3>
                        <p class="text-muted">Looks like you haven't added any products to your cart yet.</p>
                        <a href="product-listing.php" class="ps-btn">Continue Shopping</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="ps-cart-listing">
                    <div class="table-responsive">
                        <table class="table ps-table ps-table--listing">
                            <thead>
                                <tr>
                                    <th>All Products</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Total</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($_SESSION['cart'] as $product_id => $item): ?>
                                <tr>
                                    <td>
                                        <a class="ps-product--table" href="product-detail.php?id=<?php echo $item['id']; ?>">
                                            <?php if (!empty($item['image_data'])): ?>
                                                <?php echo displayImage($item['image_data'], $item['image_mime'], 'mr-15', $item['name']); ?>
                                            <?php else: ?>
                                                <img class="mr-15" src="<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>">
                                            <?php endif; ?>
                                            <?php echo $item['name']; ?>
                                        </a>
                                    </td>
                                    <td>$<?php echo number_format($item['price'], 2); ?></td>
                                    <td>
                                        <div class="form-group--number">
                                            <button class="minus" onclick="updateQuantity(<?php echo $product_id; ?>, <?php echo $item['quantity'] - 1; ?>)"><span>-</span></button>
                                            <input class="form-control" type="text" value="<?php echo $item['quantity']; ?>" onchange="updateQuantity(<?php echo $product_id; ?>, this.value)">
                                            <button class="plus" onclick="updateQuantity(<?php echo $product_id; ?>, <?php echo $item['quantity'] + 1; ?>)"><span>+</span></button>
                                        </div>
                                    </td>
                                    <td><strong>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></strong></td>
                                    <td>
                                        <div class="ps-remove" onclick="removeItem(<?php echo $product_id; ?>)"></div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="ps-cart__actions">
                        <div class="ps-cart__promotion">
                            <div class="form-group">
                                <div class="ps-form--icon">
                                    <i class="fa fa-angle-right"></i>
                                    <input class="form-control" type="text" placeholder="Promo Code">
                                </div>
                            </div>
                            <div class="form-group">
                                <a href="product-listing.php" class="ps-btn ps-btn--gray">Continue Shopping</a>
                            </div>
                        </div>
                        <div class="ps-cart__total">
                            <h3>Total Price: <span>$<?php echo number_format($cart_total, 2); ?></span></h3>
                            <a class="ps-btn" href="checkout.php">Process to checkout</a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Site Features -->
    <div class="ps-site-features">
        <div class="ps-container">
            <div class="row">
                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12 ">
                    <div class="ps-block--iconbox">
                        <i class="ba-delivery-truck-2"></i>
                        <h4>Free Shipping <span> On Order Over$199</h4>
                        <p>Want to track a package? Find tracking information and order details from Your Orders.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12 ">
                    <div class="ps-block--iconbox">
                        <i class="ba-biscuit-1"></i>
                        <h4>Master Chef<span> WITH PASSION</h4>
                        <p>Shop zillions of finds, with new arrivals added daily.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12 ">
                    <div class="ps-block--iconbox">
                        <i class="ba-flour"></i>
                        <h4>Natural Materials<span> protect your family</h4>
                        <p>We always ensure the safety of all products of store</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12 ">
                    <div class="ps-block--iconbox">
                        <i class="ba-cake-3"></i>
                        <h4>Attractive Flavor <span>ALWAYS LISTEN</span></h4>
                        <p>We offer a 24/7 customer hotline so you're never alone if you have a question.</p>
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
                        <div class="ps-site-info">
                            <a class="ps-logo" href="index.php"><img src="images/logo-dark.png" alt=""></a>
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
                <p>©  Copyright by <strong>Bready Store</strong>. Design by<a href="#"> Alena Studio.</a></p>
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
    
    <script>
    function updateQuantity(productId, quantity) {
        if (quantity > 0) {
            window.location.href = 'cart.php?action=update&id=' + productId + '&quantity=' + quantity;
        }
    }
    
    function removeItem(productId) {
        if (confirm('Are you sure you want to remove this item?')) {
            window.location.href = 'cart.php?action=remove&id=' + productId;
        }
    }
    </script>
</body>
</html> 