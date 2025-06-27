<?php
session_start();
require_once 'includes/functions.php';

// Logout user
logoutUser();

// Redirect ke homepage
header('Location: index.php');
exit;
?> 