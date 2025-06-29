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

// Initialize variables for form data
$order_data = array();
$errors = array();

// Handle form submission
if ($_POST && isset($_POST['place_order'])) {
    $customer_name = trim($_POST['customer_name']);
    $customer_email = trim($_POST['customer_email']);
    $customer_phone = trim($_POST['customer_phone']);
    $customer_address = trim($_POST['customer_address']);
    $notes = trim($_POST['notes'] ?? '');
    $user_id = $_SESSION['user_id'];
    
    // Store form data for repopulation if there are errors
    $order_data = array(
        'customer_name' => $customer_name,
        'customer_email' => $customer_email,
        'customer_phone' => $customer_phone,
        'customer_address' => $customer_address,
        'notes' => $notes
    );
    
    // Basic validation
    if (empty($customer_name)) $errors[] = "Nama wajib diisi";
    if (empty($customer_email)) $errors[] = "Email wajib diisi";
    if (!filter_var($customer_email, FILTER_VALIDATE_EMAIL)) $errors[] = "Format email tidak valid";
    if (empty($customer_phone)) $errors[] = "Nomor telepon wajib diisi";
    if (empty($customer_address)) $errors[] = "Alamat wajib diisi";
    
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
                    order_number, user_id, customer_name, customer_email, customer_phone, customer_address,
                    notes, total_amount, status, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
            ");
            
            $result = $stmt->execute([
                $order_number,
                $user_id,
                $customer_name,
                $customer_email,
                $customer_phone,
                $customer_address,
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
                'total_amount' => $cart_total
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
    <link href="favicon.png" rel="icon">
    <meta name="author" content="">
    <meta name="keywords" content="">
    <meta name="description" content="">
    <title>Checkout - Bready</title>
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
    
    <!-- Header -->
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
    <div class="ps-hero bg--cover" data-background="images/hero/product.jpg">
        <div class="ps-hero__content">
            <h1>Checkout</h1>
            <div class="ps-breadcrumb">
                <ol class="breadcrumb">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="cart.php">Cart</a></li>
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
                                                <label>Alamat Lengkap <sup>*</sup></label>
                                                <input class="form-control" type="text" name="customer_address" placeholder="Masukkan alamat lengkap" 
                                                       value="<?php echo isset($order_data['customer_address']) ? htmlspecialchars($order_data['customer_address']) : htmlspecialchars($user['address'] ?? ''); ?>" required>
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
                                <div class="ps-form__orders">
                                    <h3>Your Order</h3>
                                    <div class="ps-block--checkout-orders">
                                        <div class="ps-block__content">
                                            <?php foreach ($_SESSION['cart'] as $item): ?>
                                                <div class="ps-product--cart">
                                                    <div class="ps-product__thumbnail">
                                                        <?php if (!empty($item['image_data'])): ?>
                                                            <?php echo displayImage($item['image_data'], $item['image_mime'], 'checkout-item-image', $item['name']); ?>
                                                        <?php else: ?>
                                                            <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>" class="checkout-item-image">
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="ps-product__content">
                                                        <a class="ps-product__title" href="product-detail.php?id=<?php echo $item['id']; ?>">
                                                            <?php echo htmlspecialchars($item['name']); ?>
                                                        </a>
                                                        <p><span>Quantity:<i><?php echo $item['quantity']; ?></i></span></p>
                                                    </div>
                                                    <div class="ps-product__price">
                                                        $<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <div class="ps-block__footer">
                                            <h3>Total <span>$<?php echo number_format($cart_total, 2); ?></span></h3>
                                        </div>
                                    </div>
                                    <div class="ps-form__footer">
                                        <button type="submit" name="place_order" class="ps-btn ps-btn--yellow ps-btn--fullwidth">
                                            Kirim Pesanan
                                        </button>
                                        <a class="ps-link" href="cart.php">Back to Cart</a>
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
                            <a class="ps-logo" href="index.html"><img src="images/logo-dark.png" alt=""></a>
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
</body>
</html> 