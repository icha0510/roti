<?php
require_once 'includes/functions.php';

echo "<h2>Test Produk Featured</h2>";

// Ambil produk featured
$featuredProducts = getFeaturedProducts(3);

echo "<h3>Jumlah produk featured: " . count($featuredProducts) . "</h3>";

foreach ($featuredProducts as $product) {
    echo "<h4>Produk: " . $product['name'] . "</h4>";
    echo "ID: " . $product['id'] . "<br>";
    echo "Is Featured: " . ($product['is_featured'] ? 'Ya' : 'Tidak') . "<br>";
    echo "Image Data: " . (!empty($product['image_data']) ? 'Ada (' . strlen($product['image_data']) . ' chars)' : 'Tidak ada') . "<br>";
    echo "Image Mime: " . $product['image_mime'] . "<br>";
    echo "Image Path: " . $product['image'] . "<br>";
    
    if (!empty($product['image_data'])) {
        echo "<h5>Gambar Produk:</h5>";
        echo displayImage($product['image_data'], $product['image_mime'], 'img-fluid', $product['name']);
    }
    
    echo "<hr>";
}
?> 