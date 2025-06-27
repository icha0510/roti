<?php
require_once 'includes/functions.php';

// Ambil data dari database
$featuredProducts = getFeaturedProducts(1);
$banners = getAllBanners();

echo "<h2>Test Gambar di Index</h2>";

// Test Banner
echo "<h3>Banner:</h3>";
if (!empty($banners)) {
    $banner = $banners[0];
    echo "Title: " . $banner['title'] . "<br>";
    echo "Image Data: " . (!empty($banner['image_data']) ? 'Ada' : 'Tidak ada') . "<br>";
    echo "Image Mime: " . $banner['image_mime'] . "<br>";
    
    if (!empty($banner['image_data'])) {
        echo "<h4>Gambar Banner:</h4>";
        echo displayImage($banner['image_data'], $banner['image_mime'], 'img-fluid', $banner['title']);
    }
}

// Test Product
echo "<h3>Product:</h3>";
if (!empty($featuredProducts)) {
    $product = $featuredProducts[0];
    echo "Name: " . $product['name'] . "<br>";
    echo "Image Data: " . (!empty($product['image_data']) ? 'Ada' : 'Tidak ada') . "<br>";
    echo "Image Mime: " . $product['image_mime'] . "<br>";
    
    if (!empty($product['image_data'])) {
        echo "<h4>Gambar Product:</h4>";
        echo displayImage($product['image_data'], $product['image_mime'], 'img-fluid', $product['name']);
    }
}
?> 