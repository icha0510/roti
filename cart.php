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
    <link rel="icon" href="images/logo-rotio.png" type="image/x-icon">
    <title>Shopping Cart - Roti'O</title>
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
                                <span>Total:<i>Rp<?php echo number_format($item['price'] * $item['quantity'], 3); ?></i></span>
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
                      <p>Item Total:<span>Rp <?php echo number_format($cart_total, 3); ?></span></p>
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
                            <h3>Total Price: <span>Rp <?php echo number_format($cart_total, 3); ?></span></h3>
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