<?php
/**
 * Script to update all user pages with the new header structure
 * This script will add the CSS and JS links and replace the header structure
 */

// List of user pages to update (excluding admin and test files)
$userPages = [
    'product-detail.php',
    'order-form.php',
    'checkout.php',
    'login.php',
    'signup.php',
    'profile.php',
    'logo-orders.php',
    'store.php',
    'blog-grid.php',
    'blog-detail.php',
    'order-success.php',
    'order-timeout.php',
    'qr_payment_page.php'
];

// New header structure
$newHeader = '<header class="header" data-sticky="false">
  <div class="ps-container">
    <div class="header-wrapper">
      <!-- Logo Left -->
      <div class="header-logo">
        <a class="ps-logo" href="index.php">
          <img src="images/logo-rotio.png" alt="Roti\'O">
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
          <?php if (isset($_SESSION[\'user_id\'])): ?>
            <div class="ps-dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                <i class="ba-profile"></i>
                <span class="profile-name"><?php echo htmlspecialchars($_SESSION[\'user_name\']); ?></span>
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
                <?php if (!empty($_SESSION[\'cart\'])): ?>
                  <?php $count = 0; foreach ($_SESSION[\'cart\'] as $item): $count++; if ($count <= 6): ?>
                    <div class="ps-cart-item">
                      <a class="ps-cart-item__close" href="cart.php?action=remove&id=<?php echo $item[\'id\']; ?>"></a>
                      <div class="ps-cart-item__thumbnail">
                        <a href="product-detail.php?id=<?php echo $item[\'id\']; ?>"></a>
                        <?php if (!empty($item[\'image_data\'])): ?>
                          <?php echo displayImage($item[\'image_data\'], $item[\'image_mime\'], \'\', $item[\'name\']); ?>
                        <?php else: ?>
                          <img src="<?php echo $item[\'image\']; ?>" alt="<?php echo $item[\'name\']; ?>">
                        <?php endif; ?>
                      </div>
                      <div class="ps-cart-item__content">
                        <a class="ps-cart-item__title" href="product-detail.php?id=<?php echo $item[\'id\']; ?>">
                          <?php echo $item[\'name\']; ?>
                        </a>
                        <p>
                          <span>Jumlah:<i><?php echo $item[\'quantity\']; ?></i></span>
                          <span>Total:<i>Rp<?php echo number_format($item[\'price\'] * $item[\'quantity\'], 3); ?></i></span>
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
    <li class="menu-item-has-children">
      <a href="#">Produk</a>
      <span class="sub-toggle"><i class="fa fa-angle-down"></i></span>
      <ul class="sub-menu">
        <li><a href="product-listing.php">Daftar Produk</a></li>
        <li><a href="order-form.php"></a></li>
      </ul>
    </li>
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
</nav>';

// Old header pattern to match
$oldHeaderPattern = '/<!-- Header-->\s*<header class="header header--3" data-sticky="false">.*?<\/header>/s';

// CSS link to add
$cssLink = '    <link rel="stylesheet" href="css/header-nav.css">';

// JS script to add
$jsScript = '    <script src="js/header.js"></script>';

$updatedCount = 0;
$errors = [];

foreach ($userPages as $page) {
    if (!file_exists($page)) {
        $errors[] = "File not found: $page";
        continue;
    }
    
    $content = file_get_contents($page);
    $originalContent = $content;
    
    // Add CSS link
    if (strpos($content, 'css/header-nav.css') === false) {
        $content = preg_replace('/(<link rel="stylesheet" href="css\/style\.css">)/', '$1' . "\n" . $cssLink, $content);
    }
    
    // Replace header structure
    if (preg_match($oldHeaderPattern, $content)) {
        $content = preg_replace($oldHeaderPattern, $newHeader, $content);
    }
    
    // Add JS script before closing body tag
    if (strpos($content, 'js/header.js') === false) {
        $content = preg_replace('/(<script src="js\/main\.js"><\/script>)/', '$1' . "\n" . $jsScript, $content);
    }
    
    // Write back to file if content changed
    if ($content !== $originalContent) {
        if (file_put_contents($page, $content)) {
            $updatedCount++;
            echo "✓ Updated: $page\n";
        } else {
            $errors[] = "Failed to write: $page";
        }
    } else {
        echo "- No changes needed: $page\n";
    }
}

echo "\n=== Summary ===\n";
echo "Updated files: $updatedCount\n";
echo "Errors: " . count($errors) . "\n";

if (!empty($errors)) {
    echo "\nErrors:\n";
    foreach ($errors as $error) {
        echo "- $error\n";
    }
}

echo "\nHeader update process completed!\n";
?> 