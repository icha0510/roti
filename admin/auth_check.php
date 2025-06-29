<?php
session_start();

// Cek apakah admin sudah login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// Fungsi untuk mendapatkan nama admin yang sedang login
function getCurrentAdminName() {
    return $_SESSION['admin_name'] ?? 'Admin';
}

// Fungsi untuk mendapatkan ID admin yang sedang login
function getCurrentAdminId() {
    return $_SESSION['admin_id'] ?? null;
}

// Fungsi untuk mendapatkan email admin yang sedang login
function getCurrentAdminEmail() {
    return $_SESSION['admin_email'] ?? '';
}
?> 