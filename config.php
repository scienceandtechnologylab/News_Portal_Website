<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'bsro');

// Site configuration
define('SITE_NAME', 'bsro');
define('SITE_URL', 'http://localhost/News_Portal/');
define('ADMIN_URL', SITE_URL . 'admin/');

// Start session
session_start();

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>