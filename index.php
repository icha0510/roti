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
$testimonials = getAllTestimonials(4);
$posts = getAllPosts(3);
$awards = getAllAwards();
$categories = getAllCategories();
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
    <title>Bready</title>
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
            <li class="menu-item-has-children current-menu-item">
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
              <a class="ps-btn" href="<?php echo $banner['link']; ?>">Order Now</a>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <!-- award-->
    <div class="ps-awards">
      <div class="ps-container">
        <div class="ps-section__header">
          <h3 class="ps-section__title">Delicieux</h3>
          <p>WELCOME TO THE STORE</p><span><img src="images/icons/floral.png" alt=""></span>
        </div>
        <div class="ps-section__content">
          <div class="row">
            <?php foreach ($awards as $award): ?>
            <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12 ">
              <div class="ps-block--award">
                <?php if (!empty($award['image_data'])): ?>
                  <?php echo displayImage($award['image_data'], $award['image_mime'], '', $award['title']); ?>
                <?php else: ?>
                  <img src="<?php echo $award['icon']; ?>" alt="">
                <?php endif; ?>
                <h4><?php echo $award['title']; ?> <span><?php echo $award['subtitle']; ?></span></h4>
                <p><?php echo $award['description']; ?></p>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
      <div class="ps-block--signature">
        <div class="ps-block__thumbnail"><img src="images/signature.png" alt=""></div>
        <div class="ps-block__content">
          <p>"It seems that every country that can get its hands on butter has its opinion of what butter cream frosting should be. Some are made with eggs and butter."</p><small>Sunshine -  CEO Bakery</small><img src="images/signature-2.png" alt="">
        </div>
      </div>
    </div>
    <!-- Home 1 products-->
    <div class="ps-home-product bg--cover" data-background="images/bg/home-product.jpg">
      <div class="ps-container">
        <div class="ps-section__header">
          <h3 class="ps-section__title">Deal of the day</h3>
          <p>breads every day</p><span><img src="images/icons/floral.png" alt=""></span>
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
                    <li><a href="#" data-tooltip="Quick View"><i class="ba-magnifying-glass"></i></a></li>
                    <li><a href="#" data-tooltip="Favorite"><i class="ba-heart"></i></a></li>
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
    <!-- Testimonials-->
    <div class="ps-testimonials bg--parallax" data-background="images/bg/testimonials.jpg">
      <div class="ps-container">
        <div class="ps-carousel--testimonial owl-slider" data-owl-auto="true" data-owl-loop="true" data-owl-speed="5000" data-owl-gap="0" data-owl-nav="false" data-owl-dots="true" data-owl-item="1" data-owl-item-xs="1" data-owl-item-sm="1" data-owl-item-md="1" data-owl-item-lg="1" data-owl-duration="1000" data-owl-mousedrag="off" data-owl-animate-in="fadeIn" data-owl-animate-out="fadeOut">
          <?php foreach ($testimonials as $testimonial): ?>
          <div class="ps-block--tesimonial">
            <div class="ps-block__user">
              <?php if (!empty($testimonial['image_data'])): ?>
                <?php echo displayImage($testimonial['image_data'], $testimonial['image_mime'], '', $testimonial['name']); ?>
              <?php else: ?>
                <img src="<?php echo $testimonial['image']; ?>" alt="<?php echo $testimonial['name']; ?>">
              <?php endif; ?>
            </div>
            <div class="ps-block__content">
              <?php echo displayRating($testimonial['rating']); ?>
              <p>"<?php echo $testimonial['content']; ?>"</p>
            </div>
            <div class="ps-block__footer">
              <p><strong><?php echo $testimonial['name']; ?></strong>  - <?php echo $testimonial['position']; ?> <?php echo $testimonial['company']; ?></p>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <!-- home-blog-->
    <div class="ps-home-blog">
      <div class="ps-container">
        <div class="ps-section__header">
          <h3 class="ps-section__title">Our history</h3>
          <p>Live with passion</p><span><img src="images/icons/floral.png" alt=""></span>
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
                  <span class="ps-post__byline">By<a href="#"> <?php echo $post['author']; ?></a></span>
                  <p><?php echo $post['excerpt']; ?></p>
                  <a class="ps-post__morelink" href="blog-detail.php?id=<?php echo $post['id']; ?>">Read more</a>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>
    <!--delivery form-->
    <div class="ps-delivery-form bg--parallax" data-background="images/bg/delivery-form.jpg">
      <div class="ps-block--delivery">
        <div class="row">
          <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 ">
            <div class="ps-block__content">
              <div class="ps-block--contact">
                <h4>OFFICE AT AMERICA</h4>
                <h5>BASEMENT COMPANY, NEW YORK</h5>
                <p><i class="fa fa-envelope"></i><a href="mailto:helo@bredy.com">hello@bready.com</a></p>
                <p><i class="fa fa-phone-square"></i> +1 650-253-0000</p>
              </div>
              <div class="ps-block--contact">
                <h4>OFFICE AT PARIS</h4>
                <h5>189/32 BASEMENT COMPANY, PARIS, FRANCE</h5>
                <p><i class="fa fa-envelope"></i><a href="mailto:helo@bredy.com">hello@bready.com</a></p>
                <p><i class="fa fa-phone-square"></i> +1 650-253-0000</p>
              </div>
              <div class="ps-block--contact">
                <h4>OFFICE AT CANADA</h4>
                <h5>189/32 BASEMENT COMPANY, PARIS, FRANCE</h5>
                <p><i class="fa fa-envelope"></i><a href="mailto:helo@bredy.com">hello@bready.com</a></p>
                <p><i class="fa fa-phone-square"></i> +1 650-253-0000</p>
              </div>
            </div>
          </div>
          <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 ">
            <form class="ps-form--delivery" action="do_action" method="post">
              <h3>Delivery Now</h3>
              <p>Delivery free wafer fruitcake. Pastry toffee wafer gingerbread liquorice. Apple pie gingerbread caramels toffee tart bonbon.</p>
              <div class="form-group">
                <label>Name <sup>*</sup></label>
                <input class="form-control" type="text" placeholder="">
              </div>
              <div class="form-group">
                <label>Email <sup>*</sup></label>
                <input class="form-control" type="email" placeholder="">
              </div>
              <div class="form-group">
                <label>Phone Number <sup>*</sup></label>
                <input class="form-control" type="text" placeholder="">
              </div>
              <div class="form-group submit">
                <button class="ps-btn">Order Now</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
    <footer class="ps-footer">
      <div class="ps-footer__content">
        <div class="ps-container">
          <div class="row">
            <div class="col-lg-4 col-md-12 col-sm-12 col-xs-12 ">
              <div class="ps-site-info"><a class="ps-logo" href="index.php"><img src="images/logo-dark.png" alt=""></a>
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
                <li><a href="#"><i class="ba-shopping"></i></a></li>
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
    <div class="ps-popup" id="subscribe" data-time="10000">
      <div class="ps-popup__content"><a class="ps-popup__close" href="#"><i class="fa fa-remove"></i></a>
        <form class="ps-form--subscribe-popup bg--cover" action="do_action" method="post" data-background="images/bg/subscribe.jpg">
          <h3>subscribe email</h3>
          <p>Follow us & get<strong> 20% OFF</strong>coupon for first purchase !!!!!</p>
          <div class="form-group">
            <input class="form-control" type="text" placeholder="Enter your email...">
            <button class="ps-btn ps-btn--yellow">Subscribe</button>
          </div>
        </form>
      </div>
    </div>
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