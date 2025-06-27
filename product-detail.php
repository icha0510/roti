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

// Ambil ID produk dari URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Jika tidak ada ID produk, tampilkan halaman error yang user-friendly
if (!$product_id) {
    $page_title = "Product Not Found";
    include 'header.php';
    ?>
    <!-- Breadcrumb -->
    <div class="ps-breadcrumb">
        <div class="ps-container">
            <ul class="breadcrumb">
                <li><a href="index.php">Home</a></li>
                <li><a href="product-listing.php">Products</a></li>
                <li>Product Not Found</li>
            </ul>
        </div>
    </div>

    <!-- Error Section -->
    <div class="ps-section--error">
        <div class="ps-container">
            <div class="row">
                <div class="col-lg-8 col-md-10 col-sm-12 col-xs-12 center-block">
                    <div class="ps-error">
                        <h1>404</h1>
                        <h3>Product Not Found</h3>
                        <p>Sorry, the product you're looking for doesn't exist or has been removed.</p>
                        <p>Please browse our <a href="product-listing.php">product catalog</a> to find what you're looking for.</p>
                        <a class="ps-btn" href="product-listing.php">Browse Products</a>
                        <a class="ps-btn ps-btn--outline" href="index.php">Back to Home</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="ps-footer">
        <!-- Footer content -->
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
    </body>
    </html>
    <?php
    exit;
}

