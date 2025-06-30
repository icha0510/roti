<?php
require_once 'includes/functions.php';

echo "<h1>Test Blog Detail</h1>";

// Test 1: Cek fungsi getAllPosts
echo "<h2>1. Test getAllPosts()</h2>";
try {
    $posts = getAllPosts(5);
    echo "✅ getAllPosts() berhasil, jumlah: " . count($posts) . "<br>";
    
    if (count($posts) > 0) {
        echo "<h3>Daftar Posts:</h3>";
        foreach ($posts as $post) {
            echo "- ID: " . $post['id'] . " | Title: " . $post['title'] . " | Author: " . $post['author'] . "<br>";
        }
        
        // Test 2: Cek fungsi getPostById
        echo "<h2>2. Test getPostById()</h2>";
        $first_post = $posts[0];
        $test_post = getPostById($first_post['id']);
        
        if ($test_post) {
            echo "✅ getPostById() berhasil untuk ID: " . $first_post['id'] . "<br>";
            echo "Title: " . $test_post['title'] . "<br>";
            echo "Author: " . $test_post['author'] . "<br>";
            echo "Content length: " . strlen($test_post['content']) . " characters<br>";
            echo "Image data: " . (!empty($test_post['image_data']) ? 'Ada' : 'Tidak ada') . "<br>";
            
            // Test 3: Cek link ke blog detail
            echo "<h2>3. Test Link Blog Detail</h2>";
            echo "✅ Link ke blog detail: <a href='blog-detail.php?id=" . $test_post['id'] . "' target='_blank'>Lihat Detail</a><br>";
            
        } else {
            echo "❌ getPostById() gagal untuk ID: " . $first_post['id'] . "<br>";
        }
    } else {
        echo "⚠️ Tidak ada posts di database<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

// Test 4: Cek fungsi displayImage
echo "<h2>4. Test displayImage()</h2>";
if (function_exists('displayImage')) {
    echo "✅ Fungsi displayImage() tersedia<br>";
} else {
    echo "❌ Fungsi displayImage() tidak tersedia<br>";
}

echo "<h2>5. Link Penting</h2>";
echo "<a href='blog-grid.php'>Blog Grid</a><br>";
echo "<a href='index.php'>Homepage</a><br>";
echo "<a href='admin/posts.php'>Admin Posts</a><br>";
?> 