<?php
session_start();
require_once 'includes/functions.php';

echo "<h1>Test Dashboard Functions</h1>";

// Test koneksi database
echo "<h2>Test 1: Database Connection</h2>";
try {
    $pdo = new PDO("mysql:host=localhost;dbname=bready", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Koneksi database berhasil<br>";
} catch (Exception $e) {
    echo "❌ Error koneksi database: " . $e->getMessage() . "<br>";
}

// Test fungsi getAllProducts
echo "<h2>Test 2: getAllProducts()</h2>";
if (function_exists('getAllProducts')) {
    try {
        $products = getAllProducts();
        echo "✅ getAllProducts() berhasil, jumlah: " . count($products) . "<br>";
    } catch (Exception $e) {
        echo "❌ Error getAllProducts(): " . $e->getMessage() . "<br>";
    }
} else {
    echo "❌ Fungsi getAllProducts() tidak ditemukan<br>";
}

// Test fungsi getAllCategories
echo "<h2>Test 3: getAllCategories()</h2>";
if (function_exists('getAllCategories')) {
    try {
        $categories = getAllCategories();
        echo "✅ getAllCategories() berhasil, jumlah: " . count($categories) . "<br>";
    } catch (Exception $e) {
        echo "❌ Error getAllCategories(): " . $e->getMessage() . "<br>";
    }
} else {
    echo "❌ Fungsi getAllCategories() tidak ditemukan<br>";
}

// Test fungsi getAllTestimonials
echo "<h2>Test 4: getAllTestimonials()</h2>";
if (function_exists('getAllTestimonials')) {
    try {
        $testimonials = getAllTestimonials();
        echo "✅ getAllTestimonials() berhasil, jumlah: " . count($testimonials) . "<br>";
    } catch (Exception $e) {
        echo "❌ Error getAllTestimonials(): " . $e->getMessage() . "<br>";
    }
} else {
    echo "❌ Fungsi getAllTestimonials() tidak ditemukan<br>";
}

// Test fungsi getAllPosts
echo "<h2>Test 5: getAllPosts()</h2>";
if (function_exists('getAllPosts')) {
    try {
        $posts = getAllPosts();
        echo "✅ getAllPosts() berhasil, jumlah: " . count($posts) . "<br>";
    } catch (Exception $e) {
        echo "❌ Error getAllPosts(): " . $e->getMessage() . "<br>";
    }
} else {
    echo "❌ Fungsi getAllPosts() tidak ditemukan<br>";
}

echo "<h2>Link Penting</h2>";
echo "<a href='index.php'>Dashboard</a><br>";
echo "<a href='login.php'>Login</a><br>";
?> 