<?php
// config/config.php

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'lms');

// Application configuration
define('APP_NAME', 'Simple LMS');

// Get the correct base URL dynamically
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];
$script_dir = dirname($_SERVER['SCRIPT_NAME']);
// Remove trailing slash if exists
$script_dir = rtrim($script_dir, '/');
// Get the project root directory
$project_root = str_replace('/admin', '', $script_dir);
$project_root = str_replace('/includes', '', $project_root);
$project_root = str_replace('/config', '', $project_root);

define('APP_URL', $protocol . $host . $project_root . '/');

// Upload paths - Using absolute paths
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('UPLOAD_URL', APP_URL . 'uploads/');

// File upload limits
define('MAX_FILE_SIZE', 2 * 1024 * 1024); // 2MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png']);

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);

// Error reporting (enable for debugging)
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>