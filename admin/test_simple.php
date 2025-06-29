<?php
session_start();
require_once 'includes/functions.php';

echo "<h1>Test Dashboard Sederhana</h1>";

// Test koneksi database
echo "<h2>Test 1: Koneksi Database</h2>";
try {
    global $pdo;
    if ($pdo) {
        echo "✅ Koneksi database berhasil<br>";
    } else {
        echo "❌ Koneksi database gagal<br>";
    }
} catch (Exception $e) {
    echo "❌ Error koneksi database: " . $e->getMessage() . "<br>";
}

// Test fungsi getAllProducts
echo "<h2>Test 2: getAllProducts()</h2>";
try {
    $products = getAllProducts();
    echo "✅ getAllProducts() berhasil, jumlah: " . count($products) . "<br>";
} catch (Exception $e) {
    echo "❌ Error getAllProducts(): " . $e->getMessage() . "<br>";
}

// Test fungsi getAllCategories
echo "<h2>Test 3: getAllCategories()</h2>";
try {
    $categories = getAllCategories();
    echo "✅ getAllCategories() berhasil, jumlah: " . count($categories) . "<br>";
} catch (Exception $e) {
    echo "❌ Error getAllCategories(): " . $e->getMessage() . "<br>";
}

// Test fungsi getAllTestimonials
echo "<h2>Test 4: getAllTestimonials()</h2>";
try {
    $testimonials = getAllTestimonials();
    echo "✅ getAllTestimonials() berhasil, jumlah: " . count($testimonials) . "<br>";
} catch (Exception $e) {
    echo "❌ Error getAllTestimonials(): " . $e->getMessage() . "<br>";
}

// Test fungsi getAllPosts
echo "<h2>Test 5: getAllPosts()</h2>";
try {
    $posts = getAllPosts();
    echo "✅ getAllPosts() berhasil, jumlah: " . count($posts) . "<br>";
} catch (Exception $e) {
    echo "❌ Error getAllPosts(): " . $e->getMessage() . "<br>";
}

echo "<h2>Link Penting</h2>";
echo "<a href='index.php'>Dashboard</a><br>";
echo "<a href='login.php'>Login</a><br>";
?> 