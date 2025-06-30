<?php
require_once 'includes/functions.php';

echo "<h1>Test Newsletter System</h1>";

// Test 1: Cek fungsi subscribeNewsletter
echo "<h2>1. Test subscribeNewsletter()</h2>";
$test_email = "test" . time() . "@example.com";
$result = subscribeNewsletter($test_email);

if ($result['success']) {
    echo "✅ subscribeNewsletter() berhasil: " . $result['message'] . "<br>";
} else {
    echo "❌ subscribeNewsletter() gagal: " . $result['message'] . "<br>";
}

// Test 2: Cek fungsi getAllNewsletterSubscribers
echo "<h2>2. Test getAllNewsletterSubscribers()</h2>";
try {
    $subscribers = getAllNewsletterSubscribers();
    echo "✅ getAllNewsletterSubscribers() berhasil, jumlah: " . count($subscribers) . "<br>";
    
    if (count($subscribers) > 0) {
        echo "<h3>Daftar Subscribers:</h3>";
        foreach ($subscribers as $subscriber) {
            echo "- ID: " . $subscriber['id'] . " | Email: " . $subscriber['email'] . " | Status: " . $subscriber['status'] . "<br>";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

// Test 3: Cek fungsi unsubscribeNewsletter
echo "<h2>3. Test unsubscribeNewsletter()</h2>";
$unsubscribe_result = unsubscribeNewsletter($test_email);
if ($unsubscribe_result['success']) {
    echo "✅ unsubscribeNewsletter() berhasil: " . $unsubscribe_result['message'] . "<br>";
} else {
    echo "❌ unsubscribeNewsletter() gagal: " . $unsubscribe_result['message'] . "<br>";
}

// Test 4: Cek validasi email
echo "<h2>4. Test Email Validation</h2>";
$invalid_emails = ['invalid-email', 'test@', '@example.com', ''];
foreach ($invalid_emails as $invalid_email) {
    $result = subscribeNewsletter($invalid_email);
    echo "Email '$invalid_email': " . ($result['success'] ? '❌ Diterima (seharusnya ditolak)' : '✅ Ditolak dengan benar') . "<br>";
}

echo "<h2>5. Link Penting</h2>";
echo "<a href='index.php'>Homepage</a><br>";
echo "<a href='admin/newsletter.php'>Admin Newsletter</a><br>";
echo "<a href='newsletter_handler.php'>Newsletter Handler</a><br>";
?> 