// Ambil detail produk dari database
function getProductById($id) {
    $database = new Database();
    $db = $database->getConnection();
    
    $sql = "SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.id = :id";
    
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

$product = getProductById($product_id);

if (!$product) {
    $page_title = "Product Not Found";
    include 'header.php';
    ?>
    <!-- Breadcrumb -->
    <div class="ps-breadcrumb">
        <div class="ps-container">
            <ul class="breadcrumb">
                <li><a href="index.php">Home</a></li>
                <li><a href="product-listing.php">Products</a></li>
                <li>Product Not Found</li>
            </ul>
        </div>
    </div>

    <!-- Error Section -->
    <div class="ps-section--error">
        <div class="ps-container">
            <div class="row">
                <div class="col-lg-8 col-md-10 col-sm-12 col-xs-12 center-block">
                    <div class="ps-error">
                        <h1>404</h1>
                        <h3>Product Not Found</h3>
                        <p>Sorry, the product you're looking for doesn't exist or has been removed.</p>
                        <p>Please browse our <a href="product-listing.php">product catalog</a> to find what you're looking for.</p>
                        <a class="ps-btn" href="product-listing.php">Browse Products</a>
                        <a class="ps-btn ps-btn--outline" href="index.php">Back to Home</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="ps-footer">
        <!-- Footer content -->
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
    </body>
    </html>
    <?php
    exit;
}

// Ambil produk terkait
function getRelatedProducts($category_id, $current_product_id, $limit = 4) {
    $database = new Database();
    $db = $database->getConnection();
    
    $sql = "SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.category_id = :category_id AND p.id != :current_id AND p.stock > 0 
            ORDER BY p.created_at DESC 
            LIMIT " . $limit;
    
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':category_id', $category_id);
    $stmt->bindParam(':current_id', $current_product_id);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$related_products = getRelatedProducts($product['category_id'], $product_id);
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
    <title><?php echo $product['name']; ?> - Bready</title>
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
            <?php if (isset($_SESSION['user_id'])): ?>
              <div class="ps-dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                  <i class="ba-profile"></i>
                  <span><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                </a>
                <ul class="dropdown-menu">
                  <li><a href="logo-orders.php">My Orders</a></li>
                  <li><a href="profile.php">Profile</a></li>
                  <li><hr class="dropdown-divider"></li>
                  <li><a href="logout.php">Logout</a></li>
                </ul>
              </div>
            <?php else: ?>
              <a href="login.php"><i class="ba-profile"></i></a>
            <?php endif; ?>
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
            <li class="menu-item-has-children current-menu-item">
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

    <!-- Breadcrumb -->
    <div class="ps-breadcrumb">
        <div class="ps-container">
            <ul class="breadcrumb">
                <li><a href="index.php">Home</a></li>
                <li><a href="product-listing.php">Products</a></li>
                <li><a href="product-listing.php?category=<?php echo $product['category_id']; ?>"><?php echo $product['category_name']; ?></a></li>
                <li><?php echo $product['name']; ?></li>
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
                            <?php else: ?>
                                <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>" class="img-fluid">
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Product Info -->
                <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                    <div class="ps-product__info">
                        <h1 class="ps-product__name"><?php echo $product['name']; ?></h1>
                        
                        <div class="ps-product__meta">
                            <p>Category: <a href="product-listing.php?category=<?php echo $product['category_id']; ?>"><?php echo $product['category_name']; ?></a></p>
                        </div>

                        <div class="ps-product__rating">
                            <?php echo displayRating($product['rating']); ?>
                            <span class="ps-product__review">(<?php echo $product['rating']; ?> stars)</span>
                        </div>

                        <div class="ps-product__price">
                            <?php echo displayProductPrice($product); ?>
                        </div>

                        <div class="ps-product__description">
                            <h4>Description</h4>
                            <p><?php echo $product['description']; ?></p>
                        </div>

                        <div class="ps-product__stock">
                            <p>Stock: <span class="<?php echo $product['stock'] > 0 ? 'in-stock' : 'out-of-stock'; ?>"><?php echo $product['stock'] > 0 ? $product['stock'] . ' available' : 'Out of stock'; ?></span></p>
                        </div>

                        <?php if ($product['stock'] > 0): ?>
                        <div class="ps-product__actions">
                            <div class="ps-product__quantity">
                                <label>Quantity:</label>
                                <input type="number" min="1" max="<?php echo $product['stock']; ?>" value="1" class="form-control" id="product-quantity">
                            </div>
                            <a href="#" class="ps-btn ps-btn--fullwidth" id="add-to-cart-btn" data-product-id="<?php echo $product['id']; ?>">Add to Cart</a>
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
                <h3 class="ps-section__title">Related Products</h3>
                <p>You might also like</p>
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
                                <?php else: ?>
                                    <img src="<?php echo $related_product['image']; ?>" alt="<?php echo $related_product['name']; ?>">
                                <?php endif; ?>
                                <a class="ps-product__overlay" href="product-detail.php?id=<?php echo $related_product['id']; ?>"></a>
                                <ul class="ps-product__actions">
                                    <li><a href="#" data-tooltip="Quick View"><i class="ba-magnifying-glass"></i></a></li>
                                    <li><a href="#" data-tooltip="Favorite"><i class="ba-heart"></i></a></li>
                                    <li><a href="cart_actions.php?action=add&id=<?php echo $related_product['id']; ?>" data-tooltip="Add to Cart"><i class="ba-shopping"></i></a></li>
                                </ul>
                            </div>
                            <div class="ps-product__content">
                                <a class="ps-product__title" href="product-detail.php?id=<?php echo $related_product['id']; ?>"><?php echo $related_product['name']; ?></a>
                                <p><a href="product-listing.php?category=<?php echo $related_product['category_id']; ?>"><?php echo $related_product['category_name']; ?></a></p>
                                <?php echo displayRating($related_product['rating']); ?>
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
        <!-- Footer content -->
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
    
    <script>
    $(document).ready(function() {
        // Add to Cart functionality
        $('#add-to-cart-btn').on('click', function(e) {
            e.preventDefault();
            var productId = $(this).data('product-id');
            var quantity = $('#product-quantity').val();
            
            // Redirect to cart_actions.php with parameters
            window.location.href = 'cart_actions.php?action=add&id=' + productId + '&quantity=' + quantity;
        });
    });
    </script>
</body>
</html> 