<?php
session_start();
require_once 'includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=profile.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user = getUserById($user_id);

$error = '';
$success = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($name)) {
        $error = 'Nama tidak boleh kosong!';
    } else {
        // Update profile
        $database = new Database();
        $db = $database->getConnection();
        
        $sql = "UPDATE users SET name = :name, phone = :phone, address = :address WHERE id = :user_id";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':user_id', $user_id);
        
        if ($stmt->execute()) {
            // Update session
            $_SESSION['user_name'] = $name;
            $user['name'] = $name;
            $user['phone'] = $phone;
            $user['address'] = $address;
            
            // Handle password change if provided
            if (!empty($current_password) && !empty($new_password)) {
                if ($new_password !== $confirm_password) {
                    $error = 'Password baru dan konfirmasi password tidak cocok!';
                } elseif (strlen($new_password) < 6) {
                    $error = 'Password minimal 6 karakter!';
                } else {
                    // Verify current password
                    $current_user = authenticateUser($user['email'], $current_password);
                    if ($current_user) {
                        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                        $sql = "UPDATE users SET password = :password WHERE id = :user_id";
                        $stmt = $db->prepare($sql);
                        $stmt->bindParam(':password', $hashed_password);
                        $stmt->bindParam(':user_id', $user_id);
                        
                        if ($stmt->execute()) {
                            $success = 'Profil dan password berhasil diperbarui!';
                        } else {
                            $error = 'Gagal memperbarui password!';
                        }
                    } else {
                        $error = 'Password saat ini salah!';
                    }
                }
            } else {
                $success = 'Profil berhasil diperbarui!';
            }
        } else {
            $error = 'Gagal memperbarui profil!';
        }
    }
}

// Calculate cart totals for header
$cart_total = 0;
$cart_count = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_total += $item['price'] * $item['quantity'];
        $cart_count += $item['quantity'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Bready</title>
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
        .profile-container {
            background: #f8f9fa;
            min-height: 100vh;
            padding: 40px 0;
        }
        .profile-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .profile-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .profile-header h2 {
            margin: 0;
            font-family: 'Kaushan Script', cursive;
        }
        .profile-body {
            padding: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 12px 15px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-update {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 8px;
            padding: 12px 30px;
            font-size: 16px;
            font-weight: 600;
            color: white;
            transition: transform 0.3s ease;
        }
        .btn-update:hover {
            transform: translateY(-2px);
            color: white;
        }
        .alert {
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .section-title {
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e9ecef;
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
    <!-- Header-->
    <header class="header header--3" data-sticky="false">
      <nav class="navigation">
        <div class="ps-container">
          <a class="ps-logo" href="index.php"><img src="images/logo-light.png" alt=""></a>
          <div class="menu-toggle"><span></span></div>
          <div class="header__actions">
            <a class="ps-search-btn" href="#"><i class="ba-magnifying-glass"></i></a>
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
          </div>
        </div>
      </nav>
    </header>

    <!-- Main Content -->
    <div class="profile-container">
        <div class="ps-container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="profile-card">
                        <div class="profile-header">
                            <h2>My Profile</h2>
                            <p>Manage your account information</p>
                        </div>
                        <div class="profile-body">
                            <?php if ($error): ?>
                                <div class="alert alert-danger">
                                    <?php echo htmlspecialchars($error); ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($success): ?>
                                <div class="alert alert-success">
                                    <?php echo htmlspecialchars($success); ?>
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST" action="">
                                <h4 class="section-title">Personal Information</h4>
                                
                                <div class="form-group">
                                    <label for="name">Full Name</label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="<?php echo htmlspecialchars($user['name']); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="email">Email Address</label>
                                    <input type="email" class="form-control" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                                    <small class="text-muted">Email cannot be changed</small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="phone">Phone Number</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" 
                                           value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="address">Address</label>
                                    <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                                </div>
                                
                                <h4 class="section-title">Change Password</h4>
                                <p class="text-muted">Leave blank if you don't want to change your password</p>
                                
                                <div class="form-group">
                                    <label for="current_password">Current Password</label>
                                    <input type="password" class="form-control" id="current_password" name="current_password">
                                </div>
                                
                                <div class="form-group">
                                    <label for="new_password">New Password</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password">
                                </div>
                                
                                <div class="form-group">
                                    <label for="confirm_password">Confirm New Password</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                                </div>
                                
                                <div class="form-group">
                                    <button type="submit" class="btn btn-update">Update Profile</button>
                                    <a href="logo-orders.php" class="btn btn-secondary">View My Orders</a>
                                </div>
                            </form>
                        </div>
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
</body>
</html> 